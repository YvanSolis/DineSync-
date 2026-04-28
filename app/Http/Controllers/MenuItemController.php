<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        return response()->json(
            MenuItem::with('ingredients')->get()
        );
    }

    public function store(Request $request)
    {
        // Create menu item
        $menuItem = MenuItem::create($request->only([
            'name',
            'category',
            'price',
            'image',
            'is_available'
        ]));

        // Attach ingredients (NEW)
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredient) {
                $menuItem->ingredients()->attach(
                    $ingredient['id'],
                    ['quantity_required' => $ingredient['quantity_required']]
                );
            }
        }

        return response()->json($menuItem->load('ingredients'));
    }

    public function show(MenuItem $menuItem)
    {
        return response()->json($menuItem->load('ingredients'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $menuItem->update($request->only([
            'name',
            'category',
            'price',
            'image',
            'is_available'
        ]));

        // Sync ingredients (NEW)
        if ($request->has('ingredients')) {
            $syncData = [];

            foreach ($request->ingredients as $ingredient) {
                $syncData[$ingredient['id']] = [
                    'quantity_required' => $ingredient['quantity_required']
                ];
            }

            $menuItem->ingredients()->sync($syncData);
        }

        return response()->json($menuItem->load('ingredients'));
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json(['message' => 'Deleted']);
    }
}