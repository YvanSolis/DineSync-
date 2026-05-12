<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuController extends Controller
{
    public function index()
    {
        $query = DB::table('menu_items')
            ->select(
                'id',
                'name',
                'price'
            )
            ->orderBy('name');

        if (Schema::hasColumn('menu_items', 'description')) {
            $query->addSelect('description');
        }

        if (Schema::hasColumn('menu_items', 'category')) {
            $query->addSelect('category');
        }

        if (Schema::hasColumn('menu_items', 'is_available')) {
            $query->addSelect('is_available');
        }

        if (Schema::hasColumn('menu_items', 'status')) {
            $query->addSelect('status');
        }

        if (Schema::hasColumn('menu_items', 'image')) {
            $query->addSelect('image');
        }

        $menuItems = $query->get()->map(function ($item) {
            $isAvailable = true;

            if (property_exists($item, 'is_available')) {
                $isAvailable = (bool) $item->is_available;
            }

            if (property_exists($item, 'status')) {
                $isAvailable = strtolower($item->status) === 'available';
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'description' => $item->description ?? 'No description available.',
                'category' => $item->category ?? 'Uncategorized',
                'is_available' => $isAvailable,
                'image' => $item->image ?? null,
            ];
        });

        $categories = $menuItems
            ->pluck('category')
            ->unique()
            ->values();

        $groupedMenuItems = $menuItems->groupBy('category');

        return view('customer.menu', compact(
            'categories',
            'groupedMenuItems'
        ));
    }
}