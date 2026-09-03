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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */
            'view users',
            'view any users',
            'create users',
            'update users',
            'delete users',

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */
            'view roles',
            'view any roles',
            'create roles',
            'update roles',
            'delete roles',

            /*
            |--------------------------------------------------------------------------
            | Permissions
            |--------------------------------------------------------------------------
            */
            'view permissions',
            'view any permissions',
            'create permissions',
            'update permissions',
            'delete permissions',

            /*
            |--------------------------------------------------------------------------
            | Products
            |--------------------------------------------------------------------------
            */
            'view products',
            'view any products',
            'create products',
            'update products',
            'delete products',

            /*
            |--------------------------------------------------------------------------
            | Customers
            |--------------------------------------------------------------------------
            */
            'view customers',
            'view any customers',
            'create customers',
            'update customers',
            'delete customers',

            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */
            'view orders',
            'view any orders',
            'create orders',
            'update orders',
            'delete orders',

            /*
            |--------------------------------------------------------------------------
            | Order Fulfillment
            |--------------------------------------------------------------------------
            */
            'prepare orders',
            'mark orders ready',

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */
            'view inventory',
            'adjust stock',
            'transfer stock',
            'manage stock',

            /*
            |--------------------------------------------------------------------------
            | Purchasing
            |--------------------------------------------------------------------------
            */
            'view purchases',
            'view any purchases',
            'create purchases',
            'update purchases',
            'approve purchases',

            /*
            |--------------------------------------------------------------------------
            | Delivery Zones
            |--------------------------------------------------------------------------
            */
            'view delivery zones',
            'view any delivery zones',
            'create delivery zones',
            'update delivery zones',
            'delete delivery zones',

            /*
            |--------------------------------------------------------------------------
            | Drivers
            |--------------------------------------------------------------------------
            */
            'view drivers',
            'view any drivers',
            'create drivers',
            'update drivers',
            'delete drivers',
            'assign orders to drivers',

            /*
            |--------------------------------------------------------------------------
            | Driver Delivery
            |--------------------------------------------------------------------------
            */
            'view assigned orders',
            'accept orders',
            'pickup orders',
            'start delivery',
            'deliver orders',

            /*
            |--------------------------------------------------------------------------
            | POS
            |--------------------------------------------------------------------------
            */
            'access pos',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);

        $cashier = Role::firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);

        $driver = Role::firstOrCreate([
            'name' => 'Driver',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin->syncPermissions([
            // Users
            'view users',
            'view any users',
            'create users',
            'update users',
            'delete users',

            // Roles
            'view roles',
            'view any roles',
            'create roles',
            'update roles',
            'delete roles',

            // Permissions
            'view permissions',
            'view any permissions',
            'create permissions',
            'update permissions',
            'delete permissions',

            // Products
            'view products',
            'view any products',
            'create products',
            'update products',
            'delete products',

            // Customers
            'view customers',
            'view any customers',
            'create customers',
            'update customers',
            'delete customers',

            // Orders
            'view orders',
            'view any orders',
            'create orders',
            'update orders',
            'delete orders',

            // Fulfillment
            'prepare orders',
            'mark orders ready',

            // Inventory
            'view inventory',
            'adjust stock',
            'transfer stock',
            'manage stock',

            // Purchasing
            'view purchases',
            'view any purchases',
            'create purchases',
            'update purchases',
            'approve purchases',

            // Delivery Zones
            'view delivery zones',
            'view any delivery zones',
            'create delivery zones',
            'update delivery zones',
            'delete delivery zones',

            // Drivers
            'view drivers',
            'view any drivers',
            'create drivers',
            'update drivers',
            'delete drivers',
            'assign orders to drivers',

            // POS
            'access pos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | MANAGER
        |--------------------------------------------------------------------------
        */

        $manager->syncPermissions([
            // Products
            'view products',
            'view any products',
            'create products',
            'update products',

            // Customers
            'view customers',
            'view any customers',
            'create customers',
            'update customers',

            // Orders
            'view orders',
            'view any orders',
            'create orders',
            'update orders',

            // Fulfillment
            'prepare orders',
            'mark orders ready',

            // Inventory
            'view inventory',
            'adjust stock',
            'transfer stock',
            'manage stock',

            // Purchasing
            'view purchases',
            'view any purchases',
            'create purchases',
            'update purchases',
            'approve purchases',

            // Delivery Zones
            'view delivery zones',
            'view any delivery zones',

            // Drivers
            'view drivers',
            'view any drivers',
            'assign orders to drivers',

            // POS
            'access pos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CASHIER
        |--------------------------------------------------------------------------
        */

        $cashier->syncPermissions([
            // Products
            'view products',
            'view any products',

            // Customers
            'view customers',
            'view any customers',
            'create customers',
            'update customers',

            // Orders
            'view orders',
            'view any orders',
            'create orders',
            'update orders',

            // POS
            'access pos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | DRIVER
        |--------------------------------------------------------------------------
        */

        $driver->syncPermissions([
            'view assigned orders',
            'accept orders',
            'pickup orders',
            'start delivery',
            'deliver orders',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}