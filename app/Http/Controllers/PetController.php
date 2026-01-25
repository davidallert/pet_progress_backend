<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Pet;

class PetController extends Controller
{
    /**
     * Adds a pet to the user with the specified user_id.
     */
    public function addPet(Request $request): JsonResponse
    {
      try {
        $validatedInput = $request->validate([
          'user_id' => 'required|integer',
          'name' => 'required|string|max:255',
          'species' => 'required|string|max:255',
          'breed' => 'string|max:255',
          'birthday' => 'required|date',
        ]);

        // Old version with DB builder.
        // DB::table('pets')->insert([
        //   'user_id' => $validated_input['user_id'],
        //   'name' => strtolower($validated_input['name']),
        //   'species' => strtolower($validated_input['species']),
        //   'breed' => strtolower($validated_input['breed']),
        //   'birthday' => $validated_input['birthday'],
        // ]);

        // New version with Eloquent and Pet model.
        // Using Mass assignment. Can be risky if $fillable in the Pet model doesn't exist.
        Pet::create([
          'user_id' => $validatedInput['user_id'],
          'name' => $validatedInput['name'],
          'species' => $validatedInput['species'],
          'breed' => $validatedInput['breed'],
          'birthday' => $validatedInput['birthday'],
        ]);

        return response()->json(['message' => 'Pet was added.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      }
    }

    public function removePet(Request $request): JsonResponse
    {
      try {
        $validatedInput = $request->validate([
          'id' => 'required|integer',
        ]);

        Pet::destroy($validatedInput['id']);

        return response()->json(['message' => 'Pet was removed.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
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
          'pets.*.species' => 'required|string|max:255',
          'pets.*.breed' => 'string|max:255',
          'pets.*.birthday' => 'required|date',
        ]);

        // 'pets.*.birthday' => 'required|date' translates to:
        // "For the array named pets, go through every single object inside it, and for each one, validate that its birthday key exists and contains a valid date."

        Pet::upsert($validatedInput['pets'], uniqueBy: ['id'], update: ['name', 'species', 'breed', 'birthday']);

        return response()->json(['message' => 'Pets were updated.'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      }
    }
}