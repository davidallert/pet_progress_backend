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
      try {
        $validatedInput = $request->validate([
          'pet_id' => 'required|integer',
          'title' => 'required|string|max:255',
          'description' => 'nullable|string|max:10000',
          'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
          'type' => 'nullable|string|max:255',
          'date' => 'nullable|date',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
          $imagePath = $request->file('image')->store('images', 'public');
        }

        Event::create([
          'pet_id' => $validatedInput['pet_id'],
          'title' => $validatedInput['title'],
          'description' => $validatedInput['description'],
          'image_path' => $imagePath,
          'type' => $validatedInput['type'],
          'date' => $validatedInput['date'],
        ]);

        return response()->json(['message' => 'Event was added.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      }
    }

    public function removeEvent(Request $request): JsonResponse
    {
      try {
        $validatedInput = $request->validate([
          'id' => 'required|integer',
        ]);

        $imagePath = Event::where('id', $validatedInput['id'])->value('image_path');

        Event::destroy($validatedInput['id']);

        if ($imagePath) Storage::disk('public')->delete($imagePath);

        return response()->json(['message' => 'Event was removed.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      } 
    }

    public function getEvents(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'id' => 'required|integer',
      ]);

      try {
        $events = Event::where('pet_id', $validatedInput['id'])->orderBy('date', 'asc')->get();
        return response()->json(EventResource::collection($events), 200); // Using EventResource to change keys from snake_case to camelCase.
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      }
    }

    public function upsertEvent(Request $request): JsonResponse
    {
      $imagePath = null;
      
      try {
        $validatedInput = $request->validate([
          'id' => 'required|integer',
          'pet_id' => 'required|integer',
          'title' => 'required|string|max:255',
          'description' => 'nullable|string|max:10000',
          'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
          'type' => 'nullable|string|max:255',
          'date' => 'nullable|date',
        ]);

        // Create new array to filter which fields should be included.
        $upsert = [
          'id' => $validatedInput['id'],
          'pet_id' => $validatedInput['pet_id'],
          'title' => $validatedInput['title'],
          'description' => $validatedInput['description'] ?? null,
          'type' => $validatedInput['type'] ?? null,
          'date' => $validatedInput['date'] ?? null,
        ];

        $update = ['title', 'description', 'type', 'date'];

        $prevPath = null;

        if ($request->hasFile('image')) {
          $prevPath = Event::where('id', $validatedInput['id'])->value('image_path');

          $imagePath = $request->file('image')->store('images', 'public');

          $upsert['image_path'] = $imagePath;

          $update[] = 'image_path'; // Only update the path when a new one is included in the request.
        }

        Event::upsert($upsert, uniqueBy: ['id'], update: $update);

         // Delete previous image.
         if ($prevPath) {
          Storage::disk('public')->delete($prevPath);
         }

        return response()->json(['message' => 'Event saved.'], 200);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          // Remove the image from the disk if an error occurs to avoid bloat.
          if ($imagePath) Storage::disk('public')->delete($imagePath);

          report($e); // Log the error.

          return response()->json(['error' => 'Something went wrong'], 500);
          // return response()->json(['error' => $e->getMessage()], 500); // For debugging. May include sensitive information.
      }
    }
}