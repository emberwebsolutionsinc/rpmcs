<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private string $guardName = 'web';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',

            'administration.users.view',
            'administration.users.create',
            'administration.users.edit',
            'administration.users.delete',
            'administration.users.activate',
            'administration.users.deactivate',
            'administration.users.reset-password',

            'administration.roles.view',
            'administration.roles.create',
            'administration.roles.edit',
            'administration.roles.delete',

            'administration.permissions.view',

            'agents.view',
            'agents.view-own',
            'agents.create',
            'agents.edit',
            'agents.delete',

            'clients.view',
            'clients.view-own',
            'clients.create',
            'clients.edit',
            'clients.delete',

            'sales.view',
            'sales.view-own',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.approve',

            'policies.view',
            'policies.view-own',
            'policies.create',
            'policies.edit',
            'policies.delete',
            'policies.approve',

            'payments.view',
            'payments.view-own',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.approve',
            'payments.print',
            'payments.export',

            'receipts.view',
            'receipts.view-own',
            'receipts.create',
            'receipts.edit',
            'receipts.delete',
            'receipts.print',
            'receipts.export',

            'commissions.view',
            'commissions.view-own',
            'commissions.create',
            'commissions.edit',
            'commissions.approve',
            'commissions.export',

            'renewals.view',
            'renewals.view-own',
            'renewals.create',
            'renewals.edit',

            'documents.view',
            'documents.view-own',
            'documents.upload',
            'documents.download',
            'documents.delete',

            'reports.view',
            'reports.sales',
            'reports.collections',
            'reports.payments',
            'reports.receipts',
            'reports.commissions',
            'reports.agents',
            'reports.export',

            'master-data.view',
            'master-data.create',
            'master-data.edit',
            'master-data.delete',

            'settings.view',
            'settings.edit',

            'audit-logs.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::updateOrCreate(
                [
                    'name' => $permissionName,
                    'guard_name' => $this->guardName,
                ],
                []
            );
        }

        $superAdministrator = $this->createRole('Super Administrator');
        $administrator = $this->createRole('Administrator');
        $owner = $this->createRole('Owner');
        $accounting = $this->createRole('Accounting');
        $marketing = $this->createRole('Marketing');
        $cashier = $this->createRole('Cashier');
        $encoder = $this->createRole('Encoder');
        $agent = $this->createRole('Agent');

        $superAdministrator->syncPermissions(
            Permission::where('guard_name', $this->guardName)->get()
        );

        $administrator->syncPermissions([
            'dashboard.view',

            'administration.users.view',
            'administration.users.create',
            'administration.users.edit',
            'administration.users.delete',
            'administration.users.activate',
            'administration.users.deactivate',
            'administration.users.reset-password',

            'administration.roles.view',
            'administration.roles.create',
            'administration.roles.edit',
            'administration.roles.delete',

            'administration.permissions.view',

            'agents.view',
            'agents.create',
            'agents.edit',
            'agents.delete',

            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.approve',

            'policies.view',
            'policies.create',
            'policies.edit',
            'policies.delete',
            'policies.approve',

            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.approve',
            'payments.print',
            'payments.export',

            'receipts.view',
            'receipts.create',
            'receipts.edit',
            'receipts.delete',
            'receipts.print',
            'receipts.export',

            'commissions.view',
            'commissions.create',
            'commissions.edit',
            'commissions.approve',
            'commissions.export',

            'renewals.view',
            'renewals.create',
            'renewals.edit',

            'documents.view',
            'documents.upload',
            'documents.download',
            'documents.delete',

            'reports.view',
            'reports.sales',
            'reports.collections',
            'reports.payments',
            'reports.receipts',
            'reports.commissions',
            'reports.agents',
            'reports.export',

            'master-data.view',
            'master-data.create',
            'master-data.edit',
            'master-data.delete',

            'settings.view',
            'settings.edit',

            'audit-logs.view',
        ]);

        $owner->syncPermissions([
            'dashboard.view',
            'agents.view',
            'clients.view',
            'sales.view',
            'policies.view',
            'payments.view',
            'receipts.view',
            'commissions.view',
            'renewals.view',
            'documents.view',
            'reports.view',
            'reports.sales',
            'reports.collections',
            'reports.payments',
            'reports.receipts',
            'reports.commissions',
            'reports.agents',
            'reports.export',
        ]);

        $accounting->syncPermissions([
            'dashboard.view',

            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.approve',
            'payments.print',
            'payments.export',

            'receipts.view',
            'receipts.create',
            'receipts.edit',
            'receipts.print',
            'receipts.export',

            'reports.view',
            'reports.collections',
            'reports.payments',
            'reports.receipts',
            'reports.export',
        ]);

        $marketing->syncPermissions([
            'dashboard.view',

            'agents.view',
            'agents.create',
            'agents.edit',

            'clients.view',
            'clients.create',
            'clients.edit',

            'sales.view',
            'sales.create',
            'sales.edit',

            'policies.view',
            'policies.create',
            'policies.edit',

            'documents.view',
            'documents.upload',
            'documents.download',

            'reports.view',
            'reports.sales',
            'reports.agents',
        ]);

        $cashier->syncPermissions([
            'dashboard.view',

            'clients.view',
            'sales.view',
            'policies.view',

            'payments.view',
            'payments.create',
            'payments.print',

            'receipts.view',
            'receipts.create',
            'receipts.print',

            'reports.view',
            'reports.collections',
            'reports.payments',
            'reports.receipts',
        ]);

        $encoder->syncPermissions([
            'dashboard.view',

            'agents.view',
            'agents.create',
            'agents.edit',

            'clients.view',
            'clients.create',
            'clients.edit',

            'sales.view',
            'sales.create',
            'sales.edit',

            'policies.view',
            'policies.create',
            'policies.edit',

            'renewals.view',
            'renewals.create',
            'renewals.edit',

            'documents.view',
            'documents.upload',
            'documents.download',
        ]);

        $agent->syncPermissions([
            'dashboard.view',

            'agents.view-own',

            'clients.view-own',
            'clients.create',

            'sales.view-own',
            'sales.create',

            'policies.view-own',
            'payments.view-own',
            'receipts.view-own',
            'commissions.view-own',
            'renewals.view-own',

            'documents.view-own',
            'documents.upload',
            'documents.download',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createRole(string $roleName): Role
    {
        return Role::updateOrCreate(
            [
                'name' => $roleName,
                'guard_name' => $this->guardName,
            ],
            []
        );
    }
}
