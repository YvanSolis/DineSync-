<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    private array $categories = [
        'Authentic Ala Carte Meals',
        'Dishes',
        'Korean Kitchen Specials',
        'Noodles',
        'Salad',
        'Maki & Sushi',
        'Jeon Series',
        'Tteokbokki Series',
    ];

    public function index(Request $request)
    {
        $selectedCategory = $request->query('category');
        $search = $request->query('search');

        $query = MenuItem::with('ingredients')->orderBy('name');

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return response()->json([
            'categories' => $this->categories,
            'selected_category' => $selectedCategory,
            'menu_items' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        if (!in_array($validated['category'], $this->categories)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $menuItem = MenuItem::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'image' => $validated['image'] ?? null,
            'is_available' => $validated['is_available'] ?? true,
        ]);

        return response()->json($menuItem->load('ingredients'), 201);
    }

    public function show(MenuItem $menuItem)
    {
        return response()->json($menuItem->load('ingredients'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
        ]);

        if (isset($validated['category']) && !in_array($validated['category'], $this->categories)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $menuItem->update($validated);

        return response()->json($menuItem->load('ingredients'));
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    public function attachIngredient(Request $request, MenuItem $menuItem)
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

        return response()->json($menuItem->load('ingredients'));
    }

    public function detachIngredient(MenuItem $menuItem, $ingredientId)
    {
        $menuItem->ingredients()->detach($ingredientId);

        return response()->json($menuItem->load('ingredients'));
    }
}