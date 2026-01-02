<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions (expand later as needed)
        $permissions = [
            'view visas',
            'manage visas',
            'review documents',
            'approve users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $consultant = Role::firstOrCreate(['name' => 'consultant']);
        $client = Role::firstOrCreate(['name' => 'client']);

        // Assign permissions
        $admin->givePermissionTo(Permission::all());

        $consultant->givePermissionTo([
            'view visas',
            'review documents',
        ]);

        $client->givePermissionTo([
            'view visas',
        ]);
    }
}
