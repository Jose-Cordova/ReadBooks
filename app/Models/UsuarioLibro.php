<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioLibro extends Model
{
    protected $table = "usuarios_libros";

    protected $fillable = [
        'pagina_actual',
        'porcentaje_leido',
        'estado',
        'usuario_id',
        'libro_id'
    ];

    protected $casts = [
        'pagina_actual' => 'integer',
        'porcentaje_leido' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }
}
