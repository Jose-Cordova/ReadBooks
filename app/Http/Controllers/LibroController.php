<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Libro;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;


class LibroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $libros = Libro::with(['autor', 'categoria'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($libros, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los libros.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                'titulo' => 'required|string|min:3|max:100|unique:libros,titulo',
                'descripcion' => 'required|string',
                'precio_actual' => 'required|numeric|min:0',
                'imagen' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
                'archivo'=> 'required|file|mimes:pdf|max:20480',
                'categoria_id' => 'required|exists:categorias,id',
                'autor_id' => 'required|exists:autores,id'
                ],
                [
                    'titulo.unique' => 'Ya existe un libro con este título en el catálogo.'
                ]);
            //guardamos imagenes
            $imagen = $request->file('imagen');
            $nombreImagen = time(). '_'. $imagen->getClientOriginalName();
            $imagen->move(public_path('libros/imagenes'), $nombreImagen);

            //guardamos PDF
            $archivo = $request->file('archivo');
            $nombreArchivo = time(). '_'.$archivo->getClientOriginalName();
            $archivo->move(public_path('libros/pdfs_libros'), $nombreArchivo);

            $libro = Libro::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'precio_actual' => $request->precio_actual,
                'url_imagen'=> 'libros/imagenes/' . $nombreImagen,
                'url_archivo' => 'libros/pdfs_libros/' . $nombreArchivo,
                'categoria_id' => $request->categoria_id,
                'autor_id' => $request->autor_id
            ]);

            return response()->json([
                'message' => 'Libro creado correctamente.',
                'libro' => $libro
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'error' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno en el servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // buscar el libro por su id
            $libro = Libro::with(['autor', 'categoria'])->findOrFail($id);

            // si el libro existe con ese id
            return response()->json($libro, 200);

        } catch (ModelNotFoundException $e) {
            // si el libro no existe con ese id
            return response()->json([
                'message' => 'No existe libro con el ID ' . $id
            ], 404);
        } catch (\Exception $e) {
            // Si ocurre un error inesperado
            return response()->json([
                'message' => 'Error al obtener el libro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            // Primero buscamos el libro por el id
            $libro = Libro::findOrFail($id);

            // Validamos los datos nuevos
            $request->validate(
                [
                'titulo' => 'required|string|min:3|max:100|unique:libros,titulo,' . $id,
                'precio_actual' => 'required|numeric|min:0',
                'imagen'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
                'archivo'       => 'nullable|file|mimes:pdf|max:20480',
                'categoria_id' => 'required|exists:categorias,id',
                'autor_id' => 'required|exists:autores,id',
                ],
                [
                    'titulo.unique' => 'Este título ya está siendo usado por otro libro.'
                ]);

            //si se manda una nueva imagen se remplaza la anterior
            if ($request->hasFile('imagen')){
                $rutaImagenAnterior = public_path($libro->url_imagen);
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $imagen->move(public_path('libros/imagenes'), $nombreImagen);
                $libro->url_imagen = 'libros/imagenes/' . $nombreImagen;
            }

            //si manda nuevo PDF, se remplaza el anterior PDF
            if ($request->hasFile('archivo')) {
                $rutaArchivoAnterior = public_path($libro->url_archivo);
                if (file_exists($rutaArchivoAnterior)) {
                    unlink($rutaArchivoAnterior);
                }
                $archivo = $request->file('archivo');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('libros/pdfs_libros'), $nombreArchivo);
                $libro->url_archivo = 'libros/pdfs_libros/' . $nombreArchivo;
            }
            $libro->titulo        = $request->titulo;
            $libro->descripcion   = $request->descripcion;
            $libro->precio_actual = $request->precio_actual;
            $libro->categoria_id  = $request->categoria_id;
            $libro->autor_id      = $request->autor_id;
            $libro->save();


            // Si todo sale bien:
            // Se actualiza el libro en la base de datos
            return response()->json([
                'message' => 'Libro actualizado correctamente.',
                'libro' => $libro
            ], 200);

        } catch (ModelNotFoundException $e) {

            // Si el libro no existe
            return response()->json([
                'message' => 'Libro no encontrado con ID' .$id
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            // Buscamos el libro
            $libro = Libro::findOrFail($id);
            // Verificar si el libro tiene ventas
            $tieneVentas = DB::table('detalle_ventas')
                ->where('libro_id', $id)
                ->exists();

            if ($tieneVentas) {
                return response()->json([
                    'message' => 'Este libro no puede eliminarse porque ya ha sido vendido a uno o más usuarios.'
                ], 409);
            }
            // Verificar si hay usuarios que lo tienen en su biblioteca
            $tieneUsuarios = DB::table('usuarios_libros')
                ->where('libro_id', $id)
                ->exists();

            if ($tieneUsuarios) {
                return response()->json([
                    'message' => 'Este libro no puede eliminarse porque hay usuarios que lo tienen en su biblioteca.'
                ], 409);
            }
            DB::beginTransaction();
            // Eliminar imagen física
            $rutaImagen = public_path($libro->url_imagen);
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }

            // Eliminar PDF físico
            $rutaArchivo = public_path($libro->url_archivo);
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            $libro->delete();
            DB::commit();

            // Si todo sale bien:
            // El libro se elimina correctamente
            return response()->json([
                'message' => 'Libro eliminado correctamente.'
            ], 200);

        } catch (ModelNotFoundException $e) {

            // Si el libro no existe
            return response()->json([
                'message' => 'Libro no encontrado con el ID'. $id
            ], 404);

        } catch (\Exception $e) {

            // Error inesperado del servidor
            return response()->json([
                'message' => 'Error interno en el servidor.'
            ], 500);
        }
    }
}
