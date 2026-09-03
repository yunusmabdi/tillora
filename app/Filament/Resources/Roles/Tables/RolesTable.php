<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),

                DeleteAction::make()
                    ->before(function ($record) {
                        abort_if(
                            in_array($record->name, [
                                'Super Admin',
                                'Admin',
                                'Manager',
                                'Cashier',
                                'Rider',
                            ]),
                            403,
                            'System roles cannot be deleted.'
                        );
                    }),
            ])
            ->defaultSort('name');
    }
}