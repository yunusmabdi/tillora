<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchase Information')
                    ->schema([
                        TextInput::make('purchase_number')
                            ->disabled()
                            ->dehydrated(),

                        Select::make('supplier_id')
                            ->relationship('supplier', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('purchase_date')
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->options([
                                'Draft' => 'Draft',
                                'Ordered' => 'Ordered',
                                'Received' => 'Received',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Draft')
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->dehydrated(fn (string $operation): bool => $operation !== 'edit'),

                        Textarea::make('note')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Purchase Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Add Product')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = Product::find($state);

                                        if (! $product) {
                                            return;
                                        }

                                        $set('unit_cost', $product->cost_price);

                                        $set(
                                            'line_total',
                                            ($get('quantity') ?? 0) * $product->cost_price
                                        );
                                    }),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set(
                                            'line_total',
                                            ($get('quantity') ?? 0) * ($get('unit_cost') ?? 0)
                                        );
                                    }),

                                TextInput::make('unit_cost')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set(
                                            'line_total',
                                            ($get('quantity') ?? 0) * ($get('unit_cost') ?? 0)
                                        );
                                    }),

                                TextInput::make('line_total')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}