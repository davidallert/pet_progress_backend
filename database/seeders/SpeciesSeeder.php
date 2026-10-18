<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Species;

class SpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Dog' => 'shield-dog',
            'Cat' => 'shield-cat',
            'Bird' => 'dove',
            'Fish' => 'fish',
            'Horse' => 'horse-head',
            'Cow' => 'cow',
            'Spider' => 'spider',
            'Frog' => 'frog',
            'Snake' => 'worm',
            'Other' => 'dragon',
        ];
        foreach ($types as $name => $icon) {
            Species::updateOrCreate(
                ['name' => $name],
                ['icon' => $icon],
            );
        }
    }
}
