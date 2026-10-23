<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Pet;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_pet_and_species_relationships_resolve_models(): void
    {
        $user = User::factory()->create();
        $species = Species::create(['name' => 'Cat', 'icon' => 'cat']);
        $pet = $user->pets()->create([
            'name' => 'Miso', 'species_id' => $species->id, 'birthday' => null,
        ]);

        $this->assertTrue($pet->user->is($user));
        $this->assertTrue($pet->species->is($species));
        $this->assertTrue($user->pets()->first()->is($pet));
    }

    public function test_pet_events_relationship_sets_and_resolves_the_foreign_key(): void
    {
        $user = User::factory()->create();
        $species = Species::create(['name' => 'Dog', 'icon' => 'dog']);
        $pet = $user->pets()->create([
            'name' => 'Lily', 'species_id' => $species->id, 'birthday' => null,
        ]);
        $event = $pet->events()->create(['title' => 'First walk']);

        $this->assertSame($pet->id, $event->pet_id);
        $this->assertTrue($event->belongsToPet->is($pet));
        $this->assertTrue($pet->events()->first()->is($event));
    }
}
