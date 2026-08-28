<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Services\SalesService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sale_date', 'desc')

            ->columns([

                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Walk-in Customer'),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'partially_paid',
                        'danger' => 'pending',
                    ])
                    ->formatStateUsing(
                        fn ($state) => match ($state) {
                            'paid' => 'Paid',
                            'partially_paid' => 'Partially Paid',
                            'pending' => 'Pending',
                            default => ucfirst(
                                str_replace('_', ' ', $state ?? '')
                            ),
                        }
                    ),

                TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('KES')
                    ->alignEnd(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('KES')
                    ->alignEnd(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('KES')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('fulfillment_status')
                    ->label('Order Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'preparing',
                        'primary' => 'ready',
                        'gray' => 'out_for_delivery',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(
                        fn ($state) => match ($state) {
                            'pending' => 'Pending',
                            'preparing' => 'Preparing',
                            'ready' => 'Ready',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            default => ucfirst(
                                str_replace('_', ' ', $state ?? '')
                            ),
                        }
                    ),

                TextColumn::make('sale_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
            ])

            ->filters([

                SelectFilter::make('fulfillment_status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options([
                        'pending' => 'Pending',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                    ]),
            ])

            ->recordActions([

                ViewAction::make(),

                ActionGroup::make([

                    Action::make('preparing')
                        ->label('Mark Preparing')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->visible(
                            fn ($record) =>
                                $record->fulfillment_status === 'pending'
                        )
                        ->action(function ($record) {
                            try {
                                app(SalesService::class)
                                    ->updateFulfillmentStatus(
                                        $record,
                                        'preparing'
                                    );

                                Notification::make()
                                    ->title('Order marked as preparing.')
                                    ->success()
                                    ->send();

                            } catch (\RuntimeException $e) {
                                Notification::make()
                                    ->title('Unable to update order.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('ready')
                        ->label('Mark Ready')
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->visible(
                            fn ($record) =>
                                $record->fulfillment_status === 'preparing'
                        )
                        ->action(function ($record) {
                            try {
                                app(SalesService::class)
                                    ->updateFulfillmentStatus(
                                        $record,
                                        'ready'
                                    );

                                Notification::make()
                                    ->title('Order marked as ready.')
                                    ->success()
                                    ->send();

                            } catch (\RuntimeException $e) {
                                Notification::make()
                                    ->title('Unable to update order.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('out_for_delivery')
                        ->label('Out for Delivery')
                        ->icon('heroicon-o-truck')
                        ->color('gray')
                        ->visible(
                            fn ($record) =>
                                $record->fulfillment_status === 'ready'
                        )
                        ->action(function ($record) {
                            try {
                                app(SalesService::class)
                                    ->updateFulfillmentStatus(
                                        $record,
                                        'out_for_delivery'
                                    );

                                Notification::make()
                                    ->title('Order is out for delivery.')
                                    ->success()
                                    ->send();

                            } catch (\RuntimeException $e) {
                                Notification::make()
                                    ->title('Unable to update order.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('delivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn ($record) =>
                                $record->fulfillment_status === 'out_for_delivery'
                        )
                        ->action(function ($record) {
                            try {
                                app(SalesService::class)
                                    ->updateFulfillmentStatus(
                                        $record,
                                        'delivered'
                                    );

                                Notification::make()
                                    ->title('Order marked as delivered.')
                                    ->success()
                                    ->send();

                            } catch (\RuntimeException $e) {
                                Notification::make()
                                    ->title('Unable to update order.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('cancel')
                        ->label('Cancel Order')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->schema([
                            Textarea::make('cancellation_note')
                                ->label('Cancellation Reason')
                                ->required()
                                ->maxLength(1000),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Order')
                        ->modalDescription(
                            'The order will be cancelled and any deducted stock will be returned.'
                        )
                        ->action(function (array $data, $record) {
                            try {
                                app(SalesService::class)
                                    ->cancelSale(
                                        $record,
                                        $data['cancellation_note']
                                    );

                                Notification::make()
                                    ->title('Order cancelled.')
                                    ->success()
                                    ->send();

                            } catch (\RuntimeException $e) {
                                Notification::make()
                                    ->title('Unable to cancel order.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray')
                    ->visible(
                        fn ($record) =>
                            ! in_array(
                                $record->fulfillment_status,
                                ['delivered', 'cancelled'],
                                true
                            )
                    ),

                Action::make('receipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(
                        fn ($record) =>
                            route('receipt.show', ['sale' => $record])
                    )
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->visible(
                        fn ($record) =>
                            $record->fulfillment_status === 'cancelled'
                    ),
            ]);
    }
}