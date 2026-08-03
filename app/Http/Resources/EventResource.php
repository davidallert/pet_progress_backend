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
            'petId' => $this->pet_id,
            'title' => $this->title,
            'description' => $this->description,
            'imagePath' => $imageUrl,
            'type' => $this->type,
            'date' => $this->date,
        ];
    }
}
