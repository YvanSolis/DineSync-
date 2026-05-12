<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();

            $table->string('restaurant_name')->default('Chef Oppa');
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();

            $table->string('opening_days')->default('Monday - Sunday');
            $table->string('opening_time')->default('10:00 AM');
            $table->string('closing_time')->default('9:00 PM');

            $table->decimal('reservation_fee', 10, 2)->default(300);

            $table->string('gcash_name')->nullable();
            $table->string('gcash_number')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();

            $table->text('map_embed_url')->nullable();
            $table->text('google_maps_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};