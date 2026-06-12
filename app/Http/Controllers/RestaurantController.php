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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'qr_code_identifier' => 'required|string|unique:restaurants,qr_code_identifier',
            'superficie' => 'required|integer|min:10',
        ]);

        // 1. Exact match check
        $exactMatch = Restaurant::where('latitude', $validated['latitude'])
            ->where('longitude', $validated['longitude'])
            ->exists();

        if ($exactMatch) {
            return response()->json([
                'message' => 'A restaurant already exists at these exact GPS coordinates.'
            ], 422);
        }

        // 2. Proximity check for same name
        $potentialDuplicates = Restaurant::where('nom', $validated['nom'])->get();

        foreach ($potentialDuplicates as $existing) {
            $distance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $existing->latitude,
                $existing->longitude
            );

            if ($distance < 50) {
                return response()->json([
                    'message' => 'A restaurant with the same name already exists in this location (less than 50 meters away).'
                ], 422);
            }
        }

        // 3. Create the restaurant
        $restaurant = Restaurant::create([
            'nom' => $validated['nom'],
            'adresse' => $validated['adresse'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'qr_code_identifier' => $validated['qr_code_identifier'],
            'superficie' => $validated['superficie'],
            'gerant_id' => $request->user()->id,
            'est_valide' => false, // Default to false for administrator validation
        ]);

        return response()->json([
            'message' => 'Restaurant registered successfully. Pending administrator validation.',
            'restaurant' => $restaurant
        ], 201);
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
