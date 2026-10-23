<?php

namespace Tests\Feature;

use App\Models\EventCategory;
use Database\Seeders\EventCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTypeDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_event_categories_are_created_with_their_icon_names(): void
    {
        EventCategory::create(['name' => 'Trick', 'icon' => null]);
        $this->seed(EventCategorySeeder::class);

        $this->assertDatabaseHas('event_categories', ['name' => 'Trick', 'icon' => 'baseball']);
        $this->assertDatabaseHas('event_categories', ['name' => 'Vaccination', 'icon' => 'syringe']);
        $this->assertDatabaseHas('event_categories', ['name' => 'Other', 'icon' => 'paw']);
    }

    public function test_event_category_seeder_is_idempotent(): void
    {
        $this->seed(EventCategorySeeder::class);
        $firstCount = EventCategory::count();
        $this->seed(EventCategorySeeder::class);

        $this->assertSame($firstCount, EventCategory::count());
        $this->assertSame(24, $firstCount);
    }
}
