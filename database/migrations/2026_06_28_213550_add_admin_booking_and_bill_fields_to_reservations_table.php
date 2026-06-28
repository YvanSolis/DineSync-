<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'created_by_role')) {
                $table->string('created_by_role')->default('customer')->after('user_id');
            }

            if (!Schema::hasColumn('reservations', 'reservation_fee_billing_type')) {
                $table->string('reservation_fee_billing_type')->default('online_payment')->after('reservation_fee_amount');
            }

            if (!Schema::hasColumn('reservations', 'reservation_fee_added_to_bill')) {
                $table->boolean('reservation_fee_added_to_bill')->default(false)->after('reservation_fee_billing_type');
            }

            if (!Schema::hasColumn('reservations', 'reservation_fee_added_at')) {
                $table->timestamp('reservation_fee_added_at')->nullable()->after('reservation_fee_added_to_bill');
            }

            if (!Schema::hasColumn('reservations', 'reservation_fee_order_id')) {
                $table->unsignedBigInteger('reservation_fee_order_id')->nullable()->after('reservation_fee_added_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $columns = [
                'created_by_role',
                'reservation_fee_billing_type',
                'reservation_fee_added_to_bill',
                'reservation_fee_added_at',
                'reservation_fee_order_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};