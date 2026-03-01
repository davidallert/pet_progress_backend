<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use App\Http\Resources\PetResource;

class UserController extends Controller
{
    public function getPets()
    {
      $user = auth()->user();
      return response()->json($user->pets);
    }
    public function getUser()
    {
      $user = auth()->user();
      return response()->json($user);
    }
    public function getAllUserData()
    {
      $user = auth()->user();
      $pets = $user->pets;
      $pets = PetResource::collection($pets);
      return response()->json([
        'user' => $user,
        'pets' => $pets,
      ]);
    }
}
