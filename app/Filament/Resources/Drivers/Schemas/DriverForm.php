<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Driver Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->required()
                            ->tel()
                            ->unique(ignoreRecord: true),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true),

                        Select::make('status')
                            ->options([
                                'available' => 'Available',
                                'busy' => 'Busy',
                                'inactive' => 'Inactive',
                            ])
                            ->default('available')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Vehicle Information')
                    ->schema([
                        TextInput::make('vehicle_type')
                            ->label('Vehicle Type')
                            ->placeholder('Motorbike, Car, Van...'),

                        TextInput::make('vehicle_number')
                            ->label('Vehicle Registration')
                            ->placeholder('KDA 123A'),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
            ]);
    }
}