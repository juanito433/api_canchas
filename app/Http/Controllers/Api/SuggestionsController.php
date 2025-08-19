<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\suggestions;
use Illuminate\Http\Request;

class SuggestionsController extends Controller
{
    //crear una sugerencia 
    public function createSuggestion(Request $request)
    {
        $request->validate([
            'issuse' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
        ]);

        $suggestion = new Suggestions(
            [
                'issuse' => $request->issuse,
                'message' => $request->message,
                'user_id' => $request->user_id,
            ]
        );
        $suggestion->save();
        return response()->json([
            'message' => 'Sugerencia creada exitosamente',
            'suggestion' => $suggestion
        ], 201);
    }

    //obtener todas las sugerencias
    public function getAllSuggestions()
    {
        $suggestions = suggestions::all();
        return response()->json($suggestions, 200);
    }

    //obtener una sugerencia por del miembro
    public function getSuggestionByMember(Request $request)
    {
        $suggestion = suggestions::where('user_id', $request->id)->get();
        if ($suggestion->isEmpty()) {
            return response()->json(['message' => 'No se encontraron sugerencias de parte de este miembro'], 404);
        }
        return response()->json($suggestion, 200);
    }
}
