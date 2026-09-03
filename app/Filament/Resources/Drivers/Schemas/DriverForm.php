<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(50),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Select::make('vehicle_type')
                    ->options([
                        'motorbike' => 'Motorbike',
                        'car' => 'Car',
                        'van' => 'Van',
                        'truck' => 'Truck',
                    ])
                    ->required(),

                TextInput::make('vehicle_number')
                    ->label('Vehicle Number')
                    ->required()
                    ->maxLength(50),

                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'busy' => 'Busy',
                        'inactive' => 'Inactive',
                    ])
                    ->default('available')
                    ->required(),

                Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}