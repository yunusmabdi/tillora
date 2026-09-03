<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Driver;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Status')
                    ->description('Update the fulfillment status of this customer order.')
                    ->schema([
                        Select::make('fulfillment_status')
                            ->label('Fulfillment Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columnSpanFull(),

                Section::make('Delivery Assignment')
                    ->description('Assign a rider to this order.')
                    ->schema([
                        Select::make('driver_id')
                            ->label('Rider')
                            ->relationship(
                                name: 'driver',
                                titleAttribute: 'name'
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a rider'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}