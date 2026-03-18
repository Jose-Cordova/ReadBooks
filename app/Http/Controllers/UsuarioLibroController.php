<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
//Modelos a usar
use App\Models\UsuarioLibro;
use App\Models\Libro;

class UsuarioLibroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            //Listamos los libros comprados
            $libros = UsuarioLibro::with('libro.autor')->where('user_id', auth('api')->user()->id)->get()
            ->map(function($usuarioLibro){
                return [
                    'id' => $usuarioLibro->libro->id,
                    'titulo' => $usuarioLibro->libro->titulo,
                    'autor' => $usuarioLibro->libro->autor->nombre_completo,
                    'progreso' => $usuarioLibro->porcentaje_leido,
                    'estado' => $usuarioLibro->estado,
                    'url_imagen' => $usuarioLibro->libro->url_imagen
                ];
            });
            //Retornamos los libros
            return response()->json([
                'libros_comprados' => $libros
            ],200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener los libros comprados.'
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            //Buscar el libro en la biblioteca del usuario
            $libro_usuario = UsuarioLibro::with('libro')
            ->where('user_id', auth('api')->user()->id)
            ->where('libro_id', $id)
            ->firstOrFail();
            //Cambiamos el estado del libro a leyendo
            if($libro_usuario->estado === 'pendiente'){
                $libro_usuario->update([
                    'estado' => 'leyendo'
                ]);
            }
            //Retorna para el apartado de lectura
            return response()->json([
                'titulo' => $libro_usuario->libro->titulo,
                'autor' => $libro_usuario->libro->autor->nombre_completo,
                'url_archivo' => $libro_usuario->libro->url_archivo,
                'pagina_actual' => $libro_usuario->pagina_actual,
                'estado' => $libro_usuario->estado
            ],200);
        }
        //Por si no existe el libro
        catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Libro no encontrado para leer.'
            ],404);
        }
        //En caso de que no se pueda abrir el libro
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error al abrir el archivo'
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            //Obtenemos el id del libro
            $libro_usuario = UsuarioLibro::with('libro')
            ->where('user_id', auth('api')->user()->id)
            ->where('libro_id', $id)
            ->firstOrFail();

            //Validamos los datos
            $request->validate(
                [
                    'pagina_actual' => 'required|integer|min:0',
                    'porcentaje_leido' => 'required|numeric|min:0|max:100'
                ]
            );
            //Si finalizo el libro
            $estado = $request->porcentaje_leido >= 100 ? 'terminado' : 'leyendo';
            //Actualizamos la pagina y porcentaje
            $libro_usuario->update([
                'pagina_actual' => $request->pagina_actual,
                'porcentaje_leido' => $request->porcentaje_leido,
                'estado' => $estado
            ]);
            //Retornamos la actualizacion
            return response()->json([
                'pagina_actual' => $request->pagina_actual,
                'porcentaje_leido' => $request->porcentaje_leido,
                'estado' => $estado
            ],200);

        }
        //En caso de que haya error en la validacion
        catch(ValidationException $e){
            return response()->json([
                'message' => 'Error de validacion.',
                'errors' => $e->errors()
            ],422);
        }
        //En caso de qeu haya error en el servidor
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
        //
    }
}
