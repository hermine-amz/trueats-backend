<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function validerRestaurant(Request $request, $id)
    {
        $validated = $request->validate([
            'est_valide' => 'required|boolean',
        ]);

        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update([
            'est_valide' => $validated['est_valide']
        ]);

        return response()->json([
            'message' => $validated['est_valide'] ? 'Restaurant validated successfully.' : 'Restaurant validation rejected.',
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
}
