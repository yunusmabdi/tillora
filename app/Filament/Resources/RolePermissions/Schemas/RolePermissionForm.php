<?php

namespace App\Filament\Resources\RolePermissions\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RolePermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $groups = $permissions->groupBy(function (Permission $permission) {
            return explode('.', $permission->name, 2)[0];
        });

        $permissionRows = [];

        foreach ($groups as $groupName => $groupPermissions) {
            $groupKey = Str::slug($groupName, '_');

            $options = $groupPermissions->mapWithKeys(
                function (Permission $permission) {
                    $parts = explode('.', $permission->name, 2);

                    $action = $parts[1] ?? $parts[0];

                    return [
                        $permission->id => Str::headline($action),
                    ];
                }
            )->toArray();

            $permissionRows[] = Grid::make([
                'default' => 1,
                'md' => 5,
            ])
                ->schema([
                    Text::make(Str::headline($groupName))
                        ->weight('bold')
                        ->columnSpan(1),

                    CheckboxList::make("permissions.{$groupKey}")
                        ->label('')
                        ->options($options)
                        ->columns([
                            'default' => 2,
                            'sm' => 3,
                            'lg' => 4,
                        ])
                        ->bulkToggleable()
                        ->dehydrated(true)
                        ->columnSpan(4),
                ])
                ->columnSpanFull();
        }

        return $schema
            ->components([
                Section::make('Role Details')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->label('Role Name')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->placeholder('e.g. Sales Manager'),

                            TextInput::make('guard_name')
                                ->default('web')
                                ->required()
                                ->hidden(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Permissions')
                    ->description(
                        'Choose what users assigned to this role can access.'
                    )
                    ->schema($permissionRows)
                    ->columnSpanFull()
                    ->compact(),
            ])
            ->columns(1);
    }
}