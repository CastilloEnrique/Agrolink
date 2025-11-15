<?php


namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirige a Google para autenticación.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtiene la respuesta de Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Buscar usuario por correo
            $usuario = Usuario::where('correo', $googleUser->email)->first();

            // Si no existe, crearlo automáticamente
            if (!$usuario) {
                $usuario = Usuario::create([
                    'primer_nombre' => $googleUser->user['given_name'] ?? '',
                    'primer_apellido' => $googleUser->user['family_name'] ?? '',
                    'correo' => $googleUser->email,
                    'foto_perfil_url' => $googleUser->avatar,
                    'estado' => 'activo',
                    'contrasena_hash' => bcrypt(str()->random(16)),
                ]);
            }

            // Iniciar sesión
            Auth::login($usuario);

            // Redirigir a tu frontend (Vue)
            return redirect(config('app.url') . '/dashboard');

        } catch (\Exception $e) {
            return redirect(config('app.url') . '/login?error=google');
        }
    }
}
