<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Event;

class Pet extends Model
{
    protected $fillable = [
      'id',
      'user_id',
      'name',
      'image_path',
      'species',
      'breed',
      'birthday'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}