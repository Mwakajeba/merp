<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    protected array $permissions = [
        'view customers',
        'create customer',
        'edit customer',
        'view customer profile',
        'view customer history',
        'view sales invoices',
        'create sales invoices',
        'edit sales invoices',
        'record sales payment',
        'access pos cashier',
    ];

    public function up(): void
    {
        $salesPerson = Role::where('name', 'sales-person')->where('guard_name', 'web')->first();

        if (!$salesPerson) {
            return;
        }

        $salesPerson->revokePermissionTo($this->permissions);
    }

    public function down(): void
    {
        $salesPerson = Role::where('name', 'sales-person')->where('guard_name', 'web')->first();

        if (!$salesPerson) {
            return;
        }

        $salesPerson->givePermissionTo($this->permissions);
    }
};
