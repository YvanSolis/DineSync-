<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'table_number')) {
                $table->unsignedTinyInteger('table_number')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('table_number');
            }

            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('is_online');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'table_number')) {
                $table->dropColumn('table_number');
            }

            if (Schema::hasColumn('users', 'is_online')) {
                $table->dropColumn('is_online');
            }

            if (Schema::hasColumn('users', 'last_seen_at')) {
                $table->dropColumn('last_seen_at');
            }
        });
    }
};