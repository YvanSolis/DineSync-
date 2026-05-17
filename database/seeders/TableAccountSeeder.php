<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TableAccountSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            User::updateOrCreate(
                [
                    'email' => "table{$i}@dinesync.com",
                ],
                [
                    'name' => "Table {$i}",
                    'password' => Hash::make('DinesyncOpportuneX2026'),
                    'role' => 'table_customer',
                    'table_number' => $i,
                    'is_online' => false,
                    'last_seen_at' => null,
                ]
            );
        }
    }
}