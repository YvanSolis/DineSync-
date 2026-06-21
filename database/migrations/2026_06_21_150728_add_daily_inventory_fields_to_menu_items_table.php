<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('inventory_type')->default('per_order')->after('meal_type');
            $table->integer('daily_limit')->nullable()->after('inventory_type');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'inventory_type',
                'daily_limit',
            ]);
        });
    }
};