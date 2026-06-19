<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'payment_method')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_method')->default('Pay at Counter')->after('payment_status');
            });
        }

        DB::table('orders')
            ->whereNull('payment_method')
            ->orWhere('payment_method', '')
            ->update([
                'payment_method' => 'Pay at Counter',
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_method')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }
    }
};