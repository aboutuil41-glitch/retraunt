<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/me',
        summary: 'Get authenticated user info',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User info',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name',  type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                        new OA\Property(property: 'role',  type: 'string', example: 'user'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index()
    {
        return auth()->user()->only(['name', 'email', 'role']);
    }

    #[OA\Get(
        path: '/profile',
        summary: 'Get authenticated user dietary profile',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dietary profile',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(
                            property: 'dietary_tags',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['vegan', 'gluten_free'],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function DietaryProfile()
    {
        return auth()->user()->only(['name', 'dietary_tags']);
    }

    #[OA\Put(
        path: '/profile',
        summary: 'Update authenticated user dietary profile',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'dietary_tags',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['vegan', 'no_sugar'],
                    ),
                ],
            ),
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated dietary tags',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'dietary_tags',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function UpdateDietaryProfile(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $user->update($request->only('dietary_tags'));

        return auth()->user()->only(['dietary_tags']);
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(User $user) {}
    public function edit(User $user) {}
    public function update(Request $request, User $user) {}
    public function destroy(User $user) {}
}