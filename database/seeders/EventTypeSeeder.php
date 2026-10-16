<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventType;

class EventTypeSeeder extends Seeder
{
    /**
     * Seed the application's event types.
     */
    public function run(): void
    {
        $types = [
            'Vaccination' => 'syringe',
            'Trick' => 'volleyball',
            'Trip' => 'earth-europe',
            'Vet Visit' => 'notes-medical',
            'Other' => 'paw',
        ];
        foreach ($types as $name => $icon) {
            EventType::updateOrCreate(
                ['name' => $name],
                ['icon' => $icon],
            );
        }
    }
}
