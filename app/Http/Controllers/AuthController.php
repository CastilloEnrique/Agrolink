<?php
//
//namespace App\Http\Controllers;
//
//use Illuminate\Http\Request;
//use App\Models\Usuario;
//use Illuminate\Support\Facades\Hash;
//
//class AuthController extends Controller
//{
//    public function login(Request $request)
//    {
//        $credentials = $request->validate([
//            'correo' => ['required', 'email'],
//            'password' => ['required'],
//        ]);
//
//        $usuario = Usuario::where('correo', $credentials['correo'])->first();
//
//        if (!$usuario || !Hash::check($credentials['password'], $usuario->contrasena_hash)) {
//            return response()->json(['message' => 'Credenciales incorrectas'], 401);
//        }
//
//        if ($usuario->estado !== 'activo') {
//            return response()->json(['message' => 'Usuario inactivo o bloqueado'], 403);
//        }
//
//        $token = $usuario->createToken('auth_token')->plainTextToken;
//
//        return response()->json([
//            'access_token' => $token,
//            'usuario' => $usuario,
//        ]);
//    }
//
//    public function logout(Request $request)
//    {
//        $request->user()->tokens()->delete();
//        return response()->json(['message' => 'Sesión cerrada correctamente']);
//    }
//}


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $usuario = Usuario::where('correo', $credentials['correo'])->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->contrasena_hash)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        if ($usuario->estado !== 'activo') {
            return response()->json(['message' => 'Usuario inactivo o bloqueado'], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'usuario' => $usuario,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}
