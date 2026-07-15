<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refill_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('refill_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('ingredient_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('quantity', 10, 2);

            $table->string('unit', 50)->nullable();

            $table->timestamps();

            $table->index('refill_id');
            $table->index('ingredient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refill_items');
    }
};