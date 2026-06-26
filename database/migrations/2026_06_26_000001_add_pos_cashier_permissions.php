<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    protected array $permissions = [
        'access pos cashier',
        'view all pos bills',
        'view all sales invoices',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            \App\Models\Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($admin) {
            $admin->givePermissionTo($this->permissions);
        }

        $accountant = Role::where('name', 'accountant')->where('guard_name', 'web')->first();
        if ($accountant) {
            $accountant->givePermissionTo($this->permissions);
        }

        $salesPerson = Role::where('name', 'sales-person')->where('guard_name', 'web')->first();
        if ($salesPerson) {
            $salesPerson->givePermissionTo(['access pos cashier']);
        }
    }

    public function down(): void
    {
        foreach ($this->permissions as $name) {
            Permission::where('name', $name)->where('guard_name', 'web')->delete();
            \App\Models\Permission::where('name', $name)->where('guard_name', 'web')->delete();
        }
    }
};
