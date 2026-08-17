<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Sales\PosReceiptPrintService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PosReprintReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'require.branch', 'company.scope']);
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('view logs activity') && !auth()->user()->can('view sales reports')) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = auth()->user()->company_id;

        $totalAttempts = ActivityLog::query()
            ->where('action', PosReceiptPrintService::ACTION_REPRINT_BLOCKED)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->count();

        $suspendedCount = ActivityLog::query()
            ->where('action', PosReceiptPrintService::ACTION_REPRINT_BLOCKED)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where('description', 'like', '%account suspended%')
            ->count();

        $userIds = ActivityLog::query()
            ->where('action', PosReceiptPrintService::ACTION_REPRINT_BLOCKED)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->orderBy('name')->get();

        return view('sales.reports.pos-reprint-attempts', compact(
            'totalAttempts',
            'suspendedCount',
            'users'
        ));
    }

    public function getData(Request $request)
    {
        if (!auth()->user()->can('view logs activity') && !auth()->user()->can('view sales reports')) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = auth()->user()->company_id;

        $query = ActivityLog::with('user')
            ->where('action', PosReceiptPrintService::ACTION_REPRINT_BLOCKED)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->select('activity_logs.*');

        if ($request->filled('date_from')) {
            $query->whereDate('activity_logs.activity_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('activity_logs.activity_time', '<=', $request->date_to);
        }
        if ($request->filled('user_id')) {
            $query->where('activity_logs.user_id', $request->user_id);
        }

        return DataTables::eloquent($query)
            ->addColumn('user_name', function (ActivityLog $log) {
                return e($log->user->name ?? 'Unknown');
            })
            ->addColumn('reference', function (ActivityLog $log) {
                return e($log->new_values['reference'] ?? '—');
            })
            ->addColumn('print_count_display', function (ActivityLog $log) {
                $count = $log->new_values['print_count'] ?? '—';
                $max = $log->new_values['max_prints'] ?? '—';

                return e($count . ' / ' . $max);
            })
            ->addColumn('outcome_badge', function (ActivityLog $log) {
                $suspended = (bool) ($log->new_values['account_suspended'] ?? false);

                return $suspended
                    ? '<span class="badge bg-danger">Account Suspended</span>'
                    : '<span class="badge bg-warning text-dark">Blocked Attempt</span>';
            })
            ->editColumn('activity_time', function (ActivityLog $log) {
                return optional($log->activity_time)->format('Y-m-d H:i:s')
                    ?? $log->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['outcome_badge'])
            ->make(true);
    }
}
