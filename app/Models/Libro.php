<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'precio_actual',
        'url_imagen',
        'url_archivo',
        'categoria_id',
        'autor_id'
    ];

    protected $casts = [
        'precio_actual' => 'decimal:2'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function autor()
    {
        return $this->belongsTo(Autor::class);
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function usuariosLibros()
    {
        return $this->hasMany(UsuarioLibro::class);
    }
}
