<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EventType;

class EventTypeController extends Controller
{
    public function getEventTypes()
    {
      $eventTypes = EventType::select('name', 'icon')->get();
      return response()->json($eventTypes);
    }
}
