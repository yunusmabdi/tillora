<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RiderDashboard extends Page
{
    protected string $view = 'filament.pages.rider-dashboard';

    protected static ?string $title = 'Rider Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    public function getDriver(): ?Driver
    {
        return Driver::where('user_id', Auth::id())->first();
    }

    public function getAssignedOrders()
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return collect();
        }

        return Sale::query()
            ->where('driver_id', $driver->id)
            ->whereIn('fulfillment_status', [
                'assigned',
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

    public function getPendingOrders()
    {
        return $this->getAssignedOrders()
            ->where('fulfillment_status', 'assigned');
    }

    public function getActiveOrder(): ?Sale
    {
        return $this->getAssignedOrders()
            ->whereIn('fulfillment_status', [
                'accepted',
                'picked_up',
                'out_for_delivery',
            ])
            ->sortByDesc('sale_date')
            ->first();
    }

    public function getDeliveredToday()
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return collect();
        }

        return Sale::query()
            ->where('driver_id', $driver->id)
            ->where('fulfillment_status', 'delivered')
            ->whereDate('sale_date', today())
            ->with([
                'customer',
                'deliveryZone',
            ])
            ->latest('sale_date')
            ->get();
    }

    public function getTodayCount(): int
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return 0;
        }

        return Sale::query()
            ->where('driver_id', $driver->id)
            ->whereDate('sale_date', today())
            ->count();
    }

    public function getDeliveredCount(): int
    {
        return $this->getDeliveredToday()->count();
    }

    public function getActiveCount(): int
    {
        return $this->getAssignedOrders()
            ->whereIn('fulfillment_status', [
                'accepted',
                'picked_up',
                'out_for_delivery',
            ])
            ->count();
    }

    public function acceptOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return;
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
            ->body("Order {$order->invoice_number} is now assigned to you.")
            ->success()
            ->send();
    }

    public function declineOrder(int $orderId): void
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return;
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

    public function updateStatus(int $orderId, string $status): void
    {
        $driver = $this->getDriver();

        if (! $driver) {
            return;
        }

        $allowedTransitions = [
            'accepted' => 'picked_up',
            'picked_up' => 'out_for_delivery',
            'out_for_delivery' => 'delivered',
        ];

        $order = Sale::query()
            ->where('id', $orderId)
            ->where('driver_id', $driver->id)
            ->first();

        if (! $order) {
            Notification::make()
                ->title('Order not found')
                ->danger()
                ->send();

            return;
        }

        $currentStatus = $order->fulfillment_status;

        if (
            ! isset($allowedTransitions[$currentStatus]) ||
            $allowedTransitions[$currentStatus] !== $status
        ) {
            Notification::make()
                ->title('Invalid status update')
                ->body('This action cannot be performed right now.')
                ->danger()
                ->send();

            return;
        }

        /*
         * Delivery should only be completed when the order
         * has been fully paid.
         */
        if ($status === 'delivered' && $order->payment_status !== 'paid') {
            Notification::make()
                ->title('Payment required')
                ->body('This order cannot be marked as delivered until it is fully paid.')
                ->danger()
                ->send();

            return;
        }

        $order->update([
            'fulfillment_status' => $status,
        ]);

        Notification::make()
            ->title('Delivery updated')
            ->body(
                "Order {$order->invoice_number} is now " .
                str_replace('_', ' ', $status) . '.'
            )
            ->success()
            ->send();
    }
}