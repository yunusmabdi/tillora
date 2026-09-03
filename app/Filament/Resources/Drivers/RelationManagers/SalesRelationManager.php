<?php

namespace App\Filament\Resources\Drivers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';

    protected static ?string $title = 'Assigned Orders';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('deliveryZone.name')
                    ->label('Delivery Zone'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('KES'),

                TextColumn::make('fulfillment_status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('sale_date')
                    ->label('Order Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}