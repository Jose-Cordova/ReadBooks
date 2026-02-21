<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Se importan las validaciones
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//Se importa el modelo rol
use App\Models\Rol;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            //Listamos todo los roles y los retornamos 
            $roles = Rol::orderBy('id', 'desc')->get();
            return response()->json($roles, 200);
        }
        //En caso de que haya error
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtenber los roles.',
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
            //Validamos que el nombre ingresado sea correcto y no se repita
            $request->validate(
                [
                    'nombre' => 'required|string|min:3|max:90|unique:roles,nombre'
                ],
                [
                    'nombre.unique' => 'Ya existe un rol con este nombre.'
                ]
            );
            //Creamos el rol y lo retornamos
            $rol = Rol::create([
                'nombre' => $request->nombre
            ]);
            return response()->json([
                'message' => 'Rol  registrado correctamente.',
                'rol' => $rol
            ],201);
        }
        //En caso de que haya errores a la hora de validar
        catch(ValidationException $e){
            return response()->json([
                'message' => 'Error de validacion.',
                'error' => $e->errors()
            ],422);
        }
        //En caso de error en el servidor
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
            //Listamos un rol especifico y se retorna
            $rol = Rol::findOrFail($id);
            return response()->json($rol, 200);
        }
        //En caso de que no exista el rol
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Rol no encontrado.'
            ],404);
        }
        //En caso de que haya error
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener el rol.'
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            //Obtenemos el registro de la DB
            $rol = Rol::findOrFail($id);
            //Aplicamos la validacion para obtener datos correctos
            $request->validate(
                [
                    'nombre' => [
                        'required',
                        'string',
                        'min:3',
                        'max:90',
                        //Se verefica el nombre y se ignora el registro actual para evitar que se marque como repetido
                        Rule::unique('roles', 'nombre')->ignore($id)
                    ]
                ],
                [
                    'nombre.unique' => 'Ya existe un rol con este nombre.'
                ]
            );
            //Actualizamos el rol y se retorna
            $rol->update([
                'nombre' => $request->nombre
            ]);
            return response()->json([
                'message' => 'Rol actaulizado correctamente.',
                'rol' => $rol
            ],200);
        }
        //En caso de que no exista el rol
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Rol no encontrado.'
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            //Obtenemos el id junto con users
            $rol = Rol::findOrFail($id);
            //Eliminamos el rol y retornamos
            $rol->delete();
            return response()->json([
                'message' => 'Rol eliminado correctamente.'
            ],200);
        }
        //En caso de que no exista el rol
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Rol no encontrado.'
            ],404);
        }
    }
}
