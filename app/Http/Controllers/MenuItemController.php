<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    private array $categories = [
        'Authentic Ala Carte Meals',
        'Dishes',
        'Korean Kitchen Specials',
        'Chef Oppa Special',
        'Noodles',
        'Salad',
        'Maki & Sushi',
        'Jeon Series',
        'Tteokbokki Series',
        'Drinks',
        'Unlimited',
        'Extras',
    ];

    private array $flavorTags = [
        'spicy',
        'sweet',
        'savory',
        'mild',
        'sour',
        'creamy',
        'refreshing',
        'salty',
        'crispy',
        'cheesy',
        'rich',
        'smoky',
        'umami',
        'tangy',
        'fried',
        'grilled',
        'seafood',
        'meaty',
        'broth',
        'fermented',
    ];

    private array $mealTypes = [
        'set',
        'main',
        'side',
        'drink',
        'dessert',
        'snack',
        'soup',
        'hotpot',
        'noodle',
        'sushi',
        'salad',
        'extra',
        'alcohol',
    ];

    public function index(Request $request)
    {
        $selectedCategory = $request->query('category');
        $search = $request->query('search');

        $query = MenuItem::with([
                'ingredients' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->orderBy('name');

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $menuItems = $query->get();

        return response()->json([
            'categories' => $this->categories,
            'flavor_tags_options' => $this->flavorTags,
            'meal_type_options' => $this->mealTypes,
            'selected_category' => $selectedCategory,
            'menu_items' => $menuItems->map(function ($item) {
                return $this->formatMenuItemResponse($item);
            })->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',

            'flavor_tags' => 'nullable|array',
            'flavor_tags.*' => 'string|in:' . implode(',', $this->flavorTags),
            'meal_type' => 'nullable|string|in:' . implode(',', $this->mealTypes),

            'is_available' => 'nullable|boolean',
        ]);

        if (!in_array($validated['category'], $this->categories, true)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->uploadImageToSupabase($request->file('image'));
        }

        $isCustom = $validated['category'] === 'Chef Oppa Special';

        $menuItem = MenuItem::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $imageUrl,

            'flavor_tags' => $validated['flavor_tags'] ?? [],
            'meal_type' => $validated['meal_type'] ?? 'main',

            // Keep this NOT NULL for database compatibility.
            // Availability is still ingredient-based.
            'inventory_type' => $isCustom ? 'custom' : 'per_order',
            'daily_limit' => null,

            'is_available' => $isCustom ? true : false,
        ]);

        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse($this->freshMenuItem($menuItem)),
            201
        );
    }

    public function show(MenuItem $menuItem)
    {
        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse($this->freshMenuItem($menuItem))
        );
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_available' => 'sometimes|boolean',

            'flavor_tags' => 'sometimes|nullable|array',
            'flavor_tags.*' => 'string|in:' . implode(',', $this->flavorTags),
            'meal_type' => 'sometimes|nullable|string|in:' . implode(',', $this->mealTypes),
        ]);

        if (isset($validated['category']) && !in_array($validated['category'], $this->categories, true)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $updateData = [];

        foreach (['name', 'category', 'description', 'price', 'meal_type'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        if ($request->has('flavor_tags')) {
            $updateData['flavor_tags'] = $validated['flavor_tags'] ?? [];
        }

        if ($request->hasFile('image')) {
            $updateData['image'] = $this->uploadImageToSupabase($request->file('image'));
        }

        $finalCategory = $updateData['category'] ?? $menuItem->category;

        if ($finalCategory === 'Chef Oppa Special') {
            $updateData['inventory_type'] = 'custom';
            $updateData['daily_limit'] = null;
        } else {
            // IMPORTANT:
            // inventory_type column is NOT NULL in your database.
            // Do not set this to null.
            $updateData['inventory_type'] = $menuItem->inventory_type ?: 'per_order';
            $updateData['daily_limit'] = null;
        }

        $menuItem->update($updateData);

        if ($request->has('is_available') && $request->boolean('is_available') === false) {
            $menuItem->forceFill([
                'is_available' => false,
            ])->saveQuietly();
        } else {
            $this->refreshAvailability($menuItem);
        }

        return response()->json(
            $this->formatMenuItemResponse($this->freshMenuItem($menuItem))
        );
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

        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse($this->freshMenuItem($menuItem))
        );
    }

    public function detachIngredient(MenuItem $menuItem, $ingredientId)
    {
        $menuItem->ingredients()->detach($ingredientId);

        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse($this->freshMenuItem($menuItem))
        );
    }

    private function freshMenuItem(MenuItem $menuItem): MenuItem
    {
        return MenuItem::with([
                'ingredients' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->findOrFail($menuItem->id);
    }

    private function uploadImageToSupabase($file): string
    {
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY');
        $bucket = env('SUPABASE_STORAGE_BUCKET', 'menu-items');

        if (!$supabaseUrl || !$serviceRoleKey || !$bucket) {
            abort(500, 'Supabase storage configuration is missing.');
        }

        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filePath = $filename . '-' . Str::random(12) . '.' . $extension;

        $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$filePath}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceRoleKey,
            'apikey' => $serviceRoleKey,
            'Content-Type' => $file->getMimeType(),
            'x-upsert' => 'true',
        ])->withBody(
            file_get_contents($file->getRealPath()),
            $file->getMimeType()
        )->post($uploadUrl);

        if (!$response->successful()) {
            abort(500, 'Failed to upload image to Supabase Storage: ' . $response->body());
        }

        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$filePath}";
    }

    private function refreshAvailability(MenuItem $menuItem): void
    {
        $menuItem = MenuItem::with('ingredients')->find($menuItem->id);

        if (!$menuItem) {
            return;
        }

        if ($menuItem->category === 'Chef Oppa Special') {
            $menuItem->forceFill([
                'inventory_type' => 'custom',
                'daily_limit' => null,
                'is_available' => true,
            ])->saveQuietly();

            return;
        }

        $menuItem->forceFill([
            // Keep old value, but make sure it is never null.
            'inventory_type' => $menuItem->inventory_type ?: 'per_order',
            'daily_limit' => null,
            'is_available' => $menuItem->computeAvailability(),
        ])->saveQuietly();
    }

    private function formatMenuItemResponse(MenuItem $item): array
    {
        $ingredients = $item->relationLoaded('ingredients')
            ? $item->ingredients
            : collect();

        $maxOrderQuantity = $item->max_order_quantity;
        $stockLabel = $item->stock_label;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'category' => $item->category,
            'price' => (float) $item->price,
            'image' => $item->image,
            'image_url' => $item->image_url,
            'is_available' => (bool) $item->is_available,

            'flavor_tags' => $item->flavor_tags ?? [],
            'meal_type' => $item->meal_type,

            'inventory_type' => $item->inventory_type ?: 'per_order',
            'daily_limit' => null,

            'max_order_quantity' => $maxOrderQuantity,
            'stock_label' => $stockLabel,

            // Compatibility fields para hindi masira old frontend/mobile references.
            'sold_today' => 0,
            'remaining_today' => $maxOrderQuantity,
            'daily_inventory_label' => $stockLabel,

            'ingredients' => $ingredients->map(function ($ingredient) {
                $quantityRequired = (float) ($ingredient->pivot->quantity_required ?? 0);
                $currentStock = (float) ($ingredient->current_stock ?? 0);

                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'current_stock' => $currentStock,
                    'total_stock' => (float) ($ingredient->total_stock ?? $currentStock),
                    'unit' => $ingredient->unit,
                    'threshold' => (float) ($ingredient->threshold ?? 0),
                    'quantity_required' => $quantityRequired,
                    'pivot' => [
                        'quantity_required' => $quantityRequired,
                    ],
                ];
            })->values(),

            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}