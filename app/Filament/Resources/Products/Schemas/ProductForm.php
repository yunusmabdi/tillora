<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Components\FileUpload;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('General Information')
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->visible(fn ($operation) => $operation === 'edit')
                            ->maxLength(50),

                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            })
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Product Image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->prefix('KES')
                            ->required(),

                        TextInput::make('selling_price')
                            ->label('Selling Price')
                            ->numeric()
                            ->gte('cost_price')
                            ->prefix('KES')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Discount')
                    ->description('Configure automatic discounts for this product.')
                    ->schema([

                        Toggle::make('discount_active')
                            ->label('Enable Discount')
                            ->live(),

                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage (%)',
                                'fixed' => 'Fixed Amount (KES)',
                            ])
                            ->visible(fn ($get) => $get('discount_active'))
                            ->required(fn ($get) => $get('discount_active')),

                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($get) => $get('discount_active'))
                            ->required(fn ($get) => $get('discount_active')),

                    ])
                    ->columns(3),

                Section::make('Inventory')
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('minimum_stock')
                            ->label('Minimum Stock')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Low stock alert will trigger at this level.'),

                        TextInput::make('maximum_stock')
                            ->label('Maximum Stock')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Optional maximum capacity for this product.'),

                        TextInput::make('unit')
                            ->label('Unit')
                            ->default('Piece')
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}