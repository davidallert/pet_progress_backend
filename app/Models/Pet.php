<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pet extends Model
{
    protected $fillable = [
      'user_id',
      'name',
      'species',
      'breed',
      'birthday'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}