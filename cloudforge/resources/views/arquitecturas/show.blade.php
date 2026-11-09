@extends('layouts.app')
@section('titulo', $arquitectura->nombre)

@section('contenido')
    <h1>{{ $arquitectura->nombre }}</h1>
    <p class="pie">
        Proyecto <a href="{{ route('proyectos.show', $proyecto) }}">{{ $proyecto->nombre }}</a>
        · versión {{ $arquitectura->version }}
        <span class="etiqueta">{{ strtoupper($arquitectura->estado->value) }}</span>
    </p>

    @if ($arquitectura->motivo_rechazo)
        <p class="aviso mal"><strong>Motivo del rechazo:</strong> {{ $arquitectura->motivo_rechazo }}</p>
    @endif

    <h2>Elementos</h2>
    <table>
        <thead>
        <tr><th>Producto</th><th>Tipo</th><th>Proveedor</th><th>Región</th>
            <th class="derecha">Cantidad</th><th>Configuración</th></tr>
        </thead>
        <tbody>
        @foreach ($arquitectura->elementos as $elemento)
            <tr>
                <td>{{ $elemento->producto->nombre }}</td>
                <td>{{ $elemento->producto->tipo->nombre }}</td>
                <td>{{ $elemento->producto->proveedor->nombre }}</td>
                <td><code>{{ $elemento->region->codigo }}</code></td>
                <td class="derecha">{{ $elemento->cantidad }}</td>
                <td class="pie">
                    @forelse ($elemento->configuracion ?? [] as $campo => $valor)
                        {{ $campo }}: {{ $valor }}<br>
                    @empty — @endforelse
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Comprobación de reglas</h2>
    <x-hallazgos :hallazgos="$hallazgos" />

    <a class="boton gris" href="{{ route('proyectos.show', $proyecto) }}">Volver al proyecto</a>
@endsection
