<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Se usan los controladores
use App\Http\Controllers\RolController;
use App\Http\Controllers\MetodoPagoContoller;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AutorController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
Route::apiResource('metodos_pagos', MetodoPagoContoller::class);
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('autores', AutorController::class);



Route::prefix('auth')->group(function(){

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function(){
        Route::get('me',[AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
        Route::post('refresh',[AuthController::class, 'refresh']);
    });

});