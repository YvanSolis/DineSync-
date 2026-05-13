<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\MenuItemIngredient;
use Illuminate\Http\Request;

class MenuItemIngredientController extends Controller
{
    public function index()
    {
        return response()->json(
            MenuItemIngredient::with(['menuItem', 'ingredient'])->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity_required' => 'required|numeric|min:0.01',
        ]);

        $menuItem = MenuItem::findOrFail($validated['menu_item_id']);

        $menuItem->ingredients()->syncWithoutDetaching([
            $validated['ingredient_id'] => [
                'quantity_required' => $validated['quantity_required'],
            ],
        ]);

        MenuItem::refreshAllAvailability();

        return response()->json([
            'message' => 'Ingredient linked successfully.',
            'menu_item' => $menuItem->fresh()->load('ingredients'),
        ], 201);
    }

    public function show(MenuItemIngredient $menuItemIngredient)
    {
        return response()->json(
            $menuItemIngredient->load(['menuItem', 'ingredient'])
        );
    }

    public function update(Request $request, MenuItemIngredient $menuItemIngredient)
    {
        $validated = $request->validate([
            'quantity_required' => 'required|numeric|min:0.01',
        ]);

        $menuItemIngredient->update([
            'quantity_required' => $validated['quantity_required'],
        ]);

        MenuItem::refreshAllAvailability();

        return response()->json([
            'message' => 'Ingredient requirement updated successfully.',
            'menu_item_ingredient' => $menuItemIngredient->fresh()->load(['menuItem', 'ingredient']),
        ]);
    }

    public function destroy(MenuItemIngredient $menuItemIngredient)
    {
        $menuItemIngredient->delete();

        MenuItem::refreshAllAvailability();

        return response()->json([
            'message' => 'Ingredient removed successfully.',
        ]);
    }

    public function link(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity_required' => 'required|numeric|min:0.01',
        ]);

        $menuItem->ingredients()->syncWithoutDetaching([
            $validated['ingredient_id'] => [
                'quantity_required' => $validated['quantity_required'],
            ],
        ]);

        MenuItem::refreshAllAvailability();

        return response()->json([
            'message' => 'Ingredient linked successfully.',
            'menu_item' => $menuItem->fresh()->load('ingredients'),
        ], 201);
    }

    public function unlink(MenuItem $menuItem, Ingredient $ingredient)
    {
        $menuItem->ingredients()->detach($ingredient->id);

        MenuItem::refreshAllAvailability();

        return response()->json([
            'message' => 'Ingredient removed successfully.',
            'menu_item' => $menuItem->fresh()->load('ingredients'),
        ]);
    }

    public function sync(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required|numeric|min:0.01',
        ]);

        $syncData = [];

        foreach ($validated['ingredients'] as $ingredient) {
            $syncData[$ingredient['ingredient_id']] = [
                'quantity_required' => $ingredient['quantity_required'],
            ];
        }

        $menuItem->ingredients()->sync($syncData);

        MenuItem::refreshAllAvailability();

        return response()->json([
            'message' => 'Ingredients synced successfully.',
            'menu_item' => $menuItem->fresh()->load('ingredients'),
        ]);
    }
}