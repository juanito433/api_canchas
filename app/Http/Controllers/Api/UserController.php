<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserDeletedMail;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'lastname' => 'required|string|max:255',
            'lastname2' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'lastname' => $request->lastname,
            'lastname2' => $request->lastname2,
            'username' => $request->username,
            'phone' => $request->phone,
            'role' => $request->role, // <- importante
        ]);

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

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $email = $user->email;
        $username = $user->username;

        $user->delete();

        // Enviar correo notificando eliminación
        Mail::to($email)->send(new UserDeletedMail($username));

        return response()->json(['message' => 'User deleted successfully'], 200);
    }


    public function Search(Request $request)
    {
        $query = $request->query('query', ''); // por defecto vacío
        $page = $request->query('page', 1);   // opcional, paginación

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('lastname', 'like', "%{$query}%")
                ->orWhere('lastname2', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->orderBy('name')
            ->paginate(20); // 20 usuarios por página

        return response()->json($users);
    }
    public function SearchMember(Request $request)
    {
        $query = $request->query('query', ''); // texto buscado

        $users = User::where('role', '!=', 'admin') // Excluir administradores
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('lastname', 'like', "%{$query}%")
                    ->orWhere('lastname2', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->select('id', 'name', 'email', 'role') // solo datos necesarios
            ->limit(15)
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ], 200);
    }
}
