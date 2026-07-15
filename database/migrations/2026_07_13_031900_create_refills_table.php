<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('order_item_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('menu_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('requested_by')->nullable();

            $table->string('table_number')->nullable();

            $table->enum('status', [
                'requested',
                'preparing',
                'ready',
                'served',
                'cancelled',
            ])->default('requested');

            $table->text('notes')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->foreign('requested_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['status', 'created_at']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refills');
    }
};