<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $groups = [
            'Users' => 'users',
            'Roles' => 'roles',
            'Permissions' => 'permissions',
            'Orders' => 'orders',
            'Riders' => 'riders',
            'Products' => 'products',
            'Customers' => 'customers',
        ];

        $components = [
            TextInput::make('name')
                ->label('Role Name')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('e.g. Warehouse Manager'),
        ];

        foreach ($groups as $label => $search) {
            $options = $permissions
                ->filter(fn ($permission) => str_contains($permission->name, $search))
                ->mapWithKeys(fn ($permission) => [
                    $permission->id => ucwords($permission->name),
                ])
                ->toArray();

            if (empty($options)) {
                continue;
            }

            $components[] = Section::make($label)
                ->schema([
                    CheckboxList::make("permissions_{$search}")
                        ->label('Permissions')
                        ->options($options)
                        ->columns(2)
                        ->bulkToggleable()
                        ->dehydrated(false),
                ])
                ->collapsible();
        }

        // POS is a special single permission.
        $posPermission = $permissions
            ->firstWhere('name', 'access pos');

        if ($posPermission) {
            $components[] = Section::make('POS')
                ->schema([
                    CheckboxList::make('permissions_pos')
                        ->label('Permissions')
                        ->options([
                            $posPermission->id => 'Access POS',
                        ])
                        ->bulkToggleable()
                        ->dehydrated(false),
                ])
                ->collapsible();
        }

        return $schema->components($components);
    }
}