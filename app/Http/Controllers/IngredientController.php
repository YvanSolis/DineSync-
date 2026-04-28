<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        return response()->json(Ingredient::all());
    }

    public function store(Request $request)
    {
        $ingredient = Ingredient::create($request->all());
        return response()->json($ingredient);
    }

    public function show(Ingredient $ingredient)
    {
        return response()->json($ingredient);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $ingredient->update($request->all());
        return response()->json($ingredient);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();
        return response()->json(['message' => 'Deleted']);
    }
}