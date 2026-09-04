<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Sale;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RiderNewOrders extends Page
{
    protected string $view = 'filament.pages.rider-new-orders';

    protected static ?string $title = 'New Orders';

    protected static ?string $navigationLabel = 'New Orders';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'new-orders';

    public static function getNavigationBadge(): ?string
    {
        $driver = Driver::where('user_id', Auth::id())->first();

        if (! $driver) {
            return null;
        }

        $count = Sale::query()
            ->where('driver_id', $driver->id)
            ->where('fulfillment_status', 'assigned')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function getDriver(): ?Driver
    {
        return Driver::where('user_id', Auth::id())->first();
    }

    public function getNewOrders()
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return collect();
        }

        return Sale::query()
            ->where('driver_id', $driver->id)
            ->where('fulfillment_status', 'assigned')
            ->with([
                'customer',
                'deliveryZone',
                'saleItems.product',
            ])
            ->latest('sale_date')
            ->get();
    }

    public function acceptOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        if (! $driver) {
            abort(403);
        }

        $order = Sale::query()
            ->where('id', $orderId)
            ->where('driver_id', $driver->id)
            ->where('fulfillment_status', 'assigned')
            ->first();

        if (! $order) {
            Notification::make()
                ->title('Order unavailable')
                ->body('This order is no longer available.')
                ->danger()
                ->send();

            return;
        }

        $order->update([
            'fulfillment_status' => 'accepted',
        ]);

        Notification::make()
            ->title('Order accepted')
            ->body("Order {$order->invoice_number} has been accepted.")
            ->success()
            ->send();

        $this->redirect(
            RiderOrderTracking::getUrl([
                'record' => $order->id,
            ])
        );
    }

    public function declineOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        if (! $driver) {
            abort(403);
        }

        $order = Sale::query()
            ->where('id', $orderId)
            ->where('driver_id', $driver->id)
            ->where('fulfillment_status', 'assigned')
            ->first();

        if (! $order) {
            Notification::make()
                ->title('Order unavailable')
                ->body('This order is no longer available.')
                ->danger()
                ->send();

            return;
        }

        $order->update([
            'fulfillment_status' => 'declined',
        ]);

        Notification::make()
            ->title('Order declined')
            ->body("Order {$order->invoice_number} was declined.")
            ->warning()
            ->send();
    }
}