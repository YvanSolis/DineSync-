<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'xendit_invoice_id')) {
                $table->string('xendit_invoice_id')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('reservations', 'xendit_external_id')) {
                $table->string('xendit_external_id')->nullable()->after('xendit_invoice_id');
            }

            if (!Schema::hasColumn('reservations', 'xendit_invoice_url')) {
                $table->text('xendit_invoice_url')->nullable()->after('xendit_external_id');
            }

            if (!Schema::hasColumn('reservations', 'xendit_expiry_date')) {
                $table->timestamp('xendit_expiry_date')->nullable()->after('xendit_invoice_url');
            }

            if (!Schema::hasColumn('reservations', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('xendit_expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'paid_at')) {
                $table->dropColumn('paid_at');
            }

            if (Schema::hasColumn('reservations', 'xendit_expiry_date')) {
                $table->dropColumn('xendit_expiry_date');
            }

            if (Schema::hasColumn('reservations', 'xendit_invoice_url')) {
                $table->dropColumn('xendit_invoice_url');
            }

            if (Schema::hasColumn('reservations', 'xendit_external_id')) {
                $table->dropColumn('xendit_external_id');
            }

            if (Schema::hasColumn('reservations', 'xendit_invoice_id')) {
                $table->dropColumn('xendit_invoice_id');
            }
        });
    }
};