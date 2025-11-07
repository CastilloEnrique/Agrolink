<?php
//
//namespace App\Http\Controllers\Api;
//
//use App\Http\Controllers\Controller;
//use App\Models\Pedido;
//use App\Models\PedidoItem; // 💡 Usamos tu modelo 'PedidoItem'
//use App\Models\Producto;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//
//class PedidoController extends Controller
//{
//    /**
//     * Almacena un nuevo pedido en la base de datos.
//     */
//    public function store(Request $request)
//    {
//        // El usuario que está haciendo la compra
//        $user = $request->user();
//
//        // 1. Validar que el carrito no esté vacío
//        $validated = $request->validate([
//            'items' => 'required|array|min:1',
//            'items.*.id' => 'required|integer|exists:productos,id',
//            'items.*.quantity' => 'required|integer|min:1',
//        ]);
//
//        $itemsDelCarrito = $validated['items'];
//        $totalPedido = 0;
//        $detallesPedidoData = [];
//
//        try {
//            DB::transaction(function () use ($itemsDelCarrito, $user, &$totalPedido, &$detallesPedidoData) {
//
//                // 2. Recorrer los items del carrito para validar y calcular totales
//                foreach ($itemsDelCarrito as $item) {
//                    // Usamos 'findOrFail' para detenernos si un producto no existe
//                    $producto = Producto::findOrFail($item['id']);
//
//                    // Verificación de Stock (Opcional pero recomendada)
//                    // if ($producto->stock_actual < $item['quantity']) {
//                    //    throw new \Exception("Stock insuficiente para: " . $producto->nombre);
//                    // }
//
//                    $precio = $producto->precio_referencia;
//                    $cantidad = $item['quantity'];
//                    $subtotal = $precio * $cantidad;
//                    $totalPedido += $subtotal;
//
//                    // Preparar los datos para la tabla 'pedido_items'
//                    $detallesPedidoData[] = [
//                        'producto_id' => $producto->id,
//                        'productor_id' => $producto->usuario_id, // El dueño del producto
//                        'cantidad' => $cantidad,
//                        'precio_unitario' => $precio,
//                        'subtotal' => $subtotal,
//                    ];
//                }
//
//                // 3. Crear el Pedido principal (adaptado a tu tabla 'pedidos')
//                $pedido = Pedido::create([
//                    'usuario_id' => $user->id,
//                    'precio_total' => $totalPedido, // 💡 Adaptado
//                    'estado' => 'pendiente',       // 💡 Adaptado
//                    // 'metodo_entrega_id' y 'direccion_entrega' se pueden añadir después
//                ]);
//
//                // 4. Guardar los detalles del pedido
//                // Asumiendo que el modelo Pedido tiene la relación 'detalles'
//                $pedido->detalles()->createMany($detallesPedidoData);
//
//                // 5. Opcional: Reducir el stock de los productos
//                // foreach ($detallesPedidoData as $detalle) {
//                //    Producto::find($detalle['producto_id'])->decrement('stock_actual', $detalle['cantidad']);
//                // }
//
//            }); // Fin de la transacción
//
//        } catch (\Exception $e) {
//            Log::error('Error al crear pedido: ' . $e->getMessage());
//            return response()->json(['message' => 'Error interno al procesar el pedido. ' . $e->getMessage()], 500);
//        }
//
//        return response()->json(['message' => '¡Pedido realizado exitosamente!'], 201);
//    }
//}
//
//


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * Almacena un nuevo pedido en la base de datos.
     */
    public function store(Request $request)
    {
        // El usuario que está haciendo la compra
        $user = $request->user();

        // 1. Validar que el carrito no esté vacío
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:productos,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $itemsDelCarrito = $validated['items'];
        $totalPedido = 0;
        $detallesPedidoData = [];

        try {
            DB::transaction(function () use ($itemsDelCarrito, $user, &$totalPedido, &$detallesPedidoData) {

                // 2. Recorrer los items del carrito para validar y calcular totales
                foreach ($itemsDelCarrito as $item) {
                    $producto = Producto::findOrFail($item['id']);

                    $precio = $producto->precio_referencia;
                    $cantidad = $item['quantity'];
                    $subtotal = $precio * $cantidad;
                    $totalPedido += $subtotal;

                    $detallesPedidoData[] = [
                        'producto_id' => $producto->id,
                        'productor_id' => $producto->usuario_id, // El dueño del producto
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal' => $subtotal,
                        // 💡 Añadimos timestamps si el modelo PedidoItem los usa
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 3. Crear el Pedido principal (adaptado a tu tabla 'pedidos')
                $pedido = Pedido::create([
                    'usuario_id' => $user->id,
                    'precio_total' => $totalPedido,
                    'estado_pedido' => 'pendiente', // 💡 CORRECCIÓN: Tu columna se llama 'estado_pedido', no 'estado'
                    // 'metodo_entrega_id' y 'direccion_entrega' (de tu tabla) se pueden añadir después
                ]);

                // 4. Guardar los detalles del pedido
                $pedido->detalles()->createMany($detallesPedidoData);

            }); // Fin de la transacción

        } catch (\Exception $e) {
            Log::error('Error al crear pedido: ' + $e->getMessage());
            return response()->json(['message' => 'Error interno al procesar el pedido. ' + $e->getMessage()], 500);
        }

        return response()->json(['message' => '¡Pedido realizado exitosamente!'], 201);
    }
}

