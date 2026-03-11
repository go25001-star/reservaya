<?php

namespace App\Http\Controllers\Admin;

use App\Models\Hotel;
use App\Models\StaffHotel;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TipoHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($hotelId)
    {

        try {
            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $hotelId)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver los tipos de habitaciones de este hotel',
                ], 403);
            }

            $tiposdeHabitaciones = Hotel::with('tiposHabitacion')->find($hotelId);

            if (! $tiposdeHabitaciones) {
                return response()->json([
                    'success' => 'error',
                    'message' => 'Hotel no encontrado',
                ], 404);
            }

            return response()->json([
                'status' => 'ok',
                'data' => $tiposdeHabitaciones,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          $request->validate([
                'nombre' => 'required|string|max:250',
                'descripcion' => 'nullable|string',
                'hotel_id' => 'required|integer|exists:hoteles,id',
            ]);

        try {

            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $request->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para agregar tipos de Habitaciones en este hotel ',
                ], 403);

            }

            $tipo = TipoHabitacion::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'hotel_id' => $request->hotel_id,
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Tipo de habitación agregada correctamente',
                'data' => $tipo,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Faltan Campos que son requeridos',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo crear el Tipo de Habitación',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
