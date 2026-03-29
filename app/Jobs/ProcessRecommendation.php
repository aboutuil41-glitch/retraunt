<?php

namespace App\Jobs;

use App\Models\Dish;
use App\Models\Recommendations;
use App\Models\User;
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
        $user = User::findOrFail($this->userId);


        $result = $aiService->analyzeDish(
            $user->dietary_tags ?? [],
            $dish->name,
            $dish->ingredients->map(fn($i) => ['name' => $i->name, 'tags' => $i->tags])->toArray()
        );

        
        Recommendations::where('user_id', $this->userId)
            ->where('dish_id', $this->dishId)
            ->update([
                'score'           => $result['score'],
                'label'           => $result['score'] >= 80 ? 'Highly Recommended' : ($result['score'] >= 50 ? 'Recommended with notes' : 'Not Recommended'),
                'warning_message' => $result['score'] < 50 ? ($result['warning_message'] ?? 'Not suitable for your diet.') : null,
                'status'          => 'ready',
            ]);

    }
}