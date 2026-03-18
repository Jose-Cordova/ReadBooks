<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
class ReporteController extends Controller
{
    public function reporteVentas(Request $request)
{
    $request->validate([
        'fecha_inicio' => 'nullable|date',
        'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio'
    ]);

    $ventas = Venta::with(['user', 'detalleVentas'])
        ->when($request->fecha_inicio, function ($query) use ($request) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_inicio);
        })
        ->when($request->fecha_fin, function ($query) use ($request) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_fin);
        })
        ->get()
        ->map(function ($venta) {
            return [
                'cliente'   => $venta->user->name,
                'fecha'     => $venta->fecha_venta->format('d/m/Y'),
                'estado'    => $venta->estado_pago,
                'articulos' => $venta->detalleVentas->count(),
                'total'     => $venta->total
            ];
        });

    $totalVentas  = $ventas->sum('total');
    $totalRegistros = $ventas->count();
    $fechaInicio  = $request->fecha_inicio ?? 'Inicio';
    $fechaFin     = $request->fecha_fin    ?? 'Hoy';

    $pdf = Pdf::loadView('reportes.ventas', compact(
        'ventas', 'totalVentas', 'totalRegistros', 'fechaInicio', 'fechaFin'
    ))->setPaper('a4', 'portrait');

    return $pdf->stream('reporte_ventas.pdf');
}
}
