<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $permissionIds = $this->record
            ->permissions()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $permissions = Permission::whereIn('id', $permissionIds)
            ->get();

        $groups = [
            'users' => 'users',
            'roles' => 'roles',
            'permissions' => 'permissions',
            'orders' => 'orders',
            'riders' => 'riders',
            'products' => 'products',
            'customers' => 'customers',
        ];

        foreach ($groups as $field => $search) {
            $data["permissions_{$field}"] = $permissions
                ->filter(fn ($permission) =>
                    str_contains($permission->name, $search)
                )
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        }

        $data['permissions_pos'] = $permissions
            ->filter(fn ($permission) =>
                $permission->name === 'access pos'
            )
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();

        $permissionIds = [];

        foreach ($state as $key => $value) {
            if (
                str_starts_with($key, 'permissions_')
                && is_array($value)
            ) {
                $permissionIds = array_merge($permissionIds, $value);
            }
        }

        $this->record->syncPermissions(
            Permission::whereIn('id', $permissionIds)->get()
        );
    }
}