<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_id'] = $this->record
            ->roles()
            ->where('guard_name', 'web')
            ->value('id');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['role_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $roleId = $this->form->getState()['role_id'] ?? null;

        if ($roleId) {
            $this->record->syncRoles([(int) $roleId]);
        }
    }
}