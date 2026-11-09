@extends('layouts.app')
@section('titulo', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@section('contenido')
    <h1>{{ $producto->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>

    <form method="POST" class="tarjeta"
          action="{{ $producto->exists ? route('catalogo.update', $producto) : route('catalogo.store') }}">
        @csrf
        @if ($producto->exists) @method('PATCH') @endif

        <div class="fila">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}">
                @error('nombre') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="sku">SKU</label>
                <input type="text" id="sku" name="sku" value="{{ old('sku', $producto->sku) }}">
                @error('sku') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="fila">
            <div>
                <label for="proveedor_id">Proveedor</label>
                <select id="proveedor_id" name="proveedor_id">
                    @foreach ($proveedores as $p)
                        <option value="{{ $p->id }}" @selected(old('proveedor_id', $producto->proveedor_id) == $p->id)>
                            {{ $p->nombre }}</option>
                    @endforeach
                </select>
                @error('proveedor_id') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tipo_producto_id">Tipo</label>
                <select id="tipo_producto_id" name="tipo_producto_id">
                    @foreach ($tipos as $t)
                        <option value="{{ $t->id }}" @selected(old('tipo_producto_id', $producto->tipo_producto_id) == $t->id)>
                            {{ $t->nombre }}</option>
                    @endforeach
                </select>
                @error('tipo_producto_id') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
        </div>

        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $producto->descripcion) }}</textarea>

        <div class="fila">
            <div>
                <label for="modalidad">Modalidad</label>
                <select id="modalidad" name="modalidad">
                    @foreach (\App\Enums\Modalidad::cases() as $m)
                        <option value="{{ $m->value }}"
                            @selected(old('modalidad', $producto->modalidad?->value) === $m->value)>
                            {{ $m->etiqueta() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tarifa">Tarifa en euros</label>
                <input type="number" step="0.01" min="0" id="tarifa" name="tarifa"
                       value="{{ old('tarifa', $producto->tarifa_centimos ? $producto->tarifa_centimos / 100 : '') }}">
                @error('tarifa') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="unidad">Unidad</label>
                <input type="text" id="unidad" name="unidad" value="{{ old('unidad', $producto->unidad) }}"
                       placeholder="instancia/mes, GB...">
                @error('unidad') <p class="error-campo">{{ $message }}</p> @enderror
            </div>
        </div>

        <label for="tope_mensual">Tope mensual en euros <span class="pie">(sólo modalidad por consumo)</span></label>
        <input type="number" step="0.01" min="0" id="tope_mensual" name="tope_mensual"
               value="{{ old('tope_mensual', $producto->tope_mensual_centimos ? $producto->tope_mensual_centimos / 100 : '') }}">
        @error('tope_mensual') <p class="error-campo">{{ $message }}</p> @enderror

        <button class="boton" type="submit">Guardar</button>
        <a class="boton gris" href="{{ route('catalogo.index') }}">Cancelar</a>
    </form>
@endsection
