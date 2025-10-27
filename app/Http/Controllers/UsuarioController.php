<?php




namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function perfil(Request $request)
    {
        $user = $request->user();

        // CLAVE: Cargar la relación 'roles' para que los datos del rol viajen a Vue
        $user->load('roles');

        // Devolver el objeto de usuario con la relación de roles cargada
        return response()->json($user);
    }
}
