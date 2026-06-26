<?php

namespace App\Services\Sales;

use App\Models\Customer;
use App\Models\Inventory\Item as InventoryItem;
use App\Models\Inventory\Movement as InventoryMovement;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Models\SystemSetting;
use App\Services\ExpiryStockService;
use App\Services\FxTransactionRateService;
use App\Services\InventoryCostService;
use App\Services\InventoryStockService;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PosBillService
{
    public const REFERENCE_NO = 'POS-BILL';

    /**
     * Create an unpaid sales invoice from POS cart (restaurant bill mode).
     */
    public function createBillFromPos(array $data): SalesInvoice
    {
        $user = Auth::user();
        $branchId = session('branch_id') ?? ($user->branch_id ?? null);
        $locationId = session('location_id');
        $companyId = $user->company_id;

        if (!$branchId || !$locationId) {
            throw new \RuntimeException('Branch and location must be selected before creating a bill.');
        }

        $customer = $this->resolveCustomer(
            (int) ($data['customer_id'] ?? 0),
            $data['customer_name'] ?? null,
            $companyId,
            $branchId
        );

        $functionalCurrency = SystemSetting::getValue('functional_currency', $user->company->functional_currency ?? 'TZS');
        $invoiceCurrency = $data['currency'] ?? $functionalCurrency;
        $saleDate = $data['sale_date'];

        $fxTransactionRateService = app(FxTransactionRateService::class);
        $userProvidedRate = !empty($data['exchange_rate']) ? (float) $data['exchange_rate'] : null;
        $rateResult = $fxTransactionRateService->getTransactionRate(
            $invoiceCurrency,
            $functionalCurrency,
            $saleDate,
            $companyId,
            $userProvidedRate
        );
        $exchangeRate = $rateResult['rate'];

        $invoice = SalesInvoice::create([
            'customer_id' => $customer->id,
            'invoice_date' => $saleDate,
            'due_date' => $saleDate,
            'status' => 'sent',
            'payment_terms' => 'immediate',
            'payment_days' => 0,
            'reference_no' => self::REFERENCE_NO,
            'currency' => $invoiceCurrency,
            'exchange_rate' => $exchangeRate,
            'withholding_tax_rate' => 0,
            'withholding_tax_type' => 'percentage',
            'early_payment_discount_enabled' => false,
            'late_payment_fees_enabled' => false,
            'discount_amount' => 0,
            'notes' => $data['notes'] ?? 'Created from POS bill mode',
            'branch_id' => $branchId,
            'company_id' => $companyId,
            'created_by' => $user->id,
        ]);

        $stockService = new InventoryStockService();
        $costService = new InventoryCostService();

        foreach ($data['items'] as $itemData) {
            $inventoryItem = InventoryItem::findOrFail($itemData['inventory_item_id']);
            $quantity = (float) $itemData['quantity'];
            $unitPrice = (float) $itemData['unit_price'];
            $vatType = $itemData['vat_type'];
            $vatRate = (float) $itemData['vat_rate'];

            if ($inventoryItem->item_type !== 'service' && $inventoryItem->track_stock) {
                $availableStock = $stockService->getItemStockAtLocation($inventoryItem->id, $locationId);
                if ($availableStock < $quantity) {
                    throw new \RuntimeException("Insufficient stock for {$inventoryItem->name}. Available: {$availableStock}, Requested: {$quantity}");
                }
            }

            $subtotal = $quantity * $unitPrice;
            $vatAmount = 0;
            $lineTotal = 0;

            if ($vatType === 'no_vat') {
                $lineTotal = $subtotal;
            } elseif ($vatType === 'exclusive') {
                $vatAmount = $subtotal * ($vatRate / 100);
                $lineTotal = $subtotal + $vatAmount;
            } else {
                $vatAmount = $subtotal * ($vatRate / (100 + $vatRate));
                $lineTotal = $subtotal;
            }

            $invoiceItem = SalesInvoiceItem::create([
                'sales_invoice_id' => $invoice->id,
                'inventory_item_id' => $inventoryItem->id,
                'item_name' => $inventoryItem->name,
                'item_code' => $inventoryItem->code,
                'description' => $inventoryItem->description,
                'unit_of_measure' => $inventoryItem->unit_of_measure,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'price_tier' => InventoryItem::normalizedPriceTier($itemData['price_tier'] ?? null),
                'line_total' => $lineTotal,
                'vat_type' => $vatType,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'discount_type' => null,
                'discount_rate' => 0,
                'discount_amount' => 0,
            ]);

            if ($inventoryItem->track_stock && $inventoryItem->item_type === 'product') {
                $balanceBefore = $stockService->getItemStockAtLocation($inventoryItem->id, $locationId);
                $balanceAfter = $balanceBefore - $quantity;

                $costInfo = $costService->removeInventory(
                    $inventoryItem->id,
                    $quantity,
                    'sale',
                    'POS Bill: ' . $invoice->invoice_number,
                    $saleDate,
                    $branchId,
                    $locationId
                );

                InventoryMovement::create([
                    'item_id' => $inventoryItem->id,
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'location_id' => $locationId,
                    'movement_type' => 'sold',
                    'quantity' => $quantity,
                    'unit_price' => $costInfo['average_unit_cost'],
                    'unit_cost' => $costInfo['average_unit_cost'],
                    'total_cost' => $costInfo['total_cost'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference' => 'POS Bill: ' . $invoice->invoice_number,
                    'reference_type' => 'sales_invoice',
                    'reference_id' => $invoice->id,
                    'notes' => 'Stock sold via POS bill',
                    'movement_date' => $saleDate,
                ]);

                if ($inventoryItem->track_expiry) {
                    $expiryService = new ExpiryStockService();
                    $consumedLayers = $expiryService->consumeStock(
                        $inventoryItem->id,
                        $locationId,
                        $quantity,
                        'FEFO'
                    );

                    $batchNumbers = [];
                    $earliestExpiryDate = null;
                    foreach ($consumedLayers as $layer) {
                        if (!empty($layer['batch_number'])) {
                            $batchNumbers[] = $layer['batch_number'];
                        }
                        if (!empty($layer['expiry_date']) && ($earliestExpiryDate === null || $layer['expiry_date'] < $earliestExpiryDate)) {
                            $earliestExpiryDate = $layer['expiry_date'];
                        }
                    }

                    $invoiceItem->update([
                        'batch_number' => !empty($batchNumbers) ? implode(', ', $batchNumbers) : null,
                        'expiry_date' => $earliestExpiryDate,
                        'expiry_consumption_details' => $consumedLayers,
                    ]);
                }
            }
        }

        $invoice->refresh();
        $invoice->load('items');

        $discountType = $data['discount_type'] ?? 'none';
        $discountRate = (float) ($data['discount_rate'] ?? 0);
        $lineSubtotal = (float) $invoice->items()->sum('line_total') - (float) $invoice->items()->sum('vat_amount');

        if ($discountType === 'percentage' && $discountRate > 0) {
            $invoice->discount_amount = $lineSubtotal * ($discountRate / 100);
        } elseif ($discountType === 'fixed' && $discountRate > 0) {
            $invoice->discount_amount = $discountRate;
        }

        $invoice->updateTotals();
        $invoice->createDoubleEntryTransactions();

        return $invoice->fresh(['customer', 'items', 'branch', 'company']);
    }

    public function resolveCustomer(int $customerId, ?string $customerName, int $companyId, int $branchId): Customer
    {
        if ($customerId > 0) {
            return Customer::where('company_id', $companyId)->findOrFail($customerId);
        }

        $name = trim((string) $customerName) ?: (Auth::user()->name ?? 'Walk-in Customer');

        return Customer::create([
            'customerNo' => 100000 + (Customer::max('id') ?? 0) + 1,
            'name' => $name,
            'phone' => 'POS' . substr((string) time(), -7) . random_int(10, 99),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'status' => 'active',
        ]);
    }

    /**
     * Limit open POS bills to the current user unless they can view all bills.
     */
    public static function applyCashierBillVisibility(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('view all pos bills')) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    public static function userCanAccessBill(SalesInvoice $invoice, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        if ($user->can('view all pos bills')) {
            return true;
        }

        return (int) $invoice->created_by === (int) $user->id;
    }
}
