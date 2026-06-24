<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    public function up(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'pos_sale_mode'],
            [
                'value' => 'direct',
                'type' => 'string',
                'group' => 'sales',
                'label' => 'POS Sale Mode',
                'description' => 'direct = immediate cash sale (supermarket). bill = create unpaid invoice first (restaurant), pay at cashier.',
                'is_public' => false,
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'pos_auto_print_receipt'],
            [
                'value' => '1',
                'type' => 'boolean',
                'group' => 'sales',
                'label' => 'Auto Print POS Receipt',
                'description' => 'Automatically open the thermal receipt print dialog after completing a sale or bill payment.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        SystemSetting::whereIn('key', ['pos_sale_mode', 'pos_auto_print_receipt'])->delete();
    }
};
