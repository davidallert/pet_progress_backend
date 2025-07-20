<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;

class UserController extends Controller
{
    public function get_pets()
    {
      $user = auth()->user();
      return response()->json($user->pets);
    }
    public function get_user()
    {
      $user = auth()->user();
      return response()->json($user);
    }
    public function get_all_user_data()
    {
      $user = auth()->user();
      $pets = $user->pets;
      return response()->json([
        'user' => $user,
        'pets' => $pets,
      ]);
    }
}
