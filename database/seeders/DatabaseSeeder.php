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
            'sexe' => 'Masculin',
        ]);

        $gerant = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'gerant@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
            'compte_active' => true,
            'sexe' => 'Masculin',
        ]);

        $gerantTanti = User::create([
            'nom' => 'Koffi',
            'prenom' => 'Aya',
            'email' => 'tanti@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
            'compte_active' => true,
            'sexe' => 'Féminin',
        ]);

        $gerantBissap = User::create([
            'nom' => 'Soglo',
            'prenom' => 'Komi',
            'email' => 'bissap@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
            'compte_active' => true,
            'sexe' => 'Masculin',
        ]);

        $client = User::create([
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'client@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'compte_active' => true,
            'sexe' => 'Féminin',
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
            'telephone' => '+33140205050',
            'horaires' => '12h00 - 23h00',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $gerant->id,
            'superficie' => 120,
            'est_valide' => true,
            'logo_url' => '/storage/images/logos/9q8Gqgg8WI1tH6kcTJAffetOLXC4eddMFkQXm988.jpg',
            'photo_url' => '/storage/images/photos/61sxQb0jZ0oEaflnUNhU7i1yfRtDJZQFa7nxd61H.jpg',
        ]);

        Plat::create([
            'nom' => 'Pizza Margherita',
            'description' => 'Sauce tomate bio, mozzarella, basilic frais',
            'prix' => 12.50,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $pizzaCat->id,
            'image_url' => '/storage/images/plats/88Aob86N7cY1YSjRa81E1pC2tDnWnKlzBxEWX0Y3.jpg',
        ]);

        Plat::create([
            'nom' => 'Cheeseburger',
            'description' => 'Boeuf charolais, cheddar affiné, frites maison',
            'prix' => 15.00,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $burgerCat->id,
            'image_url' => '/storage/images/plats/8zVvRTg7SR2xnoCRStSL3ABNznGME4Qqv7Zhq9JM.jpg',
        ]);

        Plat::create([
            'nom' => 'Coca-Cola',
            'description' => '33cl',
            'prix' => 3.50,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $boissonCat->id,
            'image_url' => '/storage/images/plats/9d8TIxoggl2cVVFcHGUgbxRhopSJqM4hOw2Wuyoa.jpg',
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
            'telephone' => '+22997970102',
            'horaires' => '11h30 - 23h30',
            'adresse' => 'Rue 820, Haie-Vive, Cotonou',
            'latitude' => 6.35712,
            'longitude' => 2.40892,
            'qr_code_identifier' => 'trueats_restaurant_1',
            'gerant_id' => $gerantTanti->id,
            'superficie' => 150,
            'est_valide' => true,
            'logo_url' => '/storage/images/logos/DjUWzCpicRNFTBuE9O2vNHgqsXBzJt5H6NDEWYYJ.jpg',
            'photo_url' => '/storage/images/photos/B5IdQHvjfDZQ0bxXB0XzRy369qeJi9AF0RN6g9gk.jpg',
        ]);

        Plat::create([
            'nom' => 'Poulet braisé attiéké',
            'description' => 'Poulet mariné grillé au charbon de bois, attiéké fondant, sauce oignons et piment.',
            'prix' => 3500.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $platsPrincipauxCat->id,
            'image_url' => '/storage/images/plats/BgcMmi5LVLz0C3Ez8PA05vUirNczXBNfyvxMPGJ7.jpg',
        ]);

        Plat::create([
            'nom' => 'Poisson braisé alloco',
            'description' => 'Dorade entière braisée, bananes plantains frites (alloco), sauce piquante.',
            'prix' => 4200.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $platsPrincipauxCat->id,
            'image_url' => '/storage/images/plats/CSEbWIGeSVNB5Xws9brCFnFZ9i0XCjTM8l7EwyWI.jpg',
        ]);

        Plat::create([
            'nom' => 'Alloco simple',
            'description' => 'Portion de bananes plantains mûres frites.',
            'prix' => 1000.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $accompagnementsCat->id,
            'image_url' => '/storage/images/plats/DphM6534DjPMJ7jBCCn0PiEfN5szFe75Ti1hT6oY.jpg',
        ]);

        Plat::create([
            'nom' => 'Jus de Bissap maison',
            'description' => 'Jus de fleurs d\'hibiscus infusé à la menthe fraîche.',
            'prix' => 800.00,
            'disponible' => true,
            'restaurant_id' => $tanti->id,
            'categorie_id' => $boissonCat->id,
            'image_url' => '/storage/images/plats/Fx95DzGPFXl1ntF89rd4Xs90LN7uSpIdGw0aLfNo.jpg',
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
            'telephone' => '+22961610304',
            'horaires' => '08h00 - 18h00',
            'adresse' => 'Avenue Jean-Paul II, Cotonou',
            'latitude' => 6.35245,
            'longitude' => 2.39956,
            'qr_code_identifier' => 'trueats_restaurant_2',
            'gerant_id' => $gerantBissap->id,
            'superficie' => 100,
            'est_valide' => true,
            'logo_url' => '/storage/images/logos/EKttHxqnvRfSx3ByDg3LjGUHP06GfAIa2IOH6rRB.jpg',
            'photo_url' => '/storage/images/photos/mJB8O5CO8rCvkzesdqWWim54lECHFk50o00N8PXA.jpg',
        ]);

        Plat::create([
            'nom' => 'Toast Avocat & OEuf Poché',
            'description' => 'Pain de campagne grillé, purée d\'avocat assaisonnée, oeuf poché, graines de courge.',
            'prix' => 2800.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $brunchCat->id,
            'image_url' => '/storage/images/plats/GdU9AMcM8NlxXYJXfb1e5CPgtIsD0ztLcXT6ngIg.jpg',
        ]);

        Plat::create([
            'nom' => 'Pancakes aux fruits de saison',
            'description' => 'Trois pancakes moelleux, sirop d\'érable, bananes et mangues fraîches.',
            'prix' => 2500.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $brunchCat->id,
            'image_url' => '/storage/images/plats/K4vwmHLDLQTIrkHCSAY1ojzx1fIdtHoPOyWs2Lyi.jpg',
        ]);

        Plat::create([
            'nom' => 'Café Latte',
            'description' => 'Double expresso avec mousse de lait crémeuse.',
            'prix' => 1500.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $cafeteriaCat->id,
            'image_url' => '/storage/images/plats/RrSntqiPbbO3bG6zCRXYCxuWzTeitJbtsVboX6ol.jpg',
        ]);

        Plat::create([
            'nom' => 'Bissap Royal',
            'description' => 'Bissap avec morceaux de mangue et zeste de citron.',
            'prix' => 1200.00,
            'disponible' => true,
            'restaurant_id' => $bissap->id,
            'categorie_id' => $boissonCat->id,
            'image_url' => '/storage/images/plats/Ul2UJYQlRFJOGKbLWxwEcftJ72Wn9L4K7iQzBQ2i.jpg',
        ]);

        // 4. Chez Marcel
        $marcel = Restaurant::create([
            'nom' => 'Chez Marcel',
            'telephone' => '+22995950506',
            'horaires' => '12h00 - 15h00, 19h00 - 23h00',
            'adresse' => 'Zone Résidentielle, Cotonou',
            'latitude' => 6.36100,
            'longitude' => 2.42150,
            'qr_code_identifier' => 'trueats_restaurant_3',
            'gerant_id' => $gerant->id, // managed by Jean Dupont
            'superficie' => 200,
            'est_valide' => true,
            'logo_url' => '/storage/images/logos/h5DprhrybxLo4Ya4HtQBpoOIspxcNyKMk3Asei53.jpg',
            'photo_url' => '/storage/images/photos/tVxVzbySRLWNz7pl2PTcSBleHy9HpFY6tT2ahC8Q.jpg',
        ]);

        Plat::create([
            'nom' => 'Filet de capitaine grillé',
            'description' => 'Poisson capitaine local grillé, purée de patates douces au piment doux.',
            'prix' => 6500.00,
            'disponible' => true,
            'restaurant_id' => $marcel->id,
            'categorie_id' => $platsPrincipauxCat->id,
            'image_url' => '/storage/images/plats/VOtU0PxlL6w63XkQG83XWnueYaba9UzW2Vj6DMW6.jpg',
        ]);

        Plat::create([
            'nom' => 'Carpaccio de mangue et sa glace',
            'description' => 'Fines tranches de mangues béninoises, glace vanille et coulis de fruits de la passion.',
            'prix' => 2000.00,
            'disponible' => true,
            'restaurant_id' => $marcel->id,
            'categorie_id' => $dessertsCat->id,
            'image_url' => '/storage/images/plats/Z8GOf0JVMkBuOHPCaieulTampH9scBRI8QMuLu7a.jpg',
        ]);

        // --- AVIS SUR LES RESTAURANTS ---

        // Avis supplémentaires sur Le Bistro Gourmet (id 1)
        Avis::create([
            'note' => 4,
            'commentaire' => 'Cadre très chaleureux et plats soignés. Une belle adresse gastronomique !',
            'date_visite' => now()->subDays(5),
            'lat_client' => 48.8698,
            'long_client' => 2.3312,
            'est_publie' => true,
            'user_id' => $gerantTanti->id,
            'restaurant_id' => $restaurant->id,
        ]);

        Avis::create([
            'note' => 5,
            'commentaire' => 'Très bon rapport qualité-prix. Les ingrédients sont très frais et le service impeccable.',
            'date_visite' => now()->subDays(8),
            'lat_client' => 48.8698,
            'long_client' => 2.3312,
            'est_publie' => true,
            'user_id' => $gerantBissap->id,
            'restaurant_id' => $restaurant->id,
        ]);

        // Avis supplémentaires sur Maquis Chez Tanti (id 2)
        Avis::create([
            'note' => 5,
            'commentaire' => 'L\'attiéké au poulet braisé est un délice absolu ! Rapport qualité-prix imbattable à la Haie-Vive.',
            'date_visite' => now()->subDays(1),
            'lat_client' => 6.35712,
            'long_client' => 2.40892,
            'est_publie' => true,
            'user_id' => $gerant->id,
            'restaurant_id' => $tanti->id,
        ]);

        Avis::create([
            'note' => 4,
            'commentaire' => 'Une ambiance de maquis authentique et des plats locaux très savoureux. Je reviendrai sans hésiter.',
            'date_visite' => now()->subDays(4),
            'lat_client' => 6.35712,
            'long_client' => 2.40892,
            'est_publie' => true,
            'user_id' => $gerantBissap->id,
            'restaurant_id' => $tanti->id,
        ]);

        // Avis sur Le Petit Bissap (id 3)
        Avis::create([
            'note' => 5,
            'commentaire' => 'Le brunch est excellent, surtout les pancakes ! Les boissons au bissap maison sont délicieuses.',
            'date_visite' => now()->subDays(3),
            'lat_client' => 6.35245,
            'long_client' => 2.39956,
            'est_publie' => true,
            'user_id' => $client->id,
            'restaurant_id' => $bissap->id,
        ]);

        Avis::create([
            'note' => 4,
            'commentaire' => 'Très bon café et un cadre calme pour travailler. Le toast avocat est savoureux.',
            'date_visite' => now()->subDays(6),
            'lat_client' => 6.35245,
            'long_client' => 2.39956,
            'est_publie' => true,
            'user_id' => $gerant->id,
            'restaurant_id' => $bissap->id,
        ]);

        Avis::create([
            'note' => 5,
            'commentaire' => 'Mon endroit préféré pour le petit-déjeuner. Tout est frais et fait avec amour !',
            'date_visite' => now()->subDays(2),
            'lat_client' => 6.35245,
            'long_client' => 2.39956,
            'est_publie' => true,
            'user_id' => $gerantTanti->id,
            'restaurant_id' => $bissap->id,
        ]);

        // Avis sur Chez Marcel (id 4)
        Avis::create([
            'note' => 4,
            'commentaire' => 'Le poisson capitaine est délicieux, très bien braisé. Service accueillant.',
            'date_visite' => now()->subDays(7),
            'lat_client' => 6.36100,
            'long_client' => 2.42150,
            'est_publie' => true,
            'user_id' => $client->id,
            'restaurant_id' => $marcel->id,
        ]);

        Avis::create([
            'note' => 5,
            'commentaire' => 'Une excellente table. Les desserts à la mangue sont légers et raffinés, un vrai bonheur en fin de repas.',
            'date_visite' => now()->subDays(10),
            'lat_client' => 6.36100,
            'long_client' => 2.42150,
            'est_publie' => true,
            'user_id' => $gerantTanti->id,
            'restaurant_id' => $marcel->id,
        ]);

        Avis::create([
            'note' => 4,
            'commentaire' => 'Très cuisine fine dans un cadre soigné. Idéal pour un dîner d\'affaires ou en famille.',
            'date_visite' => now()->subDays(12),
            'lat_client' => 6.36100,
            'long_client' => 2.42150,
            'est_publie' => true,
            'user_id' => $gerantBissap->id,
            'restaurant_id' => $marcel->id,
        ]);
    }
}
