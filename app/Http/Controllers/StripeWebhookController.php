<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Modelos a usaar
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\UsuarioLibro;
use App\Models\Libro;

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
            //Si el pago fue exitoso
            if($eventoStripe->type === 'payment_intent.succeeded'){
                $paymentIntent = $eventoStripe->data->object;

                //Recuperamos los datos que guardamos en el metadata
                $user = $paymentIntent->metadata->user_id;
                $libroCar = explode(',', $paymentIntent->metadata->libro_id);
                $metodoPago = $paymentIntent->metadata->metodo_pago_id;
                $total = $paymentIntent->amount / 100;

                //Obtenemos los libros
                $libros = Libro::whereIn('id', $libroCar)->get();

                //Iniciamos la transaccion
                DB::beginTransaction();
                //Creamos la venta
                $venta = Venta::create([
                    'fecha_venta' => now(),
                    'total' => $total,
                    'estado_pago' => 'pagado',
                    'token_pasarela' => $paymentIntent->id,
                    'metodo_pago_id' => $metodoPago,
                    'user_id' => $user
                ]);
                //Preparamos la insercion masiva
                $detallesVentas = [];
                $entregaLibro = [];
                //Recorremos los libros comprados para llenar el detalle venta
                foreach($libros as $libro){
                    $detallesVentas[] = [
                        'precio_unitario' => $libro->precio_actual,
                        'subtotal' => $libro->precio_actual,
                        'libro_id' => $libro->id,
                        'venta_id' => $venta->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    //Hacer entrega de los libros
                    $entregaLibro[] =[
                        'pagina_actual' => 0,
                        'porcentaje_leido' => 0.00,
                        'estado' => 'pendiente',
                        'user_id' => $user,
                        'libro_id' => $libro->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                //Inserción masiva
                DetalleVenta::insert($detallesVentas);
                UsuarioLibro::insert($entregaLibro);
                //Todo salió bien
                DB::commit();
            }
            //Retornamos el Webhook
            return response()->json([
                'message' => 'Webhook procesado.'
            ],200);
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ],400);
        }
    }
}
