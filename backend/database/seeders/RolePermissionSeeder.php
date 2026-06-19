<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',

            'view property projects',
            'create property projects',
            'update property projects',
            'delete property projects',

            'view lots',
            'create lots',
            'update lots',
            'delete lots',

            'view clients',
            'create clients',
            'update clients',
            'delete clients',

            'view agents',
            'create agents',
            'update agents',
            'delete agents',

            'view payments',
            'create payments',
            'update payments',
            'delete payments',

            'view commissions',
            'create commissions',
            'update commissions',
            'delete commissions',

            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $cashierRole = Role::firstOrCreate([
            'name' => 'cashier',
            'guard_name' => 'web',
        ]);

        $agentRole = Role::firstOrCreate([
            'name' => 'agent',
            'guard_name' => 'web',
        ]);

        $accountingRole = Role::firstOrCreate([
            'name' => 'accounting',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions($permissions);

        $cashierRole->syncPermissions([
            'view dashboard',
            'view property projects',
            'view lots',
            'view clients',
            'view payments',
            'create payments',
            'view reports',
        ]);

        $agentRole->syncPermissions([
            'view dashboard',
            'view property projects',
            'view lots',
            'view clients',
            'view commissions',
        ]);

        $accountingRole->syncPermissions([
            'view dashboard',
            'view property projects',
            'view lots',
            'view clients',
            'view payments',
            'view commissions',
            'update commissions',
            'view reports',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@rpmcs.test'],
            [
                'name' => 'RPMCS Administrator',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('admin');
    }
}
