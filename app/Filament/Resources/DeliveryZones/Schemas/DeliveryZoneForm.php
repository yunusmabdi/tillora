<?php

namespace App\Filament\Resources\DeliveryZones\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Delivery Zone')
                    ->description(
                        'Define the distance range and delivery charge for this zone.'
                    )
                    ->schema([

                        TextInput::make('name')
                            ->label('Zone Name')
                            ->placeholder('e.g. Nairobi CBD')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder(
                                'Optional description of this delivery area.'
                            )
                            ->rows(3)
                            ->maxLength(1000),

                        TextInput::make('min_distance')
                            ->label('Minimum Distance')
                            ->suffix('KM')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('max_distance')
                            ->label('Maximum Distance')
                            ->suffix('KM')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->gt('min_distance'),

                        TextInput::make('fee')
                            ->label('Delivery Fee')
                            ->prefix('KSh')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText(
                                'Inactive zones will not be used when calculating delivery charges.'
                            ),
                    ])
                    ->columns(2),
            ]);
    }
}