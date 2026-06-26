<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sales\PosSaleController;
use App\Models\BankAccount;
use App\Models\LoginAttempt;
use App\Models\Sales\CashSale;
use App\Models\Sales\PosSale;
use App\Models\Sales\SalesInvoice;
use App\Models\User;
use App\Services\Sales\PosBillService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Vinkla\Hashids\Facades\Hashids;

class SmartPosMobileController extends Controller
{
    protected array $allowedRoles = ['admin', 'accountant', 'super-admin'];

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $phone = $request->phone;

        if (LoginAttempt::isLockedOut($phone)) {
            $remaining = LoginAttempt::getRemainingLockoutTime($phone);

            return response()->json([
                'success' => false,
                'message' => "Account is temporarily locked. Try again in {$remaining} minutes.",
            ], 429);
        }

        $user = find_user_by_phone($phone);

        if (!$user || !Hash::check($request->password, $user->password)) {
            LoginAttempt::record($phone, $request->ip(), $request->userAgent(), false);

            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        if (!$this->userCanAccessMobile($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This app is only available for admin and accountant users.',
            ], 403);
        }

        LoginAttempt::record($phone, $request->ip(), $request->userAgent(), true);

        $user->tokens()->where('name', 'smartpos-mobile')->delete();
        $token = $user->createToken('smartpos-mobile')->plainTextToken;

