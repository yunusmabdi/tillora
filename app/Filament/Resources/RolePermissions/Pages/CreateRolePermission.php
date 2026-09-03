<?php

namespace App\Filament\Resources\RolePermissions\Pages;

use App\Filament\Resources\RolePermissions\RolePermissionResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRolePermission extends CreateRecord
{
    protected static string $resource = RolePermissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['permission_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $permissionIds = $this->data['permission_ids'] ?? [];

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $this->record->syncPermissions($permissions);
    }
}