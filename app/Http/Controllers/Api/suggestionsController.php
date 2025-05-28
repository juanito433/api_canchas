<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suggestions;
use Illuminate\Http\Request;

class suggestionsController extends Controller
{
    //crear una sugerencia 
    public function createSuggestion(Request $request)
    {
        $request->validate([
            'issuse' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'member_id' => 'required|exists:members,id',
        ]);

        $suggestion = new Suggestions(
            [
                'issuse' => $request->issuse,
                'message' => $request->message,
                'member_id' => $request->member_id,
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
        $suggestions = Suggestions::all();
        return response()->json($suggestions, 200);
    }

    //obtener una sugerencia por del miembro
    public function getSuggestionByMember(Request $request)
    {
        $suggestion = Suggestions::where('member_id', $request->id)->get();
        if ($suggestion->isEmpty()) {
            return response()->json(['message' => 'No se encontraron sugerencias de parte de este miembro'], 404);
        }
        return response()->json($suggestion, 200);
    }
    
}
