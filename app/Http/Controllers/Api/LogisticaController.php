<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetodoEntrega;
use Illuminate\Http\Request;

class LogisticaController extends Controller
{
    /**
     * GET /api/logistica/metodos-entrega
     * Devuelve todos los métodos de entrega activos.
     */
    public function getMetodosEntrega()
    {
        // (Si en el futuro agregas una columna 'activo', puedes filtrar por ella)
        // Por ahora, los traemos todos.
        $metodos = MetodoEntrega::get(['id', 'nombre', 'costo', 'descripcion']);

        return response()->json($metodos);
    }
}
// prueba deploy desde forge
