<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'inventory_location_id')) {
                $table->foreignId('inventory_location_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('inventory_locations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'inventory_location_id')) {
                $table->dropConstrainedForeignId('inventory_location_id');
            }
        });
    }
};
