<?php

namespace App\Http\Controllers;

use App\Models\Exploration;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class ExplorationController extends Controller
{
    public function index(Request $request)
    {
        $explorations = Exploration::with('restaurant')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($explorations);
    }

    public function explore(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $exploration = Exploration::firstOrCreate([
            'user_id' => $request->user()->id,
            'restaurant_id' => $restaurant->id,
        ]);

        return response()->json($exploration, 201);
    }
}
