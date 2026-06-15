<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Plat;
use App\Models\Avis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'nom' => 'System',
            'prenom' => 'Admin',
            'email' => 'admin@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'compte_active' => true,
        ]);

        $gerant = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'gerant@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        $gerantTanti = User::create([
            'nom' => 'Koffi',
            'prenom' => 'Aya',
            'email' => 'tanti@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        $gerantBissap = User::create([
            'nom' => 'Soglo',
            'prenom' => 'Komi',
            'email' => 'bissap@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        $client = User::create([
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'client@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'compte_active' => true,
        ]);

        // Categories
        $pizzaCat = Category::create(['libelle' => 'Pizzas']);
        $burgerCat = Category::create(['libelle' => 'Burgers']);
        $boissonCat = Category::create(['libelle' => 'Boissons']);
        $platsPrincipauxCat = Category::create(['libelle' => 'Plats Principaux']);
        $accompagnementsCat = Category::create(['libelle' => 'Accompagnements']);
        $brunchCat = Category::create(['libelle' => 'Brunch']);
        $cafeteriaCat = Category::create(['libelle' => 'Cafétéria']);
        $dessertsCat = Category::create(['libelle' => 'Desserts']);

        // 1. Le Bistro Gourmet
        $restaurant = Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $gerant->id,
            'superficie' => 120,
            'est_valide' => true,
        ]);

        Plat::create([
            'nom' => 'Pizza Margherita',
            'description' => 'Sauce tomate bio, mozzarella, basilic frais',
            'prix' => 12.50,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $pizzaCat->id,
        ]);

        Plat::create([
            'nom' => 'Cheeseburger',
            'description' => 'Boeuf charolais, cheddar affiné, frites maison',
            'prix' => 15.00,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $burgerCat->id,
        ]);

        Plat::create([
            'nom' => 'Coca-Cola',
            'description' => '33cl',
            'prix' => 3.50,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $boissonCat->id,
        ]);

        Avis::create([
            'note' => 5,
            'commentaire' => 'Excellente nourriture et service rapide. Je recommande vivement !',
            'date_visite' => now(),
            'lat_client' => 48.8698,
            'long_client' => 2.3312,
            'est_publie' => true,
            'user_id' => $client->id,
            'restaurant_id' => $restaurant->id,
        ]);

        // 2. Chez Tanti
        $tanti = Restaurant::create([
            'nom' => 'Maquis Chez Tanti',
            'adresse' => 'Rue 820, Haie-Vive, Cotonou',
            'latitude' => 6.35712,
            'longitude' => 2.40892,
            'qr_code_identifier' => 'trueats_restaurant_1',
            'gerant_id' => $gerantTanti->id,
            'superficie' => 150,
            'est_valide' => true,
        ]);

        Plat::create([
            'nom' => 'Poulet braisé attiéké',
            'description' => 'Poulet mariné grillé au charbon de bois, attiéké fondant, sauce oignons et piment.',
            'prix' => 3500.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $platsPrincipauxCat->id,
        ]);

        Plat::create([
            'nom' => 'Poisson braisé alloco',
            'description' => 'Dorade entière braisée, bananes plantains frites (alloco), sauce piquante.',
            'prix' => 4200.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $platsPrincipauxCat->id,
        ]);

        Plat::create([
            'nom' => 'Alloco simple',
            'description' => 'Portion de bananes plantains mûres frites.',
            'prix' => 1000.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $accompagnementsCat->id,
        ]);

        Plat::create([
            'nom' => 'Jus de Bissap maison',
            'description' => 'Jus de fleurs d\'hibiscus infusé à la menthe fraîche.',
            'prix' => 800.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $boissonCat->id,
        ]);

        Avis::create([
            'note' => 4,
            'commentaire' => 'Le poulet braisé était parfaitement assaisonné, l\'attiéké fondant. Service rapide, ambiance conviviale...',
            'date_visite' => now()->subDays(2),
            'lat_client' => 6.35710,
            'long_client' => 2.40890,
            'est_publie' => true,
            'user_id' => $client->id,
            'restaurant_id' => $tanti->id,
        ]);

        // 3. Le Petit Bissap
        $bissap = Restaurant::create([
            'nom' => 'Le Petit Bissap',
            'adresse' => 'Avenue Jean-Paul II, Cotonou',
            'latitude' => 6.35245,
            'longitude' => 2.39956,
            'qr_code_identifier' => 'trueats_restaurant_2',
            'gerant_id' => $gerantBissap->id,
            'superficie' => 100,
            'est_valide' => true,
        ]);

        Plat::create([
            'nom' => 'Toast Avocat & OEuf Poché',
            'description' => 'Pain de campagne grillé, purée d\'avocat assaisonnée, oeuf poché, graines de courge.',
            'prix' => 2800.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $brunchCat->id,
        ]);

        Plat::create([
            'nom' => 'Pancakes aux fruits de saison',
            'description' => 'Trois pancakes moelleux, sirop d\'érable, bananes et mangues fraîches.',
            'prix' => 2500.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $brunchCat->id,
        ]);

        Plat::create([
            'nom' => 'Café Latte',
            'description' => 'Double expresso avec mousse de lait crémeuse.',
            'prix' => 1500.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $cafeteriaCat->id,
        ]);

        Plat::create([
            'nom' => 'Bissap Royal',
            'description' => 'Bissap avec morceaux de mangue et zeste de citron.',
            'prix' => 1200.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $boissonCat->id,
        ]);

        // 4. Chez Marcel
        $marcel = Restaurant::create([
            'nom' => 'Chez Marcel',
            'adresse' => 'Zone Résidentielle, Cotonou',
            'latitude' => 6.36100,
            'longitude' => 2.42150,
            'qr_code_identifier' => 'trueats_restaurant_3',
            'gerant_id' => $gerant->id, // managed by Jean Dupont
            'superficie' => 200,
            'est_valide' => true,
        ]);

        Plat::create([
            'nom' => 'Filet de capitaine grillé',
            'description' => 'Poisson capitaine local grillé, purée de patates douces au piment doux.',
            'prix' => 6500.00,
            'disponible' => true,
            'restaurant_id' => $marcel->id,
            'categorie_id' => $platsPrincipauxCat->id,
        ]);

        Plat::create([
            'nom' => 'Carpaccio de mangue et sa glace',
            'description' => 'Fines tranches de mangues béninoises, glace vanille et coulis de fruits de la passion.',
            'prix' => 2000.00,
            'disponible' => true,
            'restaurant_id' => $marcel->id,
            'categorie_id' => $dessertsCat->id,
        ]);
    }
}
