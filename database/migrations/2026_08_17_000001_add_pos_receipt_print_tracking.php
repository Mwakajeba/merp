<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_sales', 'receipt_print_count')) {
                $table->unsignedSmallInteger('receipt_print_count')->default(0)->after('receipt_printed');
            }
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'pos_receipt_print_count')) {
                $table->unsignedSmallInteger('pos_receipt_print_count')->default(0)->after('notes');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('status');
            }
        });

        SystemSetting::updateOrCreate(
            ['key' => 'pos_receipt_max_prints'],
            [
                'value' => '1',
                'type' => 'integer',
                'group' => 'sales',
                'label' => 'POS Receipt Max Prints',
                'description' => 'Maximum number of times a POS receipt may be printed. Exceeding this limit blocks the user account until an administrator reactivates it.',
                'is_public' => false,
            ]
        );

        if (Schema::hasColumn('pos_sales', 'receipt_printed') && Schema::hasColumn('pos_sales', 'receipt_print_count')) {
            \DB::table('pos_sales')
                ->where('receipt_printed', true)
                ->where('receipt_print_count', 0)
                ->update(['receipt_print_count' => 1]);
        }
    }

    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            if (Schema::hasColumn('pos_sales', 'receipt_print_count')) {
                $table->dropColumn('receipt_print_count');
            }
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'pos_receipt_print_count')) {
                $table->dropColumn('pos_receipt_print_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status_reason')) {
                $table->dropColumn('status_reason');
            }
        });

        SystemSetting::where('key', 'pos_receipt_max_prints')->delete();
    }
};
