<?php

namespace App\Filament\Resources\Roles;

use BackedEnum;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Filament\Resources\Roles\Tables\RolesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasPermissionTo('view roles') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->hasPermissionTo('create roles') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->hasPermissionTo('update roles') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->hasPermissionTo('delete roles') ?? false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}