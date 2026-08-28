<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the old approval foreign key first.
        if (Schema::hasColumn('sales', 'approved_by')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
            });

            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('approved_by');
            });
        }

        // Remove old approval fields.
        Schema::table('sales', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('sales', 'approved_at')) {
                $columns[] = 'approved_at';
            }

            if (Schema::hasColumn('sales', 'rejection_reason')) {
                $columns[] = 'rejection_reason';
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        // Add customer order delivery fields.
        if (! Schema::hasColumn('sales', 'delivery_address')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->text('delivery_address')->nullable();
            });
        }

        if (! Schema::hasColumn('sales', 'delivery_fee')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('delivery_fee', 10, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->text('rejection_reason')->nullable();
        });
    }
};
