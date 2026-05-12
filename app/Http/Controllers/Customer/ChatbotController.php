<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'reply' => 'OpenAI API key is not configured yet.',
            ], 500);
        }

        $message = trim($request->message);
        $settings = RestaurantSetting::current();

        $menuItems = MenuItem::query()
            ->select('id', 'name', 'category', 'price', 'is_available')
            ->orderBy('category')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'category' => $item->category ?? 'Menu Item',
                    'price' => (float) $item->price,
                    'availability' => $item->is_available ? 'Available' : 'Unavailable',
                ];
            })
            ->values()
            ->toArray();

        $bestSellers = $this->getBestSellers();

        $context = [
            'restaurant' => [
                'name' => $settings->restaurant_name ?? 'Chef Oppa',
                'address' => $settings->address,
                'contact_number' => $settings->contact_number,
                'opening_days' => $settings->opening_days,
                'opening_time' => $settings->opening_time,
                'closing_time' => $settings->closing_time,
                'reservation_fee' => (float) $settings->reservation_fee,
                'gcash_name' => $settings->gcash_name,
                'gcash_number' => $settings->gcash_number,
            ],
            'menu_items' => $menuItems,
            'best_sellers' => $bestSellers,
        ];

        $systemInstructions = <<<TEXT
You are DineSync Assistant, an OpenAI-powered customer chatbot for Chef Oppa restaurant.

Answer only using the provided restaurant context, menu items, best sellers, and reservation information.
Keep answers simple, helpful, and customer-friendly.
Use Philippine peso format like ₱300.00.
Recommend only available menu items.
If a customer asks for food under a budget, suggest available items within that budget.
If a customer asks about unavailable food, clearly say it is currently unavailable.
If the customer asks about reservations, explain the reservation fee, GCash payment, proof of payment, and admin approval.
If the answer is not in the provided data, say you do not have that information yet and suggest checking the menu or contacting the restaurant.
Do not invent menu items, prices, schedules, contact numbers, addresses, payment details, or policies.
TEXT;

        try {
            $response = Http::withToken($apiKey)
                ->timeout(40)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => env('OPENAI_MODEL', 'gpt-5.4-nano'),
                    'instructions' => $systemInstructions,
                    'input' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'input_text',
                                    'text' =>
                                        "Restaurant context:\n" .
                                        json_encode($context, JSON_PRETTY_PRINT) .
                                        "\n\nCustomer message:\n" .
                                        $message,
                                ],
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'reply' => 'Sorry, the AI assistant could not respond right now. Please check your OpenAI key/model and try again.',
                    'debug' => $response->json(),
                ], 500);
            }

            $data = $response->json();

            $reply = $data['output_text'] ?? null;

            if (!$reply && isset($data['output'])) {
                $reply = $this->extractOutputText($data['output']);
            }

            return response()->json([
                'reply' => $reply ?: 'Sorry, I could not generate a response. Please try again.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'reply' => 'Sorry, something went wrong while connecting to the OpenAI assistant.',
            ], 500);
        }
    }

    private function getBestSellers(): array
    {
        if (
            Schema::hasTable('order_items') &&
            Schema::hasColumn('order_items', 'menu_item_id') &&
            Schema::hasColumn('order_items', 'quantity')
        ) {
            return DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.price',
                    'menu_items.is_available',
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->groupBy(
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.price',
                    'menu_items.is_available'
                )
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'category' => $item->category ?? 'Menu Item',
                        'price' => (float) $item->price,
                        'availability' => $item->is_available ? 'Available' : 'Unavailable',
                        'total_sold' => (int) $item->total_sold,
                    ];
                })
                ->values()
                ->toArray();
        }

        return MenuItem::query()
            ->where('is_available', true)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'category' => $item->category ?? 'Menu Item',
                    'price' => (float) $item->price,
                    'availability' => $item->is_available ? 'Available' : 'Unavailable',
                    'total_sold' => null,
                ];
            })
            ->values()
            ->toArray();
    }

    private function extractOutputText(array $output): ?string
    {
        $texts = [];

        foreach ($output as $item) {
            if (!isset($item['content']) || !is_array($item['content'])) {
                continue;
            }

            foreach ($item['content'] as $content) {
                if (isset($content['text'])) {
                    $texts[] = $content['text'];
                }
            }
        }

        return count($texts) ? trim(implode("\n", $texts)) : null;
    }
}