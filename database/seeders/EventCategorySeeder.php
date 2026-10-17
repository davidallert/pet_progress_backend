<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventCategory;

class EventCategorySeeder extends Seeder
{
    /**
     * Seed the application's event types.
     */
    public function run(): void
    {
        $types = [
            'Trip' => 'earth-europe',
            'Trick' => 'baseball',
            'Zoomies' => 'poo-storm',
            'Oopsie' => 'triangle-exclamation',
            'Photo Shoot' => 'camera-retro',
            'Training' => 'graduation-cap',
            'Achievement' => 'award',
            'Event' => 'ticket',
            'Birthday' => 'cake-candles',
            'Holiday' => 'candy-cane',
            'Dress-Up' => 'hat-wizard',
            'New Friend' => 'heart-circle-plus',
            'Playdate' => 'hands-clapping',
            'Grooming' => 'scissors',
            'Bath' => 'soap',
            'Clean Habitat' => 'house-flood-water',
            'Vet Visit' => 'stethoscope',
            'Vaccination' => 'syringe',
            'Dental Check' => 'tooth',
            'Weight Check' => 'weight-scale',
            'Medicine' => 'prescription-bottle-medical',
            'Heat' => 'fire',
            'Adoption Day' => 'heart',
            'Other' => 'paw',
        ];
        foreach ($types as $name => $icon) {
            EventCategory::updateOrCreate(
                ['name' => $name],
                ['icon' => $icon],
            );
        }
    }
}
