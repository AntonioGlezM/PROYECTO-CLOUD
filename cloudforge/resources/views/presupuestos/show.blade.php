@extends('layouts.app')
@section('titulo', 'Presupuesto ' . $presupuesto->numero)

@section('contenido')
    <h1>Presupuesto {{ $presupuesto->numero }}</h1>
    <p class="pie">
        Proyecto <a href="{{ route('proyectos.show', $presupuesto->proyecto) }}">{{ $presupuesto->proyecto->nombre }}</a>
        · emitido el {{ $presupuesto->emitido_en->format('d/m/Y \a \l\a\s H:i') }}
    </p>

    <div class="aviso amarillo">
        <strong>Documento histórico.</strong>
        Las líneas que siguen guardan el nombre, el proveedor, la región y la tarifa
        que tenía cada producto en el momento de emitir. Si el catálogo cambia después,
        este presupuesto sigue mostrando exactamente lo mismo.
    </div>

    @if ($presupuesto->avisos)
        <h2>Avisos registrados al emitir</h2>
        @foreach ($presupuesto->avisos as $aviso)
            <p class="aviso amarillo"><strong>{{ $aviso['regla'] }}</strong> {{ $aviso['mensaje'] }}</p>
        @endforeach
    @endif

    <h2>Líneas</h2>
    <table>
        <thead>
        <tr><th>Producto</th><th>Tipo</th><th>Proveedor</th><th>Región</th>
            <th>Modalidad</th><th class="derecha">Tarifa</th><th class="derecha">Cantidad</th>
            <th class="derecha">Importe</th></tr>
        </thead>
        <tbody>
        @foreach ($presupuesto->lineas as $linea)
            <tr>
                <td>{{ $linea->producto_nombre }}<br><span class="pie">{{ $linea->producto_sku }}</span></td>
                <td>{{ $linea->tipo_nombre }}</td>
                <td>{{ $linea->proveedor_nombre }}</td>
                <td>{{ $linea->region_nombre }}</td>
                <td>{{ $linea->modalidad->etiqueta() }}
                    @if ($linea->consumo_estimado)
                        <br><span class="pie">{{ $linea->consumo_estimado }} {{ $linea->unidad }}</span>
                    @endif
                </td>
                <td class="derecha"><x-importe :centimos="$linea->tarifa_centimos" /></td>
                <td class="derecha">{{ $linea->cantidad }}</td>
                <td class="derecha">
                    <x-importe :centimos="$linea->importe_centimos" />
                    <br><span class="pie">{{ $linea->recurrente ? 'al mes' : 'pago único' }}</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="tarjeta">
        <table>
            <tr><td><strong>Total mensual recurrente</strong></td>
                <td class="derecha"><strong><x-importe :centimos="$presupuesto->total_mensual_centimos" /></strong></td></tr>
            <tr><td>Pagos únicos</td>
                <td class="derecha"><x-importe :centimos="$presupuesto->total_unico_centimos" /></td></tr>
            <tr><td>Coste del primer mes</td>
                <td class="derecha"><x-importe :centimos="$presupuesto->totalPrimerMesCentimos()" /></td></tr>
        </table>
    </div>

    <a class="boton gris" href="{{ route('presupuestos.index') }}">Volver</a>
@endsection
