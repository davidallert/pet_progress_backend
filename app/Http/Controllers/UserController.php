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
      return response()->json($user);
    }

    public function getAllUserData(Request $request): JsonResponse
    {
      $user = auth()->user();

      $eventLimit = $request->query('eventLimit');

      $user->load([
        'pets.events' => function ($query) use ($eventLimit) {
          $query->orderByDesc('date');
          
          if ($eventLimit) $query->limit($eventLimit);
        }
      ]);

      return response()->json([
          'user' => new UserResource($user)
      ]);
    }
}
