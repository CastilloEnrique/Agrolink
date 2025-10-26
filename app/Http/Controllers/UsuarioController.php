<?php
//
//
//namespace App\Http\Controllers;
//
//use Illuminate\Http\Request;
//
//class UsuarioController extends Controller
//{
//    public function perfil(Request $request)
//    {
//        // Devuelve los datos del usuario autenticado
//        return response()->json([
//            'usuario' => $request->user(),
//        ]);
//    }
//}


namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function perfil(Request $request)
    {
        return response()->json([
            'usuario' => $request->user(),
        ]);
    }
}
