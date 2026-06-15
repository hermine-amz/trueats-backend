<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

            $this->filterPlatsForClient($restaurant, $user);
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
            'logo_url' => 'sometimes|nullable|string|max:2048',
            'photo_url' => 'sometimes|nullable|string|max:2048',
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
            'nom'               => 'required|string|max:255',
            'adresse'           => 'required|string|max:255',
            'quartier'          => 'nullable|string|max:100',
            'categorie'         => 'nullable|string|max:100',
            'type_cuisine'      => 'nullable|string|max:100',
            'latitude'          => 'required|numeric',
            'longitude'         => 'required|numeric',
            'superficie'        => 'required|integer|min:10',
            // Documents légaux — on reçoit les URLs après upload séparé
            'cip_url'              => 'nullable|string|max:2048',
            'ifu_numero'           => 'nullable|string|max:50',
            'ifu_attestation_url'  => 'nullable|string|max:2048',
            'rccm_numero'          => 'nullable|string|max:100',
            'rccm_extrait_url'     => 'nullable|string|max:2048',
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

        // 3. Create the restaurant — est_valide = false par défaut, en attente de validation admin
        $restaurant = Restaurant::create([
            'nom'                  => $validated['nom'],
            'adresse'              => $validated['adresse'],
            'quartier'             => $validated['quartier'] ?? null,
            'categorie'            => $validated['categorie'] ?? null,
            'type_cuisine'         => $validated['type_cuisine'] ?? null,
            'latitude'             => $validated['latitude'],
            'longitude'            => $validated['longitude'],
            'qr_code_identifier'   => Str::uuid()->toString(),
            'superficie'           => $validated['superficie'],
            'gerant_id'            => $request->user()->id,
            'est_valide'           => false,
            'cip_url'              => $validated['cip_url'] ?? null,
            'ifu_numero'           => $validated['ifu_numero'] ?? null,
            'ifu_attestation_url'  => $validated['ifu_attestation_url'] ?? null,
            'rccm_numero'          => $validated['rccm_numero'] ?? null,
            'rccm_extrait_url'     => $validated['rccm_extrait_url'] ?? null,
        ]);

        return response()->json([
            'message'    => 'Restaurant registered successfully. Pending administrator validation.',
            'restaurant' => $restaurant
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Restaurant::with(['plats.category']);

        if ($request->has('manager_id')) {
            $query->where('gerant_id', $request->query('manager_id'));
        }

        if ($request->has('max_budget')) {
            $maxBudget = $request->query('max_budget');
            $query->whereHas('plats', function ($q) use ($maxBudget) {
                $q->where('prix', '<=', $maxBudget);
            });
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%");
            });
        }

        $restaurants = $query->get();

        $user = auth('sanctum')->user();
        $isAdmin = $user && $user->role === 'admin';

        $filtered = $restaurants->filter(function ($restaurant) use ($user, $isAdmin) {
            $isOwner = $user && $user->id === $restaurant->gerant_id;
            if ($isAdmin || $isOwner) {
                return true;
            }
            return $restaurant->est_valide && !$restaurant->estBloque();
        });

        $result = $filtered->values()->map(function ($restaurant) use ($user) {
            $this->filterPlatsForClient($restaurant, $user);
            return $restaurant;
        });

        return response()->json($result);
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['plats.category'])->findOrFail($id);

        $user = auth('sanctum')->user();
        $isManagerOrAdmin = $user && ($user->role === 'admin' || $user->id === $restaurant->gerant_id);

        if (!$isManagerOrAdmin) {
            if (!$restaurant->est_valide) {
                return response()->json(['message' => 'This restaurant is pending validation.'], 403);
            }

            if ($restaurant->estBloque()) {
                return response()->json(['message' => 'This restaurant is temporarily suspended.'], 403);
            }

            $this->filterPlatsForClient($restaurant, $user);
        }

        return response()->json($restaurant);
    }

    private function filterPlatsForClient(Restaurant $restaurant, $user): void
    {
        $isManagerOrAdmin = $user && (
            $user->role === 'admin' || $user->id === $restaurant->gerant_id
        );

        if (!$isManagerOrAdmin && $restaurant->relationLoaded('plats')) {
            $restaurant->setRelation(
                'plats',
                $restaurant->plats->where('disponible', true)->values()
            );
        }
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
