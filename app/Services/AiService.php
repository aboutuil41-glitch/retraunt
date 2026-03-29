<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    public function analyzeDish(array $dietaryTags, string $dishName, array $ingredients): array
    {
        $prompt = $this->buildPrompt($dietaryTags, $dishName, $ingredients);

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . env('GEMINI_API_KEY'),
            [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.2
                ]
            ]
        );

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API error.');
        }

        $output = trim($response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '');

        $output = preg_replace('/```json|```/', '', $output);

        $result = json_decode($output, true);

        if (!$result || !isset($result['score'])) {
            throw new \RuntimeException('Invalid AI response: ' . $output);
        }

        // return [
        //     'score'           => (int) $result['score'],
        //     'warning_message' => $result['warning_message'] ?? null,
        // ];
        return $result;
    }

    private function buildPrompt(array $dietaryTags, string $dishName, array $ingredients): string
    {
        return
            "User dietary restrictions: " . json_encode($dietaryTags) . "\n" .
            "Dish name: {$dishName}\n" .
            "Ingredients: " . json_encode($ingredients) . "\n\n" .
            "Dietary tags: vegan=no animal products, no_sugar=avoid sugar, no_cholesterol=avoid cholesterol, gluten_free=avoid gluten, no_lactose=avoid dairy.\n" .
            "Ingredient tags: contains_meat, contains_sugar, contains_cholesterol, contains_gluten, contains_lactose.\n\n" .
            "Return ONLY valid JSON (no markdown, no explanation). Format exactly like this:\n" .
            "{\"score\": number, \"warning_message\": string|null}";
    }

}