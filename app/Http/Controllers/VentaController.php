<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
//Modelos a usar 
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\UsuarioLibro;
use App\Models\Libro;
//Conroladoress para la pasarela de pago
use App\Http\Controllers\PaymentController;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            //Validacion para el filtro
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio'
            ]);
            //Obtenemos las ventas para el administrador
            $ventas = Venta::with(['user', 'detalleVentas'])
            //Filtro para de fechas
            ->when($request->fecha_inicio, function($query) use($request){
                $query->whereDate('fecha_venta', '>=', $request->fecha_inicio);
            })
            ->when($request->fecha_fin, function($query) use($request){
                $query->whereDate('fecha_venta', '<=', $request->fecha_fin);
            })
            ->get()
            //Recorremos cada venta y sacamos los elementos
            ->map(function($venta){
                return [
                    'cliente' => $venta->user->name,
                    'fecha' => $venta->fecha_venta->format('d/m/Y'),
                    'estado' => $venta->estado_pago,
                    'articulos' => $venta->detalleVentas->count(),
                    'total' => $venta->total
                ];
            });
            //Retornamos las ventas
            return response()->json($ventas, 200);
        }
        catch(\Exception $e){
            return response()->json([
                'messages' => 'Error al obtener las ventas.',
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            //Validamos los datos que vienen
            $request->validate(
                [
                'metodo_pago_id' => 'required|exists:metodos_pagos,id',
                'items' => 'required|array|min:1',
                'items.*.libro_id' => 'required|exists:libros,id'
                ],
                [
                    'items.required' => 'El carrito no puede estar vacío.',
                    'metodo_pago_id.required' => 'Debes seleccionar un método de pago.'
                ]
            );
            //Capturamos los libros y los convertimos a array
            $librosUsuario = collect($request->items)->pluck('libro_id')->toArray();
            //Validamos que no el usuario ya tiene el libro en su biblioteca
            $libroComprado = UsuarioLibro::where('user_id', auth('api')->user()->id)
                ->whereIn('libro_id', $librosUsuario)
                ->exists();
            if($libroComprado){
                return response()->json([
                    'message' => 'Ya posees uno o más libros de este carrito en tu biblioteca.'
                ],422);
            }
            //Obtenemos los libros del carrito
            $librosCard = Libro::whereIn('id', $librosUsuario)->get();
            $totalPagar = $librosCard->sum('precio_actual');

            //Iniciamos la transaccion
            DB::beginTransaction();
            //Creamos la venta
            $venta = Venta::create([
                'fecha_venta' => now(),
                'total' => $totalPagar,
                'estado_pago' => 'pendiente',
                'token_pasarela' => 'PENDIENTE', 
                'metodo_pago_id' => $request->metodo_pago_id,
                'user_id' => auth('api')->id()
            ]);
            // Llamamos al PaymentController para crear el PaymentIntent en Stripe
            $stripe = new PaymentController();
            $clientSecret = $stripe->crearPaymentIntent($venta);
            //Preparamos las variables para una insercion masiva
            $detallesVentas = [];
            $entregaLibro = [];
            //Recorremos los libros comprados para llenar el detalle venta
            foreach($librosCard as $libro){
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
                    'user_id' => auth('api')->user()->id,
                    'libro_id' => $libro->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            //Insercionmasiva de los datos a la DB
            DetalleVenta::insert($detallesVentas);
            UsuarioLibro::insert($entregaLibro);

            //Si todo salio bien guardar en la base de datos
            DB::commit();
            return response()->json([
                'venta_id' => $venta->id,
                'client_secret' => $clientSecret
            ],201);

        }catch(ValidationException $e){
            return response()->json([
                'message' => 'Error de validacion',
                'errors' => $e->errors()
            ],422);
        }
        catch(\Exception $e){
            //Si falla se revierte la transaccion
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la venta',
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
