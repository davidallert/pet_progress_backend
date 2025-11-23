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
    public function add_pet(Request $request): JsonResponse
    {

      // TESTING Remove the user before adding it.
      // DB::table('users')->where('email', $request->input('email'))->delete();

      try {
        $validated_input = $request->validate([
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
        // Using Mass assignment. Can be risky if not $fillable in the Pet model exists.
        Pet::create([
          'user_id' => $validated_input['user_id'],
          'name' => ($validated_input['name']),
          'species' => ($validated_input['species']),
          'breed' => ($validated_input['breed']),
          'birthday' => $validated_input['birthday'],
        ]);

        return response()->json(['message' => 'Pet added successfully'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      }
    }

    public function remove_pet(Request $request): JsonResponse
    {
      try {
        $validated_input = $request->validate([
          'pet_id' => 'required|integer',
        ]);

        Pet::destroy($validated_input['pet_id']);

        return response()->json(['message' => 'Pet removed successfully'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      } 
    }
}