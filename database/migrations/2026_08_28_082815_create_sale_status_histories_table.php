<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create sale/order status history.
     */
    public function up(): void
    {
        Schema::create('sale_status_histories', function (Blueprint $table) {

            $table->id();

            // Sale/order being tracked
            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            // Status at this point in the order lifecycle
            $table->string('status');

            // Optional explanation
            $table->text('note')
                ->nullable();

            // Admin/user who caused the status change
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at')
                ->useCurrent();

            // Useful for querying order timelines
            $table->index(['sale_id', 'status']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_status_histories');
    }
};