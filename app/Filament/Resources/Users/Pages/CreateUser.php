<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['role_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $roleId = $this->form->getState()['role_id'] ?? null;

        if ($roleId) {
            $this->record->syncRoles([(int) $roleId]);
        }
    }
}