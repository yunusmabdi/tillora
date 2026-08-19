<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TilloraUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::findByName('Admin');
        $cashierRole = Role::findByName('Cashier');

        $admin = User::updateOrCreate(
            [
                'email' => 'admin@tillora.test',
            ],
            [
                'name' => 'Tillora Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles([$adminRole]);

        $cashier = User::updateOrCreate(
            [
                'email' => 'cashier@tillora.test',
            ],
            [
                'name' => 'Tillora Cashier',
                'password' => Hash::make('password'),
            ]
        );

        $cashier->syncRoles([$cashierRole]);
    }
}