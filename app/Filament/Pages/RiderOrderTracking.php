<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RiderOrderTracking extends Page
{
    protected string $view = 'filament.pages.rider-order-tracking';

    protected static ?string $title = 'Order Tracking';

    protected static ?string $navigationLabel = 'Order Tracking';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'order-tracking/{record}';

    public Sale $order;

    public function mount(int|string $record): void
    {
        $driver = Driver::where('user_id', Auth::id())->first();

            abort_unless($driver !== null, 403);

        $this->order = Sale::query()
            ->where('id', $record)
            ->where('driver_id', $driver->id)
            ->with([
                'customer',
                'deliveryZone',
                'saleItems.product',
            ])
            ->firstOrFail();
    }

    public function startRide(): void
    {
        if ($this->order->fulfillment_status !== 'accepted') {
            return;
        }

        $this->order->update([
            'fulfillment_status' => 'picked_up',
        ]);

        $this->refreshOrder();

        Notification::make()
            ->title('Ride Started')
            ->body("Order {$this->order->invoice_number} is now picked up.")
            ->success()
            ->send();
    }

    public function startDelivery(): void
    {
        if ($this->order->fulfillment_status !== 'picked_up') {
            return;
        }

        $this->order->update([
            'fulfillment_status' => 'out_for_delivery',
        ]);

        $this->refreshOrder();

        Notification::make()
            ->title('Delivery Started')
            ->body("Order {$this->order->invoice_number} is now out for delivery.")
            ->success()
            ->send();
    }

    public function markDelivered(): void
    {
        if ($this->order->fulfillment_status !== 'out_for_delivery') {
            return;
        }

        if ($this->order->payment_status !== 'paid') {
            Notification::make()
                ->title('Payment Required')
                ->body('The customer must complete the outstanding payment before delivery can be closed.')
                ->danger()
                ->send();

            return;
        }

        $this->order->update([
            'fulfillment_status' => 'delivered',
        ]);

        $this->refreshOrder();

        Notification::make()
            ->title('Order Delivered')
            ->body("Order {$this->order->invoice_number} has been completed.")
            ->success()
            ->send();
    }

    public function getBalance(): float
    {
        return max(
            0,
            (float) $this->order->total_amount -
            (float) ($this->order->advance_amount ?? 0)
        );
    }

    protected function refreshOrder(): void
    {
        $driver = Driver::where('user_id', Auth::id())->first();

        abort_unless($driver !== null, 403);

        $this->order = Sale::query()
            ->where('id', $this->order->id)
            ->where('driver_id', $driver->id)
            ->with([
                'customer',
                'deliveryZone',
                'saleItems.product',
            ])
            ->firstOrFail();
    }
}