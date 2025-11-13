<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class NoticeController extends Controller
{
    /**
     * 📋 Listar todas las notificaciones
     */
    public function index()
    {
        $notices = Notice::with('user:id,name')
            ->orderBy('date_published', 'desc')
            ->get();

        return response()->json($notices);
    }

    /**
     * 🔍 Mostrar detalle de una notificación
     */
    public function show($id)
    {
        $notice = Notice::with('user:id,name,email')->find($id);

        if (!$notice) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        return response()->json($notice);
    }

    /**
     * 📨 Crear nueva notificación (solo administradores)
     */
    public function store(Request $request)
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Obtener usuario
        $user = User::find($validated['user_id']);

        // Verificar si el usuario es administrador
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo los administradores pueden crear notificaciones.'
            ], 403);
        }

        // Crear la notificación
        $notice = Notice::create([
            'title'           => $validated['title'],
            'content'         => $validated['content'],
            'user_id'         => $user->id,
            'date_published'  => now(),
        ]);

        // Obtener tokens de usuarios
        $tokens = User::whereNotNull('expo_push_token')->pluck('expo_push_token')->toArray();

        // Enviar notificaciones push por lotes (máximo 100)
        $chunks = array_chunk($tokens, 100);
        foreach ($chunks as $chunk) {
            $messages = array_map(function ($token) use ($notice) {
                return [
                    'to' => $token,
                    'sound' => 'default',
                    'title' => $notice->title,
                    'body' => $notice->content,
                    'data' => ['notice_id' => $notice->id],
                ];
            }, $chunk);

            Http::post('https://exp.host/--/api/v2/push/send', $messages);
        }

        // Respuesta exitosa
        return response()->json([
            'message' => 'Notificación creada correctamente.',
            'notice' => $notice->load('user:id,name,email'),
        ], 201);
    }
}
