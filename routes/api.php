<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Se usan los controladores
use App\Http\Controllers\RolController;
use App\Http\Controllers\MetodoPagoContoller;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AutorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UsuarioLibroController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\ReporteController;
//Controlador de pasarela de pago
use App\Http\Controllers\StripeWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::prefix('auth')->group(function(){

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function(){
        Route::get('me',[AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
        Route::post('refresh',[AuthController::class, 'refresh']);
    });

});

//Ruta para la pasarela de pago
Route::post('/webhook', [StripeWebhookController::class, 'procesarPago']);
//Rutas publicas
Route::get('libros', [LibroController::class, 'index']);
Route::get('libros/{id}', [LibroController::class, 'show']);
Route::get('categorias', [CategoriaController::class, 'index']);

//Rutas protegidas para el admin
Route::middleware(['auth:api', 'role:ADMIN'])->group(function() {
    Route::get('ventas', [VentaController::class, 'index']);
    Route::post('libros', [LibroController::class, 'store']);
    Route::put('libros/{id}', [LibroController::class, 'update']);
    Route::delete('libros/{id}', [LibroController::class, 'destroy']);
    Route::post('categorias', [CategoriaController::class, 'store']);
    Route::put('categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('categorias/{id}', [CategoriaController::class, 'destroy']);
    Route::post('metodos_pagos', [MetodoPagoContoller::class, 'store']);
    Route::put('metodos_pagos/{id}', [MetodoPagoContoller::class, 'update']);
    Route::delete('metodos_pagos/{id}', [MetodoPagoContoller::class, 'destroy']);
    Route::apiResource('autores', AutorController::class);

});
//Rutas para clientes autenticados
Route::middleware(['auth:api', 'role:CLIENTE'])->group(function () {
    Route::get('metodos_pagos', [MetodoPagoContoller::class, 'index']);
    Route::post('ventas', [VentaController::class, 'store']);
    Route::apiResource('usuarios_libros', UsuarioLibroController::class);
});
//rutas para reportes, para poder generar el pdf
Route::get('/reportes/ventas', [ReporteController::class, 'reporteVentas']);
