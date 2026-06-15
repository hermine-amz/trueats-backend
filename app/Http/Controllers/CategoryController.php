<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('libelle')->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
        ]);

        $libelle = trim($validated['libelle']);

        $existing = Category::whereRaw('LOWER(libelle) = ?', [mb_strtolower($libelle)])->first();
        if ($existing) {
            return response()->json($existing);
        }

        $category = Category::create(['libelle' => $libelle]);

        return response()->json($category, 201);
    }
}
