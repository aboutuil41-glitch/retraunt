<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRecommendation;
use App\Models\Dish;
use App\Models\Recommendations;
use App\Services\AiService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RecommendationController extends Controller
{
    #[OA\Post(
        path: '/recommendations/analyze/{plate_id}',
        summary: "Analyze a dish against the user's dietary restrictions using AI",
        security: [['sanctum' => []]],
        tags: ['Recommendations'],
        parameters: [
            new OA\Parameter(
                name: 'plate_id',
                in: 'path',
                required: true,
                description: 'ID of the dish to analyze',
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 202,
                description: 'Analysis queued',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id',              type: 'integer'),
                        new OA\Property(property: 'user_id',         type: 'integer'),
                        new OA\Property(property: 'dish_id',         type: 'integer'),
                        new OA\Property(property: 'score',           type: 'integer', nullable: true),
                        new OA\Property(property: 'label',           type: 'string',  nullable: true),
                        new OA\Property(property: 'warning_message', type: 'string',  nullable: true),
                        new OA\Property(property: 'status',          type: 'string',  example: 'processing'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Dish not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function analyze(int $plateId)
    {
        $dish = Dish::findOrFail($plateId);
        $user = auth()->user();

        $recommendation = Recommendations::updateOrCreate(
            ['user_id' => $user->id, 'dish_id' => $dish->id],
            [
                'score'           => null,
                'label'           => null,
                'warning_message' => null,
                'status'          => 'processing',
            ]
        );

        ProcessRecommendation::dispatch($user->id, $dish->id)->delay(now()->addMinutes(10));;

        return response()->json($recommendation, 202);
    }

    #[OA\Get(
        path: '/recommendations',
        summary: 'List all recommendations for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Recommendations'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of recommendations with dish info',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id',              type: 'integer'),
                            new OA\Property(property: 'score',           type: 'integer', nullable: true),
                            new OA\Property(property: 'label',           type: 'string',  nullable: true),
                            new OA\Property(property: 'warning_message', type: 'string',  nullable: true),
                            new OA\Property(property: 'status',          type: 'string'),
                            new OA\Property(
                                property: 'dish',
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id',    type: 'integer'),
                                    new OA\Property(property: 'name',  type: 'string'),
                                    new OA\Property(property: 'price', type: 'number'),
                                ],
                            ),
                        ],
                    ),
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index()
    {
        return response()->json(
            Recommendations::with('dish:id,name,price')
                ->where('user_id', auth()->id())
                ->latest()->get()
        );
    }

    #[OA\Get(
        path: '/recommendations/{plate_id}',
        summary: 'Get a single recommendation for a dish',
        security: [['sanctum' => []]],
        tags: ['Recommendations'],
        parameters: [
            new OA\Parameter(
                name: 'plate_id',
                in: 'path',
                required: true,
                description: 'ID of the dish',
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Recommendation details'),
            new OA\Response(response: 404, description: 'Recommendation not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
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