<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'xendit_invoice_id')) {
                $table->string('xendit_invoice_id')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'xendit_external_id')) {
                $table->string('xendit_external_id')->nullable()->after('xendit_invoice_id');
            }

            if (!Schema::hasColumn('orders', 'xendit_invoice_url')) {
                $table->text('xendit_invoice_url')->nullable()->after('xendit_external_id');
            }

            if (!Schema::hasColumn('orders', 'xendit_expiry_date')) {
                $table->timestamp('xendit_expiry_date')->nullable()->after('xendit_invoice_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'payment_status',
                'xendit_invoice_id',
                'xendit_external_id',
                'xendit_invoice_url',
                'xendit_expiry_date',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};