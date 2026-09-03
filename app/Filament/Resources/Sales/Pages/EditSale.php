<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Driver;
use App\Notifications\RiderOrderAssigned;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Order updated')
            ->body('The order has been updated successfully.')
            ->success()
            ->send();

        if (
            $this->record->wasChanged('driver_id') &&
            $this->record->driver_id
        ) {
            $driver = Driver::with('user')
                ->find($this->record->driver_id);

            if ($driver?->user) {
                $driver->user->notify(
                    new RiderOrderAssigned($this->record)
                );
            }
        }
    }
}