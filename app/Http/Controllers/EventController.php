<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Event;

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
          'description' => 'nullable|string|max:1000',
          'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
          'type' => 'nullable|string|max:255',
          'date' => 'nullable|date',
        ]);

        Event::create([
          'pet_id' => $validatedInput['pet_id'],
          'title' => ($validatedInput['title']),
          'description' => ($validatedInput['description']),
          'image' => ($validatedInput['image']),
          'type' => ($validatedInput['type']),
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

        Event::destroy($validatedInput['id']);

        return response()->json(['message' => 'Event was removed.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      } 
    }
}