        $branch = $this->resolveBranch($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->formatUser($user, $branch),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureMobileAccess($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->formatUser($user, $this->resolveBranch($user)),
            ],
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureMobileAccess($user);

        $branchId = $this->resolveBranchId($user);
        $date = $request->get('date', Carbon::today()->toDateString());

        $invoiceStats = $this->aggregateSales(
            SalesInvoice::query()->where('status', '!=', 'cancelled'),
            'invoice_date',
            $user->company_id,
            $branchId,
            $date,
            true
        );

        $posStats = $this->aggregateSales(
            PosSale::query(),
            'sale_date',
            $user->company_id,
            $branchId,
            $date,
            false
        );

        $cashStats = $this->aggregateSales(
            CashSale::query(),
            'sale_date',
            $user->company_id,
            $branchId,
            $date,
            false
        );

        $grossSales = $invoiceStats['total'] + $posStats['total'] + $cashStats['total'];
        $discounts = $invoiceStats['discount'] + $posStats['discount'] + $cashStats['discount'];
        $netSales = $grossSales - $discounts;
        $totalPaid = $invoiceStats['paid'] + $posStats['total'] + $cashStats['total'];
        $outstanding = $invoiceStats['balance'];

        $openBillsQuery = SalesInvoice::query()
            ->where('reference_no', PosBillService::REFERENCE_NO)
            ->where('company_id', $user->company_id)
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', ['paid', 'cancelled']);

        if ($branchId) {
            $openBillsQuery->where('branch_id', $branchId);
        }

        PosBillService::applyCashierBillVisibility($openBillsQuery, $user);

        $openBillsCount = (clone $openBillsQuery)->count();
        $openBillsTotal = (clone $openBillsQuery)->sum('balance_due');

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'branch' => $this->resolveBranch($user),
                'total_sales' => round($grossSales, 2),
                'net_sales' => round($netSales, 2),
                'total_paid' => round($totalPaid, 2),
                'outstanding_balance' => round($outstanding, 2),
                'transactions_count' => $invoiceStats['count'] + $posStats['count'] + $cashStats['count'],
                'open_bills_count' => $openBillsCount,
                'open_bills_total' => round((float) $openBillsTotal, 2),
                'breakdown' => [
                    'pos_sales' => round($posStats['total'], 2),
                    'cash_sales' => round($cashStats['total'], 2),
                    'invoice_sales' => round($invoiceStats['total'], 2),
                ],
            ],
        ]);
    }

    public function bills(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureMobileAccess($user);

        $branchId = $this->resolveBranchId($user);
        $search = trim((string) $request->get('search', ''));

        $query = SalesInvoice::with(['customer'])
            ->where('reference_no', PosBillService::REFERENCE_NO)
            ->where('company_id', $user->company_id)
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at');

        PosBillService::applyCashierBillVisibility($query, $user);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $bills = $query->limit(100)->get()->map(fn (SalesInvoice $bill) => [
            'encoded_id' => $bill->encoded_id,
            'invoice_number' => $bill->invoice_number,
            'customer_name' => $bill->customer->name ?? 'N/A',
            'customer_phone' => $bill->customer->phone ?? null,
            'invoice_date' => $bill->invoice_date?->format('Y-m-d'),
            'balance_due' => (float) $bill->balance_due,
            'total_amount' => (float) $bill->total_amount,
            'paid_amount' => (float) $bill->paid_amount,
            'currency' => strtoupper($bill->currency ?? 'TZS'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'bills' => $bills,
                'count' => $bills->count(),
            ],
        ]);
    }

    public function payBill(Request $request, string $encodedId): JsonResponse
    {
        $user = $request->user();
        $this->ensureMobileAccess($user);

        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'nullable|numeric|min:0.01',
            'payment_date' => 'nullable|date',
        ]);

        $invoiceId = Hashids::decode($encodedId)[0] ?? null;
        if (!$invoiceId) {
            return response()->json(['success' => false, 'message' => 'Invalid bill ID'], 404);
        }

        $invoice = SalesInvoice::where('company_id', $user->company_id)->findOrFail($invoiceId);

        if (!PosBillService::userCanAccessBill($invoice, $user)) {
            return response()->json(['success' => false, 'message' => 'You can only pay your own POS bills.'], 403);
        }

        if ($invoice->reference_no !== PosBillService::REFERENCE_NO) {
            return response()->json(['success' => false, 'message' => 'This is not a POS bill'], 422);
        }

        if ($invoice->balance_due <= 0) {
            return response()->json(['success' => false, 'message' => 'Bill is already paid'], 422);
        }

        $amount = $request->filled('amount')
            ? (float) $request->amount
            : (float) $invoice->balance_due;

        if ($amount > (float) $invoice->balance_due) {
            return response()->json([
                'success' => false,
                'message' => 'Amount exceeds balance due',
            ], 422);
        }

        Auth::login($user);
        $user->applyDefaultBranchAndLocation();

        /** @var PosSaleController $posController */
        $posController = app(PosSaleController::class);

        return $posController->payBill(
            $request->merge([
                'amount' => $amount,
                'payment_date' => $request->input('payment_date', now()->toDateString()),
            ]),
            $encodedId
        );
    }

    public function bankAccounts(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureMobileAccess($user);

        $branchId = $this->resolveBranchId($user);

        $accounts = BankAccount::orderBy('name')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where(function ($inner) use ($branchId) {
                    $inner->where('is_all_branches', true)
                        ->orWhere('branch_id', $branchId);
                });
            })
            ->get(['id', 'name'])
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name]);

        return response()->json([
            'success' => true,
            'data' => ['bank_accounts' => $accounts],
        ]);
    }

    protected function userCanAccessMobile(User $user): bool
    {
        foreach ($this->allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return $user->can('view sales reports') && $user->can('edit sales invoices');
    }

    protected function ensureMobileAccess(User $user): void
    {
        if (!$this->userCanAccessMobile($user)) {
            abort(403, 'Unauthorized for mobile app.');
        }
    }

    protected function formatUser(User $user, ?array $branch): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'branch' => $branch,
            'company' => $user->company?->name,
        ];
    }

    protected function resolveBranchId(User $user): ?int
    {
        return $user->branch_id
            ?: $user->branches()->value('branches.id')
            ?: null;
    }

    protected function resolveBranch(User $user): ?array
    {
        $branchId = $this->resolveBranchId($user);
        if (!$branchId) {
            return null;
        }

        $branch = $user->branches()->where('branches.id', $branchId)->first()
            ?? $user->branch;

        return $branch ? ['id' => $branch->id, 'name' => $branch->name] : null;
    }

    protected function aggregateSales($query, string $dateColumn, int $companyId, ?int $branchId, string $date, bool $trackBalance): array
    {
        $q = (clone $query)
            ->where('company_id', $companyId)
            ->whereDate($dateColumn, $date);

        if ($branchId) {
            $q->where('branch_id', $branchId);
        }

        return [
            'count' => (clone $q)->count(),
            'total' => (float) (clone $q)->sum('total_amount'),
            'discount' => (float) (clone $q)->sum('discount_amount'),
            'paid' => $trackBalance ? (float) (clone $q)->sum('paid_amount') : 0.0,
            'balance' => $trackBalance ? (float) (clone $q)->sum('balance_due') : 0.0,
        ];
    }
}
