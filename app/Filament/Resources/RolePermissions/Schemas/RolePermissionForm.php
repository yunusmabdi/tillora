<?php

namespace App\Filament\Resources\RolePermissions\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RolePermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role')
                    ->schema([
                        TextInput::make('name')
                            ->label('Role Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('guard_name')
                            ->default('web')
                            ->hidden()
                            ->dehydrated(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('PERMISSIONS')
                    ->schema([
                        self::permissionGroup(
                            'Products',
                            'products',
                            [
                                'view products' => 'View Products',
                                'view any products' => 'View Any Products',
                                'create products' => 'Create Products',
                                'update products' => 'Edit Products',
                                'delete products' => 'Delete Products',
                            ]
                        ),

                        self::permissionGroup(
                            'Orders',
                            'orders',
                            [
                                'view orders' => 'View Orders',
                                'view any orders' => 'View Any Orders',
                                'create orders' => 'Create Orders',
                                'update orders' => 'Edit Orders',
                                'delete orders' => 'Delete Orders',
                                'prepare orders' => 'Prepare Orders',
                                'mark orders ready' => 'Mark Orders Ready',
                            ]
                        ),

                        self::permissionGroup(
                            'Customers',
                            'customers',
                            [
                                'view customers' => 'View Customers',
                                'view any customers' => 'View Any Customers',
                                'create customers' => 'Create Customers',
                                'update customers' => 'Edit Customers',
                                'delete customers' => 'Delete Customers',
                            ]
                        ),

                        self::permissionGroup(
                            'Inventory',
                            'inventory',
                            [
                                'view inventory' => 'View Inventory',
                                'adjust stock' => 'Adjust Stock',
                                'transfer stock' => 'Transfer Stock',
                                'manage stock' => 'Manage Stock',
                            ]
                        ),

                        self::permissionGroup(
                            'Purchasing',
                            'purchases',
                            [
                                'view purchases' => 'View Purchases',
                                'view any purchases' => 'View Any Purchases',
                                'create purchases' => 'Create Purchases',
                                'update purchases' => 'Edit Purchases',
                                'approve purchases' => 'Approve Purchases',
                            ]
                        ),

                        self::permissionGroup(
                            'Delivery Zones',
                            'delivery_zones',
                            [
                                'view delivery zones' => 'View Delivery Zones',
                                'view any delivery zones' => 'View Any Delivery Zones',
                                'create delivery zones' => 'Create Delivery Zones',
                                'update delivery zones' => 'Edit Delivery Zones',
                                'delete delivery zones' => 'Delete Delivery Zones',
                            ]
                        ),

                        self::permissionGroup(
                            'Drivers',
                            'drivers',
                            [
                                'view drivers' => 'View Drivers',
                                'view any drivers' => 'View Any Drivers',
                                'create drivers' => 'Create Drivers',
                                'update drivers' => 'Edit Drivers',
                                'delete drivers' => 'Delete Drivers',
                                'assign orders to drivers' => 'Assign Orders to Drivers',
                            ]
                        ),

                        self::permissionGroup(
                            'Driver Delivery',
                            'driver_delivery',
                            [
                                'view assigned orders' => 'View Assigned Orders',
                                'accept orders' => 'Accept Orders',
                                'pickup orders' => 'Pickup Orders',
                                'start delivery' => 'Start Delivery',
                                'deliver orders' => 'Deliver Orders',
                            ]
                        ),

                        self::permissionGroup(
                            'Users',
                            'users',
                            [
                                'view users' => 'View Users',
                                'view any users' => 'View Any Users',
                                'create users' => 'Create Users',
                                'update users' => 'Edit Users',
                                'delete users' => 'Delete Users',
                            ]
                        ),

                        self::permissionGroup(
                            'Roles',
                            'roles',
                            [
                                'view roles' => 'View Roles',
                                'view any roles' => 'View Any Roles',
                                'create roles' => 'Create Roles',
                                'update roles' => 'Edit Roles',
                                'delete roles' => 'Delete Roles',
                            ]
                        ),

                        self::permissionGroup(
                            'Permissions',
                            'permissions',
                            [
                                'view permissions' => 'View Permissions',
                                'view any permissions' => 'View Any Permissions',
                                'create permissions' => 'Create Permissions',
                                'update permissions' => 'Edit Permissions',
                                'delete permissions' => 'Delete Permissions',
                            ]
                        ),

                        self::permissionGroup(
                            'POS',
                            'pos',
                            [
                                'access pos' => 'Access POS',
                            ]
                        ),
                    ])
                    ->compact()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    protected static function permissionGroup(
        string $label,
        string $key,
        array $options
    ): Grid {
        return Grid::make(5)
            ->schema([
                TextInput::make("group_{$key}")
                    ->label('')
                    ->default($label)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpan(1),

                CheckboxList::make("permissions.{$key}")
                    ->label('')
                    ->options($options)
                    ->columns(4)
                    ->bulkToggleable()
                    ->columnSpan(4),
            ])
            ->columnSpanFull();
    }
}