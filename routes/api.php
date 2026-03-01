<?php

use App\Http\Controllers\TipoHabitacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/tipohabitaciones', [TipoHabitacionController::class, 'store']);
