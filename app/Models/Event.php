<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pet;

class Event extends Model
{
    protected $fillable = [
      'pet_id',
      'title',
      'description',
      'image',
      'type',
      'date'
    ];

    public function belongsToPet()
    {
        return $this->belongsTo(Pet::class);
    }
}