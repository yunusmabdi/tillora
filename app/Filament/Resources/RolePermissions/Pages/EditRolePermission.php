<?php

namespace App\Filament\Resources\RolePermissions\Pages;

use App\Filament\Resources\RolePermissions\RolePermissionResource;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRolePermission extends EditRecord
{
    protected static string $resource = RolePermissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permission_ids'] = $this->record
            ->permissions()
            ->pluck('permissions.id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['permission_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        $permissionIds = $this->data['permission_ids'] ?? [];

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $this->record->syncPermissions($permissions);
    }
}