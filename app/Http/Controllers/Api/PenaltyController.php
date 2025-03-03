<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\penalty;
use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    //obtener todas la penalizaciones 
    public function all (){
        return response()->json(penalty::all(), 200);
    }
    //obtener una penalizaci+on por su id
    public function show(Request $request){
        $penalty = penalty::find($request -> id);
        if(!$penalty){
            return response()->json([
                'message' => 'Penalización no encontrada',
                'status' => 404,
            ], 404);
        }
        return response()->json($penalty, 200);
    }

}
