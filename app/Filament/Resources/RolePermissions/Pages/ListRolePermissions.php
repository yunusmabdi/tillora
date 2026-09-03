<?php

namespace App\Filament\Resources\RolePermissions\Pages;

use App\Filament\Resources\RolePermissions\RolePermissionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListRolePermissions extends ListRecords
{
    protected static string $resource = RolePermissionResource::class;

    protected Width|string|null $maxContentWidth = 'full';
}