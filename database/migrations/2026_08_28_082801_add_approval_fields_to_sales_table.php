<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add order approval fields to sales.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            // Admin who approved/rejected the order
            $table->foreignId('approved_by')
                ->nullable()
                ->after('fulfillment_status')
                ->constrained('users')
                ->nullOnDelete();

            // When the order was approved/rejected
            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            // Reason when an order is rejected
            $table->text('rejection_reason')
                ->nullable()
                ->after('approved_at');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'approved_by',
                'approved_at',
                'rejection_reason',
            ]);
        });
    }
};