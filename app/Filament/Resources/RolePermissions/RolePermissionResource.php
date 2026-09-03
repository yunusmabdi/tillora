<?php

namespace App\Filament\Resources\RolePermissions;

use App\Filament\Resources\RolePermissions\Pages\CreateRolePermission;
use App\Filament\Resources\RolePermissions\Pages\EditRolePermission;
use App\Filament\Resources\RolePermissions\Pages\ListRolePermissions;
use App\Filament\Resources\RolePermissions\Schemas\RolePermissionForm;
use App\Filament\Resources\RolePermissions\Tables\RolePermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RolePermissionResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedShieldCheck;

    protected static UnitEnum|string|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RolePermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolePermissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRolePermissions::route('/'),
            'create' => CreateRolePermission::route('/create'),
            'edit' => EditRolePermission::route('/{record}/edit'),
        ];
    }
}