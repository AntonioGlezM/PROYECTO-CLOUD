@extends('layouts.app')
@section('titulo', 'Proyectos')

@section('contenido')
    <h1>Proyectos</h1>
    <a class="boton" href="{{ route('proyectos.create') }}">Nuevo proyecto</a>

    <table>
        <thead>
        <tr><th>Proyecto</th><th>Cliente</th><th>Región preferida</th>
            <th class="derecha">Máximo mensual</th><th>Requisitos</th><th>Selección</th><th>Presupuestos</th></tr>
        </thead>
        <tbody>
        @forelse ($proyectos as $proyecto)
            <tr>
                <td><a href="{{ route('proyectos.show', $proyecto) }}">{{ $proyecto->nombre }}</a></td>
                <td>{{ $proyecto->cliente }}</td>
                <td>{{ $proyecto->regionPreferida?->nombre ?? '—' }}</td>
                <td class="derecha"><x-importe :centimos="$proyecto->presupuesto_max_centimos" /></td>
                <td>{{ $proyecto->requerimientos_count }}</td>
                <td>{{ $proyecto->lineas_carrito_count }}</td>
                <td>{{ $proyecto->presupuestos_count }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Todavía no hay proyectos.</td></tr>
        @endforelse
        </tbody>
    </table>

    <nav class="paginas">{{ $proyectos->links() }}</nav>
@endsection
