<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIForecastService
{
    public function generateForecast(array $businessData): array
    {
        set_time_limit(90);

        $apiKey = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-5.4-nano');

        if (!$apiKey) {
            return $this->fallbackForecast('OpenAI API key is missing.');
        }

        $prompt = $this->buildPrompt($businessData);

        try {
            $response = Http::withToken($apiKey)
                ->connectTimeout(10)
                ->timeout(60)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'max_output_tokens' => 4000,
                    'reasoning' => [
                        'effort' => 'low',
                    ],
                    'input' => [
                        [
                            'role' => 'system',
                            'content' => [
                                [
                                    'type' => 'input_text',
                                    'text' => 'You are an AI forecasting assistant for a Korean restaurant POS and inventory system. Return only JSON that follows the schema.'
                                ]
                            ]
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'input_text',
                                    'text' => $prompt
                                ]
                            ]
                        ],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'restaurant_forecast',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'summary' => [
                                        'type' => 'string'
                                    ],
                                    'forecasted_revenue_next_day' => [
                                        'type' => 'number'
                                    ],
                                    'forecast_confidence' => [
                                        'type' => 'string',
                                        'enum' => ['Low', 'Medium', 'High']
                                    ],
                                    'menu_forecast' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'additionalProperties' => false,
                                            'properties' => [
                                                'menu_item' => ['type' => 'string'],
                                                'predicted_demand' => ['type' => 'number'],
                                                'reason' => ['type' => 'string'],
                                            ],
                                            'required' => ['menu_item', 'predicted_demand', 'reason'],
                                        ],
                                    ],
                                    'ingredient_forecast' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'additionalProperties' => false,
                                            'properties' => [
                                                'ingredient' => ['type' => 'string'],
                                                'current_stock' => ['type' => 'number'],
                                                'unit' => ['type' => 'string'],
                                                'suggested_restock' => ['type' => 'number'],
                                                'risk_level' => [
                                                    'type' => 'string',
                                                    'enum' => ['Low', 'Medium', 'High']
                                                ],
                                                'reason' => ['type' => 'string'],
                                            ],
                                            'required' => [
                                                'ingredient',
                                                'current_stock',
                                                'unit',
                                                'suggested_restock',
                                                'risk_level',
                                                'reason'
                                            ],
                                        ],
                                    ],
                                    'recommendations' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                    ],
                                ],
                                'required' => [
                                    'summary',
                                    'forecasted_revenue_next_day',
                                    'forecast_confidence',
                                    'menu_forecast',
                                    'ingredient_forecast',
                                    'recommendations'
                                ],
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI forecast failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackForecast('OpenAI request failed. Status: ' . $response->status());
            }

            $data = $response->json();
            $outputText = $this->extractOutputText($data);

            if (!$outputText) {
                Log::error('OpenAI forecast empty output', [
                    'response' => $data,
                ]);

                return $this->fallbackForecast('OpenAI returned empty output.');
            }

            $jsonText = $this->extractJsonFromText($outputText);
            $decoded = json_decode($jsonText, true);

            if (!is_array($decoded)) {
                Log::error('OpenAI forecast JSON decode failed', [
                    'raw_output' => $outputText,
                    'json_attempt' => $jsonText,
                    'json_error' => json_last_error_msg(),
                ]);

                return $this->fallbackForecast('OpenAI returned invalid JSON: ' . json_last_error_msg());
            }

            return $this->normalizeForecast($decoded);

        } catch (\Throwable $e) {
            Log::error('OpenAI forecast exception', [
                'message' => $e->getMessage(),
            ]);

            return $this->fallbackForecast('OpenAI forecast exception: ' . $e->getMessage());
        }
    }

    private function buildPrompt(array $businessData): string
    {
        $compactData = [
            'total_revenue_7d' => $businessData['total_revenue_7d'] ?? 0,
            'avg_order_value' => $businessData['avg_order_value'] ?? 0,
            'total_orders_7d' => $businessData['total_orders_7d'] ?? 0,
            'basic_forecasted_revenue' => $businessData['basic_forecasted_revenue'] ?? 0,
            'sales_order_trends' => array_slice($this->toArray($businessData['sales_order_trends'] ?? []), -7),
            'revenue_by_category' => array_slice($this->toArray($businessData['revenue_by_category'] ?? []), 0, 5),
            'inventory_usage_forecast' => array_slice($this->toArray($businessData['inventory_usage_forecast'] ?? []), 0, 5),
            'forecast_details' => array_slice($this->toArray($businessData['forecast_details'] ?? []), 0, 5),
        ];

        return 'Analyze this restaurant data and create a practical next-day forecast for fresh daily stocking. Use low confidence if data is limited. Restaurant data: '
            . json_encode($compactData, JSON_PRETTY_PRINT);
    }

    private function toArray($value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->values()->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    private function extractOutputText(array $data): string
    {
        if (isset($data['output_text']) && is_string($data['output_text'])) {
            return trim($data['output_text']);
        }

        $texts = [];

        $walk = function ($value) use (&$walk, &$texts) {
            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    if (($key === 'text' || $key === 'output_text') && is_string($item)) {
                        $texts[] = $item;
                    } else {
                        $walk($item);
                    }
                }
            }
        };

        $walk($data);

        return trim(implode('', $texts));
    }

    private function extractJsonFromText(string $text): string
    {
        $text = trim($text);

        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $firstBrace = strpos($text, '{');
        $lastBrace = strrpos($text, '}');

        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            return substr($text, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        return $text;
    }

    private function normalizeForecast(array $forecast): array
    {
        return [
            'summary' => $forecast['summary'] ?? 'AI forecast generated successfully.',
            'forecasted_revenue_next_day' => (float) ($forecast['forecasted_revenue_next_day'] ?? 0),
            'forecast_confidence' => $this->normalizeLevel($forecast['forecast_confidence'] ?? 'Low'),
            'menu_forecast' => $this->normalizeMenuForecast($forecast['menu_forecast'] ?? []),
            'ingredient_forecast' => $this->normalizeIngredientForecast($forecast['ingredient_forecast'] ?? []),
            'recommendations' => is_array($forecast['recommendations'] ?? null)
                ? array_values($forecast['recommendations'])
                : [],
        ];
    }

    private function normalizeMenuForecast(array $items): array
    {
        return collect($items)->map(function ($item) {
            return [
                'menu_item' => (string) ($item['menu_item'] ?? 'Unknown Item'),
                'predicted_demand' => (float) ($item['predicted_demand'] ?? 0),
                'reason' => (string) ($item['reason'] ?? 'Based on recent demand.'),
            ];
        })->values()->toArray();
    }

    private function normalizeIngredientForecast(array $items): array
    {
        return collect($items)->map(function ($item) {
            return [
                'ingredient' => (string) ($item['ingredient'] ?? 'Unknown Ingredient'),
                'current_stock' => (float) ($item['current_stock'] ?? 0),
                'unit' => (string) ($item['unit'] ?? ''),
                'suggested_restock' => (float) ($item['suggested_restock'] ?? 0),
                'risk_level' => $this->normalizeLevel($item['risk_level'] ?? 'Low'),
                'reason' => (string) ($item['reason'] ?? 'Based on current stock and expected usage.'),
            ];
        })->values()->toArray();
    }

    private function normalizeLevel(string $level): string
    {
        $level = strtolower(trim($level));

        if ($level === 'high') {
            return 'High';
        }

        if ($level === 'medium') {
            return 'Medium';
        }

        return 'Low';
    }

    private function fallbackForecast(string $reason): array
    {
        return [
            'summary' => 'AI forecast is currently unavailable. Showing basic forecast instead. Reason: ' . $reason,
            'forecasted_revenue_next_day' => 0,
            'forecast_confidence' => 'Low',
            'menu_forecast' => [],
            'ingredient_forecast' => [],
            'recommendations' => [
                'Continue recording orders and ingredient usage to improve future forecasts.',
                'Make sure menu items are linked to ingredients for better inventory forecasting.',
            ],
        ];
    }
}