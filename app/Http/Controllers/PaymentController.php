<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Usamos el modelo
use App\Models\Venta;

class PaymentController extends Controller
{
    //Crea un PaymenIntent en Stripe y devolve el client_secret
    public function crearPaymentIntent(Venta $venta)
    {
        try{
            //Autenticamos con Stripe usando nuestra clave secreta
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            //Creamos la intención de cobro en Stripe (en centavos)
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $venta->total * 100,
                'currency' => 'usd',
                'metadata' => [
                    'venta_id' => $venta->id, //Para identificar la venta en el webhook
                    'user_id' => $venta->user_id
                ]
            ]);
            //Devolvemos el token_pasarela a venta
            $venta->update([
                'token_pasarela' => $paymentIntent->id,
            ]);
            //Devolvemos el client_secret a la venta
            return $paymentIntent->client_secret;
        }
        catch(\Exception $e){
            //Se lanza para que en VentaControoler haga rollBack
            throw new \Exception('Error al conectar con Stripe');
        }
    }
}
