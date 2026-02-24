<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categorias = Categoria::orderBy('id', 'desc')->get();
            return response()->json($categorias, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las categorías.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validamos que el nombre sea único y con restricciones
            $request->validate([
                'nombre' => [
                    'required',
                    'string',
                    'min:2',
                    'max:90',
                    'unique:categorias,nombre'
                ]
            ], [
                'nombre.unique' => 'Ya existe una categoría con ese nombre.'
            ]);

            $categoria = Categoria::create([
                'nombre' => $request->nombre
            ]);

            return response()->json([
                'message' => 'Categoría creada correctamente.',
                'categoria' => $categoria
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
      
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno en el servidor.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return response()->json($categoria, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Categoría no encontrada'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            $request->validate([
                'nombre' => [
                    'required',
                    'string',
                    'min:2',
                    'max:90',
                    Rule::unique('categorias', 'nombre')->ignore($id)
                ]
            ]);

            $categoria->update([
                'nombre' => $request->nombre
            ]);

            return response()->json([
                'message' => 'Categoría actualizada',
                'categoria' => $categoria
            ], 202);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno en el servidor.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $categoria = Categoria::with('libros')->findOrFail($id);
            if ($categoria->libros()->exists()) {
                return response()->json([
                    'message' => 'No se puede eliminar la categoría porque tiene libros asociados.'
                ], 409);
            }
            $categoria->delete();

            return response()->json([
                'message' => 'Categoría eliminada correctamente.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Categoría no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno en el servidor.'
            ], 500);
        }
    }
}

