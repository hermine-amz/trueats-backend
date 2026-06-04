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

        $client = User::create([
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'client@trueats.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'compte_active' => true,
        ]);

        $restaurant = Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $gerant->id,
        ]);

        $pizzaCat = Category::create(['libelle' => 'Pizzas']);
        $burgerCat = Category::create(['libelle' => 'Burgers']);
        $boissonCat = Category::create(['libelle' => 'Boissons']);

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
    }
}
