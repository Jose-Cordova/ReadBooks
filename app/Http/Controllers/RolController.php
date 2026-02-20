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
                'message' => 'Error al obtenber los roles',
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
            $request->validate(
                [
                    'nombre' => 'required|string|min:3|max:90|unique:roles,nombre'
                ],
                [
                    'nombre.unique' => 'Ya existe un rol con este nombre.'
                ]
            );
            $rol = Rol::create([
                'nombre' => $request->nombre
            ]);
            return response()->json([
                'message' => 'Rol  registrado correctamente.',
                'rol' => $rol
            ],200);
        }
        catch(ValidationException $e){

        }
        catch(\Exception $e){

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
                'message' => 'Rol no encontrado con el ID = ' .$id
            ],404);
        }
        //En caso de que haya error
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener el rol',
                'error' => $e->getMessage()
            ],500);
        }
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
