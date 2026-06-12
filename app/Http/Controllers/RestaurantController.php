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

        return response()->json($restaurant);
    }

    public function verifyGps(Request $request, $id)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $restaurant = Restaurant::findOrFail($id);

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
