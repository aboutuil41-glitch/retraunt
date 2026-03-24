<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

class DishController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/dishes',
        summary: 'List all dishes for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Dishes'],
        responses: [
            new OA\Response(response: 200, description: 'List of dishes'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index()
    {
        return Dish::with('category')->get();
    }

    #[OA\Post(
        path: '/dishes',
        summary: 'Create a new dish',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'category_id'],
                properties: [
                    new OA\Property(property: 'name',           type: 'string',  example: 'Grilled Salmon'),
                    new OA\Property(property: 'description',    type: 'string',  example: 'Fresh salmon with herbs'),
                    new OA\Property(property: 'price',          type: 'number',  format: 'float', example: 12.99),
                    new OA\Property(property: 'category_id',    type: 'integer', example: 1),
                    new OA\Property(
                        property: 'ingredient_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2, 3],
                    ),
                ],
            ),
        ),
        tags: ['Dishes'],
        responses: [
            new OA\Response(response: 201, description: 'Dish created'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'category_id'      => 'required|exists:categories,id',
            'ingredient_ids'   => 'nullable|array',
            'ingredient_ids.*' => 'exists:ingredients,id',
        ]);

        $dish = Dish::create([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'user_id'     => auth()->id(),
            'category_id' => $request->category_id,
        ]);

        if ($request->has('ingredient_ids')) {
            $dish->ingredients()->sync($request->ingredient_ids);
        }

        return response()->json($dish->load('ingredients'), 201);
    }

    #[OA\Get(
        path: '/dishes/{id}',
        summary: 'Get a single dish',
        security: [['sanctum' => []]],
        tags: ['Dishes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dish details'),
            new OA\Response(response: 404, description: 'Dish not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function show(string $id)
    {
        return Dish::with('category')->findOrFail($id);
    }

    #[OA\Put(
        path: '/dishes/{id}',
        summary: 'Update a dish',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name',        type: 'string',  example: 'Grilled Salmon'),
                    new OA\Property(property: 'description', type: 'string',  example: 'Updated description'),
                    new OA\Property(property: 'price',       type: 'number',  format: 'float', example: 14.99),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(
                        property: 'ingredient_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2],
                    ),
                ],
            ),
        ),
        tags: ['Dishes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dish updated'),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Dish not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'sometimes|required|numeric|min:0',
            'category_id'      => 'sometimes|required|exists:categories,id',
            'ingredient_ids'   => 'nullable|array',
            'ingredient_ids.*' => 'exists:ingredients,id',
        ]);

        $dish = Dish::findOrFail($id);
        $this->authorize('update', $dish);
        $dish->update($request->only('name', 'description', 'price', 'category_id'));

        if ($request->has('ingredient_ids')) {
            $dish->ingredients()->sync($request->ingredient_ids);
        }

        return response()->json($dish->load('ingredients'));
    }

    #[OA\Delete(
        path: '/dishes/{id}',
        summary: 'Delete a dish',
        security: [['sanctum' => []]],
        tags: ['Dishes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dish deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Deleted'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Dish not found'),
        ],
    )]
    public function destroy(string $id)
    {
        $dish = Dish::findOrFail($id);
        $this->authorize('delete', $dish);
        $dish->delete();

        return response()->json(['message' => 'Deleted']);
    }
}