<?php


use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GeografiaController;
use App\Http\Controllers\Api\LogisticaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProductorController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;

use Illuminate\Support\Facades\Route;

// --- RUTAS PÚBLICAS ---

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// LOGIN SOCIAL
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// GEOGRAFÍA
Route::get('/paises', [GeografiaController::class, 'getPaises']);
Route::get('/departamentos/{paisId}', [GeografiaController::class, 'getDepartamentos']);
Route::get('/municipios/{departamentoId}', [GeografiaController::class, 'getMunicipios']);
Route::get('/aldeas/{municipioId}', [GeografiaController::class, 'getAldeas']);

// CATÁLOGO PÚBLICO
Route::get('/catalogo', [ProductorController::class, 'getProductsCatalog']);
Route::get('/catalogo/{id}', [ProductorController::class, 'getProductByIdPublic']);

// 🔥 NUEVOS ENDPOINTS PÚBLICOS
Route::get('/producto/{id}/contactar-whatsapp', [ProductorController::class, 'contactarWhatsapp']);
Route::get('/producto/{id}/relacionados', [ProductorController::class, 'productosRelacionados']);
Route::get('/productor/{id}/perfil-publico', [ProductorController::class, 'perfilPublico']);


// --- RUTAS PROTEGIDAS ---
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // 📈 DASHBOARD
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil']);
    Route::post('/usuario/actualizar-perfil', [UsuarioController::class, 'actualizarPerfil']);
    Route::post('/usuario/cambiar-contrasena', [UsuarioController::class, 'cambiarContrasena']);

    // 🔥 CARRITO
    Route::get('/carrito', [CarritoController::class, 'getCarrito']);
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
    Route::put('/carrito/actualizar-cantidad', [CarritoController::class, 'actualizarCantidad']);
    Route::delete('/carrito/eliminar', [CarritoController::class, 'eliminar']);

    // 🔥 PEDIDOS
    Route::post('/pedidos', [PedidoController::class, 'store']); // Para crear un pedido
    Route::get('/pedidos/consumidor', [PedidoController::class, 'getMisPedidosConsumidor']); // Para el consumidor
    Route::get('/productor/pedidos-recibidos', [PedidoController::class, 'getPedidosProductor']); // Para el productor

    // 💡 NOTA: Eliminé la ruta duplicada de '/pedidos' que estaba aquí.

    // PERFIL PRODUCTOR
    Route::get('/productor/perfil-completo', [ProductorController::class, 'getPerfilCompleto']);
    Route::post('/productor/perfil-completo', [ProductorController::class, 'storePerfilCompleto']);

    // CRUD PRODUCTOS
    Route::get('/categorias-producto', [ProductorController::class, 'getCategorias']);
    Route::post('/productor/productos', [ProductorController::class, 'storeProduct']);
    Route::get('/productor/mis-productos', [ProductorController::class, 'getMisProductos']);
    Route::delete('/productor/productos/{id}', [ProductorController::class, 'destroyProduct']);
    Route::get('/productor/productos/{id}', [ProductorController::class, 'getProductoPorId']);
    Route::post('/productor/productos/{id}', [ProductorController::class, 'updateProduct']);

    // EXPORTACIONES
    Route::get('/productor/exportar/excel', [ProductorController::class, 'exportToExcel']);
    Route::get('/productor/exportar/pdf', [ProductorController::class, 'exportToPdf']);

    Route::get('/logistica/metodos-entrega', [LogisticaController::class, 'getMetodosEntrega']);
});

// --- 👇 1. GRUPO NUEVO DE RUTAS DE ADMINISTRACIÓN 👇 ---
// Este grupo requiere que el usuario esté logueado Y sea admin
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {

        // Ruta para el AdminController
        // GET /api/admin/productos-pendientes
        Route::get('/productos-pendientes', [
            \App\Http\Controllers\Api\AdminController::class, 'getProductosPendientes'
        ]);

        // POST /api/admin/productos/{id}/actualizar-estado
        Route::post('/productos/{id}/actualizar-estado', [
            \App\Http\Controllers\Api\AdminController::class, 'actualizarEstadoProducto'
        ]);
        Route::get('/usuarios', [AdminController::class, 'getUsuarios']);
        Route::get('/roles', [AdminController::class, 'getAllRoles']);
        Route::post('/usuarios/{id}/actualizar-rol', [AdminController::class, 'actualizarRolUsuario']);
        Route::post('/usuarios/{id}/actualizar-estado', [AdminController::class, 'actualizarEstadoUsuario']);


        // Pedidos
        Route::get('/pedidos-historial', [AdminController::class, 'getAllPedidos']);
    });








