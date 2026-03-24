<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Ingredient::select('id','name', 'tags')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $ingredient = Ingredient::create($validated);

        return response()->json($ingredient, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ingredient $ingredient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingredient $ingredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $ingredient->update($validated);

        return response()->json($ingredient, 200);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }
}
