<?php
//
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\AuthController;
//
//Route::post('/login', [AuthController::class, 'login']);
//Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;

// RUTA LOGIN (pública)
Route::post('/login', [AuthController::class, 'login']);

// RUTAS PROTEGIDAS POR TOKEN
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/usuario/perfil', [UsuarioController::class, 'perfil']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
