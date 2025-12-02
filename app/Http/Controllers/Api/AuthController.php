<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',

            'lastname' => 'required|string|max:255',
            'lastname2' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'role' => 'required|string',
            'remember_token' => 'nullable|string|max:100',

        ]);
        // Crear un nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'lastname' => $request->lastname,
            'lastname2' => $request->lastname2,
            'username' => $request->username,
            'phone' => $request->phone,
        ]);
        // Retornar el usuario creado
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'El correo no está registrado.'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Contraseña incorrecta.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
    //cerrar sesión de usuario
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $token = $user->currentAccessToken();
                if ($token) {
                    $token->delete();
                }

                return response()->json(['message' => 'Logout exitoso'], 200);
            }

            return response()->json(['message' => 'Usuario no autenticado'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function changePassword(Request $request)
    {
        // 1. Validar inputs
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        // 2. Obtener usuario autenticado
        $user = $request->user();

        // 3. Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual no es correcta.',
                'errors' => ['current_password' => ['La contraseña actual es incorrecta.']]
            ], 422);
        }

        // 4. Actualizar la contraseña
        // Usamos fill y save para que Laravel maneje eventos si los tienes, o forceFill
        $user->password = Hash::make($request->password);
        $user->save();

        // 5. Opcional: Borrar otros tokens para cerrar sesión en otros dispositivos
        // $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Contraseña actualizada correctamente.'
        ], 200);
    }
}
