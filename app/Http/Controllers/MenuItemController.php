<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        return response()->json(
            MenuItem::with('ingredients')->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        $menuItem = MenuItem::create($validated);

        return response()->json($menuItem->load('ingredients'), 201);
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

        return response()->json($menuItem->load('ingredients'));
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted successfully.']);
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

        return response()->json($menuItem->load('ingredients'));
    }

    public function detachIngredient(MenuItem $menuItem, $ingredientId)
    {
        $menuItem->ingredients()->detach($ingredientId);

        return response()->json($menuItem->load('ingredients'));
    }
}