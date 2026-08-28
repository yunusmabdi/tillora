<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {

            $table->id();

            // Zone name shown to admins/customers
            $table->string('name');

            // Optional explanation of the zone
            $table->text('description')->nullable();

            // Distance range in kilometres
            $table->decimal('min_distance', 8, 2);

            $table->decimal('max_distance', 8, 2);

            // Delivery charge
            $table->decimal('fee', 12, 2)->default(0);

            // Whether this zone can currently be used
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};