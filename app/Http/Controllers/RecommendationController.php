<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Recommendations;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    public function analyze(int $plateId)
    {
        $dish = Dish::with('ingredients')->findOrFail($plateId);
        $user = auth()->user();

        $prompt =
            "User dietary restrictions: " . json_encode($user->dietary_tags ?? []) . "\n" .
            "Dish name: {$dish->name}\n" .
            "Ingredients: " . json_encode($dish->ingredients->map(fn($i) => ['name' => $i->name, 'tags' => $i->tags])) . "\n\n" .
            "Dietary tags: vegan=no animal products, no_sugar=avoid sugar, no_cholesterol=avoid cholesterol, gluten_free=avoid gluten, no_lactose=avoid dairy.\n" .
            "Ingredient tags: contains_meat, contains_sugar, contains_cholesterol, contains_gluten, contains_lactose.\n\n" .
            "Return ONLY this JSON (no markdown):\n{\"score\": <0-100>, \"warning_message\": \"<reason if score<50, else null>\"}";

        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . env('GEMINI_API_KEY'), [
            "contents" => [["parts" => [["text" => $prompt]]]]
        ]);

        if ($response->successful()) {
            $output = $response->json()['candidates'][0]['content']['parts'][0]['text'];
        } else {
            return response()->json(['message' => 'Gemini API error.'], 502);
        }

        $result = json_decode($output, true);
        $score  = (int) ($result['score'] ?? 0);

        return response()->json(Recommendations::updateOrCreate(
            ['user_id' => $user->id, 'dish_id' => $dish->id],
            [
                'score'           => $score,
                'label'           => $score >= 80 ? 'Highly Recommended' : ($score >= 50 ? 'Recommended with notes' : 'Not Recommended'),
                'warning_message' => $score < 50 ? ($result['warning_message'] ?? 'Not suitable for your diet.') : null,
                'status'          => 'ready',
            ]
        ));
    }

    public function index()
    {
        return response()->json(
            Recommendations::with('dish:id,name,price')
                ->where('user_id', auth()->id())
                ->latest()->get()
        );
    }

    public function show(int $plateId)
    {
        return response()->json(
            Recommendations::with('dish:id,name,price')
                ->where('user_id', auth()->id())
                ->where('dish_id', $plateId)
                ->firstOrFail()
        );
    }
}