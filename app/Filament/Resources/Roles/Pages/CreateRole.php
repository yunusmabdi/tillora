<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
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