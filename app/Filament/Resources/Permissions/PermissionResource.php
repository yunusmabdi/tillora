<?php

namespace App\Filament\Resources\Permissions;

use BackedEnum;
use App\Filament\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Resources\Permissions\Tables\PermissionsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Permissions';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasPermissionTo('view permissions') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->hasPermissionTo('create permissions') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->hasPermissionTo('update permissions') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->hasPermissionTo('delete permissions') ?? false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}