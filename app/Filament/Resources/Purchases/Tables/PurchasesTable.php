<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Models\StockMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('purchase_number')
                    ->label('Purchase No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.company_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'Draft',
                        'warning' => 'Ordered',
                        'success' => 'Received',
                        'danger' => 'Cancelled',
                    ]),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('KES')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->since(),

            ])

            ->filters([
                //
            ])

            ->recordActions([

                /*
                 * CHANGE STATUS
                 *
                 * Draft:
                 *   → Ordered
                 *   → Received
                 *   → Cancelled
                 *
                 * Ordered:
                 *   → Received
                 */
                Action::make('changeStatus')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')

                    ->visible(fn ($record) => in_array(
                        $record->status,
                        ['Draft', 'Ordered']
                    ))

                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options(fn ($record) => $record->status === 'Draft'
                                ? [
                                    'Ordered' => 'Ordered',
                                    'Received' => 'Received',
                                    'Cancelled' => 'Cancelled',
                                ]
                                : [
                                    'Received' => 'Received',
                                ]
                            )
                            ->required()
                            ->native(false),
                    ])

                    ->requiresConfirmation()

                    ->action(function ($record, array $data) {

                        $newStatus = $data['status'];

                        /*
                         * Make sure the purchase hasn't already
                         * been received by another action/session.
                         */
                        if (
                            $record->status === 'Received' ||
                            $record->status === 'Cancelled'
                        ) {
                            Notification::make()
                                ->title('Purchase can no longer be changed.')
                                ->warning()
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($record, $newStatus) {

                            /*
                             * Only increase stock when the purchase
                             * becomes Received.
                             */
                            if ($newStatus === 'Received') {

                                foreach ($record->items as $item) {

                                    // Increase product stock
                                    $item->product->increment(
                                        'stock_quantity',
                                        $item->quantity
                                    );

                                    // Record stock movement
                                    StockMovement::create([
                                        'product_id' => $item->product_id,
                                        'type' => 'IN',
                                        'quantity' => $item->quantity,
                                        'reference_type' => 'Purchase',
                                        'reference_id' => $record->id,
                                        'user_id' => auth()->id(),
                                        'description' =>
                                            'Stock received from purchase '
                                            . $record->purchase_number,
                                    ]);
                                }
                            }

                            /*
                             * Update purchase status.
                             */
                            $record->update([
                                'status' => $newStatus,
                            ]);
                        });

                        Notification::make()
                            ->title("Purchase status changed to {$newStatus}.")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

            ])

            ->toolbarActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

            ]);
    }
}