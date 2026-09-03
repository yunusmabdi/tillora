<?php

namespace App\Filament\Resources\RolePermissions\Pages;

use App\Filament\Resources\RolePermissions\RolePermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRolePermission extends CreateRecord
{
    protected static string $resource = RolePermissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $permissions = $this->form->getState()['permissions'] ?? [];

        $selectedPermissions = collect($permissions)
            ->flatten()
            ->filter()
            ->values()
            ->all();

        $this->record->syncPermissions($selectedPermissions);
    }
}