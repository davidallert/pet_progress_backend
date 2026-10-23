<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Models\Pet;
use App\Http\Resources\PetResource;

class PetController extends Controller
{
    public function getPet(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'id' => 'required|integer',
      ]);

      try {
        $pet = Pet::where('id', $validatedInput['id'])->first();

        $pet->load([
          'species',
          'events' => function ($query) {
            $query->with('eventCategory')->orderByDesc('date');
          },
        ]);

        return response()->json(
            new PetResource($pet)
        );

      } catch (ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (Exception $e) {
          return response()->json(['error' => 'Something went wrong'], 500);
          // return response()->json(['error' => $e->getMessage(), 500);
      }
    }

    public function updateImage(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'id' => 'required|integer',
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
      ]);

      $pet = $request->user()->pets()->findOrFail($validatedInput['id']); // Ensure the pet belongs to the authenticated user.

      if (!Storage::disk('public')->exists('images')) {
        Storage::disk('public')->makeDirectory('images');
      }

      $prevPath = $pet->image_path;

      $imagePath = $validatedInput['image']->store('images', 'public');

      $pet->update(['image_path' => $imagePath]);

      if ($prevPath) {
        Storage::disk('public')->delete($prevPath);
      }

      $imageUrl = asset(Storage::url($imagePath));

      return response()->json([
          'message' => 'Image was updated.',
          'imageUrl' => $imageUrl
        ], 200);
    }

    /**
     * Adds a pet to the user with the specified user_id.
     */
    public function addPet(Request $request): JsonResponse
    {
      $validatedInput = $request->validate([
        'name' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        'species_id' => 'required|integer',
        'breed' => 'nullable|max:255',
        'birthday' => 'nullable|date',
      ],
      [],
      [
        'species_id' => 'species',
      ]);

      if (!Storage::disk('public')->exists('images')) {
          Storage::disk('public')->makeDirectory('images');
      }

      $imagePath = null;
      if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
      }

      $pet = $request->user()->pets();

      // New version with Eloquent and Pet model.
      // Using Mass assignment. Can be risky if $fillable in the Pet model doesn't exist.
      $pet::create([
        'name' => $validatedInput['name'],
        'image_path' => $imagePath,
        'species_id' => $validatedInput['species_id'],
        'breed' => $validatedInput['breed'],
        'birthday' => $validatedInput['birthday'],
      ]);

      return response()->json([
        'message' => 'Pet was added.'
        ], 201); // 201 is for creation.
    }

    public function removePet(Request $request): JsonResponse
    {
      try {
        $validatedInput = $request->validate([
          'id' => 'required|integer',
        ]);

        $imagePath = Pet::where('id', $validatedInput['id'])->value('image_path');

        Pet::destroy($validatedInput['id']);

        if ($imagePath) {
          Storage::disk('public')->delete($imagePath);
        }

        return response()->json(['message' => 'Pet was removed.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Something went wrong'], 500);
          // return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      } 
    }

    public function upsertPets(Request $request): JsonResponse
    {
      try {
        $validatedInput = $request->validate([
          'pets' => 'required|array',
          'pets.*.id' => 'required|integer',
          'pets.*.user_id' => 'required|integer',
          'pets.*.name' => 'required|string|max:255',
          'pets.*.species_id' => 'required|integer',
          'pets.*.breed' => 'nullable|max:255',
          'pets.*.birthday' => 'nullable|date',
        ],
        [],
        // Custom field names, i.e. "name" is displayed instead of "pet.0.name".
        [
          'pets.*.name' => 'name',
          'pets.*.species_id' => 'species',
          'pets.*.breed' => 'breed',
          'pets.*.birthday' => 'birthday',
        ]
        );

        // 'pets.*.birthday' => 'required|date' translates to:
        // "For the array named pets, go through every single object inside it, and for each one, validate that its birthday key exists and contains a valid date."

        Pet::upsert($validatedInput['pets'], uniqueBy: ['id'], update: ['name', 'species_id', 'breed', 'birthday']);

        return response()->json(['message' => 'Pets were updated.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Something went wrong'], 500);
          //return response()->json(['error' => $e->getMessage()], 500); // For debugging. May include sensitive information.
      }
    }
}