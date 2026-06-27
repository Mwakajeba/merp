<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    protected array $permissions = [
        'access pos cashier',
        'access pos list',
        'view all pos bills',
        'view all pos sales',
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

        $roles = Role::whereIn('name', ['super-admin', 'admin', 'manager'])
            ->where('guard_name', 'web')
            ->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($this->permissions);
        }
    }

    public function down(): void
    {
        // Permissions remain; only role assignments are not reverted.
    }
};
