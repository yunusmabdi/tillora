<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge(),

                TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])

            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->defaultSort('name');
    }
}