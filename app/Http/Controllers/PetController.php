<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PetController extends Controller
{
    /**
     * Adds a pet to the user with the specified user_id.
     * TODO Need to provide the ID somehow, should not be done by the user.
     */
    public function add_pet_to_user(Request $request): JsonResponse
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

        DB::table('pets')->insert([
          'user_id' => $validated_input['user_id'],
          'name' => strtolower($validated_input['name']),
          'species' => strtolower($validated_input['species']),
          'breed' => strtolower($validated_input['breed']),
          'birthday' => $validated_input['birthday'],
        ]);

        return response()->json(['message' => 'Pet added successfully'], 201);
      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['error' => $e->errors()], 422);
      } catch (\Exception $e) {
          return response()->json(['error' => 'Operation failed', 'details' => $e->getMessage()], 500);
      }
    }
}