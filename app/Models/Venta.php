<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'fecha_venta',
        'total',
        'estado_pago',
        'token_pasarela',
        'user_id',
        'metodo_pago_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }
}
