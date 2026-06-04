<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Plat;
use App\Models\Avis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrueatsApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $restaurant;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'client@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'compte_active' => true,
        ]);

        $gerant = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'gerant@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        $this->restaurant = Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $gerant->id,
        ]);

        $this->category = Category::create([
            'libelle' => 'Pizzas'
        ]);

        Plat::create([
            'nom' => 'Pizza Margherita',
            'description' => 'Sauce tomate',
            'prix' => 12.50,
            'disponible' => true,
            'restaurant_id' => $this->restaurant->id,
            'categorie_id' => $this->category->id,
        ]);
    }

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'client'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'access_token']);
    }

    public function test_user_can_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'client@trueats.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'access_token']);
    }

    public function test_get_restaurant_by_qr_code()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/restaurants/qr/BISTRO_GOURMET_QR');

        $response->assertStatus(200)
            ->assertJsonPath('nom', 'Le Bistro Gourmet');
    }

    public function test_verify_gps_in_perimeter()
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/verify-gps", [
            'latitude' => 48.8698,
            'longitude' => 2.3312
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'in_perimeter' => true
            ]);
    }

    public function test_verify_gps_out_of_perimeter()
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/verify-gps", [
            'latitude' => 45.0,
            'longitude' => 2.0
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'in_perimeter' => false
            ]);
    }

    public function test_submit_review_in_perimeter()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/avis', [
            'restaurant_id' => $this->restaurant->id,
            'note' => 4,
            'commentaire' => 'Bon repas',
            'latitude_client' => 48.8698,
            'longitude_client' => 2.3312
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('note', 4);
    }

    public function test_submit_review_out_of_perimeter()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/avis', [
            'restaurant_id' => $this->restaurant->id,
            'note' => 4,
            'commentaire' => 'Bon repas',
            'latitude_client' => 45.0,
            'longitude_client' => 2.0
        ]);

        $response->assertStatus(403);
    }

    public function test_flag_review()
    {
        $this->actingAs($this->user);

        $avis = Avis::create([
            'note' => 5,
            'commentaire' => 'Test',
            'date_visite' => now(),
            'lat_client' => 48.8698,
            'long_client' => 2.3312,
            'user_id' => $this->user->id,
            'restaurant_id' => $this->restaurant->id,
        ]);

        $response = $this->postJson("/api/avis/{$avis->id}/signal", [
            'libelle' => 'Abusif'
        ]);

        $response->assertStatus(201);
    }

    public function test_explore_restaurant()
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/explore");

        $response->assertStatus(201);

        $indexResponse = $this->getJson('/api/explorations');
        $indexResponse->assertStatus(200)
            ->assertJsonCount(1);
    }
}
