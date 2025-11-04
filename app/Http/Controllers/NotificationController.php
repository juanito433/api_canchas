<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Guarda o actualiza el expo_push_token en la tabla users.
     */
    public function savePushToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'expo_push_token' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $user->expo_push_token = $request->expo_push_token;
        $user->save();

        return response()->json(['message' => 'Token registrado correctamente'], 200);
    }

    /**
     * Enviar notificación de prueba a un usuario (útil para debugging).
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (!$user->expo_push_token) {
            return response()->json(['error' => 'Usuario no tiene token registrado'], 400);
        }

        $response = Http::post('https://exp.host/--/api/v2/push/send', [
            'to' => $user->expo_push_token,
            'title' => 'Prueba de notificación',
            'body' => 'Este es un mensaje de prueba desde el backend de Laravel.',
            'data' => ['test' => true],
        ]);

        return response()->json([
            'expo_response' => $response->json(),
        ], 200);
    }
}
