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

    public function attachIngredient(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity_required' => 'required|numeric|min:0.01',
        ]);

        $menuItem->ingredients()->syncWithoutDetaching([
            $validated['ingredient_id'] => [
                'quantity_required' => $validated['quantity_required']
            ]
        ]);

        return response()->json([
            'message' => 'Ingredient linked successfully.',
            'menu_item' => $menuItem->load('ingredients')
        ]);
    }
}