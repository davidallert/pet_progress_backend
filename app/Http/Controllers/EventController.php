<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Event;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Storage;

// use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Adds a pet to the user with the specified user_id.
     */
    public function addEvent(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'pet_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:10000',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        'category_id' => 'nullable|string|max:255',
        'date' => 'nullable|date',
      ]);

      $pet = $request->user()->pets()->findOrFail($validatedInput['pet_id']);
      $event = $pet->events();

      $imagePath = null;
      if ($request->hasFile('image')) {
        $imagePath = $validatedInput['image']->store('images', 'public');
      }

      $event->create([
        'title' => $validatedInput['title'],
        'description' => $validatedInput['description'],
        'image_path' => $imagePath,
        'category_id' => $validatedInput['category_id'],
        'date' => $validatedInput['date'],
      ]);

      return response()->json([
        'message' => 'Event was added.'
        ], 201);
    }

    public function getEvents(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'id' => 'required|integer',
      ]);

        $events = Event::with('eventCategory')
          ->where('pet_id', $validatedInput['id'])
          ->orderBy('date', 'asc')
          ->get();

        return response()->json(EventResource::collection($events), 200); // Using EventResource to change keys from snake_case to camelCase.
    }

    public function removeEvent(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'id' => 'required|integer',
        'pet_id' => 'required|integer',
      ]);

      $pet = $request->user()->pets()->findOrFail($validatedInput['pet_id']);
      $event = $pet->events()->findOrFail($validatedInput['id']);

      $imagePath = $event->image_path;

      $event->delete();

      if ($imagePath) Storage::disk('public')->delete($imagePath);

      return response()->json([
        'message' => 'Event was removed.'
        ], 200);
    }

    public function upsertEvent(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'id' => 'required|integer',
        'pet_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:10000',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        'category_id' => 'nullable|string|max:255',
        'date' => 'nullable|date',
      ]);

      $pet = $request->user()->pets()->findOrFail($validatedInput['pet_id']);
      $event = $pet->events()->findOrFail($validatedInput['id']);

      // Create new array to filter which fields should be included.
      $upsert = [
        'id' => $validatedInput['id'],
        'pet_id' => $validatedInput['pet_id'],
        'title' => $validatedInput['title'],
        'description' => $validatedInput['description'] ?? null,
        'category_id' => $validatedInput['category_id'] ?? null,
        'date' => $validatedInput['date'] ?? null,
      ];

      $update = ['title', 'description', 'category_id', 'date'];

      $imagePath = null;
      $prevPath = null;

      if ($request->hasFile('image')) {
        $prevPath = $event->image_path;

        $imagePath = $request->file('image')->store('images', 'public');

        $upsert['image_path'] = $imagePath;

        $update[] = 'image_path'; // Only update the path when a new one is included in the request.
      }

      $event->upsert($upsert, uniqueBy: ['id'], update: $update);

      // Delete previous image.
      if ($prevPath) {
        Storage::disk('public')->delete($prevPath);
      }

      return response()->json([
        'message' => 'Event saved.'
        ], 200);
    }
}