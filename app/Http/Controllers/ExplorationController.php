<?php

namespace App\Http\Controllers;

use App\Models\Exploration;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class ExplorationController extends Controller
{
    public function index(Request $request)
    {
        $explorations = Exploration::with('restaurant.plats.category')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($explorations);
    }

    public function explore(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $exploration = Exploration::where('user_id', $request->user()->id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if ($exploration) {
            $exploration->delete();
            return response()->json(['message' => 'Removed from explorations.', 'is_explored' => false], 200);
        }

        $newExploration = Exploration::create([
            'user_id' => $request->user()->id,
            'restaurant_id' => $restaurant->id,
        ]);

        return response()->json($newExploration, 201);
    }
}
