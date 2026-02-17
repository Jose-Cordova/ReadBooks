<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = "detalle_ventas";

    protected $fillable = [
        'precio_unitario',
        'subtotal',
        'libro_id',
        'venta_id'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
