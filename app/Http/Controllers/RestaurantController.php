<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function getByQrCode($qrCode)
    {
        $restaurant = Restaurant::with(['plats.category'])
            ->where('qr_code_identifier', $qrCode)
            ->firstOrFail();

        $user = auth('sanctum')->user();
        $isManagerOrAdmin = $user && ($user->role === 'admin' || $user->id === $restaurant->gerant_id);

        if (!$isManagerOrAdmin) {
            if (!$restaurant->est_valide) {
                return response()->json(['message' => 'This restaurant is pending validation.'], 403);
            }

            if ($restaurant->estBloque()) {
                return response()->json(['message' => 'This restaurant is temporarily suspended.'], 403);
            }
        }

        return response()->json($restaurant);
    }

    public function verifyGps(Request $request, $id)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $restaurant = Restaurant::findOrFail($id);

        if (!$restaurant->est_valide || $restaurant->estBloque()) {
            return response()->json([
                'message' => 'This restaurant is currently unavailable.'
            ], 403);
        }

        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $restaurant->latitude,
            $restaurant->longitude
        );

        $inPerimeter = $distance <= $restaurant->rayon_validation;

        return response()->json([
            'in_perimeter' => $inPerimeter,
            'distance' => round($distance, 2)
        ]);
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        // Check if current user is the owner
        if ($restaurant->gerant_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this restaurant.'
            ], 403);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'superficie' => 'sometimes|integer|min:1',
        ]);

        $restaurant->update($validated);

        return response()->json([
            'message' => 'Restaurant updated successfully.',
            'restaurant' => $restaurant
        ]);
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
