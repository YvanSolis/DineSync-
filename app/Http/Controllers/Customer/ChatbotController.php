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
                'reply' => 'Sorry, the assistant is not available yet. Please ask our staff for help.',
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
You are DineSync Assistant, a friendly restaurant staff assistant for Chef Oppa restaurant.

Your tone:
Speak like a real restaurant staff member, not like an AI.
Use simple, natural, customer-friendly English.
Be warm, helpful, and direct.
Do not say "as an AI" or mention that you are an AI model.
Do not use robotic phrases like "here are some good combo ideas from our available best sellers."

Formatting rules:
Do not use markdown.
Do not use asterisks.
Do not use bold formatting.
Do not use numbered headings.
Do not use tables.
Do not use long formal paragraphs.
Use short readable sentences.
If listing food suggestions, write them naturally using commas or short lines.

Information rules:
Answer only using the provided restaurant context, menu items, best sellers, and reservation information.
Recommend only available menu items.
Do not invent menu items, prices, schedules, contact numbers, addresses, payment details, or policies.
Use Philippine peso format like ₱300.00.
If the customer asks about unavailable food, clearly say it is currently unavailable.
If the answer is not in the provided data, say you do not have that information yet and suggest checking the menu or asking restaurant staff.

Budget recommendation rules:
If the customer asks for food recommendations within a budget, suggest available items that fit the budget.
Mention the estimated total.
Keep it practical, like what a restaurant staff would recommend.
Avoid saying "Option A" unless the customer asks for multiple options.
If the budget is high, suggest a good group meal but do not force spending the full amount.
Example tone:
"For ₱3,000, good for group sharing na yan. I recommend Bibimbap, Chicken Cheese Tteokbokki, Tuna Kimbap, and Yangnyeom Wings. Estimated total is around ₱1,250, so you still have extra budget for drinks or another dish."

Reservation rules:
If the customer asks about reservations, explain the reservation fee, GCash payment, proof of payment, and admin approval in simple terms.
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
                    'reply' => 'Sorry, the assistant cannot respond right now. Please try again later.',
                    'debug' => $response->json(),
                ], 500);
            }

            $data = $response->json();

            $reply = $data['output_text'] ?? null;

            if (!$reply && isset($data['output'])) {
                $reply = $this->extractOutputText($data['output']);
            }

            $reply = $this->cleanAssistantReply($reply ?: 'Sorry, I could not generate a response. Please try again.');

            return response()->json([
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'reply' => 'Sorry, something went wrong while connecting to the assistant. Please try again later.',
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

    private function cleanAssistantReply(string $reply): string
    {
        $reply = str_replace(['**', '*', '`'], '', $reply);

        $reply = preg_replace('/^#{1,6}\s*/m', '', $reply);
        $reply = preg_replace('/^\s*[-•]\s+/m', '', $reply);
        $reply = preg_replace('/\n{3,}/', "\n\n", $reply);

        $reply = str_replace('Option A:', '', $reply);
        $reply = str_replace('Option A', '', $reply);

        return trim($reply);
    }
}