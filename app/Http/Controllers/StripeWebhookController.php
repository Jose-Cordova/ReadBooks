<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Usamos el modelo
use App\Models\Venta;

class StripeWebhookController extends Controller
{
    //Stripe llamara a este método automáticamente cuando un pago haya sido confirmado
    public function procesarPago(Request $request)
    {
        try{
            //Obtenemos el contenido de la petición de Stripe
            $datosStripe = $request->getContent();
            //Obtenemos la firma que manda Stripe
            $firmaStripe = $request->header('Stripe-Signature');
            //Verificamos que la petición viene realmente de Stripe
            $eventoStripe = \Stripe\Webhook::constructEvent(
                $datosStripe,
                $firmaStripe,
                config('services.stripe.webhook_secret')
            );
            //Verificamos el tipo de evento que manda Stripe
            if($eventoStripe->type === 'payment_intent.succeeded'){
                $paymentIntent = $eventoStripe->data->object;
                //Buscamos la venta por el token de Stripe
                Venta::where('token_pasarela', $paymentIntent->id)
                ->update(['estado_pago' => 'pagado']);
            }
            //Retornamos el Webhook
            return response()->json([
                'message' => 'Webhook procesado.'
            ],200);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ],400);
        }
    }
}
