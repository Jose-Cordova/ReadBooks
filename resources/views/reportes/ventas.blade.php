<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        @include('reportes.css.pdf')
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <table class="header">
        <tr>
            <td width="20%">
                <img src="{{ public_path('libros/imagenes/logoREADBOOKS2.png') }}" alt="logo" class="logo">
            </td>
            <td width="80%">
                <div class="empresa">ReadBooks</div>
                <div class="titulo">REPORTE DE VENTAS</div>
                <div class="subtitulo">
                    Del {{ $fechaInicio }} al {{ $fechaFin }}
                </div>
            </td>
        </tr>
    </table>

    {{-- TABLA DE VENTAS --}}
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Artículos</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventas as $venta)
                <tr>
                    <td>{{ $venta['cliente'] }}</td>
                    <td>{{ $venta['fecha'] }}</td>
                    <td>{{ ucfirst($venta['estado']) }}</td>
                    <td style="text-align:center;">{{ $venta['articulos'] }}</td>
                    <td style="text-align:right;">${{ number_format($venta['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- RESUMEN FINAL --}}
    <div class="resumen">
        <strong>Total de Ventas:</strong> {{ $totalRegistros }} <br>
        <strong>Monto Total:</strong> ${{ number_format($totalVentas, 2) }}
    </div>

    {{-- PAGINACION --}}
    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $pdf->page_text(500, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 9);
        }
    </script>

</body>
</html>
