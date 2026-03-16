<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
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

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Reserva #'.$request->reserva_id,
                        ],
                        'unit_amount' => intval($request->cantidad * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://localhost:5173/pago-exitoso?reserva='.$request->reserva_id,
                'cancel_url' => 'http://localhost:5173/pago-cancelado',
            ]);

            return response()->json([
                'url' => $session->url,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
