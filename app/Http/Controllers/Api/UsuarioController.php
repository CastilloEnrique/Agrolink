<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    /**
     * GET /api/usuario/perfil
     * Obtiene la información del perfil del usuario autenticado.
     * (Esta función ya la tenías en tus rutas, ahora la implementamos)
     */
    public function perfil(Request $request)
    {
        // Devuelve los datos del usuario autenticado
        return response()->json($request->user());
    }

    /**
     * POST /api/usuario/actualizar-perfil
     * Actualiza la información básica del perfil del usuario.
     */
    public function actualizarPerfil(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'primer_nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'correo' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios')->ignore($user->id), // Ignora el correo del propio usuario
            ],
        ]);

        try {
            $user->update($validated);
            return response()->json(['message' => 'Perfil actualizado exitosamente.', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el perfil.'], 500);
        }
    }

    /**
     * POST /api/usuario/cambiar-contrasena
     * Cambia la contraseña del usuario autenticado.
     */
    public function cambiarContrasena(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'contrasena_actual' => [
                'required',
                'string',
                // Verifica que la contraseña actual sea correcta
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->contrasena_hash)) {
                        $fail('La contraseña actual no es correcta.');
                    }
                },
            ],
            'nueva_contrasena' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers(), // Requiere 8+ chars, mayús, minús y números
                'confirmed', // Requiere un campo 'nueva_contrasena_confirmation'
            ],
        ]);

        try {
            $user->update([
                'contrasena_hash' => Hash::make($validated['nueva_contrasena'])
            ]);
            return response()->json(['message' => 'Contraseña actualizada exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cambiar la contraseña.'], 500);
        }
    }
}
