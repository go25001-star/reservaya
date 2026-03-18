<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\StaffHotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        try {
            $hotelId = $request->query('hotel');

            if (!$hotelId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El hotel_id es requerido',
                ], 422);
            }

            // Reservas totales del hotel
            $reservasTotales = Reserva::whereHas('detalles.habitacion', function ($q) use ($hotelId) {
                $q->where('hotel_id', $hotelId);
            })->count();

            // Ingresos totales del hotel
            $ingresosTotales = Reserva::whereHas('detalles.habitacion', function ($q) use ($hotelId) {
                $q->where('hotel_id', $hotelId);
            })->sum('total_precio');

            // Ingresos mensuales del año actual
            $ingresosMensuales = Reserva::whereHas('detalles.habitacion', function ($q) use ($hotelId) {
                $q->where('hotel_id', $hotelId);
            })
            ->whereYear('fecha_reserva', now()->year)
            ->select(
                DB::raw('MONTH(fecha_reserva) as mes'),
                DB::raw('SUM(total_precio) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

            // Top 5 habitaciones más rentadas
            $topHabitaciones = DB::table('detalle_reservas')
                ->join('habitaciones', 'detalle_reservas.habitacion_id', '=', 'habitaciones.id')
                ->where('habitaciones.hotel_id', $hotelId)
                ->select('habitaciones.nombre', DB::raw('COUNT(*) as reservas'))
                ->groupBy('habitaciones.id', 'habitaciones.nombre')
                ->orderByDesc('reservas')
                ->limit(5)
                ->get();

            $maxReservas = $topHabitaciones->max('reservas') ?? 1;
            $topHabitaciones = $topHabitaciones->map(function ($hab) use ($maxReservas) {
                $hab->porcentaje = round(($hab->reservas / $maxReservas) * 100) . '%';
                return $hab;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'reservas_totales'   => $reservasTotales,
                    'ingresos_totales'   => $ingresosTotales,
                    'ingresos_mensuales' => $ingresosMensuales,
                    'top_habitaciones'   => $topHabitaciones,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
