<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function demandes(Request $request)
    {
        // Restaurants en attente de validation avec les infos du gérant
        $demandes = Restaurant::with('gerant')
            ->where('est_valide', false)
            ->whereNull('bloque_jusqua')
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'id'                   => $r->id,
                    'nom'                  => $r->nom,
                    'adresse'              => $r->adresse,
                    'quartier'             => $r->quartier,
                    'categorie'            => $r->categorie,
                    'type_cuisine'         => $r->type_cuisine,
                    'superficie'           => $r->superficie,
                    'latitude'             => $r->latitude,
                    'longitude'            => $r->longitude,
                    'created_at'           => $r->created_at,
                    'cip_url'              => $r->cip_url,
                    'ifu_numero'           => $r->ifu_numero,
                    'ifu_attestation_url'  => $r->ifu_attestation_url,
                    'rccm_numero'          => $r->rccm_numero,
                    'rccm_extrait_url'     => $r->rccm_extrait_url,
                    'gerant' => $r->gerant ? [
                        'id'     => $r->gerant->id,
                        'nom'    => $r->gerant->nom,
                        'prenom' => $r->gerant->prenom,
                        'email'  => $r->gerant->email,
                    ] : null,
                ];
            });

        return response()->json($demandes);
    }

    public function validerRestaurant(Request $request, $id)
    {
        $validated = $request->validate([
            'est_valide'   => 'required|boolean',
            'motif_rejet'  => 'nullable|string|max:500',
        ]);

        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update([
            'est_valide'  => $validated['est_valide'],
            'motif_rejet' => $validated['est_valide'] ? null : ($validated['motif_rejet'] ?? null),
        ]);

        if ($validated['est_valide'] && $restaurant->gerant_id) {
            $gerant = User::find($restaurant->gerant_id);
            if ($gerant && $gerant->role === 'client') {
                $gerant->update(['role' => 'gerant']);
            }
        }

        return response()->json([
            'message'    => $validated['est_valide'] ? 'Restaurant validated successfully.' : 'Restaurant validation rejected.',
            'restaurant' => $restaurant
        ]);
    }

    public function bloquerRestaurant(Request $request, $id)
    {
        $validated = $request->validate([
            'bloque' => 'required|boolean',
            'duree_jours' => 'nullable|integer|min:1',
        ]);

        $restaurant = Restaurant::findOrFail($id);

        if ($validated['bloque']) {
            $bloqueJusqua = isset($validated['duree_jours']) 
                ? now()->addDays($validated['duree_jours']) 
                : now()->addYears(99); // permanent suspension

            $restaurant->update([
                'bloque_jusqua' => $bloqueJusqua
            ]);

            return response()->json([
                'message' => 'Restaurant blocked successfully.',
                'bloque_jusqua' => $bloqueJusqua
            ]);
        } else {
            $restaurant->update([
                'bloque_jusqua' => null
            ]);

            return response()->json([
                'message' => 'Restaurant unblocked successfully.'
            ]);
        }
    }

    public function bloquerUser(Request $request, $id)
    {
        $validated = $request->validate([
            'bloque' => 'required|boolean',
            'duree_jours' => 'nullable|integer|min:1',
        ]);

        $user = User::findOrFail($id);

        // Prevent self-blocking
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot block your own admin account.'
            ], 400);
        }

        if ($validated['bloque']) {
            if (isset($validated['duree_jours'])) {
                $bloqueJusqua = now()->addDays($validated['duree_jours']);
                $user->update([
                    'bloque_jusqua' => $bloqueJusqua,
                    'compte_active' => true // kept active but blocked temporarily by timestamp
                ]);
            } else {
                $user->update([
                    'bloque_jusqua' => null,
                    'compte_active' => false // permanently deactivated
                ]);
            }

            return response()->json([
                'message' => 'User account restricted/blocked successfully.',
                'user' => $user
            ]);
        } else {
            $user->update([
                'bloque_jusqua' => null,
                'compte_active' => true
            ]);

            return response()->json([
                'message' => 'User account activated/unblocked successfully.',
                'user' => $user
            ]);
        }
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === auth()->user()->id) {
            return response()->json([
                'message' => 'You cannot delete your own admin account.'
            ], 400);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User account deleted successfully.'
        ]);
    }

    public function allUsers(Request $request)
    {
        return response()->json(User::all());
    }

    public function allSignalements(Request $request)
    {
        $signals = \App\Models\Signal::with(['user', 'avis.user', 'avis.restaurant'])->latest()->get();
        return response()->json($signals);
    }

    public function handleSignalement(Request $request, $id)
    {
        $validated = $request->validate([
            'keep_review' => 'required|boolean',
        ]);

        $signal = \App\Models\Signal::findOrFail($id);

        if (!$validated['keep_review']) {
            if ($signal->avis) {
                $signal->avis->delete();
            }
        } else {
            $signal->delete();
        }

        return response()->json([
            'message' => 'Signalement processed successfully.'
        ]);
    }

    public function stats(Request $request)
    {
        $totalUsers = User::count();
        $totalClients = User::whereIn('role', ['client', 'utilisateur'])->count();
        $totalGerants = User::where('role', 'gerant')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        $totalRestaurants = Restaurant::count();
        $totalRestaurantsValides = Restaurant::where('est_valide', true)->count();
        $totalRestaurantsEnAttente = Restaurant::where('est_valide', false)->whereNull('bloque_jusqua')->count();
        $totalRestaurantsBloques = Restaurant::whereNotNull('bloque_jusqua')->count();

        $totalAvis = \App\Models\Avis::count();
        $totalAvisSignales = \App\Models\Signal::distinct('avis_id')->count('avis_id');
        $averageRating = \App\Models\Avis::avg('note') ?? 0;

        return response()->json([
            'users' => [
                'total' => $totalUsers,
                'clients' => $totalClients,
                'gerants' => $totalGerants,
                'admins' => $totalAdmins,
            ],
            'restaurants' => [
                'total' => $totalRestaurants,
                'valides' => $totalRestaurantsValides,
                'en_attente' => $totalRestaurantsEnAttente,
                'bloques' => $totalRestaurantsBloques,
            ],
            'avis' => [
                'total' => $totalAvis,
                'signales' => $totalAvisSignales,
                'note_moyenne' => round($averageRating, 2),
            ]
        ]);
    }
}
