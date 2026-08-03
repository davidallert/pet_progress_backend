<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

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
      $user->load('pets.events');

      return response()->json([
          'user' => new UserResource($user)
      ]);
    }
}
