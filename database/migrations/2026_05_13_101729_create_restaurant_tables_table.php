<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();

            $table->string('table_number')->unique();
            $table->integer('capacity')->default(4);

            $table->string('status')->default('available');
            // available, occupied, reserved, cleaning

            $table->integer('current_guest_count')->nullable();

            $table->foreignId('current_order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->foreignId('current_reservation_id')
                ->nullable()
                ->constrained('reservations')
                ->nullOnDelete();

            $table->timestamp('occupied_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};