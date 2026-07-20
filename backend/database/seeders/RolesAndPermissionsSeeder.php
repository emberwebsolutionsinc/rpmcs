<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Clear cached permissions before creating
         * or updating roles and permissions.
         */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [
            /*
             * Dashboard
             */
            'dashboard.view',

            /*
             * Projects
             */
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',

            /*
             * Clients
             */
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',

            /*
             * Agents
             */
            'agents.view',
            'agents.create',
            'agents.update',
            'agents.delete',

            /*
             * Reservations
             */
            'reservations.view',
            'reservations.create',
            'reservations.update',
            'reservations.delete',

            /*
             * Sales
             */
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',

            /*
             * Collections
             */
            'collections.view',
            'collections.create',
            'collections.update',
            'collections.delete',

            /*
             * Reports
             */
            'reports.view',

            /*
             * Administration: Users
             */
            'administration.users.view',
            'administration.users.create',
            'administration.users.update',
            'administration.users.delete',
            'administration.users.status',
            'administration.users.reset-password',

            /*
             * Administration: Roles
             */
            'administration.roles.view',
            'administration.roles.create',
            'administration.roles.update',
            'administration.roles.delete',

            /*
             * Administration: Permissions
             */
            'administration.permissions.view',
        ];

        /*
         * Create permissions without duplicating
         * existing database records.
         */
        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
         * Super Admin
         *
         * Receives every permission in the system.
         */
        $superAdmin = Role::query()->firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        /*
         * Admin
         *
         * Receives all permissions except destructive
         * administration actions.
         */
        $admin = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->whereNotIn('name', [
                    'administration.roles.delete',
                    'administration.users.delete',
                ])
                ->get()
        );

        /*
         * Staff
         *
         * Receives operational permissions only.
         * No administration permissions are assigned.
         */
        $staff = Role::query()->firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]);

        $staff->syncPermissions([
            'dashboard.view',

            'projects.view',

            'clients.view',
            'clients.create',
            'clients.update',

            'agents.view',

            'reservations.view',
            'reservations.create',
            'reservations.update',

            'sales.view',

            'collections.view',
            'collections.create',

            'reports.view',
        ]);

        /*
         * Clear permission cache again after all
         * roles and permissions are synchronized.
         */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
