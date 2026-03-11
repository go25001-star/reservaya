<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoHabitacionEnum;
use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\StaffHotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function index($hotel_id)
    {
        try {
            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $hotel_id)
                ->exists();

            if (!$verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver las habitaciones de este hotel',
                ], 403);
            }

            $habitaciones = Habitacion::where('hotel_id', $hotel_id)
                ->with(['tipoHabitacion', 'imagenes'])
                ->get();

            if ($habitaciones->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontraron habitaciones para este hotel',
                ], 404);
            }

            return response()->json([
                'status' => 'ok',
                'data' => $habitaciones,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_habitacion' => 'required|string|max:250',
            'num_habitacion'    => 'required|integer',
            'precio'            => 'required|numeric',
            'capacidad'         => 'required|integer',
            'tipo_habitacion_id'=> 'required|integer|exists:tipo_habitaciones,id',
            'hotel_id'          => 'required|integer|exists:hoteles,id',
        ]);

        try {
            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $request->hotel_id)
                ->exists();

            if (!$verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para agregar habitaciones en este hotel',
                ], 403);
            }

            $habitacion = Habitacion::create([
                'nombre_habitacion'  => $request->nombre_habitacion,
                'estado'             => EstadoHabitacionEnum::DISPONIBLE->value,
                'num_habitacion'     => $request->num_habitacion,
                'precio'             => $request->precio,
                'capacidad'          => $request->capacidad,
                'tipo_habitacion_id' => $request->tipo_habitacion_id,
                'hotel_id'           => $request->hotel_id,
            ]);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Habitación creada correctamente',
                'data'    => $habitacion->load('tipoHabitacion'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $AuthUserId = auth('api')->id();

            $habitacion = Habitacion::with(['tipoHabitacion', 'imagenes'])->findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $habitacion->hotel_id)
                ->exists();

            if (!$verificarUsuario) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No tienes permiso para ver esta habitación',
                ], 403);
            }

            return response()->json([
                'status' => 'ok',
                'data'   => $habitacion,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Habitación no encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre_habitacion'  => 'required|string|max:250',
            'estado'             => 'required|in:' . implode(',', array_column(EstadoHabitacionEnum::cases(), 'value')),
            'num_habitacion'     => 'required|integer',
            'precio'             => 'required|numeric',
            'capacidad'          => 'required|integer',
            'tipo_habitacion_id' => 'required|integer|exists:tipo_habitaciones,id',
        ]);

        try {
            $AuthUserId = auth('api')->id();

            $habitacion = Habitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $habitacion->hotel_id)
                ->exists();

            if (!$verificarUsuario) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No tienes permiso para actualizar esta habitación',
                ], 403);
            }

            $habitacion->update([
                'nombre_habitacion'  => $request->nombre_habitacion,
                'estado'             => $request->estado,
                'num_habitacion'     => $request->num_habitacion,
                'precio'             => $request->precio,
                'capacidad'          => $request->capacidad,
                'tipo_habitacion_id' => $request->tipo_habitacion_id,
            ]);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Habitación actualizada correctamente',
                'data'    => $habitacion->load('tipoHabitacion'),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Habitación no encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $AuthUserId = auth('api')->id();

            $habitacion = Habitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $habitacion->hotel_id)
                ->exists();

            if (!$verificarUsuario) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No tienes permiso para eliminar esta habitación',
                ], 403);
            }

            $habitacion->update([
                'estado' => EstadoHabitacionEnum::FUERA_DE_SERVICIO->value,
            ]);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Habitación dada de baja correctamente',
                'data'    => $habitacion,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Habitación no encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}