<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    //Crea un PaymenIntent en Stripe y devolve el client_secret
    public function crearPaymentIntent(Request $request)
    {
        try{
            //Autenticamos con Stripe usando nuestra clave secreta
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            //Convertimos los id de los libros a string para guardarlos en la metadata
            $librosCar = implode(',', $request->libro_id);
            //Creamos la intención de cobro en Stripe (en centavos)
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $request->total * 100,
                'currency' => 'usd',
                'metadata' => [
                    'metodo_pago_id' => $request->metodo_pago_id,
                    'user_id' => $request->user_id,
                    'libro_id' => $librosCar
                ]
            ]);
            //Devolvemos el client_secret a la venta
            return $paymentIntent->client_secret;
        }
        //Por si hay error al conectarse con stripe
        catch(\Exception $e){
            throw new \Exception('Error al conectar con Stripe');
        }
    }
}
