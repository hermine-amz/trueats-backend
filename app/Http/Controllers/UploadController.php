<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimes:jpeg,jpg,png,webp|max:5120',
            'type' => 'nullable|in:logo,photo,plat',
        ]);

        $folder = match ($validated['type'] ?? null) {
            'logo' => 'images/logos',
            'photo' => 'images/photos',
            'plat' => 'images/plats',
            default => 'images/general',
        };

        Storage::disk('public')->makeDirectory($folder);

        $path = $request->file('image')->store($folder, 'public');
        $url = '/storage/' . str_replace('\\', '/', $path);

        return response()->json([
            'url' => $url,
            'path' => $path,
        ], 201);
    }

    // Upload séparé pour les documents légaux (PDF ou image)
    // Accessible uniquement aux gérants — les URLs sont envoyées avec
    // la création du restaurant et seront vérifiées par l'admin
    public function storeDocument(Request $request)
    {
        $request->validate([
            // On accepte PDF + images pour les pièces justificatives
            'document' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'type' => 'nullable|in:cip,ifu_attestation,rccm_extrait',
        ]);

        $folder = 'documents/' . ($request->input('type') ?? 'general');
        Storage::disk('public')->makeDirectory($folder);

        $path = $request->file('document')->store($folder, 'public');
        $url = '/storage/' . str_replace('\\', '/', $path);

        return response()->json([
            'url' => $url,
            'path' => $path,
        ], 201);
    }

    public function serveFile($path)
    {
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $filePath = Storage::disk('public')->path($path);
        return response()->file($filePath);
    }
}
