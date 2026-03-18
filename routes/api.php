<?php

use App\Enums\RolEnum;
use App\Http\Controllers\Admin\AdminHotelController;
use App\Http\Controllers\Admin\EstadisticasController;
use App\Http\Controllers\Admin\HabitacionController as AdminHabitacionController;
use App\Http\Controllers\Admin\ImagenHabitacionController;
use App\Http\Controllers\Admin\RecepcionistaReservaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\StaffHotelController;
use App\Http\Controllers\Admin\TipoHabitacionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\HabitacionController;
use App\Http\Controllers\Client\HotelController;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\Client\ReservaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('refresh', [AuthController::class, 'refresh']);

    });

});

Route::prefix('admin')->group(function () {

    Route::middleware(['auth:api', 'role.and:USUARIOADMIN'])->group(function () {
        Route::apiResource('hoteles', AdminHotelController::class);
        Route::get('estadisticas', [EstadisticasController::class, 'index']);
    });

    $roles = implode('|', [
        RolEnum::PROPIETARIO->value,
        RolEnum::GERENTE->value,
        RolEnum::RECEPCIONISTA->value
    ]);

    Route::middleware(['auth:api', 'role:'.$roles])->group(function () {
        Route::apiResource('habitaciones', AdminHabitacionController::class);
        Route::apiResource('hotelusuarios', StaffHotelController::class);
        Route::apiResource('tipohabitaciones', TipoHabitacionController::class);
        Route::apiResource('imagenesHabitacion', ImagenHabitacionController::class);
        Route::apiResource('reservas', RecepcionistaReservaController::class );
    });



});

Route::prefix('principalpage')->group(function () {
    Route::get('hoteles', [HotelController::class, 'index']);
    Route::get('habitaciones/{hotel_id}', [HabitacionController::class, 'index']);
});

Route::get('reportes/ingresos', [ReporteController::class, 'reporteIngresos']);

Route::prefix('user')->group(function () {

    Route::middleware(['auth:api', 'role:'.RolEnum::USUARIO->value])->group(function () {
        Route::apiResource('reserva', ReservaController::class);
    });
});

    Route::post('pago', [PaymentController::class, 'procesarPago']);
    Route::post('/webhook/stripe', [PaymentController::class, 'webhook']);
