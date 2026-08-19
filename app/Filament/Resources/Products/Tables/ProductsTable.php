<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->imageSize(50),
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('cost_price')
                    ->label('Cost Price')
                    ->money('KES', true)
                    ->sortable(),
                
                TextColumn::make('selling_price')
                    ->label('Sell Price')
                    ->money('KES', true)
                    ->sortable(),
                    
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(function ($record) {

                        if ($record->stock_quantity <= 0) {
                            return 'danger';
                        }

                        if ($record->stock_quantity <= $record->minimum_stock) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->sortable(),
                
                TextColumn::make('minimum_stock')
                    ->label('Min Stock')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('maximum_stock')
                    ->label('Max Stock')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                    
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
