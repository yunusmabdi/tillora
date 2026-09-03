<?php

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Resources\Drivers\DriverResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use App\Filament\Resources\Drivers\Schemas\DriverInfolist;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    public function infolist(Schema $schema): Schema
    {
        return DriverInfolist::configure($schema);
    }
}