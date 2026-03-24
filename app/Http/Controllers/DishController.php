<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DishController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display a listing of the resource.
     * Only returns dishes belonging to the authenticated user.
     */
    public function index()
    {
        return auth()->user()->dishes()->with('category')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'category_id'    => 'required|exists:categories,id',
            'ingredient_ids' => 'nullable|array',
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Dish::with('category')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dish = Dish::findOrFail($id);

        $this->authorize('delete', $dish);

        $dish->delete();

        return response()->json(['message' => 'Deleted']);
    }
}