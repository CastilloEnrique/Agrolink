<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\CategoriaProducto; // 💡 1. IMPORTAMOS EL MODELO DE CATEGORÍA
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Obtiene las estadísticas clave Y los datos de gráficos.
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        $tarjetas = [];
        $graficoVentas = ['labels' => [], 'data' => []];
        $graficoTopProductos = ['labels' => [], 'data' => []];
        $diasAtras = 7; // Gráficos de los últimos 7 días

        // -------------------------------------
        // ESTADÍSTICAS PARA ADMINISTRADORES
        // -------------------------------------
        if ($user->tieneRol('admin')) {
            $totalIngresos = Pedido::sum('precio_total');
            $totalPedidos = Pedido::count();
            $totalUsuarios = Usuario::count();
            $productosPendientes = Producto::where('estado_publicacion', 'pendiente_revision')->count();

            $tarjetas = [
                ['titulo' => 'Ingresos Totales', 'valor' => 'Q' . number_format($totalIngresos, 2), 'icono' => 'tabler-currency-quetzal', 'color' => 'success'],
                ['titulo' => 'Pedidos Totales', 'valor' => $totalPedidos, 'icono' => 'tabler-truck-delivery', 'color' => 'info'],
                ['titulo' => 'Usuarios Registrados', 'valor' => $totalUsuarios, 'icono' => 'tabler-users', 'color' => 'primary'],
                ['titulo' => 'Productos Pendientes', 'valor' => $productosPendientes, 'icono' => 'tabler-shield-check', 'color' => 'warning'],
            ];

            // --- 💡 GRÁFICOS (Admin) ---
            $ventasSemanales = Pedido::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('SUM(precio_total) as total')
            )
                ->where('created_at', '>=', now()->subDays($diasAtras))
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();

            $topProductos = PedidoItem::join('productos', 'pedido_items.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(pedido_items.cantidad) as total_vendido'))
                ->groupBy('productos.nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->get();

            $graficoVentas = [
                'labels' => $ventasSemanales->pluck('fecha')->map(fn($d) => date('d/m', strtotime($d))),
                'data' => $ventasSemanales->pluck('total'),
            ];
            $graficoTopProductos = [
                'labels' => $topProductos->pluck('nombre'),
                'data' => $topProductos->pluck('total_vendido'),
            ];

            // -------------------------------------
            // ESTADÍSTICAS PARA PRODUCTORES
            // -------------------------------------
        } elseif ($user->tieneRol('productor')) {
            $productorId = $user->id;

            $misIngresos = PedidoItem::where('productor_id', $productorId)->sum('subtotal');
            $misItemsVendidos = PedidoItem::where('productor_id', $productorId)->sum('cantidad');
            $misProductosActivos = Producto::where('usuario_id', $productorId)->where('estado_publicacion', 'aprobado')->count();
            $misProductosPendientes = Producto::where('usuario_id', $productorId)->where('estado_publicacion', 'pendiente_revision')->count();

            $tarjetas = [
                ['titulo' => 'Mis Ingresos Totales', 'valor' => 'Q' . number_format($misIngresos, 2), 'icono' => 'tabler-currency-quetzal', 'color' => 'success'],
                ['titulo' => 'Items Vendidos', 'valor' => $misItemsVendidos, 'icono' => 'tabler-box', 'color' => 'info'],
                ['titulo' => 'Productos Aprobados', 'valor' => $misProductosActivos, 'icono' => 'tabler-packages', 'color' => 'primary'],
                ['titulo' => 'Productos Pendientes', 'valor' => $misProductosPendientes, 'icono' => 'tabler-shield-check', 'color' => 'warning'],
            ];

            // --- 💡 GRÁFICOS (Productor) ---
            $ventasSemanales = PedidoItem::join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
                ->select(
                    DB::raw('DATE(pedidos.created_at) as fecha'),
                    DB::raw('SUM(pedido_items.subtotal) as total')
                )
                ->where('pedido_items.productor_id', $productorId)
                ->where('pedidos.created_at', '>=', now()->subDays($diasAtras))
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();

            $topProductos = PedidoItem::join('productos', 'pedido_items.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(pedido_items.cantidad) as total_vendido'))
                ->where('pedido_items.productor_id', $productorId)
                ->groupBy('productos.nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->get();

            $graficoVentas = [
                'labels' => $ventasSemanales->pluck('fecha')->map(fn($d) => date('d/m', strtotime($d))),
                'data' => $ventasSemanales->pluck('total'),
            ];
            $graficoTopProductos = [
                'labels' => $topProductos->pluck('nombre'),
                'data' => $topProductos->pluck('total_vendido'),
            ];

            // -------------------------------------
            // ESTADÍSTICAS PARA CONSUMIDORES
            // -------------------------------------
        } else {
            $misPedidos = Pedido::where('usuario_id', $user->id)->count();
            $gastado = Pedido::where('usuario_id', $user->id)->sum('precio_total');

            $tarjetas = [
                ['titulo' => 'Mis Pedidos Realizados', 'valor' => $misPedidos, 'icono' => 'tabler-history', 'color' => 'primary'],
                ['titulo' => 'Total Gastado', 'valor' => 'Q' . number_format($gastado, 2), 'icono' => 'tabler-receipt-2', 'color' => 'success'],
            ];
            // (Los consumidores no ven los gráficos grandes)
        }

        // --- PRECIOS DE REFERENCIA (Esto ya lo teníamos) ---
        $preciosReferencia = [
            'cafe' => ['nombre' => 'Café (Quintal)', 'precio' => $this->getPrecioPromedioPorCategoria('Café'), 'icono' => 'tabler-coffee'],
            'frijol' => ['nombre' => 'Frijol (Quintal)', 'precio' => $this->getPrecioPromedioPorCategoria('Frijol'), 'icono' => 'tabler-leaf'],
            'maiz' => ['nombre' => 'Maíz (Quintal)', 'precio' => $this->getPrecioPromedioPorCategoria('Maíz'), 'icono' => 'tabler-corn'],
        ];

        // --- RESPUESTA JSON FINAL ---
        return response()->json([
            'rol' => $user->roles->first()->nombre ?? 'consumidor',
            'tarjetas' => $tarjetas,
            'precios_referencia' => $preciosReferencia,
            'grafico_ventas' => $graficoVentas,             // 💡 AÑADIDO
            'grafico_top_productos' => $graficoTopProductos, // 💡 AÑADIDO
        ]);
    }

    /**
     * Calcula el precio promedio de los productos aprobados de una categoría.
     * (Esta función ya la teníamos y está bien)
     */
    private function getPrecioPromedioPorCategoria($nombreCategoria)
    {
        try {
            $categoria = CategoriaProducto::where('nombre', $nombreCategoria)->first();
            if (!$categoria) {
                return 0;
            }
            $promedio = Producto::where('categoria_id', $categoria->id)
                ->where('estado_publicacion', 'aprobado')
                ->avg('precio_referencia');
            return (float) $promedio;
        } catch (\Exception $e) {
            Log::error("Error al calcular precio promedio para $nombreCategoria: " . $e->getMessage());
            return 0;
        }
    }
}
