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
            'table_number' => $this->normalizeTableNumber($data['table_number'] ?? null),
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

        $this->addLineItemsToInvoice($invoice, $data['items'] ?? [], 1, $saleDate, $branchId, $locationId);

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

    /**
     * Append items to an existing unpaid POS bill (running tab / extra round).
     *
     * @return array{invoice: SalesInvoice, round: int, added_item_ids: array<int>}
     */
    public function addItemsToBill(SalesInvoice $invoice, array $items, ?string $tableNumber = null): array
    {
        $user = Auth::user();
        $branchId = session('branch_id') ?? ($user->branch_id ?? null);
        $locationId = session('location_id');

        if (!$branchId || !$locationId) {
            throw new \RuntimeException('Branch and location must be selected before adding to a bill.');
        }

        if ($invoice->reference_no !== self::REFERENCE_NO) {
            throw new \RuntimeException('Only POS bills can receive additional items.');
        }

        if (!self::userCanAccessBill($invoice, $user)) {
            throw new \RuntimeException('You do not have access to this bill.');
        }

        if ((float) $invoice->paid_amount > 0
            || (float) $invoice->balance_due <= 0
            || in_array($invoice->status, ['paid', 'cancelled'], true)) {
            throw new \RuntimeException('This bill is already paid or closed and cannot accept more items.');
        }

        if ($user->company_id && (int) $invoice->company_id !== (int) $user->company_id) {
            throw new \RuntimeException('Invalid bill for this company.');
        }

        $normalizedTable = $this->normalizeTableNumber($tableNumber);
        if ($normalizedTable !== null) {
            $invoice->table_number = $normalizedTable;
            $invoice->save();
        }

        $nextRound = ((int) $invoice->items()->max('pos_round')) + 1;
        if ($nextRound < 1) {
            $nextRound = 1;
        }

        $saleDate = $invoice->invoice_date?->format('Y-m-d') ?? now()->toDateString();
        $addedIds = $this->addLineItemsToInvoice(
            $invoice,
            $items,
            $nextRound,
            $saleDate,
            $branchId,
            $locationId
        );

        $invoice->refresh();
        $invoice->load('items');
        $invoice->updateTotals();
        $invoice->createDoubleEntryTransactions();

        return [
            'invoice' => $invoice->fresh(['customer', 'items', 'branch', 'company']),
            'round' => $nextRound,
            'added_item_ids' => $addedIds,
        ];
    }

    /**
     * @return array<int> Created invoice item IDs
     */
    protected function addLineItemsToInvoice(
        SalesInvoice $invoice,
        array $items,
        int $round,
        string $saleDate,
        int $branchId,
        int $locationId
    ): array {
        $user = Auth::user();
        $stockService = new InventoryStockService();
        $costService = new InventoryCostService();
        $addedIds = [];

        foreach ($items as $itemData) {
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
                'pos_round' => $round,
            ]);

            $addedIds[] = (int) $invoiceItem->id;

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
                    'reference' => 'POS Bill: ' . $invoice->invoice_number . ' (round ' . $round . ')',
                    'reference_type' => 'sales_invoice',
                    'reference_id' => $invoice->id,
                    'notes' => 'Stock sold via POS bill round ' . $round,
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

        return $addedIds;
    }

    protected function normalizeTableNumber(?string $tableNumber): ?string
    {
        $tableNumber = trim((string) $tableNumber);

        return $tableNumber !== '' ? mb_substr($tableNumber, 0, 50) : null;
    }

    public function resolveCustomer(
        int $customerId,
        ?string $customerName,
        int $companyId,
        int $branchId,
        ?User $user = null
    ): Customer {
        $user = $user ?? Auth::user();

        if ($customerId > 0) {
            return Customer::where('company_id', $companyId)->findOrFail($customerId);
        }

        $name = trim((string) $customerName) ?: ($user?->name ?? 'Walk-in Customer');
        $existing = $this->findExistingPosCustomer($name, $user, $companyId, $branchId);

        if ($existing) {
            if ($user && $this->shouldUseUserProfileForPosCustomer($name, $user)) {
                $this->syncCustomerFromUser($existing, $user, $name);
            }

            return $existing->fresh();
        }

        return $this->createPosCustomer($name, $user, $companyId, $branchId);
    }

    protected function shouldUseUserProfileForPosCustomer(string $name, User $user): bool
    {
        return strcasecmp(trim($name), trim((string) $user->name)) === 0;
    }

    protected function findExistingPosCustomer(
        string $name,
        ?User $user,
        int $companyId,
        int $branchId
    ): ?Customer {
        $baseQuery = Customer::query()->where('company_id', $companyId);

        if ($user && $this->shouldUseUserProfileForPosCustomer($name, $user)) {
            if (!empty($user->phone)) {
                $customer = $this->findCustomerByPhone($baseQuery->clone(), $user->phone);
                if ($customer) {
                    return $customer;
                }
            }

            if (!empty($user->email)) {
                $customer = $baseQuery->clone()->where('email', $user->email)->first();
                if ($customer) {
                    return $customer;
                }
            }
        }

        return $baseQuery
            ->where('branch_id', $branchId)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->first();
    }

    protected function findCustomerByPhone($query, string $phone): ?Customer
    {
        $normalized = normalize_phone_number($phone);
        $candidates = array_unique(array_filter([
            $phone,
            $normalized,
            str_starts_with($normalized, '255') && strlen($normalized) === 12
                ? '0' . substr($normalized, 3)
                : null,
        ]));

        return $query->whereIn('phone', $candidates)->first();
    }

    protected function syncCustomerFromUser(Customer $customer, User $user, string $name): void
    {
        $updates = [];

        if ($name !== '' && $customer->name !== $name) {
            $updates['name'] = $name;
        }

        if (!empty($user->phone)) {
            $phone = normalize_phone_number($user->phone);
            if ($phone !== '' && $customer->phone !== $phone) {
                $updates['phone'] = $phone;
            }
        }

        if (!empty($user->email) && $customer->email !== $user->email) {
            $updates['email'] = $user->email;
        }

        if ($updates !== []) {
            $customer->update($updates);
        }
    }

    protected function createPosCustomer(
        string $name,
        ?User $user,
        int $companyId,
        int $branchId
    ): Customer {
        $useUserProfile = $user && $this->shouldUseUserProfileForPosCustomer($name, $user);

        $phone = null;
        if ($useUserProfile && !empty($user->phone)) {
            $phone = normalize_phone_number($user->phone);
        }

        if (!$phone) {
            $phone = $this->generateUniquePosPhone($companyId, $name);
        }

        $email = ($useUserProfile && !empty($user->email)) ? $user->email : null;

        return Customer::create([
            'customerNo' => 100000 + (Customer::max('id') ?? 0) + 1,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'status' => 'active',
        ]);
    }

    protected function generateUniquePosPhone(int $companyId, string $name): string
    {
        $hash = abs(crc32($companyId . '|' . strtolower(trim($name))));
        $phone = '255' . str_pad((string) ($hash % 1000000000), 9, '0', STR_PAD_LEFT);

        $attempt = 0;
        while (Customer::where('company_id', $companyId)->where('phone', $phone)->exists()) {
            $attempt++;
            $phone = '255' . str_pad((string) (($hash + $attempt) % 1000000000), 9, '0', STR_PAD_LEFT);
        }

        return $phone;
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
