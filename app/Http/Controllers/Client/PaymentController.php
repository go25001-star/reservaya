<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Models\Pago;

class PaymentController extends Controller
{
    public function procesarPago(Request $request)
    {
        try {
            $request->validate([
                'reserva_id' => 'required|exists:reservas,id',
                'cantidad'   => 'required|numeric|min:1',
            ]);

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Reserva #' . $request->reserva_id,
                        ],
                        'unit_amount' => intval($request->cantidad * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                // ✅ Guardamos el reserva_id para usarlo en el webhook
                'metadata' => [
                    'reserva_id' => $request->reserva_id,
                ],
                'success_url' => 'http://localhost:5173/pago-exitoso?reserva=' . $request->reserva_id,
                'cancel_url'  => 'http://localhost:5173/pago-cancelado',
            ]);

            return response()->json(['url' => $session->url]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el pago',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Solo cuando el pago fue completado
        if ($event->type === 'checkout.session.completed') {
            $session   = $event->data->object;
            $reservaId = $session->metadata->reserva_id;
            $cantidad  = $session->amount_total / 100;

            Pago::create([
                'reserva_id' => $reservaId,
                'cantidad'   => $cantidad,
                'fecha_pago' => now(),
                'tipo_pago'  => 'Tarjeta',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}