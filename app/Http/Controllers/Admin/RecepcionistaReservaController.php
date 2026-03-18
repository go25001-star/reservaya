<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoReservaEnum;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\StaffHotel;
use Illuminate\Http\Request;

class RecepcionistaReservaController extends Controller
{
    public function index()
    {
        try {

            $AuthUserId = auth('api')->id();


            $staffHotel = StaffHotel::where('user_id', $AuthUserId)->firstOrFail();

            $hotel_id = $staffHotel->hotel_id;


            if (!$hotel_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El hotel_id es requerido',
                ], 422);
            }

            $reservas = Reserva::with([
                'user:id,name',
                'detalles:id,reserva_id,habitacion_id,precio',
                'detalles.habitacion:id,nombre,hotel_id',
                'detalles.habitacion.hotel:id,nombre',
            ])->whereHas('detalles.habitacion', function ($query) use ($hotel_id) {
                $query->where('hotel_id', $hotel_id);
            })->latest()->paginate(10);

            if ($reservas->total() === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No hay reservas para este hotel',
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
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'estado' => 'required|string|in:FINALIZADA,CANCELADA',
            ]);

            $reserva = Reserva::findOrFail($id);

            if ($reserva->estado === EstadoReservaEnum::CANCELADA->value) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes modificar una reserva cancelada',
                ], 403);
            }

            if ($reserva->estado === EstadoReservaEnum::FINALIZADA->value) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes modificar una reserva finalizada',
                ], 403);
            }

            $reserva->estado = $request->estado;
            $reserva->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Estado de reserva actualizado correctamente',
                'data' => $reserva,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reserva no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
                'data' => $e -> getMessage()
            ], 500);
        }

    }
}
