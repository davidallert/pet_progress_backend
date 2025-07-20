<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\UserController;

// The router allows you to register routes that respond to any HTTP verb:
// Route::get($uri, $callback);
// Route::post($uri, $callback);
// Route::put($uri, $callback);
// Route::patch($uri, $callback);
// Route::delete($uri, $callback);
// Route::options($uri, $callback);
// Read more here: https://laravel.com/docs/12.x/routing

// For publicly available information.
Route::get('/data', function () {
    $data = ['data' => 'data in a string'];
    return $data;
});

// Public POST.
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);

// Protected POST.
Route::middleware(['auth:sanctum'])->post('/add-pet-to-user', [PetController::class, 'add_pet_to_user']);

// Protected GET.
Route::middleware(['auth:sanctum'])->get('/user', [UserController::class, 'get_user']);
Route::middleware(['auth:sanctum'])->get('/user/pets', [UserController::class, 'get_pets']);
Route::middleware(['auth:sanctum'])->get('/user/data', [UserController::class, 'get_all_user_data']);

// Prev.
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');