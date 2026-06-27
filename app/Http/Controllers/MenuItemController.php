<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Carbon\Carbon;
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

    private array $inventoryTypes = [
        'per_order',
        'per_head',
        'custom',
    ];

    public function index(Request $request)
    {
        $selectedCategory = $request->query('category');
        $search = $request->query('search');

        $selectedDate = now('Asia/Manila')->toDateString();

        $startOfDayUtc = Carbon::parse($selectedDate, 'Asia/Manila')
            ->startOfDay()
            ->timezone('UTC');

        $endOfDayUtc = Carbon::parse($selectedDate, 'Asia/Manila')
            ->endOfDay()
            ->timezone('UTC');

        $query = MenuItem::with('ingredients')
            ->withSum([
                'orderItems as sold_today' => function ($query) use ($startOfDayUtc, $endOfDayUtc) {
                    $query->whereHas('order', function ($orderQuery) use ($startOfDayUtc, $endOfDayUtc) {
                        $orderQuery->whereBetween('created_at', [$startOfDayUtc, $endOfDayUtc]);
                    });
                },
            ], 'quantity')
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
            'inventory_type_options' => $this->inventoryTypes,
            'selected_category' => $selectedCategory,
            'selected_date' => $selectedDate,
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

            'inventory_type' => 'nullable|string|in:' . implode(',', $this->inventoryTypes),
            'daily_limit' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        if (!in_array($validated['category'], $this->categories, true)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $inventoryType = $validated['inventory_type'] ?? 'per_order';

        if (!in_array($inventoryType, $this->inventoryTypes, true)) {
            $inventoryType = 'per_order';
        }

        if ($validated['category'] === 'Chef Oppa Special') {
            $inventoryType = 'custom';
        }

        $dailyLimit = $inventoryType === 'custom'
            ? null
            : (int) ($validated['daily_limit'] ?? 0);

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
            'meal_type' => $validated['meal_type'] ?? 'main',

            'inventory_type' => $inventoryType,
            'daily_limit' => $dailyLimit,

            'is_available' => $request->boolean('is_available', true),
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

            'inventory_type' => 'sometimes|nullable|string|in:' . implode(',', $this->inventoryTypes),
            'daily_limit' => 'sometimes|nullable|integer|min:0',
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

        if ($request->has('inventory_type')) {
            $updateData['inventory_type'] = $validated['inventory_type'] ?? 'per_order';
        }

        if ($request->has('daily_limit')) {
            $updateData['daily_limit'] = (int) ($validated['daily_limit'] ?? 0);
        }

        $finalCategory = $updateData['category'] ?? $menuItem->category;
        $finalInventoryType = $updateData['inventory_type'] ?? $menuItem->inventory_type ?? 'per_order';

        if (!in_array($finalInventoryType, $this->inventoryTypes, true)) {
            $finalInventoryType = 'per_order';
        }

        if ($finalCategory === 'Chef Oppa Special') {
            $finalInventoryType = 'custom';
        }

        $updateData['inventory_type'] = $finalInventoryType;

        if ($finalInventoryType === 'custom') {
            $updateData['daily_limit'] = null;
        } elseif (!array_key_exists('daily_limit', $updateData) && $menuItem->daily_limit === null) {
            $updateData['daily_limit'] = 0;
        }

        if ($request->has('flavor_tags')) {
            $updateData['flavor_tags'] = $validated['flavor_tags'] ?? [];
        }

        if ($request->hasFile('image')) {
            $updateData['image'] = $this->uploadImageToSupabase($request->file('image'));
        }

        $menuItem->update($updateData);

        if ($request->has('is_available') && $request->boolean('is_available') === false) {
            $menuItem->update([
                'is_available' => false,
            ]);
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
        $selectedDate = now('Asia/Manila')->toDateString();

        $startOfDayUtc = Carbon::parse($selectedDate, 'Asia/Manila')
            ->startOfDay()
            ->timezone('UTC');

        $endOfDayUtc = Carbon::parse($selectedDate, 'Asia/Manila')
            ->endOfDay()
            ->timezone('UTC');

        return MenuItem::with('ingredients')
            ->withSum([
                'orderItems as sold_today' => function ($query) use ($startOfDayUtc, $endOfDayUtc) {
                    $query->whereHas('order', function ($orderQuery) use ($startOfDayUtc, $endOfDayUtc) {
                        $orderQuery->whereBetween('created_at', [$startOfDayUtc, $endOfDayUtc]);
                    });
                },
            ], 'quantity')
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
        $menuItem->refresh();

        if ($menuItem->category === 'Chef Oppa Special') {
            $menuItem->update([
                'inventory_type' => 'custom',
                'daily_limit' => null,
                'is_available' => true,
            ]);

            return;
        }

        $menuItem->update([
            'inventory_type' => $menuItem->inventory_type ?: 'per_order',
            'daily_limit' => $menuItem->inventory_type === 'custom'
                ? null
                : ($menuItem->daily_limit ?? 0),
            'is_available' => $menuItem->computeAvailability(),
        ]);
    }

    private function formatMenuItemResponse(MenuItem $item): array
    {
        $soldToday = (int) ($item->sold_today ?? 0);

        $remainingToday = null;

        if ($item->inventory_type !== 'custom'
            && in_array($item->inventory_type, ['per_order', 'per_head'], true)
            && $item->daily_limit !== null) {
            $remainingToday = max(0, (int) $item->daily_limit - $soldToday);
        }

        $unit = $item->inventory_type === 'per_head' ? 'heads' : 'orders';

        if (!$item->inventory_type) {
            $dailyInventoryLabel = 'Inventory type not set';
        } elseif ($item->inventory_type === 'custom') {
            $dailyInventoryLabel = 'Staff confirms';
        } elseif ($item->daily_limit === null) {
            $dailyInventoryLabel = 'Daily limit not set';
        } elseif ((int) $remainingToday <= 0) {
            $dailyInventoryLabel = 'Sold out today';
        } else {
            $dailyInventoryLabel = "{$remainingToday} {$unit} left today";
        }

        if ($item->inventory_type === 'custom') {
            $maxOrderQuantity = 99;
        } elseif ($remainingToday !== null) {
            $maxOrderQuantity = max(0, (int) $remainingToday);
        } else {
            $maxOrderQuantity = 0;
        }

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

            'inventory_type' => $item->inventory_type,
            'daily_limit' => $item->daily_limit,
            'sold_today' => $soldToday,
            'remaining_today' => $remainingToday,
            'daily_inventory_label' => $dailyInventoryLabel,
            'stock_label' => $dailyInventoryLabel,
            'max_order_quantity' => $maxOrderQuantity,

            'ingredients' => $item->ingredients,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}