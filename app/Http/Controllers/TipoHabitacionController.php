php<?php

namespace App\Http\Controllers;

use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TipoHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotel = Hotel ::with('tiposHabitacion')->find($hoteld);

    if (!$hotel) {
        return response()->json([
            'success' => false,
            'message' => 'Hotel no encontrado'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'hotel_id' => $hotel->id,
            'hotel_nombre' => $hotel->nombre,
            'habitaciones' => $hotel->tiposHabitacion
        ]
    ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
        $request->validate([
            'nombre'      => 'required|string|max:250',
            'descripcion' => 'string',
            'hotel_id'    => 'required|integer|exists:hoteles,id'
        ]);

        $tipo = TipoHabitacion::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'hotel_id'    => $request->hotel_id
        ]);

        return response()->json([
            'mensaje' => 'Tipo de habitación agregada correctamente',
            'data'    => $tipo
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'mensaje' => 'Campos Requeridos',
            'errores' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'mensaje' => 'No se pudo crear el Tipo de Habitación',
            'error'   => $e->getMessage()
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
