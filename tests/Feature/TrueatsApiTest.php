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
    private $gerant;
    private $admin;
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

        $this->gerant = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'gerant@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        $this->admin = User::create([
            'nom' => 'System',
            'prenom' => 'Admin',
            'email' => 'admin@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'compte_active' => true,
        ]);

        $this->restaurant = Restaurant::create([
            'nom' => 'Le Bistro Gourmet',
            'adresse' => '10 Rue de la Paix, 75002 Paris',
            'latitude' => 48.8698,
            'longitude' => 2.3312,
            'qr_code_identifier' => 'BISTRO_GOURMET_QR',
            'gerant_id' => $this->gerant->id,
            'est_valide' => true,
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

    public function test_custom_superficie_validation_radius()
    {
        $this->actingAs($this->user);

        // 1. Small restaurant (50 m2) -> Radius = sqrt(50/pi) + 15 = 4 + 15 = 19 meters
        $this->restaurant->update(['superficie' => 50]);
        $this->assertEquals(19, $this->restaurant->fresh()->rayon_validation);

        // Latitude offset of 0.0002 ~ 22.2 meters away
        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/verify-gps", [
            'latitude' => $this->restaurant->latitude + 0.0002,
            'longitude' => $this->restaurant->longitude
        ]);

        // Should be out of perimeter (radius 19m < 22.2m distance)
        $response->assertStatus(200)
            ->assertJson(['in_perimeter' => false]);

        // 2. Large restaurant (500 m2) -> Radius = sqrt(500/pi) + 15 = 13 + 15 = 28 meters
        $this->restaurant->update(['superficie' => 500]);
        $this->assertEquals(28, $this->restaurant->fresh()->rayon_validation);

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/verify-gps", [
            'latitude' => $this->restaurant->latitude + 0.0002,
            'longitude' => $this->restaurant->longitude
        ]);

        // Should be in perimeter (radius 28m > 22.2m distance)
        $response->assertStatus(200)
            ->assertJson(['in_perimeter' => true]);
    }

    public function test_user_profile_management()
    {
        $this->actingAs($this->user);

        // Update profile
        $response = $this->putJson('/api/user/profile', [
            'nom' => 'NewNom',
            'prenom' => 'NewPrenom',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.nom', 'NewNom')
            ->assertJsonPath('user.prenom', 'NewPrenom');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'nom' => 'NewNom',
            'prenom' => 'NewPrenom',
        ]);

        // Self delete
        $response = $this->deleteJson('/api/user/profile');
        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id,
        ]);
    }

    public function test_manager_can_update_restaurant()
    {
        $otherGerant = User::create([
            'nom' => 'Other',
            'prenom' => 'Gerant',
            'email' => 'other_gerant@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        // 1. Client cannot update restaurant
        $this->actingAs($this->user);
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}", [
            'nom' => 'Hacked Bistro',
        ]);
        $response->assertStatus(403);

        // 2. Other manager cannot update restaurant
        $this->actingAs($otherGerant);
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}", [
            'nom' => 'Hacked Bistro 2',
        ]);
        $response->assertStatus(403);

        // 3. Owner manager can update restaurant
        $this->actingAs($this->gerant);
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}", [
            'nom' => 'Le Bistro Mis à Jour',
            'superficie' => 200,
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('restaurant.nom', 'Le Bistro Mis à Jour')
            ->assertJsonPath('restaurant.superficie', 200);

        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'nom' => 'Le Bistro Mis à Jour',
            'superficie' => 200,
        ]);
    }

    public function test_manager_update_restaurant_name_resets_validation()
    {
        $this->actingAs($this->gerant);

        // 1. Initially valid
        $this->restaurant->update([
            'nom' => 'Initial Name',
            'est_valide' => true,
            'motif_rejet' => 'some reject motif'
        ]);

        // 2. Change name -> should reset validation and clear rejection motif
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}", [
            'nom' => 'New Name'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'nom' => 'New Name',
            'est_valide' => false,
            'motif_rejet' => null
        ]);

        // 3. Admin updates name -> does NOT reset validation
        $restaurant = $this->restaurant->fresh();
        $restaurant->update([
            'nom' => 'Another Name',
            'est_valide' => true,
        ]);

        $this->actingAs($this->admin);
        $response = $this->putJson("/api/restaurants/{$restaurant->id}", [
            'nom' => 'Admin Renamed'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'nom' => 'Admin Renamed',
            'est_valide' => true,
        ]);
    }

    public function test_manager_can_crud_plats()
    {
        $otherGerant = User::create([
            'nom' => 'Other',
            'prenom' => 'Gerant',
            'email' => 'other_gerant@trueats.com',
            'password' => bcrypt('password'),
            'role' => 'gerant',
            'compte_active' => true,
        ]);

        // 1. Create Plat - Other manager fails
        $this->actingAs($otherGerant);
        $response = $this->postJson('/api/plats', [
            'nom' => 'Burger',
            'prix' => 8.50,
            'restaurant_id' => $this->restaurant->id,
            'categorie_id' => $this->category->id,
        ]);
        $response->assertStatus(403);

        // 2. Create Plat - Owner manager succeeds
        $this->actingAs($this->gerant);
        $response = $this->postJson('/api/plats', [
            'nom' => 'Burger Gourmet',
            'description' => 'Avec frites',
            'prix' => 10.50,
            'restaurant_id' => $this->restaurant->id,
            'categorie_id' => $this->category->id,
        ]);
        $response->assertStatus(201);
        $platId = $response->json('id');

        $this->assertDatabaseHas('plats', [
            'id' => $platId,
            'nom' => 'Burger Gourmet',
        ]);

        // 3. Update Plat
        $response = $this->putJson("/api/plats/{$platId}", [
            'prix' => 11.00,
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('prix', 11);

        // 4. Delete Plat
        $response = $this->deleteJson("/api/plats/{$platId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('plats', [
            'id' => $platId,
        ]);
    }

    public function test_admin_can_moderate_restaurant()
    {
        // 1. Unvalidate restaurant
        $this->actingAs($this->admin);
        $response = $this->patchJson("/api/admin/restaurants/{$this->restaurant->id}/valider", [
            'est_valide' => false,
        ]);
        $response->assertStatus(200);

        // Verify public client cannot view menu
        $this->actingAs($this->user);
        $response = $this->getJson("/api/restaurants/qr/BISTRO_GOURMET_QR");
        $response->assertStatus(403);

        // Verify client cannot post review
        $response = $this->postJson('/api/avis', [
            'restaurant_id' => $this->restaurant->id,
            'note' => 5,
            'commentaire' => 'Super',
            'latitude_client' => 48.8698,
            'longitude_client' => 2.3312
        ]);
        $response->assertStatus(403);

        // 2. Validate restaurant
        $this->actingAs($this->admin);
        $response = $this->patchJson("/api/admin/restaurants/{$this->restaurant->id}/valider", [
            'est_valide' => true,
        ]);
        $response->assertStatus(200);

        // 3. Block restaurant temporarily (e.g. 5 days)
        $response = $this->postJson("/api/admin/restaurants/{$this->restaurant->id}/bloquer", [
            'bloque' => true,
            'duree_jours' => 5,
        ]);
        $response->assertStatus(200);

        // Verify client cannot view menu
        $this->actingAs($this->user);
        $response = $this->getJson("/api/restaurants/qr/BISTRO_GOURMET_QR");
        $response->assertStatus(403);

        // 4. Unblock restaurant
        $this->actingAs($this->admin);
        $response = $this->postJson("/api/admin/restaurants/{$this->restaurant->id}/bloquer", [
            'bloque' => false,
        ]);
        $response->assertStatus(200);

        // Verify client can view menu now
        $this->actingAs($this->user);
        $response = $this->getJson("/api/restaurants/qr/BISTRO_GOURMET_QR");
        $response->assertStatus(200);
    }

    public function test_admin_can_moderate_user()
    {
        $this->actingAs($this->admin);

        // 1. Block user temporarily for 1 day
        $response = $this->postJson("/api/admin/users/{$this->user->id}/bloquer", [
            'bloque' => true,
            'duree_jours' => 1,
        ]);
        $response->assertStatus(200);

        // Verify login fails
        $response = $this->postJson('/api/login', [
            'email' => 'client@trueats.com',
            'password' => 'password',
        ]);
        $response->assertStatus(403)
            ->assertJsonMissing(['access_token']);

        // 2. Unblock user
        $this->actingAs($this->admin);
        $response = $this->postJson("/api/admin/users/{$this->user->id}/bloquer", [
            'bloque' => false,
        ]);
        $response->assertStatus(200);

        // Verify login succeeds
        $response = $this->postJson('/api/login', [
            'email' => 'client@trueats.com',
            'password' => 'password',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['access_token']);

        // 3. Block user permanently
        $this->actingAs($this->admin);
        $response = $this->postJson("/api/admin/users/{$this->user->id}/bloquer", [
            'bloque' => true,
        ]);
        $response->assertStatus(200);

        // Verify login fails
        $response = $this->postJson('/api/login', [
            'email' => 'client@trueats.com',
            'password' => 'password',
        ]);
        $response->assertStatus(403);

        // 4. Delete user
        $this->actingAs($this->admin);
        $response = $this->deleteJson("/api/admin/users/{$this->user->id}");
        $response->assertStatus(200);

        // Verify login returns 422/ValidationException because user is deleted (not found)
        $response = $this->postJson('/api/login', [
            'email' => 'client@trueats.com',
            'password' => 'password',
        ]);
        $response->assertStatus(422);
    }

    public function test_public_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'nom' => 'Hacker',
            'prenom' => 'Admin',
            'email' => 'hacked.admin@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ]);

        $response->assertStatus(422);
    }

    public function test_manager_cannot_create_duplicate_restaurant()
    {
        $this->actingAs($this->gerant);

        // 1. Create a base restaurant
        $response = $this->postJson('/api/restaurants', [
            'nom' => 'Chez Jean',
            'adresse' => 'Cotonou',
            'latitude' => 6.3676,
            'longitude' => 2.4253,
            'superficie' => 80
        ]);
        $response->assertStatus(201);
        $qrCode = $response->json('restaurant.qr_code_identifier');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $qrCode);

        // 2. Exact coordinates duplicate
        $response = $this->postJson('/api/restaurants', [
            'nom' => 'Chez Marie',
            'adresse' => 'Cotonou',
            'latitude' => 6.3676,
            'longitude' => 2.4253,
            'superficie' => 100
        ]);
        $response->assertStatus(422);

        // 3. Proximity name duplicate (~22m offset)
        $response = $this->postJson('/api/restaurants', [
            'nom' => 'Chez Jean', // same name
            'adresse' => 'Cotonou Bis',
            'latitude' => 6.3676 + 0.0002, // ~22m away
            'longitude' => 2.4253,
            'superficie' => 80
        ]);
        $response->assertStatus(422);

        // 4. Same name but far away (~110m offset)
        $response = $this->postJson('/api/restaurants', [
            'nom' => 'Chez Jean', // same name
            'adresse' => 'Cotonou Loin',
            'latitude' => 6.3676 + 0.001, // ~110m away
            'longitude' => 2.4253,
            'superficie' => 80
        ]);
        $response->assertStatus(201);
    }

    public function test_avis_xss_protection()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/avis', [
            'restaurant_id' => $this->restaurant->id,
            'note' => 4,
            'commentaire' => '<script>alert("hack")</script>Excellent repas <b>incroyable</b>!',
            'latitude_client' => 48.8698,
            'longitude_client' => 2.3312
        ]);

        $response->assertStatus(201);
        $this->assertEquals('alert("hack")Excellent repas incroyable!', $response->json('commentaire'));
        $this->assertDatabaseHas('avis', [
            'id' => $response->json('id'),
            'commentaire' => 'alert("hack")Excellent repas incroyable!',
        ]);
    }

    public function test_image_upload_and_restaurant_dish_update()
    {
        $this->actingAs($this->gerant);

        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('logo.png');

        $response = $this->postJson('/api/upload/image', [
            'image' => $file,
            'type' => 'logo'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['url', 'path']);

        $url = $response->json('url');

        // Update restaurant
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}", [
            'logo_url' => $url,
            'photo_url' => $url,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'logo_url' => $url,
            'photo_url' => $url,
        ]);

        // Add dish with image
        $response = $this->postJson('/api/plats', [
            'nom' => 'Pizza Speciale',
            'prix' => 15.00,
            'restaurant_id' => $this->restaurant->id,
            'categorie_id' => $this->category->id,
            'image_url' => $url,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('plats', [
            'nom' => 'Pizza Speciale',
            'image_url' => $url,
        ]);

        // Update dish
        $platId = $response->json('id');
        $response = $this->putJson("/api/plats/{$platId}", [
            'image_url' => 'new_image_url',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('plats', [
            'id' => $platId,
            'image_url' => 'new_image_url',
        ]);
    }

    public function test_client_restaurant_submission_and_upgrade_upon_admin_validation()
    {
        // 1. Client submits restaurant
        $this->actingAs($this->user);

        $response = $this->postJson('/api/restaurants', [
            'nom' => 'Maquis du Coin',
            'adresse' => 'Cotonou, Fidjrosse',
            'quartier' => 'Fidjrosse',
            'latitude' => 6.3500,
            'longitude' => 2.3800,
            'superficie' => 120,
        ]);

        $response->assertStatus(201);
        $restaurantId = $response->json('restaurant.id');

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurantId,
            'nom' => 'Maquis du Coin',
            'est_valide' => false,
            'gerant_id' => $this->user->id,
        ]);

        // Client role is still client
        $this->assertEquals('client', $this->user->fresh()->role);

        // 2. Admin validates the restaurant
        $this->actingAs($this->admin);

        $response = $this->patchJson("/api/admin/restaurants/{$restaurantId}/valider", [
            'est_valide' => true,
        ]);

        $response->assertStatus(200);

        // Restaurant is validated
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurantId,
            'est_valide' => true,
        ]);

        // Client role upgraded to gerant!
        $this->assertEquals('gerant', $this->user->fresh()->role);
    }
}

