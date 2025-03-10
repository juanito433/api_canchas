<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SportController extends Controller
{
    // Consultar todos los deportes
    public function index()
    {
        $sports = Sport::all();

        if ($sports->isEmpty()) {
            return response()->json([
                'message' => 'No hay deportes registrados',
                'status' => 404,
            ], 404);
        }

        return response()->json($sports, 200);
    }

    // Consultar un solo deporte por ID
    public function show($id)
    {
        $sport = Sport::find($id);

        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json($sport, 200);
    }

    // Crear un nuevo deporte
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'image' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $sport = Sport::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->file('image')->store('sports', 'public'),
        ]);

        return response()->json([
            'sport' => $sport,
            'message' => 'Deporte creado correctamente',
            'status' => 201,
        ], 201);
    }
    //actualizar un deporte
    public function update(Request $request){
        $sport = Sport::find($request->id);
        if(!$sport){
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'image' => 'required|image',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }
        $sport->update(
            [
                'name' => $request->name,
                'description' => $request->description,
                'image' => $request->file('image')->store('sports', 'public'),
            ]
        );
        return response()->json([
            'message' => 'Deporte actualizado correctamente',
            'status' => 200,
        ], 200);
    }
    //eliminar un deporte
    public function destroy(Request $request){
        $sport = Sport::find($request->id);
        if(!$sport){
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }
        $sport->delete();
        return response()->json([
            'message' => 'Deporte eliminado correctamente',
            'status' => 200,
        ], 200);
    }
    
}
