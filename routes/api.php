<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//Se usan los controladores
use App\Http\Controllers\RolController;
use App\Http\Controllers\MetodoPagoContoller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Rutas para los controladores
Route::apiResource('roles', RolController::class);
Route::apiResource('metodos_pagos', MetodoPagoContoller::class);
