<?php




namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\MetodoEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CarritoItem;

class PedidoController extends Controller
{
    /**
     * FUNCIÓN STORE (Sin cambios - Ya funciona)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:productos,id',
            'items.*.quantity' => 'required|integer|min:1',
            'metodo_entrega_id' => 'required|integer|exists:metodos_entrega,id',
            'direccion_entrega' => 'nullable|string|max:1000',
        ]);

        $itemsDelCarrito = $validated['items'];
        $totalPedido = 0;
        $detallesPedidoData = [];

        try {
            $metodoEntrega = MetodoEntrega::findOrFail($validated['metodo_entrega_id']);
            $costoEnvio = (float)$metodoEntrega->costo;

            DB::transaction(function () use ($itemsDelCarrito, $user, $costoEnvio, $validated, &$totalPedido, &$detallesPedidoData) {

                foreach ($itemsDelCarrito as $item) {
                    $producto = Producto::findOrFail($item['id']);
                    $precio = $producto->precio_referencia;
                    $cantidad = $item['quantity'];
                    $subtotal = $precio * $cantidad;
                    $totalPedido += $subtotal;

                    $detallesPedidoData[] = [
                        'producto_id' => $producto->id,
                        'productor_id' => $producto->usuario_id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal' => $subtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $totalPedidoConEnvio = $totalPedido + $costoEnvio;

                $pedido = Pedido::create([
                    'usuario_id' => $user->id,
                    'precio_total' => $totalPedidoConEnvio,
                    'estado_pedido' => 'pendiente',
                    'metodo_entrega_id' => $validated['metodo_entrega_id'],
                    'direccion_entrega' => $validated['direccion_entrega'],
                ]);

                $pedido->detalles()->createMany($detallesPedidoData);
            });

            CarritoItem::where('usuario_id', $user->id)->delete();

        } catch (\Exception $e) {
            Log::error('Error al crear pedido: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno al procesar el pedido. ' . $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => '¡Pedido realizado exitosamente!'], 201);
    }

    /**
     * GET /api/pedidos/consumidor (Sin cambios)
     */
    public function getMisPedidosConsumidor(Request $request)
    {
        $usuario = $request->user();

        $pedidos = Pedido::where('usuario_id', $usuario->id)
            ->with([
                'detalles.producto.imagenes' => function ($query) {
                    $query->where('principal', true);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosFormateados = $pedidos->map(function ($pedido) {

            return [
                'id' => $pedido->id,
                'fecha' => $pedido->created_at->format('d/m/Y H:i'),
                'estado' => $pedido->estado_pedido,
                'total' => (float) $pedido->precio_total,

                'productos' => $pedido->detalles->map(function ($detalle) {

                    if (!$detalle->producto) {
                        return [ 'nombre' => '[Producto no disponible]' ];
                    }
                    $imagen = $detalle->producto->imagenes->first();
                    return [
                        'id' => $detalle->producto->id,
                        'nombre' => $detalle->producto->nombre,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => (float) $detalle->precio_unitario,
                        'imagen_url' => $imagen ? asset('storage/' . $imagen->ruta_imagen) : null,
                    ];
                }),
            ];
        });

        return response()->json($pedidosFormateados);
    }

    /**
     * 💡 --- GET /api/productor/pedidos-recibidos (CORREGIDA) --- 💡
     * Usamos colecciones y sortByDesc() para evitar conflictos de join.
     */
    public function getPedidosProductor(Request $request)
    {
        $productor = $request->user();

        // 1. Buscamos en 'pedido_items'
        $itemsVendidos = \App\Models\PedidoItem::where('productor_id', $productor->id)
            // 2. Cargamos TODAS las relaciones que necesitamos
            ->with([
                'producto.imagenes' => fn($q) => $q->where('principal', true),
                'pedido' => fn($q) => $q->orderBy('created_at', 'desc'), // Pedimos que cargue el pedido
                'pedido.usuario:id,primer_nombre,primer_apellido' // Y el usuario de ese pedido
            ])
            ->get()
            // 3. Ordenamos la colección final por la fecha del pedido
            ->sortByDesc(function ($item) {
                // Si el pedido existe, usamos su fecha
                return $item->pedido ? $item->pedido->created_at : null;
            });

        // 4. Formateamos los datos
        $pedidosFormateados = $itemsVendidos->map(function ($item) {

            // 5. El filtro de seguridad: si alguna relación falló, lo saltamos
            if (!$item->pedido || !$item->producto || !$item->pedido->usuario) {
                Log::warning("Saltando PedidoItem ID {$item->id} por datos incompletos.");
                return null;
            }

            $imagen = $item->producto->imagenes->first();

            return [
                'id' => $item->id, // ID del PedidoItem
                'pedido_id' => $item->pedido->id,
                'fecha' => $item->pedido->created_at->format('d/m/Y'),
                'estado_pedido' => $item->pedido->estado_pedido,
                'producto_nombre' => $item->producto->nombre,
                'comprador_nombre' => $item->pedido->usuario->primer_nombre . ' ' . $item->pedido->usuario->primer_apellido,
                'cantidad' => $item->cantidad,
                'precio_unitario' => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
                'imagen_url' => $imagen ? asset('storage/' . $imagen->ruta_imagen) : null,
            ];

        })->filter()->values();

        return response()->json($pedidosFormateados);
    }

    // --- 👇 ¡NUEVA FUNCIÓN PARA ACTUALIZAR ESTADO! 👇 ---
    /**
     * POST /api/pedidos/{id}/actualizar-estado
     * Actualiza el estado de un pedido (ej. 'en_preparacion', 'enviado').
     */
    public function actualizarEstadoPedido(Request $request, $id) // $id es el ID del Pedido
    {
        $productor = $request->user();

        $validated = $request->validate([
            // Añadimos todos los estados posibles
            'estado' => 'required|string|in:en_preparacion,enviado,entregado,cancelado',
        ]);

        try {
            $pedido = Pedido::findOrFail($id);

            // 💡 Verificación de Seguridad:
            // ¿Este productor tiene al menos un item en este pedido?
            $itemEnPedido = PedidoItem::where('pedido_id', $pedido->id)
                ->where('productor_id', $productor->id)
                ->exists();

            if (!$itemEnPedido && !$productor->tieneRol('admin')) {
                // Si no tiene items Y no es admin, no puede modificarlo
                return response()->json(['message' => 'No autorizado para modificar este pedido.'], 403);
            }

            // Si está autorizado, actualiza el estado
            $pedido->update(['estado_pedido' => $validated['estado']]);

            return response()->json(['message' => 'Estado del pedido actualizado.']);

        } catch (\Exception $e) {
            Log::error("Error al actualizar estado del pedido ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el estado.'], 500);
        }
    }
}
