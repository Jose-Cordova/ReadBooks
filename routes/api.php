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
//Rutas para los controladores
Route::apiResource('metodos_pagos', MetodoPagoContoller::class);
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('autores', AutorController::class);
Route::apiResource('libros', LibroController::class);

Route::middleware('auth:api')->group(function () {
    Route::apiResource('ventas', VentaController::class);
    Route::apiResource('usuarios_libros', UsuarioLibroController::class);
});