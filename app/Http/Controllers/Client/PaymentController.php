<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function procesarPago(Request $request)
    {
        try {
            $request->validate([
                'reserva_id' => 'required|exists:reservas,id',
                'cantidad' => 'required|numeric|min:1',
            ]);

            Stripe::setApiKey(config('services.stripe.secret'));  // Configurar stripe con las clave secreta

            $procesoPago = PaymentIntent::create([
                'amount' => intval($request->cantidad * 100), // convierte a centavos
                'currency' => 'usd',
                'metadata' => [
                    'reserva_id' => $request->reserva_id,
                ],
            ]);

            $pago = Pago::create([
                'fecha_pago' => now(),
                'cantidad' => $request->cantidad,
                'tipo_pago' => 'Tarjeta',
                'reserva_id' => $request->reserva_id,
            ]);

            return response()->json([
                'clientSecret' => $procesoPago->client_secret,
                'pago_id' => $pago?->id,
                'message' => 'pago creado correctamente'], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
