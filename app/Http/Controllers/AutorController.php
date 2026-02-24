<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Autor;

class AutorController extends Controller
{
    //En el index mostramos todos los autores 
    public function index()
{
    try {

        // Aquí consultamos todos los autores de la base de datos
        $autores = Autor::orderBy('id', 'desc')->get();

        // Si todo sale bien:
        // Se devuelve la lista de autores y el sistema mostrará los registros correctamente
        return response()->json($autores, 200);

    } catch (\Exception $e) {

        // Si algo falla:
        // Ejemplo: hay un error en la consulta
        // Entonces se retorna este mensaje de error
        return response()->json([
            'message' => 'Error al obtener los autores.',
            'error' => $e->getMessage()
        ], 500);
    }
}


   public function store(Request $request)
{
    try {

        // Primero validamos los datos que envía el usuario
        $request->validate([
            'nombre_completo' => 'required|string|min:3|max:100', //tendra un minimo de 3 caracteres y un max de 100
            'nacionalidad' => 'required|string|min:3|max:100'
        ]);

        // Si los datos son correctos se crea el autor
        $autor = Autor::create([
            'nombre_completo' => $request->nombre_completo,
            'nacionalidad' => $request->nacionalidad
        ]);

        // Si todo sale bien:
        // El autor se guarda en la base de datos
        return response()->json([
            'message' => 'Autor registrado correctamente.',
            'autor' => $autor
        ], 201);

    } catch (ValidationException $e) {

        // Si la validación falla:
        // Ejemplo:
        // el nombre está vacío
        // el usuario envió números en lugar de texto
        // el campo no cumple las reglas
        return response()->json([
            'message' => 'Error de validación.',
            'error' => $e->errors()
        ], 422);

    } catch (\Exception $e) {

        // Si ocurre otro error inesperado:
        // Ejemplo: error del servidor o problema con la base de datos
        return response()->json([
            'message' => 'Error interno en el servidor.'
        ], 500);
    }
}

    public function show(string $id)
{
    try {

        // Buscamos el autor por su ID
        $autor = Autor::findOrFail($id);

        // Si el autor existe:
        // Se mostrará la información del autor
        return response()->json($autor, 200);

    } catch (ModelNotFoundException $e) {

        // Si el autor no existe:
        // Ejemplo: alguien busca un ID que no está en la base de datos
        return response()->json([
            'message' => 'Autor no encontrado.'
        ], 404);

    } catch (\Exception $e) {

        // Si ocurre un error inesperado
        return response()->json([
            'message' => 'Error al obtener el autor.'
        ], 500);
    }
}


    public function update(Request $request, string $id)
{
    try {

        // Primero buscamos el autor
        $autor = Autor::findOrFail($id);

        // Validamos los datos nuevos
        $request->validate([
            'nombre_completo' => 'required|string|min:3|max:100',
            'nacionalidad' => 'required|string|min:3|max:100'
        ]);

        // Si todo es correcto se actualiza
        $autor->update([
            'nombre_completo' => $request->nombre_completo,
            'nacionalidad' => $request->nacionalidad
        ]);

        // Si todo sale bien:
        // Se actualiza el autor en la base de datos
        return response()->json([
            'message' => 'Autor actualizado correctamente.',
            'autor' => $autor
        ], 200);

    } catch (ModelNotFoundException $e) {

        // Si el autor no existe
        return response()->json([
            'message' => 'Autor no encontrado.'
        ], 404);

    } catch (ValidationException $e) {

        // Si los datos no cumplen las reglas
        return response()->json([
            'message' => 'Error de validación.',
            'error' => $e->errors()
        ], 422);

    } catch (\Exception $e) {

        // Error interno del servidor
        return response()->json([
            'message' => 'Error interno en el servidor.'
        ], 500);
    }
}

    public function destroy(string $id)
{
    try {

        // Buscamos el autor
        $autor = Autor::findOrFail($id);
        // Verificamos si tiene libros asociados
        if ($autor->libros()->exists()) {

            // Si tiene libros, no se puede eliminar
            return response()->json([
                'message' => 'No se puede eliminar el autor porque tiene libros asociados.'
            ], 409);
        }

        // Eliminamos el autor
        $autor->delete();

        // Si todo sale bien:
        // El autor se elimina correctamente
        return response()->json([
            'message' => 'Autor eliminado correctamente.'
        ], 200);

    } catch (ModelNotFoundException $e) {

        // Si el autor no existe
        return response()->json([
            'message' => 'Autor no encontrado.'
        ], 404);

    } catch (\Exception $e) {

        // Error inesperado del servidor
        return response()->json([
            'message' => 'Error interno en el servidor.'
        ], 500);
    }
}
}