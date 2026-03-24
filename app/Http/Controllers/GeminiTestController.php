<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Attributes as OA;

class GeminiTestController extends Controller
{
    #[OA\Get(
        path: '/gemini-test',
        summary: 'Test Gemini API connectivity',
        security: [['sanctum' => []]],
        tags: ['Gemini'],
        responses: [
            new OA\Response(response: 200, description: 'Raw Gemini API response'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function test()
    {
        $apiKey = env('GEMINI_API_KEY');

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
            [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => "Say hello like a cool AI"]
                        ]
                    ]
                ]
            ]
        );

        return $response->json();
    }

    #[OA\Get(
        path: '/list-models',
        summary: 'List available Gemini models',
        security: [['sanctum' => []]],
        tags: ['Gemini'],
        responses: [
            new OA\Response(response: 200, description: 'List of available Gemini models'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function listModels()
    {
        $apiKey = env('GEMINI_API_KEY');

        $response = Http::get(
            "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}"
        );

        return $response->json();
    }
}