<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class adminpanel extends Controller
{
    #[OA\Get(
        path: '/admin/stats',
        summary: 'Get platform statistics',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Platform stats',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'users',               type: 'integer', example: 42),
                        new OA\Property(property: 'active_categories',   type: 'integer', example: 5),
                        new OA\Property(property: 'disabled_categories', type: 'integer', example: 2),
                        new OA\Property(property: 'dishes',              type: 'integer', example: 120),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function status()
    {
        $user       = User::where('role', 'user')->count();
        $activecata = Category::where('is_active', true)->count();
        $descata    = Category::where('is_active', false)->count();
        $dishes     = Dish::count();

        return response()->json([
            'users'               => $user,
            'active_categories'   => $activecata,
            'disabled_categories' => $descata,
            'dishes'              => $dishes
        ]);
    }
}