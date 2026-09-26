<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Destructive wipe: inventory items + sales + opening balances
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

    protected $description = 'Delete all inventory items, sales, opening balances, and related stock/sales data';

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
        if ($companyId !== null && Schema::hasTable('inventory_items')) {
            $itemIds = DB::table('inventory_items')->where('company_id', $companyId)->pluck('id')->all();
        }

        $this->warn($dryRun ? 'DRY RUN — nothing will be deleted.' : 'DESTRUCTIVE WIPE');
        $this->info($companyId ? "Scope: company_id = {$companyId}" : 'Scope: ALL companies');

        $rows = [];
        foreach ($existing as $table) {
            $rows[] = [$table, $this->countForTable($table, $companyId, $itemIds)];
        }
        $this->table(['table', 'rows to clear'], $rows);

        if ($dryRun) {
            $this->info('Dry run complete. Re-run with --force to delete.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();
        try {
            foreach ($existing as $table) {
                $deleted = $this->deleteForTable($table, $companyId, $itemIds);
                $this->line("Cleared {$table} ({$deleted} rows)");
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function countForTable(string $table, ?int $companyId, ?array $itemIds): int
    {
        return $this->queryForTable(DB::table($table), $table, $companyId, $itemIds)->count();
    }

    private function deleteForTable(string $table, ?int $companyId, ?array $itemIds): int
    {
        if ($companyId === null) {
            return DB::table($table)->delete();
        }

        return $this->queryForTable(DB::table($table), $table, $companyId, $itemIds)->delete();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $q
     * @param  list<int>|null  $itemIds
     * @return \Illuminate\Database\Query\Builder
     */
    private function queryForTable($q, string $table, ?int $companyId, ?array $itemIds)
    {
        if ($companyId === null) {
            return $q;
        }

        if (Schema::hasColumn($table, 'company_id')) {
            return $q->where('company_id', $companyId);
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
