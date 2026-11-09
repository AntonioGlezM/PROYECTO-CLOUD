@extends('layouts.app')
@section('titulo', 'CloudForge · Inicio')

@section('contenido')
    <h1>CloudForge</h1>
    <p>Plataforma para diseñar, presupuestar y contratar infraestructura Cloud simulada.
       Esta versión corresponde a la fase UT2: catálogo, proyectos, requisitos,
       arquitecturas manuales, precios, reglas, selección y presupuestos.</p>

    <div class="fila">
        <div class="tarjeta">
            <h2>{{ $totalProductos }}</h2>
            <p class="pie">productos activos en el catálogo</p>
            <a class="boton" href="{{ route('catalogo.index') }}">Ver catálogo</a>
        </div>
        <div class="tarjeta">
            <h2>{{ $totalProyectos }}</h2>
            <p class="pie">proyectos registrados</p>
            <a class="boton" href="{{ route('proyectos.index') }}">Ver proyectos</a>
        </div>
        <div class="tarjeta">
            <h2>{{ $totalPresupuestos }}</h2>
            <p class="pie">presupuestos emitidos</p>
            <a class="boton" href="{{ route('presupuestos.index') }}">Ver presupuestos</a>
        </div>
    </div>

    <h2>Últimos proyectos</h2>
    @forelse ($ultimosProyectos as $proyecto)
        <div class="tarjeta">
            <strong><a href="{{ route('proyectos.show', $proyecto) }}">{{ $proyecto->nombre }}</a></strong>
            <span class="etiqueta">{{ $proyecto->requerimientos_count }} requisitos</span>
            <span class="etiqueta">{{ $proyecto->presupuestos_count }} presupuestos</span>
            <p class="pie">{{ $proyecto->cliente }} · máximo
                <x-importe :centimos="$proyecto->presupuesto_max_centimos" sufijo="al mes" /></p>
        </div>
    @empty
        <p>Todavía no hay proyectos. <a href="{{ route('proyectos.create') }}">Crea el primero</a>.</p>
    @endforelse
@endsection
