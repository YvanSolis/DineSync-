<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $settings = RestaurantSetting::current();

        $bestSellers = [];

        if (
            Schema::hasTable('order_items') &&
            Schema::hasColumn('order_items', 'menu_item_id') &&
            Schema::hasColumn('order_items', 'quantity')
        ) {
            $bestSellers = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.id',
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.price',
                    'menu_items.image',
                    'menu_items.is_available',
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->groupBy(
                    'menu_items.id',
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.price',
                    'menu_items.image',
                    'menu_items.is_available'
                )
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'category' => $item->category ?? 'Menu Item',
                        'price' => $item->price ?? 0,
                        'image' => $item->image,
                        'status' => $item->is_available ? 'Available' : 'Unavailable',
                        'total_sold' => $item->total_sold ?? 0,
                        'description' => 'One of the most ordered dishes by customers. A popular Chef Oppa favorite.',
                    ];
                })
                ->toArray();
        }

        $recommendedToday = MenuItem::query()
            ->where('is_available', true)
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category ?? 'Menu Item',
                    'price' => $item->price ?? 0,
                    'image' => $item->image,
                    'status' => $item->is_available ? 'Available' : 'Unavailable',
                    'description' => 'Recommended meal available today.',
                ];
            })
            ->toArray();

        $availableSpecials = MenuItem::query()
            ->where('is_available', true)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category ?? 'Menu Item',
                    'price' => $item->price ?? 0,
                    'image' => $item->image,
                    'status' => $item->is_available ? 'Available' : 'Unavailable',
                    'description' => 'Available special from Chef Oppa.',
                ];
            })
            ->toArray();

        if (empty($bestSellers)) {
            $bestSellers = $recommendedToday;
        }

        $latestReservation = Reservation::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();

        return view('customer.home', compact(
            'settings',
            'bestSellers',
            'recommendedToday',
            'availableSpecials',
            'latestReservation'
        ));
    }
}