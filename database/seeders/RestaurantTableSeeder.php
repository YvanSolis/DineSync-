<?php

namespace Database\Seeders;

use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['table_number' => '1', 'capacity' => 4],
            ['table_number' => '2', 'capacity' => 4],
            ['table_number' => '3', 'capacity' => 4],
            ['table_number' => '4', 'capacity' => 6],
            ['table_number' => '5', 'capacity' => 2],
            ['table_number' => '6', 'capacity' => 4],
            ['table_number' => '7', 'capacity' => 6],
            ['table_number' => '8', 'capacity' => 4],
        ];

        foreach ($tables as $table) {
            RestaurantTable::updateOrCreate(
                ['table_number' => $table['table_number']],
                [
                    'capacity' => $table['capacity'],
                    'status' => 'available',
                    'current_guest_count' => null,
                    'current_order_id' => null,
                    'current_reservation_id' => null,
                    'occupied_at' => null,
                    'notes' => null,
                ]
            );
        }
    }
}