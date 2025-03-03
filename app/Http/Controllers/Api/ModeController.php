<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\mode;
use App\Models\sportcourt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModeController extends Controller
{
    //listar todas las modalidades
    public function all(){
        $mode = mode::all();
        return response()->json($mode, 200);
    }
    //listar todas la modalidades de una cancha especifica
    public function index($sportcourt_id)
    {
        $sportcourt = sportcourt::find($sportcourt_id);

        if (!$sportcourt) {
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json($sportcourt->courts, 200);
    }
    //Mostrar un modalidad por id
    public function show($id){
        $mode = mode::find($id);
        if (!$mode) {
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }
        return response()->json($mode, 200);
    }
    //Crear una modalidad
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'sportcourt_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $mode = mode::create([
            'name' => $request->name,
            'description' => $request->description,
            'sportcourt_id' => $request->sportcourt_id,
        ]);

        return response()->json($mode, 201);
    }
    //actualizar una modalidad
    public function update(Request $request){
        $mode = mode::find($request->id);
        if(!$mode){
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'sportcourt_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }
        $mode->update(
            [
                'name' => $request->name,
                'description' => $request->description,
                'sportcourt_id' => $request->sportcourt_id,
            ]
        );
        return response()->json([
            'message' => 'Modalidad actualizado correctamente',
            'status' => 200,
        ], 200);
    }
    //eliminar una modalidad
    public function destroy(Request $request){
        $mode = mode::find($request->id);
        if(!$mode){
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }
        $mode->delete();
        return response()->json([
            'message' => 'Modalidad eliminado correctamente',
            'status' => 200,
        ], 200);
    }
}
