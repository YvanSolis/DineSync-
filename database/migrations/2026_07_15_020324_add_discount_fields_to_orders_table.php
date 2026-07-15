<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->enum('discount_type', [
                'none',
                'senior',
                'pwd'
            ])->default('none')
              ->after('payment_method');

            $table->unsignedInteger('qualified_diners')
                  ->default(0)
                  ->after('discount_type');

            $table->unsignedInteger('total_diners')
                  ->default(0)
                  ->after('qualified_diners');

            $table->string('discount_holder_name')
                  ->nullable()
                  ->after('total_diners');

            $table->string('discount_id_number')
                  ->nullable()
                  ->after('discount_holder_name');

            $table->decimal('vat_exempt_amount',10,2)
                  ->default(0)
                  ->after('total_amount');

            $table->decimal('discount_amount',10,2)
                  ->default(0)
                  ->after('vat_exempt_amount');

            $table->decimal('final_amount',10,2)
                  ->default(0)
                  ->after('discount_amount');

            $table->foreignId('discount_verified_by')
                  ->nullable()
                  ->after('final_amount')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('discount_verified_at')
                  ->nullable()
                  ->after('discount_verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign(['discount_verified_by']);

            $table->dropColumn([
                'discount_type',
                'qualified_diners',
                'total_diners',
                'discount_holder_name',
                'discount_id_number',
                'vat_exempt_amount',
                'discount_amount',
                'final_amount',
                'discount_verified_by',
                'discount_verified_at',
            ]);

        });
    }
};