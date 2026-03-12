<?php

use App\Enums\RolEnum;
use App\Http\Controllers\Admin\AdminHotelController;
use App\Http\Controllers\Admin\HabitacionController as AdminHabitacionController;
use App\Http\Controllers\Admin\StaffHotelController;
use App\Http\Controllers\Admin\TipoHabitacionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\HabitacionController;
use App\Http\Controllers\Client\HotelController;
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

    $rolesPropietario = implode('|', [
        RolEnum::USUARIOADMIN->value,
        RolEnum::PROPIETARIO->value,
    ]);

    Route::middleware(['auth:api', 'staff.activo','role:'.$rolesPropietario])->group(function () {
        Route::apiResource('hoteles', AdminHotelController::class);
    });

    $roles = implode('|', [
        RolEnum::PROPIETARIO->value,
        RolEnum::GERENTE->value,
    ]);

    Route::middleware(['auth:api', 'staff.activo', 'role:'.$roles])->group(function () {
        Route::apiResource('habitaciones', AdminHabitacionController::class);
        Route::apiResource('hotelusuarios', StaffHotelController::class);
        Route::apiResource('tipohabitaciones', TipoHabitacionController::class);
    });

    $rolesIndex = implode('|', [
        RolEnum::PROPIETARIO->value,
        RolEnum::GERENTE->value,
        RolEnum::RECEPCIONISTA->value,
    ]);

    Route::middleware(['auth:api', 'staff.activo', 'role:'.$rolesIndex])->group(function () {
        Route::apiResource('habitaciones', AdminHabitacionController::class)->only('index');
    });

});



Route::prefix('principalpage')->group(function () {
    Route::get('hoteles', [HotelController::class, 'index']);
    Route::get('habitaciones/{hotel_id}', [HabitacionController::class, 'index']);
});
