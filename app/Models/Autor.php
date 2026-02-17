<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    protected $table = 'autores';

    protected $fillable = [
        'nombre_completo',
        'nacionalidad'
    ];
    public function libros()
    {
        return $this->hasMany(Libro::Class);
    }
}
