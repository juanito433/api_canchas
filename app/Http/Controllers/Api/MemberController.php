<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::all();

        if ($members->isEmpty()) {
            return response()->json([
                'message' => 'No hay miembros registrados',
                'status' => 404,
            ], 404);
        }

        return response()->json($members, 200);
    }
    public function show($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'message' => 'Miembro no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json($member, 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'lastname' => 'required',
            'phone' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }
        $member = Member::create([
            'name' => $request->name,
            'email' => $request->email,
            'lastname' => $request->lastname,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);
        return response()->json([
            'member' => $member,
            'message' => 'Miembro creado correctamente',
            'status' => 201,

        ], 201);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }
        $member = Member::where('email', $request->email)->first();
        if (!$member || !Hash::check($request->password, $member->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
                'status' => 401,
            ], 401);
        }
        $token = $member->createToken('auth_token')->plainTextToken;
        return response()->json([
            'member' => $member,
            'token' => $token,
            'message' => 'Inicio de sesión correcto',
            'status' => 200,
        ], 200);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'status' => 200,
        ], 200);
    }
    public function destroy($id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json([
                'message' => 'Miembro no encontrado',
                'status' => 404,
            ], 404);
        }
        $member->delete();
        return response()->json([
            'message' => 'Miembro eliminado correctamente',
            'status' => 200,
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json([
                'message' => 'Miembro no encontrado',
                'status' => 404,
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }
        $member->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);
        return response()->json([
            'member' => $member,
            'message' => 'Miembro actualizado correctamente',
            'status' => 200,
        ], 200);
    }
}
