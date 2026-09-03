<?php

namespace App\Filament\Resources\Drivers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rider')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('vehicle_type')
                    ->label('Vehicle')
                    ->formatStateUsing(
                        fn ($state) => ucfirst($state ?? '-')
                    ),

                TextColumn::make('vehicle_number')
                    ->label('Vehicle Number')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'busy',
                        'danger' => 'inactive',
                    ]),

                TextColumn::make('sales_count')
                    ->label('Assigned Orders')
                    ->counts('sales')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}