<?php

use App\Http\Controllers\Api\GeografiaController;
use App\Http\Controllers\Api\ProductorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Api\PedidoController;

// --- RUTAS PÚBLICAS ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// en routes/api.php

// ... (tus otras rutas de login/register)
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
// --- RUTAS DE GEOGRAFÍA (PÚBLICAS) ---
Route::get('/paises', [GeografiaController::class, 'getPaises']);
Route::get('/departamentos/{paisId}', [GeografiaController::class, 'getDepartamentos']);
Route::get('/municipios/{departamentoId}', [GeografiaController::class, 'getMunicipios']);
Route::get('/aldeas/{municipioId}', [GeografiaController::class, 'getAldeas']);

// RUTA PÚBLICA: CATÁLOGO
Route::get('/catalogo', [ProductorController::class, 'getProductsCatalog']);

// 💡 RUTA PÚBLICA: DETALLE DEL PRODUCTO (Punto 3 del módulo consumidor)
Route::get('/catalogo/{id}', [ProductorController::class, 'getProductByIdPublic']);


// --- RUTAS PROTEGIDAS POR TOKEN ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil']);

    // RUTA PARA PEDIDOS
        Route::post('/pedidos', [PedidoController::class, 'store']);


    // Perfil Completo
    Route::get('/productor/perfil-completo', [ProductorController::class, 'getPerfilCompleto']);
    Route::post('/productor/perfil-completo', [ProductorController::class, 'storePerfilCompleto']);

    // Publicación/CRUD de Productos
    Route::get('/categorias-producto', [ProductorController::class, 'getCategorias']);
    Route::post('/productor/productos', [ProductorController::class, 'storeProduct']);
    Route::get('/productor/mis-productos', [ProductorController::class, 'getMisProductos']);
    Route::delete('/productor/productos/{id}', [ProductorController::class, 'destroyProduct']);

    // Edición
    Route::get('/productor/productos/{id}', [ProductorController::class, 'getProductoPorId']);
    Route::post('/productor/productos/{id}', [ProductorController::class, 'updateProduct']);

    // Exportación
    Route::get('/productor/exportar/excel', [ProductorController::class, 'exportToExcel']);
    Route::get('/productor/exportar/pdf', [ProductorController::class, 'exportToPdf']);
});
