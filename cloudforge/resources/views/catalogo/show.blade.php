@extends('layouts.app')
@section('titulo', $producto->nombre)

@section('contenido')
    <h1>{{ $producto->nombre }}</h1>
    <p class="pie"><code>{{ $producto->sku }}</code> · {{ $producto->proveedor->nombre }}
       · {{ $producto->tipo->nombre }}</p>

    <div class="tarjeta">
        <p>{{ $producto->descripcion }}</p>
        <table>
            <tr><th style="width:35%">Dato</th><th>Valor</th></tr>
            <tr><td>Modalidad de tarifa</td><td>{{ $producto->modalidad->etiqueta() }}</td></tr>
            <tr><td>Tarifa base</td>
                <td><x-importe :centimos="$producto->tarifa_centimos" /> por {{ $producto->unidad }}</td></tr>
            @if ($producto->tope_mensual_centimos)
                <tr><td>Tope mensual</td>
                    <td><x-importe :centimos="$producto->tope_mensual_centimos" sufijo="por unidad y mes" /></td></tr>
            @endif
            <tr><td>Estado</td>
                <td>{{ $producto->activo ? 'Activo' : 'Retirado del catálogo' }}</td></tr>
        </table>
    </div>

    <h2>Disponibilidad por región</h2>
    <table>
        <tr><th>Región</th><th>País</th><th>¿Disponible?</th><th class="derecha">Tarifa aplicada</th></tr>
        @foreach ($producto->regiones as $region)
            <tr>
                <td>{{ $region->nombre }} <code>{{ $region->codigo }}</code></td>
                <td>{{ $region->pais }}</td>
                <td>{{ $region->pivot->disponible ? 'Sí' : 'No' }}</td>
                <td class="derecha">
                    <x-importe :centimos="$region->pivot->tarifa_centimos ?? $producto->tarifa_centimos" />
                    @unless ($region->pivot->tarifa_centimos)<span class="pie">(base)</span>@endunless
                </td>
            </tr>
        @endforeach
    </table>

    <a class="boton" href="{{ route('catalogo.edit', $producto) }}">Editar</a>
    <form method="POST" action="{{ route('catalogo.destroy', $producto) }}" style="display:inline">
        @csrf @method('DELETE')
        <button class="boton rojo" type="submit">Retirar del catálogo</button>
    </form>
    <a class="boton gris" href="{{ route('catalogo.index') }}">Volver</a>
@endsection
