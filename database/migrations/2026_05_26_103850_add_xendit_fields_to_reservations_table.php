<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable()->after('payment_status');
            $table->string('xendit_external_id')->nullable()->after('xendit_invoice_id');
            $table->text('xendit_invoice_url')->nullable()->after('xendit_external_id');
            $table->timestamp('paid_at')->nullable()->after('xendit_invoice_url');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'xendit_invoice_id',
                'xendit_external_id',
                'xendit_invoice_url',
                'paid_at',
            ]);
        });
    }
};