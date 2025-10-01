<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'image' => 'required|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        try {
            // Almacenamos la imagen en el directorio 'sports' dentro de 'public'
            $imagePath = $request->file('image')->store('sports', 'public');
            $imageUrl = Storage::url($imagePath);

            // Creamos el registro de 'sport' en la base de datos
            $sport = Sport::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imageUrl,
            ]);

            return response()->json([
                'sport' => $sport,
                'message' => 'Deporte creado correctamente',
                'status' => 201,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la imagen o crear el deporte.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    // Actualizar un deporte
    public function update(Request $request)
    {
        $sport = Sport::find($request->id);
        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Si se sube imagen, guardar la nueva y eliminar la anterior si existe
        if ($request->hasFile('image')) {
            // Eliminar la imagen anterior si existe
            if ($sport->image && Storage::disk('public')->exists(str_replace('/storage/', '', $sport->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $sport->image));
            }

            $imagePath = $request->file('image')->store('sports', 'public');
            $imageUrl = Storage::url($imagePath); // Obtiene la URL pública, ej: /storage/sports/filename.jpg
            $sport->image = $imageUrl;
        }

        $sport->name = $request->input('name');
        $sport->description = $request->input('description');
        $sport->save();

        return response()->json([
            'message' => 'Deporte actualizado correctamente',
            'status' => 200,
            'sport' => $sport,
        ], 200);
    }

    //eliminar un deporte
    public function destroy(Request $request)
    {
        $sport = Sport::find($request->id);
        if (!$sport) {
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
    public function imageUpload(Request $request, $id)
    { {
            $sport = sport::find($id);

            if (!$sport) {
                return response()->json(['message' => 'Deporte no encontrado'], 404);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('sports_images', 'public');

                // Guardar ruta en la BD
                $sport->image = url("storage/$path");
                $sport->save();

                return response()->json([
                    'message' => 'Imagen subida correctamente',
                    'image_url' => $sport->image
                ], 200);
            }

            return response()->json(['message' => 'No se recibió una imagen'], 400);
        }
    }
}
