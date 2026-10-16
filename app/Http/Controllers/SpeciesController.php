<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Species;

class SpeciesController extends Controller
{
    public function getSpecies()
    {
      $species = Species::select('id', 'name', 'icon')->get();
      return response()->json($species);
    }
}
