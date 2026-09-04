<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RiderDashboard extends Page
{
    protected static ?string $title = 'Rider Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.rider-dashboard';

    /**
     * Get the driver associated with the logged-in rider.
     */
    public function getDriver(): ?Driver
    {
        return Driver::where('user_id', Auth::id())->first();
    }

    /**
     * Get newly assigned orders waiting for acceptance.
     */
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
            ])
            ->latest('sale_date')
            ->get();
    }

    /**
     * Compatibility with dashboard Blade.
     */
    public function getPendingOrders()
    {
        return $this->getNewOrders();
    }

    /**
     * Get orders currently being handled by the rider.
     */
    public function getActiveOrders()
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return collect();
        }

        return Sale::query()
            ->where('driver_id', $driver->id)
            ->whereIn('fulfillment_status', [
                'accepted',
                'picked_up',
                'out_for_delivery',
            ])
            ->with([
                'customer',
                'deliveryZone',
            ])
            ->latest('sale_date')
            ->get();
    }

    /**
     * Compatibility with dashboard Blade.
     */
    public function getActiveOrder(): ?Sale
    {
        return $this->getActiveOrders()->first();
    }

    /**
     * Get all completed deliveries.
     */
    public function getCompletedOrders()
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

    /**
     * Get deliveries completed today.
     */
    public function getDeliveredToday()
    {
        return $this->getCompletedOrders()
            ->filter(
                fn (Sale $order) =>
                    $order->sale_date?->isToday()
            );
    }

    /**
     * Compatibility with dashboard Blade.
     */
    public function getTodayCount(): int
    {
        return $this->getDeliveredToday()->count();
    }

    /**
     * Number of newly assigned orders.
     */
    public function getNewOrdersCount(): int
    {
        return $this->getNewOrders()->count();
    }

    /**
     * Number of active deliveries.
     */
    public function getActiveOrdersCount(): int
    {
        return $this->getActiveOrders()->count();
    }

    /**
     * Number of completed deliveries.
     */
    public function getCompletedOrdersCount(): int
    {
        return $this->getCompletedOrders()->count();
    }

    /**
     * Accept an assigned order.
     */
    public function acceptOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        $order = Sale::query()
            ->where('id', $orderId)
            ->where('driver_id', $driver?->id)
            ->where('fulfillment_status', 'assigned')
            ->firstOrFail();

        $order->update([
            'fulfillment_status' => 'accepted',
        ]);

        Notification::make()
            ->title('Order accepted')
            ->body("Order {$order->invoice_number} has been accepted.")
            ->success()
            ->send();

        $this->redirect(
            \App\Filament\Pages\RiderOrderDetails::getUrl([
                'record' => $order->id,
            ])
        );
    }

    /**
     * Decline an assigned order.
     */
    public function declineOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        $order = Sale::query()
            ->where('id', $orderId)
            ->where('driver_id', $driver?->id)
            ->where('fulfillment_status', 'assigned')
            ->firstOrFail();

        $order->update([
            'fulfillment_status' => 'declined',
        ]);

        Notification::make()
            ->title('Order declined')
            ->body("Order {$order->invoice_number} has been declined.")
            ->warning()
            ->send();
    }

    /**
     * Open an order's details/tracking page.
     */
    public function viewOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        $order = Sale::query()
            ->where('id', $orderId)
            ->where('driver_id', $driver?->id)
            ->firstOrFail();

        $this->redirect(
            \App\Filament\Pages\RiderOrderDetails::getUrl([
                'record' => $order->id,
            ])
        );
    }
}