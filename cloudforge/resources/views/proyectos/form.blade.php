@extends('layouts.app')
@section('titulo', $proyecto->exists ? 'Editar proyecto' : 'Nuevo proyecto')

@section('contenido')
    <h1>{{ $proyecto->exists ? 'Editar proyecto' : 'Nuevo proyecto' }}</h1>

    <form method="POST" class="tarjeta"
          action="{{ $proyecto->exists ? route('proyectos.update', $proyecto) : route('proyectos.store') }}">
        @csrf
        @if ($proyecto->exists) @method('PATCH') @endif

        <div class="fila">
            <div>
                <label for="nombre">Nombre del proyecto</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $proyecto->nombre) }}">
                @error('nombre') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="cliente">Cliente</label>
                <input type="text" id="cliente" name="cliente" value="{{ old('cliente', $proyecto->cliente) }}">
                @error('cliente') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
        </div>

        <label for="descripcion">Necesidad del cliente</label>
        <textarea id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $proyecto->descripcion) }}</textarea>
        @error('descripcion') <p class="error-campo">{{ $message }}</p> @enderror

        <div class="fila">
            <div>
                <label for="presupuesto_max">Presupuesto máximo mensual (€)</label>
                <input type="number" step="0.01" min="1" id="presupuesto_max" name="presupuesto_max"
                       value="{{ old('presupuesto_max', $proyecto->presupuesto_max_centimos ? $proyecto->presupuesto_max_centimos / 100 : '') }}">
                @error('presupuesto_max') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="region_preferida_id">Región preferida</label>
                <select id="region_preferida_id" name="region_preferida_id">
                    <option value="">Sin preferencia</option>
                    @foreach ($regiones as $r)
                        <option value="{{ $r->id }}" @selected(old('region_preferida_id', $proyecto->region_preferida_id) == $r->id)>
                            {{ $r->nombre }} ({{ $r->codigo }}) · {{ $r->proveedor->nombre }}</option>
                    @endforeach
                </select>
                @error('region_preferida_id') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
        </div>

        <button class="boton" type="submit">Guardar</button>
        <a class="boton gris" href="{{ route('proyectos.index') }}">Cancelar</a>
    </form>
@endsection
