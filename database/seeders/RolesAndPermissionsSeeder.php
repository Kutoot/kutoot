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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // All Models
        $models = [
            'campaign',
            'campaign-category',
            'coupon-category',
            'coupon-redemption',
            'discount-coupon',
            'merchant',
            'merchant-location',
            'qr-code',
            'stamp',
            'subscription-plan',
            'transaction',
            'user',
            'user-subscription',
        ];

        // Actions
        $actions = [
            'view-any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force-delete',
        ];

        // System Permissions
        $systemPermissions = [
            'manage-permissions',
            'view-logs',
        ];

        // Generate Permissions
        $allPermissions = [...$systemPermissions];
        foreach ($models as $model) {
            foreach ($actions as $action) {
                $allPermissions[] = "{$action}-{$model}";
            }
        }

        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        // Super Admin gets everything via a Gate::before in AuthServiceProvider or similar.

        $merchantAdmin = Role::firstOrCreate(['name' => 'Merchant Admin', 'guard_name' => 'web']);
        $merchantAdmin->syncPermissions([
            'view-any-campaign', 'view-campaign', 'create-campaign', 'update-campaign',
            'view-any-discount-coupon', 'view-discount-coupon', 'create-discount-coupon', 'update-discount-coupon',
            'view-any-merchant-location', 'view-merchant-location', 'update-merchant-location',
            'view-any-qr-code', 'view-qr-code', 'create-qr-code', 'update-qr-code',
            'view-any-stamp', 'view-stamp', 'create-stamp',
            'view-any-coupon-redemption', 'view-coupon-redemption',
            'view-any-transaction', 'view-transaction',
        ]);

        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
    // Users typically have limited access via API, but we define the role here.
    }
}
