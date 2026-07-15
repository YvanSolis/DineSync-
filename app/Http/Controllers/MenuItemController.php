<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $bestSellerIds = $this->getBestSellerIds();

        $query = MenuItem::query()
            ->select($this->menuItemColumns())
            ->with([
                'ingredients' => function ($query) {
                    $query->select([
                        'ingredients.id',
                        'ingredients.name',
                        'ingredients.current_stock',
                        'ingredients.unit',
                        'ingredients.threshold',
                    ])->orderBy('name');
                },
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
            'menu_items' => $menuItems
                ->map(fn ($item) => $this->formatMenuItemResponse(
                    $item,
                    $bestSellerIds
                ))
                ->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'flavor_tags' => ['nullable', 'array'],
            'flavor_tags.*' => [
                'string',
                'in:' . implode(',', $this->flavorTags),
            ],

            'meal_type' => [
                'nullable',
                'string',
                'in:' . implode(',', $this->mealTypes),
            ],

            'is_available' => ['nullable', 'boolean'],
            'is_unlimited' => ['nullable', 'boolean'],
        ]);

        if (!in_array($validated['category'], $this->categories, true)) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->uploadImageToSupabase(
                $request->file('image')
            );
        }

        $isCustom = $validated['category'] === 'Chef Oppa Special';

        /*
        |--------------------------------------------------------------------------
        | Unlimited default
        |--------------------------------------------------------------------------
        | Category Unlimited automatically enables refill mode unless the admin
        | explicitly sends is_unlimited=false.
        */
        $isUnlimited = $request->has('is_unlimited')
            ? $request->boolean('is_unlimited')
            : $validated['category'] === 'Unlimited';

        $menuItem = MenuItem::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $imageUrl,

            'flavor_tags' => $validated['flavor_tags'] ?? [],
            'meal_type' => $validated['meal_type'] ?? 'main',

            'inventory_type' => $isCustom ? 'custom' : 'per_order',
            'daily_limit' => null,
            'is_available' => $isCustom,
            'is_unlimited' => $isCustom ? false : $isUnlimited,
        ]);

        $this->refreshAvailability($menuItem);

        $freshMenuItem = $this->freshMenuItem($menuItem);

        AuditService::record(
            module: 'Menu',
            action: 'create',
            description: "Created menu item {$freshMenuItem->name}.",
            auditable: $freshMenuItem,
            oldValues: [],
            newValues: $freshMenuItem->only([
                'name',
                'category',
                'description',
                'price',
                'is_available',
                'is_unlimited',
                'meal_type',
                'inventory_type',
            ]),
            request: $request
        );

        return response()->json(
            $this->formatMenuItemResponse($freshMenuItem),
            201
        );
    }

    public function show(MenuItem $menuItem)
    {
        $this->refreshAvailability($menuItem);

        return response()->json(
            $this->formatMenuItemResponse(
                $this->freshMenuItem($menuItem)
            )
        );
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'price' => ['sometimes', 'required', 'numeric', 'min:0'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'is_available' => ['sometimes', 'boolean'],
            'is_unlimited' => ['sometimes', 'boolean'],

            'flavor_tags' => ['sometimes', 'nullable', 'array'],
            'flavor_tags.*' => [
                'string',
                'in:' . implode(',', $this->flavorTags),
            ],

            'meal_type' => [
                'sometimes',
                'nullable',
                'string',
                'in:' . implode(',', $this->mealTypes),
            ],
        ]);

        if (
            isset($validated['category'])
            && !in_array(
                $validated['category'],
                $this->categories,
                true
            )
        ) {
            return response()->json([
                'message' => 'Invalid menu category selected.',
            ], 422);
        }

        $oldValues = $menuItem->only([
            'name',
            'category',
            'description',
            'price',
            'image',
            'is_available',
            'is_unlimited',
            'flavor_tags',
            'meal_type',
            'inventory_type',
            'daily_limit',
        ]);

        $updateData = [];

        foreach (
            [
                'name',
                'category',
                'description',
                'price',
                'meal_type',
            ] as $field
        ) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        if ($request->has('flavor_tags')) {
            $updateData['flavor_tags'] =
                $validated['flavor_tags'] ?? [];
        }

        if ($request->hasFile('image')) {
            $updateData['image'] = $this->uploadImageToSupabase(
                $request->file('image')
            );
        }

        if ($request->has('is_unlimited')) {
            $updateData['is_unlimited'] =
                $request->boolean('is_unlimited');
        }

        $finalCategory =
            $updateData['category'] ?? $menuItem->category;

        if ($finalCategory === 'Chef Oppa Special') {
            $updateData['inventory_type'] = 'custom';
            $updateData['daily_limit'] = null;
            $updateData['is_unlimited'] = false;
        } else {
            $updateData['inventory_type'] =
                $menuItem->inventory_type ?: 'per_order';

            $updateData['daily_limit'] = null;

            /*
            |--------------------------------------------------------------------------
            | Automatically enable unlimited category
            |--------------------------------------------------------------------------
            | Only apply this when is_unlimited was not explicitly sent.
            */
            if (
                $finalCategory === 'Unlimited'
                && !$request->has('is_unlimited')
            ) {
                $updateData['is_unlimited'] = true;
            }
        }

        $menuItem->update($updateData);

        if (
            $request->has('is_available')
            && $request->boolean('is_available') === false
        ) {
            $menuItem->forceFill([
                'is_available' => false,
            ])->saveQuietly();
        } else {
            $this->refreshAvailability($menuItem);
        }

        $freshMenuItem = $this->freshMenuItem($menuItem);

        $newValues = $freshMenuItem->only([
            'name',
            'category',
            'description',
            'price',
            'image',
            'is_available',
            'is_unlimited',
            'flavor_tags',
            'meal_type',
            'inventory_type',
            'daily_limit',
        ]);

        AuditService::record(
            module: 'Menu',
            action: 'update',
            description: "Updated menu item {$freshMenuItem->name}.",
            auditable: $freshMenuItem,
            oldValues: $oldValues,
            newValues: $newValues,
            request: $request
        );

        return response()->json(
            $this->formatMenuItemResponse($freshMenuItem)
        );
    }

    public function destroy(Request $request, MenuItem $menuItem)
    {
        $oldValues = $menuItem->only([
            'name',
            'category',
            'description',
            'price',
            'is_available',
            'is_unlimited',
            'meal_type',
            'inventory_type',
        ]);

        $menuItemId = $menuItem->id;
        $menuItemName = $menuItem->name;

        $menuItem->delete();

        AuditService::record(
            module: 'Menu',
            action: 'delete',
            description: "Deleted menu item {$menuItemName}.",
            auditable: MenuItem::class,
            oldValues: array_merge(
                ['id' => $menuItemId],
                $oldValues
            ),
            newValues: [],
            request: $request
        );

        return response()->json([
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    public function attachIngredient(
        Request $request,
        MenuItem $menuItem
    ) {
        $validated = $request->validate([
            'ingredient_id' => [
                'required',
                'integer',
                'exists:ingredients,id',
            ],

            'quantity_required' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'is_refillable' => [
                'nullable',
                'boolean',
            ],

            'refill_quantity' => [
                'nullable',
                'numeric',
                'min:0.01',
                'required_if:is_refillable,1',
            ],
        ]);

        $ingredient = Ingredient::findOrFail(
            $validated['ingredient_id']
        );

        $existingIngredient = $menuItem->ingredients()
            ->where('ingredients.id', $ingredient->id)
            ->first();

        $oldPivotValues = $existingIngredient
            ? [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'quantity_required' => (float) (
                    $existingIngredient->pivot->quantity_required ?? 0
                ),
                'is_refillable' => (bool) (
                    $existingIngredient->pivot->is_refillable ?? false
                ),
                'refill_quantity' =>
                    $existingIngredient->pivot->refill_quantity !== null
                        ? (float) $existingIngredient->pivot->refill_quantity
                        : null,
            ]
            : [];

        $isRefillable = $request->boolean('is_refillable');

        if (!$menuItem->is_unlimited) {
            $isRefillable = false;
        }

        $refillQuantity = $isRefillable
            ? (float) $validated['refill_quantity']
            : null;

        /*
        |--------------------------------------------------------------------------
        | syncWithoutDetaching
        |--------------------------------------------------------------------------
        | Adds a new ingredient or updates its existing pivot settings.
        */
        $menuItem->ingredients()->syncWithoutDetaching([
            $validated['ingredient_id'] => [
                'quantity_required' =>
                    (float) $validated['quantity_required'],

                'is_refillable' => $isRefillable,
                'refill_quantity' => $refillQuantity,
                'updated_at' => now(),
            ],
        ]);

        $this->refreshAvailability($menuItem);

        $freshMenuItem = $this->freshMenuItem($menuItem);

        $newPivotValues = [
            'ingredient_id' => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'quantity_required' =>
                (float) $validated['quantity_required'],
            'is_refillable' => $isRefillable,
            'refill_quantity' => $refillQuantity,
        ];

        AuditService::record(
            module: 'Menu',
            action: $existingIngredient
                ? 'ingredient_updated'
                : 'ingredient_attached',
            description: $existingIngredient
                ? "Updated {$ingredient->name} settings for {$menuItem->name}."
                : "Attached {$ingredient->name} to {$menuItem->name}.",
            auditable: $menuItem,
            oldValues: $oldPivotValues,
            newValues: $newPivotValues,
            request: $request
        );

        return response()->json(
            $this->formatMenuItemResponse($freshMenuItem),
            201
        );
    }

    public function detachIngredient(
        Request $request,
        MenuItem $menuItem,
        $ingredientId
    ) {
        $ingredient = Ingredient::findOrFail($ingredientId);

        $linkedIngredient = $menuItem->ingredients()
            ->where('ingredients.id', $ingredient->id)
            ->first();

        $oldValues = $linkedIngredient
            ? [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'quantity_required' => (float) (
                    $linkedIngredient->pivot->quantity_required ?? 0
                ),
                'is_refillable' => (bool) (
                    $linkedIngredient->pivot->is_refillable ?? false
                ),
                'refill_quantity' =>
                    $linkedIngredient->pivot->refill_quantity !== null
                        ? (float) $linkedIngredient->pivot->refill_quantity
                        : null,
            ]
            : [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
            ];

        $menuItem->ingredients()->detach($ingredientId);

        $this->refreshAvailability($menuItem);

        AuditService::record(
            module: 'Menu',
            action: 'ingredient_detached',
            description: "Removed {$ingredient->name} from {$menuItem->name}.",
            auditable: $menuItem,
            oldValues: $oldValues,
            newValues: [],
            request: $request
        );

        return response()->json(
            $this->formatMenuItemResponse(
                $this->freshMenuItem($menuItem)
            )
        );
    }

    private function menuItemColumns(): array
    {
        return [
            'id',
            'name',
            'description',
            'category',
            'price',
            'image',
            'is_available',
            'is_unlimited',
            'flavor_tags',
            'meal_type',
            'inventory_type',
            'daily_limit',
            'created_at',
            'updated_at',
        ];
    }

    private function freshMenuItem(MenuItem $menuItem): MenuItem
    {
        return MenuItem::query()
            ->select($this->menuItemColumns())
            ->with([
                'ingredients' => function ($query) {
                    $query->select([
                        'ingredients.id',
                        'ingredients.name',
                        'ingredients.current_stock',
                        'ingredients.unit',
                        'ingredients.threshold',
                    ])->orderBy('name');
                },
            ])
            ->findOrFail($menuItem->id);
    }

    private function uploadImageToSupabase($file): string
    {
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY');
        $bucket = env('SUPABASE_STORAGE_BUCKET', 'menu-items');

        if (!$supabaseUrl || !$serviceRoleKey || !$bucket) {
            abort(
                500,
                'Supabase storage configuration is missing.'
            );
        }

        $extension = $file->getClientOriginalExtension();

        $filename = Str::slug(
            pathinfo(
                $file->getClientOriginalName(),
                PATHINFO_FILENAME
            )
        );

        $filePath =
            $filename
            . '-'
            . Str::random(12)
            . '.'
            . $extension;

        $uploadUrl =
            "{$supabaseUrl}/storage/v1/object/"
            . "{$bucket}/{$filePath}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceRoleKey,
            'apikey' => $serviceRoleKey,
            'Content-Type' => $file->getMimeType(),
            'x-upsert' => 'true',
        ])
            ->withBody(
                file_get_contents($file->getRealPath()),
                $file->getMimeType()
            )
            ->post($uploadUrl);

        if (!$response->successful()) {
            abort(
                500,
                'Failed to upload image to Supabase Storage: '
                . $response->body()
            );
        }

        return "{$supabaseUrl}/storage/v1/object/public/"
            . "{$bucket}/{$filePath}";
    }

    private function refreshAvailability(MenuItem $menuItem): void
    {
        $menuItem = MenuItem::with('ingredients')
            ->find($menuItem->id);

        if (!$menuItem) {
            return;
        }

        if ($menuItem->category === 'Chef Oppa Special') {
            $menuItem->forceFill([
                'inventory_type' => 'custom',
                'daily_limit' => null,
                'is_available' => true,
                'is_unlimited' => false,
            ])->saveQuietly();

            return;
        }

        $menuItem->forceFill([
            'inventory_type' =>
                $menuItem->inventory_type ?: 'per_order',

            'daily_limit' => null,
            'is_available' => $menuItem->computeAvailability(),
        ])->saveQuietly();
    }

    private function calculateMaxOrderQuantity(
        MenuItem $item
    ): int {
        if (
            $item->category === 'Chef Oppa Special'
            || $item->inventory_type === 'custom'
        ) {
            return 99;
        }

        $ingredients = $item->relationLoaded('ingredients')
            ? $item->ingredients
            : collect();

        if ($ingredients->isEmpty()) {
            return 0;
        }

        $maxServings = null;

        foreach ($ingredients as $ingredient) {
            $required = (float) (
                $ingredient->pivot->quantity_required ?? 0
            );

            $stock = (float) (
                $ingredient->current_stock ?? 0
            );

            if ($required <= 0) {
                return 0;
            }

            $possibleServings = (int) floor(
                $stock / $required
            );

            if ($possibleServings <= 0) {
                return 0;
            }

            if (
                $maxServings === null
                || $possibleServings < $maxServings
            ) {
                $maxServings = $possibleServings;
            }
        }

        return max(0, (int) ($maxServings ?? 0));
    }

    private function getStockLabel(
        MenuItem $item,
        int $maxOrderQuantity
    ): string {
        if (
            $item->category === 'Chef Oppa Special'
            || $item->inventory_type === 'custom'
        ) {
            return 'Staff confirms availability';
        }

        $ingredients = $item->relationLoaded('ingredients')
            ? $item->ingredients
            : collect();

        if ($ingredients->isEmpty()) {
            return 'No ingredients linked';
        }

        foreach ($ingredients as $ingredient) {
            $required = (float) (
                $ingredient->pivot->quantity_required ?? 0
            );

            $stock = (float) (
                $ingredient->current_stock ?? 0
            );

            if ($required <= 0) {
                return 'Invalid ingredient usage';
            }

            if ($stock < $required) {
                return 'Insufficient ingredients';
            }
        }

        if ($maxOrderQuantity <= 0) {
            return 'Insufficient ingredients';
        }

        return $maxOrderQuantity
            . ' orders available based on ingredients';
    }

    private function getBestSellerIds(): array
    {
        return DB::table('order_items')
            ->select('menu_item_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->whereNotNull('menu_item_id')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->limit(3)
            ->pluck('menu_item_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    private function formatMenuItemResponse(
        MenuItem $item,
        ?array $bestSellerIds = null
    ): array {
        $ingredients = $item->relationLoaded('ingredients')
            ? $item->ingredients
            : collect();

        $bestSellerIds =
            $bestSellerIds ?? $this->getBestSellerIds();

        $maxOrderQuantity =
            $this->calculateMaxOrderQuantity($item);

        $stockLabel =
            $this->getStockLabel($item, $maxOrderQuantity);

        $isBestSeller = in_array(
            (int) $item->id,
            $bestSellerIds,
            true
        );

        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'category' => $item->category,
            'price' => (float) $item->price,
            'image' => $item->image,
            'image_url' => $item->image_url,
            'is_available' => (bool) $item->is_available,
            'is_unlimited' => (bool) $item->is_unlimited,

            'is_best_seller' => $isBestSeller,
            'is_popular' => $isBestSeller,

            'flavor_tags' => $item->flavor_tags ?? [],
            'meal_type' => $item->meal_type,

            'inventory_type' =>
                $item->inventory_type ?: 'per_order',

            'daily_limit' => null,

            'max_order_quantity' => $maxOrderQuantity,
            'stock_label' => $stockLabel,

            'sold_today' => 0,
            'remaining_today' => $maxOrderQuantity,
            'daily_inventory_label' => $stockLabel,

            'ingredients' => $ingredients
                ->map(function ($ingredient) {
                    $quantityRequired = (float) (
                        $ingredient->pivot
                            ->quantity_required ?? 0
                    );

                    $isRefillable = (bool) (
                        $ingredient->pivot
                            ->is_refillable ?? false
                    );

                    $refillQuantity =
                        $ingredient->pivot->refill_quantity;

                    $currentStock = (float) (
                        $ingredient->current_stock ?? 0
                    );

                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'current_stock' => $currentStock,
                        'total_stock' => $currentStock,
                        'unit' => $ingredient->unit,

                        'threshold' => (float) (
                            $ingredient->threshold ?? 0
                        ),

                        'quantity_required' =>
                            $quantityRequired,

                        'is_refillable' => $isRefillable,

                        'refill_quantity' =>
                            $refillQuantity !== null
                                ? (float) $refillQuantity
                                : null,

                        'pivot' => [
                            'quantity_required' =>
                                $quantityRequired,

                            'is_refillable' =>
                                $isRefillable,

                            'refill_quantity' =>
                                $refillQuantity !== null
                                    ? (float) $refillQuantity
                                    : null,
                        ],
                    ];
                })
                ->values(),

            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}