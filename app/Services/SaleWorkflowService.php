<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleWorkflowService
{
    /**
     * Approve an order.
     *
     * Only orders awaiting admin approval can be approved.
     */
    public function approve(
        Sale $sale,
        User $admin
    ): Sale {

        if ($sale->fulfillment_status !== 'awaiting_approval') {
            throw new RuntimeException(
                'Only orders awaiting approval can be approved.'
            );
        }

        return DB::transaction(function () use ($sale, $admin) {

            $sale->update([
                'fulfillment_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->recordHistory(
                sale: $sale,
                status: 'approved',
                note: 'Order approved by admin.',
                userId: $admin->id,
            );

            return $sale->fresh([
                'customer',
                'items.product',
                'statusHistories.updatedBy',
                'approvedBy',
            ]);
        });
    }

    /**
     * Reject an order.
     *
     * Only orders awaiting admin approval can be rejected.
     */
    public function reject(
        Sale $sale,
        User $admin,
        string $reason
    ): Sale {

        if ($sale->fulfillment_status !== 'awaiting_approval') {
            throw new RuntimeException(
                'Only orders awaiting approval can be rejected.'
            );
        }

        if (trim($reason) === '') {
            throw new RuntimeException(
                'A rejection reason is required.'
            );
        }

        return DB::transaction(function () use (
            $sale,
            $admin,
            $reason
        ) {

            $reason = trim($reason);

            $sale->update([
                'fulfillment_status' => 'rejected',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->recordHistory(
                sale: $sale,
                status: 'rejected',
                note: $reason,
                userId: $admin->id,
            );

            return $sale->fresh([
                'customer',
                'items.product',
                'statusHistories.updatedBy',
                'approvedBy',
            ]);
        });
    }

    /**
     * Move an approved order into preparation.
     */
    public function startPreparing(
        Sale $sale,
        ?User $user = null
    ): Sale {

        $this->ensureStatus(
            $sale,
            'approved'
        );

        return $this->changeStatus(
            sale: $sale,
            status: 'preparing',
            note: 'Order is being prepared.',
            user: $user,
        );
    }

    /**
     * Mark an order as ready for delivery.
     */
    public function markReady(
        Sale $sale,
        ?User $user = null
    ): Sale {

        $this->ensureStatus(
            $sale,
            'preparing'
        );

        return $this->changeStatus(
            sale: $sale,
            status: 'ready',
            note: 'Order is ready for delivery.',
            user: $user,
        );
    }

    /**
     * Mark an order as out for delivery.
     */
    public function markOutForDelivery(
        Sale $sale,
        ?User $user = null
    ): Sale {

        $this->ensureStatus(
            $sale,
            'ready'
        );

        return $this->changeStatus(
            sale: $sale,
            status: 'out_for_delivery',
            note: 'Order has been dispatched for delivery.',
            user: $user,
        );
    }

    /**
     * Mark an order as delivered.
     */
    public function markDelivered(
        Sale $sale,
        ?User $user = null
    ): Sale {

        $this->ensureStatus(
            $sale,
            'out_for_delivery'
        );

        return DB::transaction(function () use ($sale, $user) {

            $sale->update([
                'fulfillment_status' => 'delivered',
            ]);

            $this->recordHistory(
                sale: $sale,
                status: 'delivered',
                note: 'Order delivered successfully.',
                userId: $user?->id,
            );

            return $sale->fresh([
                'customer',
                'items.product',
                'statusHistories.updatedBy',
                'approvedBy',
            ]);
        });
    }

    /**
     * Cancel an order.
     */
    public function cancel(
        Sale $sale,
        ?User $user = null,
        ?string $reason = null
    ): Sale {

        if (in_array($sale->fulfillment_status, [
            'delivered',
            'cancelled',
        ], true)) {
            throw new RuntimeException(
                'This order can no longer be cancelled.'
            );
        }

        return $this->changeStatus(
            sale: $sale,
            status: 'cancelled',
            note: $reason
                ? trim($reason)
                : 'Order cancelled.',
            user: $user,
        );
    }

    /**
     * Change fulfillment status and create history record.
     */
    protected function changeStatus(
        Sale $sale,
        string $status,
        string $note,
        ?User $user = null
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $status,
            $note,
            $user
        ) {

            $sale->update([
                'fulfillment_status' => $status,
            ]);

            $this->recordHistory(
                sale: $sale,
                status: $status,
                note: $note,
                userId: $user?->id,
            );

            return $sale->fresh([
                'customer',
                'items.product',
                'statusHistories.updatedBy',
                'approvedBy',
            ]);
        });
    }

    /**
     * Record a fulfillment status history entry.
     */
    protected function recordHistory(
        Sale $sale,
        string $status,
        ?string $note,
        ?int $userId
    ): void {

        $sale->statusHistories()->create([
            'status' => $status,
            'note' => $note,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Ensure the order is currently in the expected state.
     */
    protected function ensureStatus(
        Sale $sale,
        string $expectedStatus
    ): void {

        if ($sale->fulfillment_status !== $expectedStatus) {

            throw new RuntimeException(
                "Order must be '{$expectedStatus}' before it can be moved to the next stage."
            );
        }
    }
}