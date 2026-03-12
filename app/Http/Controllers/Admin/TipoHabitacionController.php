<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\StaffHotel;
use App\Models\TipoHabitacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TipoHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $hotelId = $request->query('hotel_id');

            $AuthUserId = auth('api')->id();

            $hotel = Hotel::find($hotelId);

            if (! $hotel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hotel no encontrado',
                ], 404);
            }

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $hotelId)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver los tipos de habitaciones de este hotel',
                ], 403);
            }

            $tipos = TipoHabitacion::where('hotel_id', $hotelId)->get();

            return response()->json([
                'status' => 'success',
                'data' => $tipos,
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
        try {
            $request->validate([
                'nombre' => 'required|string|max:250',
                'descripcion' => 'nullable|string',
                'hotel_id' => 'required|integer|exists:hoteles,id',
            ]);

            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $request->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para agregar tipos de habitaciones en este hotel',
                ], 403);
            }

            $tipo = TipoHabitacion::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'hotel_id' => $request->hotel_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de habitación agregado correctamente',
                'data' => $tipo,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Faltan campos requeridos',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo crear el tipo de habitación',
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $AuthUserId = auth('api')->id();

            $tipo = TipoHabitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $tipo->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver este tipo de habitación',
                ], 403);
            }

            $tipo->load(['habitaciones']);

            return response()->json([
                'status' => 'success',
                'data' => $tipo
                   
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tipo de habitación no existe',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error'
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:250',
                'descripcion' => 'nullable|string',
            ]);

            $AuthUserId = auth('api')->id();

            $tipo = TipoHabitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $tipo->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para actualizar este tipo de habitación',
                ], 403);
            }

            $tipo->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de habitación actualizado correctamente',
                'data' => $tipo,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Faltan campos requeridos',
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tipo de habitación no encontrado',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $AuthUserId = auth('api')->id();

            $tipo = TipoHabitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $tipo->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para eliminar este tipo de habitación',
                ], 403);
            }

            // Verificar si tiene habitaciones asociadas
            if ($tipo->habitaciones()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar un tipo de habitación que tiene habitaciones asociadas',
                ], 409);
            }

            $tipo->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de habitación eliminado correctamente',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tipo de habitación no encontrado',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
