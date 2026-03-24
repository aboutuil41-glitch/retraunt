<?php

namespace App\Jobs;

use App\Models\Dish;
use App\Models\Recommendations;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRecommendation implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $userId,
        private readonly int $dishId,
    ) {}

    public function handle(AiService $aiService): void
    {
        $dish = Dish::with('ingredients')->findOrFail($this->dishId);
        $user = config('auth.providers.users.model')::findOrFail($this->userId);

        $result = $aiService->analyzeDish(
            $user->dietary_tags ?? [],
            $dish->name,
            $dish->ingredients->map(fn($i) => ['name' => $i->name, 'tags' => $i->tags])->toArray()
        );

        $score = $result['score'];

        Recommendations::where('user_id', $this->userId)
            ->where('dish_id', $this->dishId)
            ->update([
                'score'           => $score,
                'label'           => $score >= 80 ? 'Highly Recommended' : ($score >= 50 ? 'Recommended with notes' : 'Not Recommended'),
                'warning_message' => $score < 50 ? ($result['warning_message'] ?? 'Not suitable for your diet.') : null,
                'status'          => 'ready',
            ]);
    }
}