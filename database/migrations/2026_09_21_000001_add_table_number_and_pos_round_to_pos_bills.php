<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'table_number')) {
                $table->string('table_number', 50)->nullable()->after('reference_no');
            }
        });

        Schema::table('sales_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoice_items', 'pos_round')) {
                $table->unsignedSmallInteger('pos_round')->default(1)->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'table_number')) {
                $table->dropColumn('table_number');
            }
        });

        Schema::table('sales_invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoice_items', 'pos_round')) {
                $table->dropColumn('pos_round');
            }
        });
    }
};
