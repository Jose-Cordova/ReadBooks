<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Inportamos validaciones
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//Inportamos el modelo
use App\Models\MetodoPago;

class MetodoPagoContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            //Listamos los metodos de pago y los retornamos
            $metodosPagos = MetodoPago::orderBy('id', 'desc')->get();
            return response()->json($metodosPagos, 200);
        }
        //En caso de que haya error
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener los metodos de pagos.'
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            //Validamos que el dato ingresado se correcto y que no se repita
            $request->validate(
                [
                    'nombre' => 'required|string|min:6|max:90|unique:metodos_pagos,nombre'
                ],
                [
                    'nombre.unique' => 'Ya existe un metodo de pago con ese nombre.' 
                ]
            );
            //Creamos el metodo de pago y lo retornamos
            $metodoPago = MetodoPago::create([
                'nombre' => $request->nombre
            ]);
            return response()->json([
                'message' => 'Metodo de pago creado correctamente.',
                'metodoPago' => $metodoPago
            ],201);
        }
        catch(ValidationException $e){
            return response()->json([
                'message' => 'Error de validacion.',
                'errors' => $e->errors()
            ],422);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error interno en el servidor.'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            //Listamos el metodo de pago y lo retornamos
            $metodoPago = MetodoPago::findOrFail($id);
            return response()->json($metodoPago, 200);
        }
        //En caso de que no exista
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Metodo de pago no encontrado.'
            ],404);
        }
        //En caso de que haya error
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener el método de pago.'
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            //Obtenemos el id
            $metodoPago = MetodoPago::findOrFail($id);
            //Validamos el dato y se ignora el registro actual
            $request->validate(
                [
                    'nombre' => [
                        'required',
                        'string',
                        'min:6',
                        'max:90',
                        Rule::unique('metodos_pagos', 'nombre')->ignore($id)
                    ]
                ],
                [
                    'nombre.unique' => 'Ya existe un metodo de pago con ese nombre.' 
                ]
            );
            //Actualizamos el metodo de pago y retornamos
            $metodoPago->update([
                'nombre' => $request->nombre
            ]);
            return response()->json([
                'message' => 'Metodo de pago actualizado correctamente.',
                'metodoPago' => $metodoPago
            ],200);
        }
        //En caso de que de error a la hora de validar
        catch(ValidationException $e){
            return response()->json([
                'message' => 'Error de validacion.',
                'error' => $e->errors()
            ],422);
        }
        //En caso de que no exista
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Metodo de pago no encontrado.'
            ],404);
        }
        //En caso de un error inesperado
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error interno en el servidor'
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            //Obtenemos el id
            $metodoPago = MetodoPago::findOrFail($id);
            //Validamos que no se tengan ventas asociadas
            if($metodoPago->ventas()->exists()){
                return response()->json([
                    'message' => 'No se puede eliminar el método de pago porque tiene ventas asociadas.'
                ],409);
            }
            //Eliminamos el metodo de pago
            $metodoPago->delete();
            return response()->json([
                'message' => 'El método de pago ha sido eliminado correctamente.'
            ],200);
        }
        //En caso de que no exista el id
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Metodo de pago no encontrado.'
            ],404);
        }
        //En caso de errores inesperados
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error interno en el servidor.'
            ],500);
        }
    }
}
