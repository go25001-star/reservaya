<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DetalleReserva;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\EstadoReservaEnum;

class ReservaController extends Controller
{
    public function index()
    {
        try {

            $AuthUserId = auth('api')->id();

            $reservas = Reserva::with([
                'user:id,name',
                'detalles:id,reserva_id,habitacion_id,precio',
                'detalles.habitacion:id,hotel_id',
                'detalles.habitacion.hotel:id,nombre',
            ])->select('id', 'fecha_entrada', 'fecha_salida', 'total_precio', 'estado', 'user_id')
                ->where('user_id', $AuthUserId)
                ->latest()
                ->paginate(10);

            if ($reservas->total() === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No tienes reservas registradas actualmente',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $reservas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno de el servidor',

            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'fecha_entrada' => 'required|date|after_or_equal:today',
                'fecha_salida' => 'required|date|after:fecha_entrada',
                'total_precio' => 'required|numeric|min:0',

                'habitaciones' => 'required|array|min:1', // validacion de habitacion

                // Usamos el punto y el asterisco para validar cada objeto dentro del arreglo
                'habitaciones.*.habitacion_id' => 'required|exists:habitaciones,id',
                'habitaciones.*.precio' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $AuthUserId = auth('api')->id();

            $reserva = Reserva::create([
                'fecha_entrada' => $request->fecha_entrada,
                'fecha_salida' => $request->fecha_salida,
                'fecha_reserva' => now(),
                'total_precio' => $request->total_precio,
                'estado' => EstadoReservaEnum::EN_PROCESO,
                'user_id' => $AuthUserId,

            ]);
            foreach ($request->habitaciones as $item) {
                DetalleReserva::create([
                    'reserva_id' => $reserva->id,
                    'habitacion_id' => $item['habitacion_id'],
                    'precio' => $item['precio'],
                ]);
            }

            DB::commit();
            $reserva->load([
                'user:id,name',
                'detalles.habitacion:id,nombre,num_habitacion,hotel_id',
                'detalles.habitacion.hotel:id,nombre',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'reserva creada correctamente',
                'data' => $reserva,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la reserva',
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno de el servidor',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            $AuthUserId = auth('api')->id();
            $reserva = Reserva::with([

                'detalles' => function ($query) {
                    $query->select('id', 'reserva_id', 'habitacion_id', 'precio');
                },

                'detalles.habitacion' => function ($query) {
                    $query->select('id', 'nombre', 'hotel_id');
                },

                'detalles.habitacion.hotel' => function ($query) { // el "query" se le denomina como una consulta osea que consulta la tabla y trae lo qeu se le pone dentro de las llaves
                    $query->select('id', 'nombre');
                },
            ])->findOrFail($id);

            if ($reserva->user_id !== $AuthUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permisos para ver los detalles de la reserva',
                ], 403);

            }

            return response()->json([
                'status' => 'success',
                'data' => $reserva,
            ], 200);
        } catch (ModelNotFoundException $m) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró la reserva',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno de el servidor',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy(string $id)
        {
            try {

                $AuthUserId = auth('api')->id();

                $reserva = Reserva::findOrFail($id);

                if ($reserva->user_id !== $AuthUserId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No tienes permiso para cancelar esta reserva',
                    ], 403);
                }
                if ($reserva->estado === EstadoReservaEnum::FINALIZADA->value) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No puedes cancelar una reserva finalizada',
                    ], 403);
                }

                if ($reserva->estado === EstadoReservaEnum::CANCELADA->value) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Esta reserva ya está cancelada',
                    ], 403);
                }

                $reserva->estado = EstadoReservaEnum::CANCELADA->value;
                $reserva->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Reserva cancelada con exito',
                ], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo encontrar la reserva',
                ], 404);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'messagw' => 'Error interno del servidor',
                ], 500);
            }
        }
}
