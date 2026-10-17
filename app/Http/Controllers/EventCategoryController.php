<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EventCategory;

class EventCategoryController extends Controller
{
    public function getEventCategories()
    {
      $eventCategories = EventCategory::select('id', 'name', 'icon')->get();
      return response()->json($eventCategories);
    }
}
