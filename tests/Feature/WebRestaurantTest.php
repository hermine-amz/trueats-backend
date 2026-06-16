<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Plat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRestaurantTest extends TestCase
{
    use RefreshDatabase;

    private $gerant;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'gerant@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        $this->category = Category::create([
            'libelle' => 'Pizzas',
        ]);
    }

    public function test_visitor_can_view_valid_restaurant_menu()
    {
        $restaurant = Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'telephone' => '+33140205050',
            'horaires' => '12h00 - 23h00',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $this->gerant->id,
            'superficie' => 120,
            'est_valide' => true,
            'est_archive' => false,
        ]);

        $plat = Plat::create([
            'nom' => 'Pizza Margherita',
            'description' => 'Sauce tomate bio, mozzarella, basilic frais',
            'prix' => 12.50,
            'disponible' => true,
            'restaurant_id' => $restaurant->id,
            'categorie_id' => $this->category->id,
        ]);

        $response = $this->get("/scan/BISTRO_GOURMET_QR");

        $response->assertStatus(200);
        $response->assertViewIs('scan');
        $response->assertSee('Le Bistro Gourmet');
        $response->assertSee('Pizza Margherita');
        $response->assertSee('Voir le menu');
        $response->assertSee('Donner mon avis');
    }

    public function test_visitor_cannot_view_non_existent_restaurant()
    {
        $response = $this->get("/scan/NON_EXISTENT_QR");

        $response->assertStatus(200);
        $response->assertViewIs('scan');
        $response->assertSee('Établissement indisponible');
        $response->assertSee('Ce code QR ne correspond à aucun établissement enregistré.');
    }

    public function test_visitor_cannot_view_pending_restaurant()
    {
        Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'telephone' => '+33140205050',
            'horaires' => '12h00 - 23h00',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $this->gerant->id,
            'superficie' => 120,
            'est_valide' => false, // pending admin approval
            'est_archive' => false,
        ]);

        $response = $this->get("/scan/BISTRO_GOURMET_QR");

        $response->assertStatus(200);
        $response->assertViewIs('scan');
        $response->assertSee('Établissement indisponible');
        $response->assertSee('Cet établissement est en cours de validation administrative.');
    }

    public function test_visitor_cannot_view_blocked_restaurant()
    {
        Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'telephone' => '+33140205050',
            'horaires' => '12h00 - 23h00',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $this->gerant->id,
            'superficie' => 120,
            'est_valide' => true,
            'est_archive' => false,
            'bloque_jusqua' => now()->addDays(2), // blocked
        ]);

        $response = $this->get("/scan/BISTRO_GOURMET_QR");

        $response->assertStatus(200);
        $response->assertViewIs('scan');
        $response->assertSee('Établissement indisponible');
        $response->assertSee('Cet établissement est temporairement suspendu.');
    }

    public function test_visitor_cannot_view_archived_restaurant()
    {
        Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'telephone' => '+33140205050',
            'horaires' => '12h00 - 23h00',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $this->gerant->id,
            'superficie' => 120,
            'est_valide' => true,
            'est_archive' => true, // archived
        ]);

        $response = $this->get("/scan/BISTRO_GOURMET_QR");

        $response->assertStatus(200);
        $response->assertViewIs('scan');
        $response->assertSee('Établissement indisponible');
        $response->assertSee('Cet établissement est actuellement masqué.');
    }
}
