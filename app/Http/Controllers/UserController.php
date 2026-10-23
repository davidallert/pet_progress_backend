<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

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

      return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
      ]);
    }

    public function getAllUserData(Request $request): JsonResponse
    {
      $user = auth()->user();

      $eventLimit = $request->query('event_limit');

      $user->load([
        'pets.species',
        'pets.events' => function ($query) use ($eventLimit) {
          $query->with('eventCategory')->orderByDesc('date');
          
          if ($eventLimit) $query->limit($eventLimit);
        },
      ]);

      return response()->json([
          'user' => new UserResource($user)
      ]);
    }
}
