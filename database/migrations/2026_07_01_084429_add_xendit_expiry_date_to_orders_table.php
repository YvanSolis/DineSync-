<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'xendit_expiry_date')) {
                $table->timestamp('xendit_expiry_date')->nullable()->after('xendit_invoice_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'xendit_expiry_date')) {
                $table->dropColumn('xendit_expiry_date');
            }
        });
    }
};