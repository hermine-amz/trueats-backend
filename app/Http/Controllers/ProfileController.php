<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'telephone' => ['nullable', 'string', 'regex:/^\+22901[0-9]{8}$/'],
            'sexe' => 'nullable|string|max:10',
        ];

        // Only require current_password if email is changing or new password is being set
        $emailChanged = $request->has('email') && $request->input('email') !== $user->email;
        $passwordChanged = $request->filled('password');

        if ($emailChanged) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $user->id;
        } else {
            $rules['email'] = 'sometimes|string|email|max:255|unique:users,email,' . $user->id;
        }

        if ($passwordChanged) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        if ($emailChanged || $passwordChanged) {
            $rules['current_password'] = 'required|string';
        }

        $validated = $request->validate($rules);

        if ($emailChanged || $passwordChanged) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Le mot de passe actuel est incorrect.',
                    'errors' => [
                        'current_password' => ['Le mot de passe actuel est incorrect.']
                    ]
                ], 422);
            }
        }

        $user->fill($request->only(['nom', 'prenom', 'email', 'telephone', 'sexe']));

        if ($passwordChanged) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user
        ]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Your account has been deleted successfully.'
        ]);
    }
}
