<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>
    <style>
        @include('reportes.CSS.pdf')
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <table class="header">
        <tr>
            <td width="20%">       
              <img  class="img "src="{{ public_path('storage/images/logo.jpg') }}" alt="logo">
            </td>
            <td width="80%">
                <div class="empresa">ReservaYa S.A. de C.V.</div>
                <div class="titulo">REPORTE DE INGRESOS</div>
                <div class="subtitulo">
                    Hotel: {{ $hotel->nombre }} <br>
                    Del {{ $fechaInicio }} al {{ $fechaFin }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabla de pagos --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha Pago</th>
                <th>Huésped</th>
                <th>Habitación</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Estado</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $index => $pago)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                    <td>{{ $pago->reserva->user->name }}</td>
                    <td>
                        @foreach ($pago->reserva->detalles as $detalle)
                            {{ $detalle->habitacion->nombre }} <br>
                        @endforeach
                    </td>
                    <td>{{ \Carbon\Carbon::parse($pago->reserva->fecha_entrada)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($pago->reserva->fecha_salida)->format('d/m/Y') }}</td>
                    <td>{{ $pago->reserva->estado }}</td>
                    <td>$ {{ number_format($pago->cantidad, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">
                        No hay ingresos en este período
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Resumen --}}
    <div class="resumen">
        <strong>Total Reservas Pagadas:</strong> {{ $totalReservas }} <br>
        <strong>Total Ingresos:</strong> $ {{ number_format($totalIngresos, 2) }}
    </div>

    {{-- Paginación --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $pdf->page_text(500, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 9);
        }
    </script>

</body>
</html>