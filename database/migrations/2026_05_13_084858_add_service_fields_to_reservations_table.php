<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('table_number')->nullable()->after('guest_count');
            $table->timestamp('arrived_at')->nullable()->after('status');
            $table->timestamp('seated_at')->nullable()->after('arrived_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'table_number',
                'arrived_at',
                'seated_at',
            ]);
        });
    }
};