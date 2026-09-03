<?php

namespace App\Filament\Resources\RolePermissions\Pages;

use App\Filament\Resources\RolePermissions\RolePermissionResource;
use Filament\Resources\Pages\EditRecord;

class EditRolePermission extends EditRecord
{
    protected static string $resource = RolePermissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $permissions = $this->record
            ->permissions()
            ->pluck('name')
            ->toArray();

        $data['permissions'] = $this->groupPermissions($permissions);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(): void
    {
        $permissions = $this->form->getState()['permissions'] ?? [];

        $selectedPermissions = collect($permissions)
            ->flatten()
            ->filter()
            ->values()
            ->all();

        $this->record->syncPermissions($selectedPermissions);
    }

    protected function groupPermissions(array $permissions): array
    {
        $groups = [
            'products' => [
                'view products',
                'view any products',
                'create products',
                'update products',
                'delete products',
            ],

            'orders' => [
                'view orders',
                'view any orders',
                'create orders',
                'update orders',
                'delete orders',
                'prepare orders',
                'mark orders ready',
            ],

            'customers' => [
                'view customers',
                'view any customers',
                'create customers',
                'update customers',
                'delete customers',
            ],

            'inventory' => [
                'view inventory',
                'adjust stock',
                'transfer stock',
                'manage stock',
            ],

            'purchases' => [
                'view purchases',
                'view any purchases',
                'create purchases',
                'update purchases',
                'approve purchases',
            ],

            'delivery_zones' => [
                'view delivery zones',
                'view any delivery zones',
                'create delivery zones',
                'update delivery zones',
                'delete delivery zones',
            ],

            'drivers' => [
                'view drivers',
                'view any drivers',
                'create drivers',
                'update drivers',
                'delete drivers',
                'assign orders to drivers',
            ],

            'driver_delivery' => [
                'view assigned orders',
                'accept orders',
                'pickup orders',
                'start delivery',
                'deliver orders',
            ],

            'users' => [
                'view users',
                'view any users',
                'create users',
                'update users',
                'delete users',
            ],

            'roles' => [
                'view roles',
                'view any roles',
                'create roles',
                'update roles',
                'delete roles',
            ],

            'permissions' => [
                'view permissions',
                'view any permissions',
                'create permissions',
                'update permissions',
                'delete permissions',
            ],

            'pos' => [
                'access pos',
            ],
        ];

        $result = [];

        foreach ($groups as $group => $groupPermissions) {
            $result[$group] = array_values(
                array_intersect($groupPermissions, $permissions)
            );
        }

        return $result;
    }
}