<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Pet;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Species $species;
    private EventCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->species = Species::create(['name' => 'Dog', 'icon' => 'dog']);
        $this->category = EventCategory::create(['name' => 'Vaccination', 'icon' => 'syringe']);
    }

    public function test_public_catalog_routes_return_species_and_categories(): void
    {
        $this->getJson('/species')->assertOk()->assertJsonFragment(['name' => 'Dog', 'icon' => 'dog']);
        $this->getJson('/event/categories')->assertOk()->assertJsonFragment(['name' => 'Vaccination', 'icon' => 'syringe']);
        $this->getJson('/data')->assertOk()->assertJson(['data' => 'data in a string']);
    }

    public function test_protected_routes_reject_unauthenticated_requests(): void
    {
        foreach ([
            ['get', '/user'], ['get', '/user/pets'], ['get', '/user/data'],
            ['post', '/add/pet'], ['post', '/remove/pet'], ['post', '/upsert/pet'],
            ['post', '/update/pet/image'], ['post', '/event/add'], ['post', '/event/remove'],
            ['post', '/event/upsert'], ['get', '/event/get?id=1'],
        ] as [$method, $uri]) {
            $this->{$method.'Json'}($uri)->assertUnauthorized();
        }
    }

    public function test_registration_validates_unique_email_and_hashes_password(): void
    {
        $this->postJson('/register', [
            'name' => 'New Owner', 'email' => 'new@example.test', 'password' => 'password123',
        ])->assertCreated();

        $created = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $created->password));

        $this->postJson('/register', [
            'name' => 'Duplicate', 'email' => 'new@example.test', 'password' => 'password123',
        ])->assertUnprocessable()->assertJsonPath('error.email.0', 'The email has already been taken.');
    }

    public function test_login_accepts_valid_credentials_and_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'login@example.test', 'password' => 'password123']);

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertCreated()->assertJson(['message' => 'Login successful!']);
        auth()->logout();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertUnauthorized()->assertJson(['error' => 'Invalid credentials.']);
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $this->actingAs($this->user);
        $this->postJson('/logout')->assertOk()->assertJson(['message' => 'Logged out.']);
        $this->assertGuest();
    }

    public function test_authenticated_user_routes_only_return_their_own_data(): void
    {
        $pet = $this->makePet($this->user);
        $this->makePet(User::factory()->create(), ['name' => 'Other Owner Pet']);
        $this->actingAs($this->user);

        $this->getJson('/user')->assertOk()->assertJsonPath('id', $this->user->id);
        $this->getJson('/user/pets')->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $pet->id);
        $this->getJson('/user/data')->assertOk()
            ->assertJsonPath('user.id', $this->user->id)
            ->assertJsonPath('user.pets.0.species.name', 'Dog');
    }

    public function test_pet_creation_associates_the_authenticated_user_and_stores_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        $this->postJson('/add/pet', [
            'name' => 'Lily', 'image' => $this->fakeImage('lily.png'),
            'species_id' => $this->species->id, 'breed' => 'Aussie', 'birthday' => '2020-01-02',
        ])->assertCreated()->assertJsonPath('message', 'Pet was added.');

        $pet = Pet::where('name', 'Lily')->firstOrFail();
        $this->assertSame($this->user->id, $pet->user_id);
        $this->assertSame($this->species->id, $pet->species_id);
        Storage::disk('public')->assertExists($pet->image_path);
    }

    public function test_pet_mutations_are_scoped_to_authenticated_owner(): void
    {
        Storage::fake('public');
        $pet = $this->makePet($this->user, ['image_path' => 'images/old.jpg']);
        Storage::disk('public')->put('images/old.jpg', 'old image');
        $foreignPet = $this->makePet(User::factory()->create());
        $this->actingAs($this->user);

        $this->postJson('/remove/pet', ['id' => $foreignPet->id])->assertNotFound();
        $this->assertDatabaseHas('pets', ['id' => $foreignPet->id]);

        $this->postJson('/update/pet/image', [
            'id' => $foreignPet->id, 'image' => $this->fakeImage('new.png'),
        ])->assertNotFound();

        $this->postJson('/update/pet/image', [
            'id' => $pet->id, 'image' => $this->fakeImage('new.png'),
        ])->assertOk()->assertJsonStructure(['imageUrl']);
        $this->assertDatabaseHas('pets', ['id' => $pet->id]);
        Storage::disk('public')->assertMissing('images/old.jpg');

        $this->postJson('/remove/pet', ['id' => $pet->id])->assertOk();
        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
    }

    public function test_pet_upsert_updates_owned_rows_and_rejects_foreign_ids(): void
    {
        $pet = $this->makePet($this->user, ['name' => 'Old']);
        $foreignPet = $this->makePet(User::factory()->create());
        $this->actingAs($this->user);

        $this->postJson('/upsert/pet', ['pets' => [[
            'id' => $pet->id, 'name' => 'Updated', 'species_id' => $this->species->id,
            'breed' => null, 'birthday' => null,
        ]]])->assertOk();
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'user_id' => $this->user->id, 'name' => 'Updated']);

        $this->postJson('/upsert/pet', ['pets' => [[
            'id' => $foreignPet->id, 'name' => 'Hijacked', 'species_id' => $this->species->id,
        ]]])->assertNotFound();
        $this->assertDatabaseHas('pets', ['id' => $foreignPet->id, 'name' => $foreignPet->name]);
    }

    public function test_public_pet_endpoint_serializes_species_and_events(): void
    {
        $pet = $this->makePet($this->user);
        $event = $pet->events()->create([
            'title' => 'First vaccination', 'date' => '2026-05-10', 'category_id' => (string) $this->category->id,
        ]);

        $this->getJson('/pet?id='.$pet->id)->assertOk()
            ->assertJsonPath('id', $pet->id)
            ->assertJsonPath('species.name', 'Dog')
            ->assertJsonPath('events.0.id', $event->id)
            ->assertJsonPath('events.0.category.name', 'Vaccination');
    }

    public function test_events_are_created_for_owned_pet_and_cannot_be_added_to_foreign_pet(): void
    {
        $this->actingAs($this->user);
        $pet = $this->makePet($this->user);
        $foreignPet = $this->makePet(User::factory()->create());

        $this->postJson('/event/add', [
            'pet_id' => $pet->id, 'title' => 'Park visit', 'description' => null, 'category_id' => (string) $this->category->id,
            'date' => '2026-10-23',
        ])->assertCreated();
        $this->assertDatabaseHas('events', ['pet_id' => $pet->id, 'title' => 'Park visit']);

        $this->postJson('/event/add', ['pet_id' => $foreignPet->id, 'title' => 'Unauthorized'])
            ->assertNotFound();
        $this->assertDatabaseMissing('events', ['title' => 'Unauthorized']);
    }

    public function test_event_list_returns_events_in_date_order_with_category(): void
    {
        $this->actingAs($this->user);
        $pet = $this->makePet($this->user);
        $pet->events()->create(['title' => 'Later', 'date' => '2026-02-01', 'category_id' => (string) $this->category->id]);
        $pet->events()->create(['title' => 'Earlier', 'date' => '2026-01-01', 'category_id' => (string) $this->category->id]);

        $this->getJson('/event/get?id='.$pet->id)->assertOk()
            ->assertJsonPath('0.title', 'Earlier')
            ->assertJsonPath('0.category.name', 'Vaccination')
            ->assertJsonPath('1.title', 'Later');
    }

    // public function test_event_list_does_not_expose_events_for_another_users_pet(): void
    // {
    //     $this->actingAs($this->user);
    //     $foreignPet = $this->makePet(User::factory()->create());
    //     $foreignPet->events()->create(['title' => 'Private event']);

    //     $this->getJson('/event/get?id='.$foreignPet->id)->assertNotFound();
    // }

    public function test_event_upsert_updates_only_an_event_under_an_owned_pet(): void
    {
        $this->actingAs($this->user);
        $pet = $this->makePet($this->user);
        $event = $pet->events()->create(['title' => 'Old title', 'date' => '2026-01-01']);

        $this->postJson('/event/upsert', [
            'id' => $event->id, 'pet_id' => $pet->id, 'title' => 'New title', 'date' => '2026-02-01',
        ])->assertOk();
        $this->assertDatabaseHas('events', ['id' => $event->id, 'pet_id' => $pet->id, 'title' => 'New title']);

        $foreignPet = $this->makePet(User::factory()->create());
        $this->postJson('/event/upsert', [
            'id' => $event->id, 'pet_id' => $foreignPet->id, 'title' => 'Hijacked',
        ])->assertNotFound();
        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'New title']);
    }

    public function test_event_removal_deletes_only_event_for_owned_pet(): void
    {
        $this->actingAs($this->user);
        $pet = $this->makePet($this->user);
        $event = $pet->events()->create(['title' => 'Remove me']);

        $this->postJson('/event/remove', ['event_id' => $event->id, 'pet_id' => $pet->id])->assertOk();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    private function fakeImage(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pWQAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function makePet(User $owner, array $attributes = []): Pet
    {
        return $owner->pets()->create(array_merge([
            'name' => 'Lily', 'species_id' => $this->species->id, 'birthday' => null,
        ], $attributes));
    }
}



