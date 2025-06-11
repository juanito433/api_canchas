<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        // Si se proporciona un parámetro de búsqueda, filtrar los usuarios
        if ($request->has('search')) {
            $search = $request->input('search');
            $users = $users->filter(function ($user) use ($search) {
                return str_contains(strtolower($user->name), strtolower($search)) ||
                    str_contains(strtolower($user->email), strtolower($search));
            });
        }
        //si no hay usuarios, retornar un mensaje
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 404);
        }
        return response()->json($users);
    }
    //consultar un usuario por id
    public function show($id)
    {
        $user = User::find($id);
        //si no se encuentra el usuario, retornar un mensaje
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }
    //crear un nuevo usuario
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
    //actualizar un usuario, solo se actualizara el username, phone y photo_url}
    public function update(Request $request, $id)
    {
        // Validar los datos de entrada
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'phone' => 'required|string|max:15',
            'photo_url' => 'nullable|url',
        ]);

        $user = User::find($id);
        //si no se encuentra el usuario, retornar un mensaje
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Actualizar los campos permitidos
        $user->username = $request->username;
        $user->phone = $request->phone;
        if ($request->has('photo_url')) {
            $user->photo_url = $request->photo_url;
        }
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }
    public function destroy($id)
    {
        $user = User::find($id);
        //si no se encuentra el usuario, retornar un mensaje
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
