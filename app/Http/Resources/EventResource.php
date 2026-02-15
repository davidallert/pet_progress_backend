<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
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
            'petId' => $this->pet_id, // snake_case to camelCase
            'title' => $this->title,
            'description' => $this->description,
            'imagePath' => $imageUrl, // snake_case to camelCase
            'type' => $this->type,
            'date' => $this->date,
            // Exclude fields like created_at, updated_at if you don't need them
        ];
    }
}
