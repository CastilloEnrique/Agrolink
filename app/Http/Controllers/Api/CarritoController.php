<?php
//
//namespace App\Http\Controllers\Api;
//
//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
//use App\Models\Producto;
//use Illuminate\Support\Facades\Log;
//class CarritoController extends Controller
//{
//    /**
//     * GET /carrito
//     */
//    public function getCarrito(Request $request)
//    {
//        return response()->json([
//            'items' => $request->user()->carrito ?? [],
//        ]);
//    }
//
//    /**
//     * POST /carrito/agregar
//     */
////    public function agregar(Request $request)
////    {
////        $validated = $request->validate([
////            'producto_id' => 'required|integer|exists:productos,id',
////            'cantidad' => 'required|integer|min:1',
////        ]);
////
////        $user = $request->user();
////
////        // Crear carrito si no existe
////        if (!$user->carrito) {
////            $user->carrito = [];
////        }
////
////        // ¿Ya existe este producto?
////        $carrito = $user->carrito;
////
////        if (isset($carrito[$validated['producto_id']])) {
////            $carrito[$validated['producto_id']]['cantidad'] += $validated['cantidad'];
////        } else {
////            $producto = Producto::findOrFail($validated['producto_id']);
////
////            $carrito[$validated['producto_id']] = [
////                'id' => $producto->id,
////                'nombre' => $producto->nombre_producto,
////                'precio' => $producto->precio_referencia,
////                'image' => $producto->imagen_url,
////                'productor' => $producto->usuario_id,
////                'cantidad' => $validated['cantidad'],
////            ];
////        }
////
////        // Guardar el carrito en usuarios (JSON)
////        $user->carrito = $carrito;
////        $user->save();
////
////        return response()->json(['message' => 'Producto agregado al carrito']);
////    }
//    public function agregar(Request $request)
//    {
//        $validated = $request->validate([
//            'producto_id' => 'required|integer|exists:productos,id',
//            'cantidad' => 'required|integer|min:1',
//        ]);
//
//        $user = $request->user();
//
//        try {
//            // 1. Cargar producto CON sus relaciones
//            $producto = Producto::with([
//                'user:id,primer_nombre,primer_apellido',
//                'imagenes' => fn($q) => $q->where('principal', true)
//            ])->findOrFail($validated['producto_id']);
//
//            // --- 🐞 INICIO DEBUG ---
//            Log::info('--- DEBUG CARRITO ---');
//            Log::info('1. Producto Cargado: ' . $producto->nombre);
//            Log::info('2. Producto Precio: ' . $producto->precio_referencia);
//            Log::info('3. Producto User (Relación): ' . json_encode($producto->user));
//            // --- 🐞 FIN DEBUG ---
//
//            // 2. Obtener los datos correctos
//            $imagen = $producto->imagenes->first();
//            $urlImagen = $imagen ? $imagen->ruta_imagen : null;
//            $productorNombre = $producto->user ? ($producto->user->primer_nombre . ' ' . $producto->user->primer_apellido) : 'N/A';
//
//            // 3. Asegurar que carrito sea un array
//            $carrito = is_string($user->carrito) ? json_decode($user->carrito, true) : ($user->carrito ?? []);
//
//            // 4. Crear el item
//            $itemNuevo = [
//                'id' => $producto->id,
//                'nombre' => $producto->nombre,
//                'precio' => $producto->precio_referencia,
//                'image' => $urlImagen,
//                'productor' => $productorNombre,
//                'unidad' => $producto->unidad_medida,
//                'cantidad' => $validated['cantidad'],
//            ];
//
//            // --- 🐞 DEBUG 2 ---
//            Log::info('4. Datos a guardar en JSON: ' . json_encode($itemNuevo));
//
//            // 5. Agregar o actualizar
//            if (isset($carrito[$validated['producto_id']])) {
//                $itemNuevo['cantidad'] += $carrito[$validated['producto_id']]['cantidad'];
//            }
//            $carrito[$validated['producto_id']] = $itemNuevo;
//
//            $user->carrito = $carrito;
//            $user->save();
//
//            return response()->json([ 'message' => 'Producto agregado al carrito' ]);
//
//        } catch (\Exception $e) {
//            Log::error('--- 🚨 ERROR AL AGREGAR CARRITO 🚨 ---');
//            Log::error($e->getMessage());
//            return response()->json(['message' => 'Error interno al agregar al carrito.'], 500);
//        }
//    }
//
//
//    /**
//     * PUT /carrito/actualizar-cantidad
//     */
//    public function actualizarCantidad(Request $request)
//    {
//        $validated = $request->validate([
//            'producto_id' => 'required|integer',
//            'cantidad' => 'required|integer|min:1',
//        ]);
//
//        $user = $request->user();
//
//        if (!$user->carrito || !isset($user->carrito[$validated['producto_id']])) {
//            return response()->json(['message' => 'El producto no está en el carrito'], 404);
//        }
//
//        $carrito = $user->carrito;
//        $carrito[$validated['producto_id']]['cantidad'] = $validated['cantidad'];
//
//        $user->carrito = $carrito;
//        $user->save();
//
//        return response()->json(['message' => 'Cantidad actualizada']);
//    }
//
//    /**
//     * DELETE /carrito/eliminar
//     */
//    public function eliminar(Request $request)
//    {
//        $validated = $request->validate([
//            'producto_id' => 'required|integer',
//        ]);
//
//        $user = $request->user();
//
//        if (!$user->carrito || !isset($user->carrito[$validated['producto_id']])) {
//            return response()->json(['message' => 'El producto no está en el carrito'], 404);
//        }
//
//        $carrito = $user->carrito;
//        unset($carrito[$validated['producto_id']]);
//
//        $user->carrito = $carrito;
//        $user->save();
//
//        return response()->json(['message' => 'Producto eliminado']);
//    }
//}




namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarritoItem;
use App\Models\Producto; // 💡 Asegúrate que el modelo Producto esté importado
use Illuminate\Support\Facades\Auth;


class CarritoController extends Controller
{
    /**
     * GET /carrito
     * Obtiene los items del carrito del usuario autenticado.
     */
    public function getCarrito(Request $request)
    {
        $items = CarritoItem::where('usuario_id', $request->user()->id)
            ->with([
                // ✅ ESTA ES LA PARTE CLAVE QUE TE FALTA ✅
                // Carga el producto, su usuario (productor) y su imagen principal
                'producto.user:id,primer_nombre,primer_apellido',
                'producto.imagenes' => fn($q) => $q->where('principal', true)
            ])
            ->get();

        // Formatear la respuesta exacta que el frontend espera
        $formatoFrontend = $items->map(function ($item) {

            $producto = $item->producto; // $item->producto ahora SÍ tiene datos

            if (!$producto) {
                // Si el producto fue eliminado de la BD, no lo enviamos
                return null;
            }

            // Obtenemos los datos de las relaciones cargadas
            $imagen = $producto->imagenes->first();
            $productor = $producto->user;

            return [
                'id' => $producto->id, // 💡 ID del Producto
                'item_id' => $item->id, // ID único del CarritoItem
                'name' => $producto->nombre,
                'price' => (float) $producto->precio_referencia,
                'image_url' => $imagen ? asset('storage/' . $imagen->ruta_imagen) : null,
                'producer' => $productor ? ($productor->primer_nombre . ' ' . $productor->primer_apellido) : 'N/A',
                'unit' => $producto->unidad_medida,
                'quantity' => $item->cantidad,
                'subtotal' => (float) $producto->precio_referencia * $item->cantidad,
            ];
        })->filter(); // filter() elimina los 'null' si un producto no se encontró

        // Devolvemos 'items' como un array, no un objeto
        return response()->json(['items' => $formatoFrontend]);
    }

    /**
     * POST /carrito/agregar
     * Agrega un producto al carrito.
     */
    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $usuario_id = $request->user()->id;
        $producto_id = $validated['producto_id'];
        $cantidad = $validated['cantidad'];

        // Buscar si el item ya existe
        $itemExistente = CarritoItem::where('usuario_id', $usuario_id)
            ->where('producto_id', $producto_id)
            ->first();

        if ($itemExistente) {
            // Si existe, solo actualizamos la cantidad
            $itemExistente->cantidad += $cantidad;
            $itemExistente->save();
            $message = 'Cantidad actualizada en el carrito';
        } else {
            // Si no existe, lo creamos
            CarritoItem::create([
                'usuario_id' => $usuario_id,
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
            ]);
            $message = 'Producto agregado al carrito';
        }

        return response()->json(['message' => $message], 201);
    }

    /**
     * PUT /carrito/actualizar-cantidad
     * Actualiza la cantidad de un item específico.
     */
    public function actualizarCantidad(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $item = CarritoItem::where('usuario_id', $request->user()->id)
            ->where('producto_id', $validated['producto_id'])
            ->first();

        if (!$item) {
            return response()->json(['message' => 'El producto no está en el carrito'], 404);
        }

        $item->cantidad = $validated['cantidad'];
        $item->save();

        return response()->json(['message' => 'Cantidad actualizada']);
    }

    /**
     * DELETE /carrito/eliminar
     * Elimina un producto del carrito.
     */
    public function eliminar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
        ]);

        $item = CarritoItem::where('usuario_id', $request->user()->id)
            ->where('producto_id', $validated['producto_id'])
            ->first();

        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Producto eliminado']);
        }

        return response()->json(['message' => 'El producto no está en el carrito'], 404);
    }
}
