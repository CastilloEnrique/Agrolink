<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * GET /api/admin/productos-pendientes
     * Obtiene todos los productos que están pendientes de revisión.
     */
    public function getProductosPendientes()
    {
        $productos = Producto::where('estado_publicacion', 'pendiente_revision')
            ->with([
                'user:id,primer_nombre,primer_apellido', // El productor
                'categoria:id,nombre',
                'imagenes' // Cargamos TODAS las imágenes
            ])
            ->orderBy('created_at', 'asc') // Mostrar los más antiguos primero
            ->get();

        // Formatear para la tabla y el modal
        $formato = $productos->map(function ($producto) {

            // Buscar imagen principal (o la primera si no hay principal)
            $imagenPrincipal = $producto->imagenes->where('principal', true)->first() ?? $producto->imagenes->first();

            // Mapear todas las imágenes para el carrusel del modal
            $todasLasImagenes = $producto->imagenes->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->ruta_imagen)
                ];
            });

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion ?? 'El productor no agregó una descripción.',
                'productor_nombre' => $producto->user->primer_nombre . ' ' . $producto->user->primer_apellido,
                'categoria_nombre' => $producto->categoria->nombre ?? 'N/A',
                'precio_referencia' => (float)$producto->precio_referencia,
                'stock_actual' => $producto->stock_actual,
                'fecha_publicacion' => $producto->created_at->format('d/m/Y'),

                // --- ✅ ESTA ES LA LÍNEA CORREGIDA (sin la 'G') ---
                'imagen_url_avatar' => $imagenPrincipal ? asset('storage/' . $imagenPrincipal->ruta_imagen) : null,

                // Este es el array que necesita el modal
                'imagenes_completas' => $todasLasImagenes,
            ];
        });

        return response()->json($formato);
    }

    /**
     * POST /api/admin/productos/{id}/actualizar-estado
     * Aprueba o rechaza un producto.
     */
    public function actualizarEstadoProducto(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:aprobado,rechazado',
            // 'motivo_rechazo' => 'nullable|string|required_if:estado,rechazado' // Opcional
        ]);

        try {
            $producto = Producto::findOrFail($id);

            $producto->update([
                'estado_publicacion' => $validated['estado']
            ]);

            Log::info("Admin ID {$request->user()->id} actualizó el producto ID {$id} a estado: {$validated['estado']}");

            return response()->json(['message' => 'Producto ' . $validated['estado'] . ' exitosamente.']);

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado del producto: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el producto.'], 500);
        }
    }
    /**
     * GET /api/admin/usuarios
     * ✅ ACTUALIZADO para funcionar con el botón "Guardar"
     */
    public function getUsuarios(Request $request)
    {
        $adminActual = $request->user();

        $usuarios = Usuario::with('roles')
            ->where('id', '!=', $adminActual->id)
            ->orderBy('primer_apellido')
            ->get()
            ->map(function ($usuario) {
                // Obtenemos el ID del rol actual del usuario
                $rolId = $usuario->roles->first()->id ?? null;
                return [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->primer_nombre . ' ' . $usuario->primer_apellido,
                    'correo' => $usuario->correo,
                    'estado' => $usuario->estado,
                    'fecha_registro' => $usuario->created_at->format('d/m/Y'),
                    'roles' => $usuario->roles->pluck('nombre'),

                    // Este es el rol "original" guardado en la BD
                    'rol_principal_id' => $rolId,

                    // Este es el rol que el admin seleccionará en el dropdown
                    // (Empiezan siendo el mismo)
                    'rol_seleccionado_id' => $rolId,
                ];
            });

        return response()->json($usuarios);
    }

    /**
     * GET /api/admin/roles
     * ✅ ACTUALIZADO para mostrar TODOS los roles (incluyendo 'admin')
     */
    public function getAllRoles()
    {
        // $roles = Rol::where('nombre', '!=', 'admin')->get(['id', 'nombre']); // ❌ Versión Antigua
        $roles = Rol::get(['id', 'nombre']); // ✅ Nueva Versión
        return response()->json($roles);
    }

    /**
     * POST /api/admin/usuarios/{id}/actualizar-rol
     * (Esta función ya la tenías y estaba bien)
     */
    public function actualizarRolUsuario(Request $request, $id)
    {
        $validated = $request->validate([
            'rol_id' => 'required|integer|exists:roles,id',
        ]);

        try {
            $usuario = Usuario::findOrFail($id);

            $usuario->roles()->sync([$validated['rol_id']]);

            Log::info("Admin ID {$request->user()->id} actualizó el ROL del usuario ID {$id} a ROL ID {$validated['rol_id']}");

            return response()->json(['message' => 'Rol de usuario actualizado exitosamente.']);

        } catch (\Exception $e) {
            Log::error('Error al actualizar rol de usuario: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el rol.'], 500);
        }
    }

    /**
     * POST /api/admin/usuarios/{id}/actualizar-estado
     * (Esta función ya la tenías y estaba bien)
     */
    public function actualizarEstadoUsuario(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:activo,inactivo,bloqueado',
        ]);

        try {
            $usuario = Usuario::findOrFail($id);

            $usuario->update(['estado' => $validated['estado']]);

            Log::info("Admin ID {$request->user()->id} actualizó el ESTADO del usuario ID {$id} a {$validated['estado']}");

            return response()->json(['message' => 'Estado del usuario actualizado.']);

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de usuario: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el estado.'], 500);
        }
}
    /**
     * GET /api/admin/pedidos-historial
     * Obtiene un historial maestro de todos los pedidos en el sistema.
     */
    public function getAllPedidos(Request $request)
    {
        try {
            $pedidos = Pedido::with([
                'usuario:id,primer_nombre,primer_apellido', // El comprador
                'detalles', // Para contar cuántos items tiene
            ])
                ->orderBy('created_at', 'desc') // Los más nuevos primero
                ->get();

            // Formatear los datos para la tabla del admin
            $formato = $pedidos->map(function ($pedido) {
                return [
                    'id' => $pedido->id,
                    'fecha' => $pedido->created_at->format('d/m/Y H:i'),
                    'estado_pedido' => $pedido->estado_pedido,
                    'total' => (float) $pedido->precio_total,
                    'comprador_nombre' => $pedido->usuario->primer_nombre . ' ' . $pedido->usuario->primer_apellido,
                    'items_count' => $pedido->detalles->count(), // Contar cuántos items diferentes tiene
                ];
            });

            return response()->json($formato);

        } catch (\Exception $e) {
            Log::error('Error al cargar historial de pedidos (Admin): ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al cargar el historial de pedidos.'], 500);
        }
    }
}
