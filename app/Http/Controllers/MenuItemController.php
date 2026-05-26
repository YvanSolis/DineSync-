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
        'Noodles',
        'Salad',
        'Maki & Sushi',
        'Jeon Series',
        'Tteokbokki Series',
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

        $query = MenuItem::with('ingredients')->orderBy('name');

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
            }),
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
        ]);

        if (!in_array($validated['category'], $this->categories)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->uploadImageToSupabase($request->file('image'));
        }

        $menuItem = MenuItem::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $imageUrl,

            'flavor_tags' => $validated['flavor_tags'] ?? [],
            'meal_type' => $validated['meal_type'] ?? null,

            // New food item should be unavailable first
            // because it has no linked ingredients yet.
            'is_available' => false,
        ]);

        return response()->json(
            $this->formatMenuItemResponse($menuItem->load('ingredients')),
            201
        );
    }

    public function show(MenuItem $menuItem)
    {
        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse($menuItem->fresh()->load('ingredients'))
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

        if (isset($validated['category']) && !in_array($validated['category'], $this->categories)) {
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

        $menuItem->update($updateData);

        /*
         * If admin manually turns it OFF, allow it.
         * If admin manually turns it ON, still check ingredients and stock first.
         */
        if ($request->has('is_available') && $request->boolean('is_available') === false) {
            $menuItem->update([
                'is_available' => false,
            ]);
        } else {
            $this->refreshAvailability($menuItem);
        }

        return response()->json(
            $this->formatMenuItemResponse($menuItem->fresh()->load('ingredients'))
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
            $this->formatMenuItemResponse($menuItem->fresh()->load('ingredients'))
        );
    }

    public function detachIngredient(MenuItem $menuItem, $ingredientId)
    {
        $menuItem->ingredients()->detach($ingredientId);

        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse($menuItem->fresh()->load('ingredients'))
        );
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
        $menuItem->load('ingredients');

        if ($menuItem->ingredients->isEmpty()) {
            $menuItem->update([
                'is_available' => false,
            ]);

            return;
        }

        foreach ($menuItem->ingredients as $ingredient) {
            $quantityRequired = (float) ($ingredient->pivot->quantity_required ?? 0);

            $availableStock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);

            if ($quantityRequired <= 0 || $availableStock < $quantityRequired) {
                $menuItem->update([
                    'is_available' => false,
                ]);

                return;
            }
        }

        $menuItem->update([
            'is_available' => true,
        ]);
    }

    private function formatMenuItemResponse(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'category' => $item->category,
            'price' => (float) $item->price,
            'image' => $item->image,
            'is_available' => (bool) $item->is_available,

            'flavor_tags' => $item->flavor_tags ?? [],
            'meal_type' => $item->meal_type,

            'ingredients' => $item->ingredients,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}