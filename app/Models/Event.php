<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventCategory;
use App\Models\Pet;

class Event extends Model
{
    protected $fillable = [
      'pet_id',
      'title',
      'description',
      'image_path',
      'category_id',
      'date'
    ];

    public function belongsToPet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }
}