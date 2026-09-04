<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RiderOrderDetails extends Page
{
    protected string $view = 'filament.pages.rider-order-details';

    protected static ?string $title = 'Order Details';

    protected static ?string $navigationLabel = 'Order Details';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'order/{record}';

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

    public function getDriver(): ?Driver
    {
        return Driver::where('user_id', Auth::id())->first();
    }

    public function acceptOrder(): void
    {
        if ($this->order->fulfillment_status !== 'assigned') {
            $this->refreshOrder();
            return;
        }

        $this->order->update([
            'fulfillment_status' => 'accepted',
        ]);

        $this->refreshOrder();

        Notification::make()
            ->title('Order accepted')
            ->body("Order {$this->order->invoice_number} has been accepted.")
            ->success()
            ->send();
    }

    public function declineOrder(): void
    {
        if ($this->order->fulfillment_status !== 'assigned') {
            $this->refreshOrder();
            return;
        }

        $this->order->update([
            'fulfillment_status' => 'declined',
        ]);

        $this->refreshOrder();

        Notification::make()
            ->title('Order declined')
            ->body("Order {$this->order->invoice_number} was declined.")
            ->warning()
            ->send();
    }

    public function updateStatus(string $status): void
    {
        $allowedTransitions = [
            'accepted' => 'picked_up',
            'picked_up' => 'out_for_delivery',
            'out_for_delivery' => 'delivered',
        ];

        $currentStatus = $this->order->fulfillment_status;

        if (
            ! isset($allowedTransitions[$currentStatus]) ||
            $allowedTransitions[$currentStatus] !== $status
        ) {
            Notification::make()
                ->title('Invalid action')
                ->body('This delivery cannot be moved to that status.')
                ->danger()
                ->send();

            return;
        }

        if (
            $status === 'delivered' &&
            $this->order->payment_status !== 'paid'
        ) {
            Notification::make()
                ->title('Payment required')
                ->body('The order must be fully paid before it can be delivered.')
                ->danger()
                ->send();

            return;
        }

        $this->order->update([
            'fulfillment_status' => $status,
        ]);

        $this->refreshOrder();

        Notification::make()
            ->title('Delivery updated')
            ->body(
                "Order {$this->order->invoice_number} is now " .
                str_replace('_', ' ', $status) . '.'
            )
            ->success()
            ->send();
    }

    protected function refreshOrder(): void
    {
        $driver = $this->getDriver();

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

    public function getStatusLabel(): string
    {
        return str_replace(
            '_',
            ' ',
            ucfirst($this->order->fulfillment_status)
        );
    }

    public function getBalance(): float
    {
        return max(
            0,
            (float) $this->order->total_amount -
            (float) ($this->order->advance_amount ?? 0)
        );
    }
}