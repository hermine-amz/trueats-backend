<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Restaurant;
use App\Models\Signal;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'required|string',
            'latitude_client' => 'required|numeric',
            'longitude_client' => 'required|numeric',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);

        if (!$restaurant->est_valide || $restaurant->estBloque()) {
            return response()->json([
                'message' => 'This restaurant is currently unavailable.'
            ], 403);
        }

        $distance = $this->calculateDistance(
            $validated['latitude_client'],
            $validated['longitude_client'],
            $restaurant->latitude,
            $restaurant->longitude
        );

        if ($distance > $restaurant->rayon_validation) {
            return response()->json([
                'message' => 'Vous devez être dans le restaurant'
            ], 403);
        }

        $avis = Avis::create([
            'note' => $validated['note'],
            'commentaire' => strip_tags($validated['commentaire']),
            'date_visite' => now(),
            'lat_client' => $validated['latitude_client'],
            'long_client' => $validated['longitude_client'],
            'est_publie' => true,
            'user_id' => $request->user()->id,
            'restaurant_id' => $validated['restaurant_id'],
        ]);

        return response()->json($avis, 201);
    }

    public function signal(Request $request, $id)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
        ]);

        $avis = Avis::findOrFail($id);

        $signal = Signal::create([
            'libelle' => $validated['libelle'],
            'user_id' => $request->user()->id,
            'avis_id' => $avis->id,
        ]);

        return response()->json($signal, 201);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
