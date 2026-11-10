@extends('layouts.app')
@section('titulo', 'Presupuestos')

@section('contenido')
    <h1>Presupuestos emitidos</h1>
    <p class="pie">Un presupuesto emitido no cambia aunque después se modifique el catálogo.</p>

    <table>
        <thead>
        <tr><th>Número</th><th>Proyecto</th><th>Emitido</th><th>Líneas</th>
            <th class="derecha">Mensual</th><th class="derecha">Pago único</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($presupuestos as $presupuesto)
            <tr>
                <td><code>{{ $presupuesto->numero }}</code></td>
                <td>{{ $presupuesto->proyecto->nombre }}</td>
                <td>{{ $presupuesto->emitido_en->format('d/m/Y H:i') }}</td>
                <td>{{ $presupuesto->lineas_count }}</td>
                <td class="derecha"><x-importe :centimos="$presupuesto->total_mensual_centimos" /></td>
                <td class="derecha"><x-importe :centimos="$presupuesto->total_unico_centimos" /></td>
                <td><a href="{{ route('presupuestos.show', $presupuesto) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="7">Todavía no se ha emitido ningún presupuesto.</td></tr>
        @endforelse
        </tbody>
    </table>

    <nav class="paginas">{{ $presupuestos->links() }}</nav>
@endsection
