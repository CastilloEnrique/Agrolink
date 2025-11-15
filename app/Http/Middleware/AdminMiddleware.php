<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Verifica si el usuario está autenticado
        // 2. Verifica si el usuario tiene una relación 'roles' y si uno de ellos se llama 'admin'
        // (Esto asume que tu modelo Usuario tiene una relación 'roles()')
        if (Auth::check() && $request->user()->roles()->where('nombre', 'admin')->exists()) {
            // Si es admin, permite continuar
            return $next($request);
        }

        // Si no es admin, rechaza el acceso
        return response()->json(['message' => 'Acceso no autorizado. Se requiere rol de administrador.'], 403);
    }
}
