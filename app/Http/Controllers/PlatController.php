<?php

namespace App\Http\Controllers;

use App\Models\Plat;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class PlatController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'disponible' => 'boolean',
            'restaurant_id' => 'required|exists:restaurants,id',
            'categorie_id' => 'required|exists:categories,id',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);

        // Check if the current manager is the owner of the restaurant
        if ($restaurant->gerant_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this restaurant.'
            ], 403);
        }

        $plat = Plat::create($validated);

        return response()->json($plat, 201);
    }

    public function update(Request $request, $id)
    {
        $plat = Plat::findOrFail($id);
        $restaurant = $plat->restaurant;

        // Check ownership
        if ($restaurant->gerant_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this restaurant.'
            ], 403);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'sometimes|numeric|min:0',
            'disponible' => 'sometimes|boolean',
            'categorie_id' => 'sometimes|exists:categories,id',
        ]);

        $plat->update($validated);

        return response()->json($plat);
    }

    public function destroy(Request $request, $id)
    {
        $plat = Plat::findOrFail($id);
        $restaurant = $plat->restaurant;

        // Check ownership
        if ($restaurant->gerant_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this restaurant.'
            ], 403);
        }

        $plat->delete();

        return response()->json([
            'message' => 'Dish deleted successfully.'
        ]);
    }
}
