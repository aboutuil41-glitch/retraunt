<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/categories',
        summary: 'List all categories with their dishes',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'List of categories'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index()
    {
        return Category::with('dishes')->get();
    }

    #[OA\Get(
        path: '/categories/{id}/dish',
        summary: 'Get all dishes belonging to a category',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of dishes in the category'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
        ],
    )]
    public function indexDishes($id)
    {
        return Category::find($id)->dishes;
    }

    #[OA\Post(
        path: '/categories',
        summary: 'Create a new category',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Starters'),
                ],
            ),
        ),
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 201, description: 'Category created'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name'    => $request->name,
            'user_id' => auth()->id()
        ]);

        return response()->json($category, 201);
    }

    #[OA\Get(
        path: '/categories/{id}',
        summary: 'Get a single category with its dishes',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category details'),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function show(string $id)
    {
        return Category::with('dishes')->findOrFail($id);
    }

    #[OA\Put(
        path: '/categories/{id}',
        summary: 'Update a category',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Main Course'),
                ],
            ),
        ),
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category updated'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Category not found'),
        ],
    )]
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update($request->only('name'));

        return response()->json($category);
    }

    #[OA\Delete(
        path: '/categories/{id}',
        summary: 'Delete a category',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Deleted'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Category not found'),
        ],
    )]
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);
        $category->delete();

        return response()->json(['message' => 'Deleted']);
    }
}