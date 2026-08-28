<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Order Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Order / Invoice #')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('sale_date')
                            ->label('Order Date')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('fulfillment_status')
                            ->label('Fulfillment Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (?string $state): string => match ($state) {
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'preparing' => 'Preparing',
                                    'ready' => 'Ready',
                                    'out_for_delivery' => 'Out for Delivery',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                    default => ucfirst(
                                        str_replace('_', ' ', $state ?? 'Unknown')
                                    ),
                                }
                            )
                            ->color(
                                fn (?string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'preparing' => 'primary',
                                    'ready' => 'success',
                                    'out_for_delivery' => 'info',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'gray',
                                }
                            ),
                    ]),

                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Customer')
                            ->placeholder('Walk-in Customer'),

                        TextEntry::make('customer.email')
                            ->label('Email')
                            ->placeholder('Not provided'),

                        TextEntry::make('customer.phone')
                            ->label('Phone')
                            ->placeholder('Not provided'),
                    ]),

                Section::make('Delivery')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('deliveryZone.name')
                            ->label('Delivery Zone')
                            ->placeholder('Not specified'),

                        TextEntry::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->money('KES'),

                        TextEntry::make('delivery_address')
                            ->label('Delivery Address')
                            ->columnSpanFull()
                            ->placeholder('No delivery address'),

                        TextEntry::make('notes')
                            ->label('Customer Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                    ]),

                Section::make('Payment')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->badge()
                            ->placeholder('Not specified'),

                        TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge(),

                        TextEntry::make('amount_paid')
                            ->label('Amount Paid')
                            ->money('KES'),

                        TextEntry::make('advance_amount')
                            ->label('Advance Paid')
                            ->money('KES'),

                        TextEntry::make('balance_amount')
                            ->label('Balance')
                            ->money('KES'),

                        TextEntry::make('change_amount')
                            ->label('Change')
                            ->money('KES'),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Product')
                                    ->weight('bold'),

                                TextEntry::make('quantity')
                                    ->label('Qty'),

                                TextEntry::make('original_price')
                                    ->label('Original Price')
                                    ->money('KES'),

                                TextEntry::make('unit_price')
                                    ->label('Unit Price')
                                    ->money('KES'),

                                TextEntry::make('discount_amount')
                                    ->label('Discount')
                                    ->money('KES'),

                                TextEntry::make('line_total')
                                    ->label('Line Total')
                                    ->money('KES')
                                    ->weight('bold'),
                            ])
                            ->columns(6),
                    ]),

                Section::make('Order Summary')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('KES'),

                        TextEntry::make('discount')
                            ->label('Discount')
                            ->money('KES'),

                        TextEntry::make('tax')
                            ->label('Tax')
                            ->money('KES'),

                        TextEntry::make('delivery_fee')
                            ->label('Delivery')
                            ->money('KES'),

                        TextEntry::make('total_amount')
                            ->label('Grand Total')
                            ->money('KES')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
