<?php

namespace App\Http\Controllers;

use App\Models\sportcourt;
use Illuminate\Http\Request;

class GetSportCourtsController extends Controller
{
    
public function getCanchas()
{
    $canchas = sportcourt::with('sport')->get(); 
    return response()->json($canchas);
}
}
