<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_item_ingredients', function (Blueprint $table) {

            $table->boolean('is_refillable')
                ->default(false)
                ->after('quantity_required');

            $table->decimal('refill_quantity', 10, 2)
                ->nullable()
                ->after('is_refillable');

        });
    }

    public function down(): void
    {
        Schema::table('menu_item_ingredients', function (Blueprint $table) {

            $table->dropColumn([
                'is_refillable',
                'refill_quantity',
            ]);

        });
    }
};