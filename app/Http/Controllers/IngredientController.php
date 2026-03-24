<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class IngredientController extends Controller
{
    #[OA\Get(
        path: '/ingredients',
        summary: 'List all ingredients',
        security: [['sanctum' => []]],
        tags: ['Ingredients'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of ingredients',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id',   type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
                        ],
                    ),
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index()
    {
        return Ingredient::select('id', 'name', 'tags')->get();
    }

    public function create() {}

    #[OA\Post(
        path: '/ingredients',
        summary: 'Create a new ingredient',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Chicken'),
                    new OA\Property(
                        property: 'tags',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['contains_meat'],
                    ),
                ],
            ),
        ),
        tags: ['Ingredients'],
        responses: [
            new OA\Response(response: 201, description: 'Ingredient created'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $ingredient = Ingredient::create($validated);

        return response()->json($ingredient, 201);
    }

    #[OA\Get(
        path: '/ingredients/{ingredient}',
        summary: 'Get a single ingredient',
        security: [['sanctum' => []]],
        tags: ['Ingredients'],
        parameters: [
            new OA\Parameter(name: 'ingredient', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ingredient details'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(Ingredient $ingredient) {}

    public function edit(Ingredient $ingredient) {}

    #[OA\Put(
        path: '/ingredients/{ingredient}',
        summary: 'Update an ingredient',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Beef'),
                    new OA\Property(
                        property: 'tags',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['contains_meat', 'contains_cholesterol'],
                    ),
                ],
            ),
        ),
        tags: ['Ingredients'],
        parameters: [
            new OA\Parameter(name: 'ingredient', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ingredient updated'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $ingredient->update($validated);

        return response()->json($ingredient, 200);
    }

    #[OA\Delete(
        path: '/ingredients/{ingredient}',
        summary: 'Delete an ingredient',
        security: [['sanctum' => []]],
        tags: ['Ingredients'],
        parameters: [
            new OA\Parameter(name: 'ingredient', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ingredient deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Deleted'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }
}