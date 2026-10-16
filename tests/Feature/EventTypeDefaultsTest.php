<?php

namespace Tests\Feature;

use Database\Seeders\EventTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventTypeDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_event_categories_are_created_with_their_icon_names(): void
    {
        DB::table('event_types')->insert([
            'name' => 'Trick',
            'icon' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(EventTypeSeeder::class);

        $categories = DB::table('event_types')
            ->orderBy('name')
            ->get(['name', 'icon'])
            ->mapWithKeys(fn (object $category): array => [$category->name => $category->icon])
            ->all();

        $this->assertSame([
            'Other' => 'paw',
            'Trick' => 'volleyball',
            'Trip' => 'earth-europe',
            'Vaccination' => 'syringe',
            'Vet Visit' => 'notes-medical',
        ], $categories);
    }

    public function test_event_type_seeder_can_be_run_more_than_once(): void
    {
        $this->seed(EventTypeSeeder::class);
        $this->seed(EventTypeSeeder::class);

        $this->assertSame(5, DB::table('event_types')->count());
    }
}
