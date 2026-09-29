<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Destructive wipe: inventory items + sales + opening balances + GL
 * (and related rows required to satisfy foreign keys).
 *
 * Usage:
 *   php artisan inventory:wipe-items-sales --force
 *   php artisan inventory:wipe-items-sales --company=1 --force
 *   php artisan inventory:wipe-items-sales --dry-run
 */
class WipeItemsSalesAndOpeningBalances extends Command
{
    protected $signature = 'inventory:wipe-items-sales
                            {--company= : Limit wipe to a company_id (recommended)}
                            {--force : Skip confirmation and run the wipe}
                            {--dry-run : Show row counts without deleting}';

    protected $description = 'Delete inventory items, sales, opening balances, GL journals/transactions, and related data';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->option('dry-run')) {
            $this->error('Refusing to run without --force (or use --dry-run).');
            $this->line('  php artisan inventory:wipe-items-sales --force');
            $this->line('  php artisan inventory:wipe-items-sales --company=1 --force');

            return self::FAILURE;
        }

        $companyId = $this->option('company');
        $companyId = ($companyId !== null && $companyId !== '') ? (int) $companyId : null;
        $dryRun = (bool) $this->option('dry-run');

        // Delete order: children first, inventory_items last.
        $tables = [
            // GL / journals
            'journal_entry_approvals',
            'gl_revaluation_history',
            'gl_transactions',
            'journal_items',
            'journals',

            'credit_note_applications',
            'credit_note_items',
            'credit_notes',
            'receipt_items',
            'receipts',
            'payment_items',
            'payments',
            'sales_invoice_items',
            'sales_invoices',
            'sales_opening_balances',
            'pos_sale_items',
            'pos_sales',
            'cash_sale_items',
            'cash_sales',
            'delivery_items',
            'deliveries',
            'sales_order_items',
            'sales_orders',
            'sales_proforma_items',
            'sales_proformas',
            'debit_note_items',
            'debit_notes',
            'goods_receipt_items',
            'goods_receipts',
            'purchase_invoice_items',
            'purchase_invoices',
            'cash_purchase_items',
            'cash_purchases',
            'purchase_order_items',
            'purchase_orders',
            'purchase_quotation_items',
            'purchase_quotations',
            'purchase_requisition_lines',
            'purchase_requisitions',
            'inventory_count_adjustments',
            'inventory_count_variances',
            'inventory_count_entries',
            'inventory_count_teams',
            'inventory_count_sessions',
            'inventory_count_periods',
            'inventory_expiry_tracking',
            'inventory_cost_layers',
            'inventory_stock_levels',
            'inventory_movements',
            'inventory_opening_balances',
            'inventory_import_batches',
            'inventory_item_location_prices',
            'inventory_item_prices',
            'item_batches',
            'production_batches',
            'work_order_boms',
            'transfer_request_items',
            'transfer_requests',
            'write_off_items',
            'write_offs',
            'inventory_items',
        ];

        $existing = array_values(array_filter($tables, fn ($t) => Schema::hasTable($t)));

        $itemIds = null;
        $branchIds = null;
        $userIds = null;
        $journalIds = null;

        if ($companyId !== null) {
            if (Schema::hasTable('inventory_items')) {
                $itemIds = DB::table('inventory_items')->where('company_id', $companyId)->pluck('id')->all();
            }
            if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'company_id')) {
                $branchIds = DB::table('branches')->where('company_id', $companyId)->pluck('id')->all();
            }
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'company_id')) {
                $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id')->all();
            }
            if (Schema::hasTable('journals')) {
                $jq = DB::table('journals');
                if (Schema::hasColumn('journals', 'company_id')) {
                    $jq->where('company_id', $companyId);
                } elseif ($branchIds) {
                    $jq->whereIn('branch_id', $branchIds);
                } elseif ($userIds) {
                    $jq->whereIn('user_id', $userIds);
                }
                $journalIds = $jq->pluck('id')->all();
            }
        }

        $this->warn($dryRun ? 'DRY RUN — nothing will be deleted.' : 'DESTRUCTIVE WIPE (includes GL)');
        $this->info($companyId ? "Scope: company_id = {$companyId}" : 'Scope: ALL companies');

        $rows = [];
        foreach ($existing as $table) {
            $rows[] = [$table, $this->countForTable($table, $companyId, $itemIds, $branchIds, $userIds, $journalIds)];
        }
        $this->table(['table', 'rows to clear'], $rows);

        if ($dryRun) {
            $this->info('Dry run complete. Re-run with --force to delete.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();
        try {
            foreach ($existing as $table) {
                $deleted = $this->deleteForTable($table, $companyId, $itemIds, $branchIds, $userIds, $journalIds);
                $this->line("Cleared {$table} ({$deleted} rows)");
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Done. Items, sales, opening balances, and GL wiped.');

        return self::SUCCESS;
    }

    private function countForTable(
        string $table,
        ?int $companyId,
        ?array $itemIds,
        ?array $branchIds,
        ?array $userIds,
        ?array $journalIds
    ): int {
        return $this->queryForTable(DB::table($table), $table, $companyId, $itemIds, $branchIds, $userIds, $journalIds)->count();
    }

    private function deleteForTable(
        string $table,
        ?int $companyId,
        ?array $itemIds,
        ?array $branchIds,
        ?array $userIds,
        ?array $journalIds
    ): int {
        if ($companyId === null) {
            return DB::table($table)->delete();
        }

        return $this->queryForTable(DB::table($table), $table, $companyId, $itemIds, $branchIds, $userIds, $journalIds)->delete();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $q
     * @param  list<int>|null  $itemIds
     * @param  list<int>|null  $branchIds
     * @param  list<int>|null  $userIds
     * @param  list<int>|null  $journalIds
     * @return \Illuminate\Database\Query\Builder
     */
    private function queryForTable($q, string $table, ?int $companyId, ?array $itemIds, ?array $branchIds, ?array $userIds, ?array $journalIds)
    {
        if ($companyId === null) {
            return $q;
        }

        if (Schema::hasColumn($table, 'company_id')) {
            return $q->where('company_id', $companyId);
        }

        // Journal children
        if ($table === 'journal_items' || $table === 'journal_entry_approvals') {
            if (! $journalIds) {
                return $q->whereRaw('1 = 0');
            }

            return $q->whereIn('journal_id', $journalIds);
        }

        // GL / journals scoped by branch or user
        if (in_array($table, ['gl_transactions', 'journals', 'gl_revaluation_history'], true)) {
            if ($branchIds && Schema::hasColumn($table, 'branch_id')) {
                return $q->whereIn('branch_id', $branchIds);
            }
            if ($userIds && Schema::hasColumn($table, 'user_id')) {
                return $q->whereIn('user_id', $userIds);
            }
        }

        // Child tables: scope via item FK when possible
        foreach (['item_id', 'inventory_item_id', 'material_item_id'] as $col) {
            if (Schema::hasColumn($table, $col)) {
                if ($itemIds === null || $itemIds === []) {
                    return $q->whereRaw('1 = 0');
                }

                return $q->whereIn($col, $itemIds);
            }
        }

        // Fallback: no safe company scope — leave alone when --company is set
        return $q->whereRaw('1 = 0');
    }
}
