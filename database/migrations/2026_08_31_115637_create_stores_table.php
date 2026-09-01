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
        Schema::create('stores', function (Blueprint $table) {

            $table->id();

            // Store information
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Physical address
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            // Store coordinates
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Store status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};