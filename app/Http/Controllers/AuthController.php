<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  public function login(Request $request): JsonResponse
  {
    try {
      $validated_input = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:8',
      ]);

      if (!Auth::attempt($validated_input)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
      }

      $request->session()->regenerate();

      return response()->json(['message' => 'Login successful'], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Login failed', 'details' => $e->getMessage()], 500);
    }
  }

  public function logout(Request $request): JsonResponse
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out']);
  }

  public function register(Request $request): JsonResponse
  {

    // TESTING Remove the user before adding it.
    // DB::table('users')->where('email', $request->input('email'))->delete();
    try {

      $validated_input = $request->validate([
        'name' => 'required|string|max:255|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8',
      ]);

      DB::table('users')->insert([
        'name' => $validated_input['name'],
        'email' => $validated_input['email'],
        'password' => Hash::make($validated_input['password']),
      ]);

      return response()->json(['message' => 'User registered successfully'], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Registration failed', 'details' => $e->getMessage()], 500);
    }
  }
}
