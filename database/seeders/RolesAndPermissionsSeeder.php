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
            | Rider Delivery
            |--------------------------------------------------------------------------
            */
            'view assigned orders',
            'accept orders',
            'pickup orders',
            'start delivery',
            'deliver orders',

            /*
            |--------------------------------------------------------------------------
            | Riders
            |--------------------------------------------------------------------------
            */
            'view riders',
            'view any riders',
            'create riders',
            'update riders',
            'delete riders',

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
            | POS
            |--------------------------------------------------------------------------
            */
            'access pos',
        ];

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

        $rider = Role::firstOrCreate([
            'name' => 'Rider',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        |
        | Super Admin gets absolutely everything.
        |
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
            'view users',
            'view any users',
            'create users',
            'update users',
            'delete users',

            'view roles',
            'view any roles',
            'create roles',
            'update roles',
            'delete roles',

            'view permissions',
            'view any permissions',
            'create permissions',
            'update permissions',
            'delete permissions',

            'view orders',
            'view any orders',
            'create orders',
            'update orders',
            'delete orders',

            'prepare orders',
            'mark orders ready',

            'view riders',
            'view any riders',
            'create riders',
            'update riders',
            'delete riders',

            'view products',
            'view any products',
            'create products',
            'update products',
            'delete products',

            'view customers',
            'view any customers',
            'create customers',
            'update customers',
            'delete customers',

            'access pos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | MANAGER
        |--------------------------------------------------------------------------
        */

        $manager->syncPermissions([
            'view users',
            'view any users',

            'view orders',
            'view any orders',
            'create orders',
            'update orders',

            'prepare orders',
            'mark orders ready',

            'view riders',
            'view any riders',

            'view products',
            'view any products',
            'create products',
            'update products',

            'view customers',
            'view any customers',
            'create customers',
            'update customers',

            'access pos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CASHIER
        |--------------------------------------------------------------------------
        */

        $cashier->syncPermissions([
            'view orders',
            'view any orders',
            'create orders',
            'update orders',

            'view customers',
            'view any customers',
            'create customers',
            'update customers',

            'access pos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | RIDER
        |--------------------------------------------------------------------------
        */

        $rider->syncPermissions([
            'view assigned orders',
            'accept orders',
            'pickup orders',
            'start delivery',
            'deliver orders',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}