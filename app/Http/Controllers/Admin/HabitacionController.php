<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoHabitacionEnum;
use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\HabitacionImagen;
use App\Models\StaffHotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $hotel_id = $request->query('hotel_id');
            $estado = $request->query('estado');

            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver las habitaciones de este hotel',
                ], 403);
            }

            $query = Habitacion::where('hotel_id', $hotel_id)
                ->with(['tipoHabitacion', 'imagenes']);

            
            if ($estado && in_array($estado, array_column(EstadoHabitacionEnum::cases(), 'value'))) {
                $query->where('estado', $estado);
            }

            $habitaciones = $query->get();

            if ($habitaciones->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No se encontraron habitaciones',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'status' => 'success',
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
            try {
                if (! $request->has('habitacion')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El objeto habitacion es requerido',
                    ], 422);
                }

                $habitacionData = json_decode($request->habitacion, true);

                if (! $habitacionData) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El formato del JSON es inválido',
                    ], 422);
                }

                $data = [
                    'nombre' => $habitacionData['nombre'] ?? null,
                    'num_habitacion' => $habitacionData['num_habitacion'] ?? null,
                    'precio' => $habitacionData['precio'] ?? null,
                    'capacidad' => $habitacionData['capacidad'] ?? null,
                    'tipo_habitacion_id' => $habitacionData['tipo_habitacion_id'] ?? null,
                    'hotel_id' => $habitacionData['hotel_id'] ?? null,
                ];

                $validator = Validator::make($data, [
                    'nombre' => 'required|string|max:250',
                    'num_habitacion' => 'required|integer',
                    'precio' => 'required|numeric',
                    'capacidad' => 'required|integer',
                    'tipo_habitacion_id' => 'required|integer|exists:tipo_habitaciones,id',
                    'hotel_id' => 'required|integer|exists:hoteles,id',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Faltan campos requeridos',
                    ], 422);
                }

                $AuthUserId = auth('api')->id();

                $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                    ->where('hotel_id', $data['hotel_id'])
                    ->exists();

                if (! $verificarUsuario) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No tienes permiso para agregar habitaciones en este hotel',
                    ], 403);
                }

                DB::beginTransaction();

                $habitacion = Habitacion::create([
                    'nombre' => $data['nombre'],
                    'estado' => EstadoHabitacionEnum::DISPONIBLE->value,
                    'num_habitacion' => $data['num_habitacion'],
                    'precio' => $data['precio'],
                    'capacidad' => $data['capacidad'],
                    'tipo_habitacion_id' => $data['tipo_habitacion_id'],
                    'hotel_id' => $data['hotel_id'],
                ]);

                if ($request->hasFile('imagenes')) {
                    foreach ($request->file('imagenes') as $file) {
                        $url = asset('storage/'.$file->store('habitaciones', 'public'));
                        HabitacionImagen::create([
                            'url' => $url,
                            'habitacion_id' => $habitacion->id,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Habitación creada correctamente',
                    'data' => $habitacion->load(['tipoHabitacion', 'imagenes']),
                ], 201);

            } catch (ValidationException $e) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Faltan campos requeridos',  
                ], 422);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
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

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver esta habitación',
                ], 403);
            }

            return response()->json([
                'status' => 'ok',
                'data' => $habitacion,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habitación no encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:250',
                'estado' => 'required|in:'.implode(',', array_column(EstadoHabitacionEnum::cases(), 'value')),
                'num_habitacion' => 'required|integer',
                'precio' => 'required|numeric',
                'capacidad' => 'required|integer',
                'tipo_habitacion_id' => 'required|integer|exists:tipo_habitaciones,id',
            ]);

            $AuthUserId = auth('api')->id();

            $habitacion = Habitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $habitacion->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para actualizar esta habitación',
                ], 403);
            }

            $habitacion->update([
                'nombre' => $request->nombre,
                'estado' => $request->estado,
                'num_habitacion' => $request->num_habitacion,
                'precio' => $request->precio,
                'capacidad' => $request->capacidad,
                'tipo_habitacion_id' => $request->tipo_habitacion_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Habitación actualizada correctamente',
                'data' => $habitacion->load('tipoHabitacion'),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Faltan campos requeridos',
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habitación no encontrada',
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

            $habitacion = Habitacion::findOrFail($id);

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $habitacion->hotel_id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para eliminar esta habitación',
                ], 403);
            }

            $habitacion->update([
                'estado' => EstadoHabitacionEnum::FUERA_DE_SERVICIO->value,
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Habitación dada de baja correctamente',
                'data' => $habitacion,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habitación no encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
