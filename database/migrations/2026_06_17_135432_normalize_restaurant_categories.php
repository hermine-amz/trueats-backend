<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Liste officielle des catégories autorisées.
     * Doit rester synchronisée avec kRestaurantCategories dans benin_locations.dart
     * et la validation in: dans RestaurantController.php.
     */
    private const ALLOWED = [
        'Maquis',
        'Restaurant',
        'Fast-food',
        'Snack',
        'Buvette',
        'Café',
        'Bar',
        'Pâtisserie',
    ];

    /**
     * Table de correspondance : valeur brute (lowercase) → valeur normalisée.
     * Couvre les fautes de frappe et variantes courantes.
     */
    private const MAPPING = [
        // Maquis
        'maquis'        => 'Maquis',
        'le maquis'     => 'Maquis',

        // Restaurant
        'restaurant'    => 'Restaurant',
        'restaurants'   => 'Restaurant',
        'resto'         => 'Restaurant',
        'restau'        => 'Restaurant',

        // Fast-food
        'fast-food'     => 'Fast-food',
        'fast food'     => 'Fast-food',
        'fastfood'      => 'Fast-food',
        'fast_food'     => 'Fast-food',

        // Snack
        'snack'         => 'Snack',
        'snack-bar'     => 'Snack',
        'snackbar'      => 'Snack',

        // Buvette
        'buvette'       => 'Buvette',

        // Café
        'café'          => 'Café',
        'cafe'          => 'Café',
        'cafeteria'     => 'Café',
        'cafétéria'     => 'Café',
        'coffee'        => 'Café',

        // Bar
        'bar'           => 'Bar',
        'bar restaurant'=> 'Bar',
        'pub'           => 'Bar',

        // Pâtisserie
        'pâtisserie'    => 'Pâtisserie',
        'patisserie'    => 'Pâtisserie',
        'boulangerie'   => 'Pâtisserie',
        'boulangerie-pâtisserie' => 'Pâtisserie',
    ];

    public function up(): void
    {
        $restaurants = DB::table('restaurants')
            ->whereNotNull('categorie')
            ->select('id', 'categorie')
            ->get();

        $allowed = array_map('strtolower', self::ALLOWED);

        foreach ($restaurants as $r) {
            $raw     = trim($r->categorie);
            $rawLower = mb_strtolower($raw);

            // 1. Déjà une valeur valide (comparaison insensible à la casse)
            if (in_array($rawLower, $allowed)) {
                // Corriger seulement la casse si nécessaire
                $correct = self::ALLOWED[array_search($rawLower, $allowed)];
                if ($raw !== $correct) {
                    DB::table('restaurants')
                        ->where('id', $r->id)
                        ->update(['categorie' => $correct]);
                }
                continue;
            }

            // 2. Correspondance via la table de mapping
            if (isset(self::MAPPING[$rawLower])) {
                DB::table('restaurants')
                    ->where('id', $r->id)
                    ->update(['categorie' => self::MAPPING[$rawLower]]);
                continue;
            }

            // 3. Valeur inconnue → défaut "Restaurant"
            DB::table('restaurants')
                ->where('id', $r->id)
                ->update(['categorie' => 'Restaurant']);
        }

        // Restaurants sans catégorie → défaut "Restaurant"
        DB::table('restaurants')
            ->whereNull('categorie')
            ->orWhere('categorie', '')
            ->update(['categorie' => 'Restaurant']);
    }

    public function down(): void
    {
        // La normalisation est irréversible par nature.
        // On ne peut pas retrouver les anciennes valeurs libres.
    }
};
