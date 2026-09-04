<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Sale;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RiderDeliveryHistory extends Page
{
    protected string $view = 'filament.pages.rider-delivery-history';

    protected static ?string $title = 'Delivery History';

    protected static ?string $navigationLabel = 'Delivery History';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 2;

    public function getDriver(): ?Driver
    {
        return Driver::where('user_id', Auth::id())->first();
    }

    public function getDeliveredOrders()
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return collect();
        }

        return Sale::query()
            ->where('driver_id', $driver->id)
            ->where('fulfillment_status', 'delivered')
            ->with([
                'customer',
                'deliveryZone',
            ])
            ->latest('sale_date')
            ->get();
    }

    public function getTodayDeliveries()
    {
        return $this->getDeliveredOrders()
            ->filter(fn (Sale $order) => $order->sale_date?->isToday());
    }

    public function getTotalDeliveries(): int
    {
        return $this->getDeliveredOrders()->count();
    }

    public function getTodayCount(): int
    {
        return $this->getTodayDeliveries()->count();
    }
}