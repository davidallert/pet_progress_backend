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

        return [
            'id' => $this->id,
            'user_id' => $this->user_id, // snake_case to camelCase
            'name' => $this->name,
            'imagePath' => $imageUrl,
            'species' => $this->species,
            'breed' => $this->breed,
            'birthday' => $this->birthday,
            // Exclude fields like created_at, updated_at if you don't need them
        ];
    }
}
