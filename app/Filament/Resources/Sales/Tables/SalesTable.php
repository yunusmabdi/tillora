<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Models\Driver;
use App\Services\SalesService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
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
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('driver.name')
                    ->label('Rider')
                    ->placeholder('Not Assigned')
                    ->searchable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'pending' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(
                        fn (string $state): string =>
                            match ($state) {
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid',
                                'pending' => 'Pending',
                                'failed' => 'Failed',
                                default => ucfirst($state),
                            }
                    ),

                TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('KES')
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('KES')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('KES')
                    ->sortable(),

                TextColumn::make('fulfillment_status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'preparing' => 'info',
                        'ready' => 'success',
                        'picked_up' => 'warning',
                        'out_for_delivery' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(
                        fn (string $state): string =>
                            match ($state) {
                                'pending' => 'Pending',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'picked_up' => 'Picked Up',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                                default => ucfirst($state),
                            }
                    ),

                TextColumn::make('sale_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])

            ->filters([

                SelectFilter::make('fulfillment_status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'picked_up' => 'Picked Up',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
            ])

            ->recordActions([

                /*
                |--------------------------------------------------------------------------
                | VIEW
                |--------------------------------------------------------------------------
                */

                ViewAction::make(),

                /*
                |--------------------------------------------------------------------------
                | ADMIN: PREPARING
                |--------------------------------------------------------------------------
                */

                Action::make('preparing')
                    ->label('Start Preparing')
                    ->icon('heroicon-o-fire')
                    ->color('info')
                    ->visible(
                        fn ($record): bool =>
                            $record->fulfillment_status === 'pending'
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        try {

                            app(SalesService::class)
                                ->updateFulfillmentStatus(
                                    $record,
                                    'preparing'
                                );

                            Notification::make()
                                ->title('Order is being prepared')
                                ->success()
                                ->send();

                        } catch (\Throwable $e) {

                            Notification::make()
                                ->title('Unable to update order')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                /*
                |--------------------------------------------------------------------------
                | ADMIN: READY
                |--------------------------------------------------------------------------
                */

                Action::make('ready')
                    ->label('Mark Ready')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn ($record): bool =>
                            $record->fulfillment_status === 'preparing'
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        try {

                            app(SalesService::class)
                                ->updateFulfillmentStatus(
                                    $record,
                                    'ready'
                                );

                            Notification::make()
                                ->title('Order marked ready')
                                ->success()
                                ->send();

                        } catch (\Throwable $e) {

                            Notification::make()
                                ->title('Unable to update order')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                /*
                |--------------------------------------------------------------------------
                | ADMIN: ASSIGN RIDER
                |--------------------------------------------------------------------------
                */

                Action::make('assign_rider')
                    ->label('Assign Rider')
                    ->icon('heroicon-o-user')
                    ->color('warning')
                    ->visible(
                        fn ($record): bool =>
                            $record->fulfillment_status === 'ready'
                            && ! $record->driver_id
                    )
                    ->form([

                        Select::make('driver_id')
                            ->label('Rider')
                            ->options(
                                fn () =>
                                    Driver::query()
                                        ->where('status', 'available')
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                    ])
                    ->action(function ($record, array $data) {

                        try {

                            app(SalesService::class)
                                ->assignDriver(
                                    $record,
                                    (int) $data['driver_id']
                                );

                            Notification::make()
                                ->title('Rider assigned successfully')
                                ->success()
                                ->send();

                        } catch (\Throwable $e) {

                            Notification::make()
                                ->title('Unable to assign rider')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                /*
                |--------------------------------------------------------------------------
                | ADMIN: CANCEL
                |--------------------------------------------------------------------------
                */

                Action::make('cancel')
                    ->label('Cancel Order')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn ($record): bool =>
                            ! in_array(
                                $record->fulfillment_status,
                                [
                                    'cancelled',
                                    'delivered',
                                ],
                                true
                            )
                    )
                    ->form([

                        Textarea::make('reason')
                            ->label('Cancellation Reason')
                            ->placeholder(
                                'Enter the reason for cancelling this order...'
                            )
                            ->required()
                            ->minLength(3)
                            ->rows(3),

                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
                    ->modalDescription(
                        'This action will cancel the order and restore any stock that was already issued.'
                    )
                    ->action(function ($record, array $data) {

                        try {

                            app(SalesService::class)
                                ->cancelSale(
                                    $record,
                                    $data['reason']
                                );

                            Notification::make()
                                ->title('Order cancelled')
                                ->success()
                                ->send();

                        } catch (\Throwable $e) {

                            Notification::make()
                                ->title('Unable to cancel order')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                /*
                |--------------------------------------------------------------------------
                | DELETE
                |--------------------------------------------------------------------------
                */

                DeleteAction::make()
                    ->visible(
                        fn ($record): bool =>
                            $record->fulfillment_status === 'cancelled'
                    ),
            ])

            ->toolbarActions([

                ActionGroup::make([]),

            ]);
    }
}