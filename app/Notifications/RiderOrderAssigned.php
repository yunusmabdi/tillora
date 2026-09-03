<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class RiderOrderAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public Sale $sale,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_assigned',
            'sale_id' => $this->sale->id,
            'invoice_number' => $this->sale->invoice_number,
            'message' => "Order {$this->sale->invoice_number} has been assigned to you.",
        ];
    }
}