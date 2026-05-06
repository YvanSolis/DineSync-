<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ingredient_id')
                ->constrained('ingredients')
                ->cascadeOnDelete();

            $table->decimal('quantity_received', 10, 2);
            $table->decimal('quantity_remaining', 10, 2);

            $table->decimal('unit_cost', 10, 2);

            $table->date('received_date');
            $table->date('expiry_date');

            $table->string('supplier')->nullable();
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};