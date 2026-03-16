<?php

namespace App\Http\Controllers;

use App\Models\DetalleReserva;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $reserva = Reserva::with([

                'detalles' => function ($query) {
                    $query->select('id', 'reserva_id', 'habitacion_id', 'precio');
                },

                'detalles.habitacion' => function ($query) {
                    $query->select('id', 'nombre', 'hotel_id');
                },

                'detalles.habitacion.hotel' => function ($query) {
                    $query->select('id', 'nombre');
                }
            ])
                ->latest()
                ->paginate(10);
            return response()->json([
                "message" => $reserva
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Error interno de el servidor",

            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_entrada' => 'required|date|after_or_equal:today',
            'fecha_salida'  => 'required|date|after:fecha_entrada', //todos lo datos se validaran de la tabla reservas
            'total_precio'  => 'required|numeric|min:0',
            'user_id'       => 'required|exists:users,id',
            'estado'        => 'required|string|max:50',


            'habitaciones'   => 'required|array|min:1', // validacion de habbiitacion

            // Usamos el punto y el asterisco para validar cada objeto dentro del arreglo segun gemini
            'habitaciones.*.habitacion_id' => 'required|exists:habitaciones,id',
            'habitaciones.*.precio'        => 'required|numeric|min:0',
        ]);
        DB::beginTransaction();

        try {
            $reserva = Reserva::create([
                'fecha_entrada' => $request->fecha_entrada,
                'fecha_salida' => $request->fecha_salida,
                'fecha_reserva' => now(),
                'total_precio' => $request->total_precio,
                'estado' => true,
                'user_id' => $request->user_id


            ]);
            foreach ($request->habitaciones as $item) {
                DetalleReserva::create([
                    'reserva_id'    => $reserva->id,      // El ID de la reserva que acabamos de crear
                    'habitacion_id' => $item['habitacion_id'],
                    'precio'        => $item['precio'],
                ]);
            }

            DB::commit();
            $reserva->load([
                'user:id,name',
                'detalles.habitacion:id,nombre,num_habitacion,hotel_id', // Cambié 'nombre_habitacion' por 'nombre'
                'detalles.habitacion.hotel:id,nombre'
            ]);
            return response()->json([
                "message" => "reserva creada con exiito",
                "data" => $reserva
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Error al crear la reserva",
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Error interno de el servidor",
                "message" => $e->getMessage()

            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $reserva = Reserva::with([

                'detalles' => function ($query) {
                    $query->select('id', 'reserva_id', 'habitacion_id', 'precio');
                },

                'detalles.habitacion' => function ($query) {
                    $query->select('id', 'nombre', 'hotel_id');
                },

                'detalles.habitacion.hotel' => function ($query) { //el "query" se le denomina como una consulta osea que consulta la tabla y trae lo qeu se le pone dentro de las llaves
                    $query->select('id', 'nombre');
                }
            ])->findOrFail($id);
            return response()->json([
                "message" => $reserva
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "status" => "Error",
                "error" => "No se encontro la reserva"
            ], 404);
        } catch (\Exception) {
            return response()->json([
                "error" => "Error interno de el servidor",
            ], 500);
        }
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
        try {
            $reserva = Reserva::findOrFail($id);
            $reserva -> estado = false;
            $reserva->save();
            return response()->json([
                "message" => "Reserva cancelada con exito"
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "Error" => "No se pudo encontrarla reserva",
                "message"=> $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Error critico de el servidor",
                "message"=> $e->getMessage()

            ], 500);
        }
    }
}
