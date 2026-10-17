<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->image_path ? asset(Storage::url($this->image_path)) : null;

        // Exclude fields like created_at, updated_at if they're not needed.
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'imagePath' => $imageUrl,
            'speciesId' => $this->species_id,
            'species' => $this->whenLoaded('species', fn ($species) => [
                'name' => $species->name,
                'icon' => $species->icon,
            ]),
            'breed' => $this->breed,
            'birthday' => $this->birthday,
            'events' => EventResource::collection($this->whenLoaded('events'))
        ];
    }
}
