<?php

use App\Http\Controllers\Api\GeografiaController;
use App\Http\Controllers\Api\ProductorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;

// --- RUTAS PÚBLICAS ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- RUTAS DE GEOGRAFÍA (PÚBLICAS) ---  <-- LUGAR CORRECTO
Route::get('/paises', [GeografiaController::class, 'getPaises']);
Route::get('/departamentos/{paisId}', [GeografiaController::class, 'getDepartamentos']); // <-- Ruta corregida
Route::get('/municipios/{departamentoId}', [GeografiaController::class, 'getMunicipios']);
Route::get('/aldeas/{municipioId}', [GeografiaController::class, 'getAldeas']);


// --- RUTAS PROTEGIDAS POR TOKEN ---
Route::middleware('auth:sanctum')->group(function () {
    // (Solo dejamos una ruta de logout)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil']);

    // Ruta para que el productor complete/actualice su perfil
    Route::post('/productor/perfil', [ProductorController::class, 'storeProfile']);
    // API para obtener categorías
    Route::get('/categorias-producto', [ProductorController::class, 'getCategorias']);

    // API para publicar productos (Recibe FormData)
    Route::post('/productor/productos', [ProductorController::class, 'storeProduct']);
    // BORRA LAS RUTAS DE GEOGRAFÍA DE AQUÍ DENTRO
});
