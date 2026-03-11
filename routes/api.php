<?php

use App\Http\Controllers\Client\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/pagos/procesar', [PaymentController::class, 'procesarPago']);
