<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DriverInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rider Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Rider'),

                        TextEntry::make('phone'),

                        TextEntry::make('email'),

                        TextEntry::make('vehicle_type')
                            ->label('Vehicle'),

                        TextEntry::make('vehicle_number')
                            ->label('Vehicle Number'),

                        TextEntry::make('status')
                            ->badge(),
                    ])
                    ->columns(2),
            ]);
    }
}