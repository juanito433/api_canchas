<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    #obtener todos los admisnitradores
    public function all(){
        return response()->json(admin::all(), 200);
    }


    //obtener al administrador por su id
    public function show($id){
        $admin = admin::find($id);
        if(!$admin){
            return response()->json([
                'message' => 'Admin no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json($admin, 200);
    }
    //loguear al admin
    public function login(Request $request ){
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
        $admin = admin::where('email', $request->email)->first();
        if(!$admin || !Hash::check($request->password, $admin->password)){
            return response()->json([
                'message' => 'Credenciales incorrectas',
                'status' => 401,
            ], 401);
        }
        $token = $admin->createToken($request->email)->plainTextToken;
        return response()->json([
            'admin' => $admin,
            'token' => $token,
            'message' => 'Inicio de sesión exitoso',
            'status' => 200,
        ], 200);
    }
    //Cerrar sesión
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'status' => 200,
        ], 200);
    }
    //actualizar al admin
    public function update(Request $request){
        $admin = admin::find($request->id);
        if(!$admin){
            return response()->json([
                'message' => 'Admin no encontrado',
                'status' => 404,
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'lastname' => 'required',
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
        $admin->update(
            [
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
            ]
        );
        return response()->json([
            'message' => 'Admin actualizado correctamente',
            'admin' => $admin,
            'status' => 200,
        ], 200);
    }
    //registrar un nuevo administrador
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }
        $admin=admin::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);
        return response()->json([
            'admin' => $admin,
            'message' => 'Admin creado correctamente',
            'status' => 201,
        ], 201);
    }

    public function destroy(Request $request){
        $admin = admin::find($request->id);
        if(!$admin){
            return response()->json([
                'menssage'=> 'administrador no encontrado',
                'status' => 404
            ], 404);
        }
        $admin -> delete();
        return response()->json([
            'menssage' => 'Administrador eliminado de manera exitosa',
            'status' => 200,
        ], 200);
    }

}
