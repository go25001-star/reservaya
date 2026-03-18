<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function reporteIngresos(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $hotelId = $request->hotel_id;

        $pagos = Pago::with([
            'reserva.user',
            'reserva.detalles.habitacion.hotel',
        ])
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin.' 23:59:59'])
            ->whereHas('reserva', function ($q) {
                $q->where('estado', 'FINALIZADA');  
            })
            ->whereHas('reserva.detalles.habitacion', function ($q) use ($hotelId) {
                $q->where('hotel_id', $hotelId);
            })
            ->orderBy('fecha_pago', 'desc')
            ->get();

        $totalIngresos = $pagos->sum('cantidad');
        $totalReservas = $pagos->count();
        $hotel = Hotel::find($hotelId);

        $pdf = Pdf::loadView('reportes.ingresos', [
            'pagos' => $pagos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'hotel' => $hotel,
            'totalIngresos' => $totalIngresos,
            'totalReservas' => $totalReservas,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option('enable_php', true);

        return $pdf->stream('reporte_ingresos.pdf');
    }
}