//
//use App\Http\Controllers\Api\CarritoController;
//use App\Http\Controllers\Api\GeografiaController;
//use App\Http\Controllers\Api\PedidoController;
//use App\Http\Controllers\Api\ProductorController;
//use App\Http\Controllers\AuthController;
//use App\Http\Controllers\GoogleController;
//use App\Http\Controllers\UsuarioController;
//use Illuminate\Support\Facades\Route;
//// --- RUTAS PÚBLICAS ---
//
//Route::post('/login', [AuthController::class, 'login']);
//Route::post('/register', [AuthController::class, 'register']);
//
//Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
//Route::post('/reset-password', [AuthController::class, 'resetPassword']);
//
//// LOGIN SOCIAL
//Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
//Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
//
//// GEOGRAFÍA
//Route::get('/paises', [GeografiaController::class, 'getPaises']);
//Route::get('/departamentos/{paisId}', [GeografiaController::class, 'getDepartamentos']);
//Route::get('/municipios/{departamentoId}', [GeografiaController::class, 'getMunicipios']);
//Route::get('/aldeas/{municipioId}', [GeografiaController::class, 'getAldeas']);
//
//// CATÁLOGO PÚBLICO
//Route::get('/catalogo', [ProductorController::class, 'getProductsCatalog']);
//Route::get('/catalogo/{id}', [ProductorController::class, 'getProductByIdPublic']);
//
//// 🔥 NUEVOS ENDPOINTS PÚBLICOS
//Route::get('/producto/{id}/contactar-whatsapp', [ProductorController::class, 'contactarWhatsapp']);
//Route::get('/producto/{id}/relacionados', [ProductorController::class, 'productosRelacionados']);
//Route::get('/productor/{id}/perfil-publico', [ProductorController::class, 'perfilPublico']);
//
//
//// --- RUTAS PROTEGIDAS ---
//Route::middleware('auth:sanctum')->group(function () {
//
//    Route::post('/logout', [AuthController::class, 'logout']);
//    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil']);
//
//    // 🔥 CARRITO
//    Route::get('/carrito', [CarritoController::class, 'getCarrito']);
//    Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
//    Route::put('/carrito/actualizar-cantidad', [CarritoController::class, 'actualizarCantidad']);
//    Route::delete('/carrito/eliminar', [CarritoController::class, 'eliminar']);
//
//    // Pedidos existentes
//    Route::post('/pedidos', [PedidoController::class, 'store']);
//    Route::get('/pedidos/consumidor', [PedidoController::class, 'getMisPedidosConsumidor']);
//    Route::get('/productor/pedidos-recibidos', [PedidoController::class, 'getPedidosProductor']);// <-- 💡 NUEVA RUTA
//
//
//    // PEDIDOS
//    Route::post('/pedidos', [PedidoController::class, 'store']);
//
//    // PERFIL PRODUCTOR
//    Route::get('/productor/perfil-completo', [ProductorController::class, 'getPerfilCompleto']);
//    Route::post('/productor/perfil-completo', [ProductorController::class, 'storePerfilCompleto']);
//
//    // CRUD PRODUCTOS
//    Route::get('/categorias-producto', [ProductorController::class, 'getCategorias']);
//    Route::post('/productor/productos', [ProductorController::class, 'storeProduct']);
//    Route::get('/productor/mis-productos', [ProductorController::class, 'getMisProductos']);
//    Route::delete('/productor/productos/{id}', [ProductorController::class, 'destroyProduct']);
//    Route::get('/productor/productos/{id}', [ProductorController::class, 'getProductoPorId']);
//    Route::post('/productor/productos/{id}', [ProductorController::class, 'updateProduct']);
//
//    // EXPORTACIONES
//    Route::get('/productor/exportar/excel', [ProductorController::class, 'exportToExcel']);
//    Route::get('/productor/exportar/pdf', [ProductorController::class, 'exportToPdf']);
//
//
//});
//
