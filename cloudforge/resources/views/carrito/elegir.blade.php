@extends('layouts.app')
@section('titulo', 'Añadir servicio')

@section('contenido')
    <h1>Añadir servicio a «{{ $proyecto->nombre }}»</h1>

    {{--
        Formulario dinámico en tres pasos, todos resueltos en el servidor.
        No hay una línea de JavaScript: cada paso es un GET que devuelve
        una vista con los campos que corresponden a lo elegido antes.
    --}}

    <form method="GET" action="{{ route('carrito.elegir', $proyecto) }}" class="tarjeta">
        <label for="tipo_producto_id">Paso 1 · Tipo de servicio</label>
        <select id="tipo_producto_id" name="tipo_producto_id">
            <option value="">— Elegir —</option>
            @foreach ($tipos as $t)
                <option value="{{ $t->id }}" @selected($tipo?->id === $t->id)>{{ $t->nombre }}</option>
            @endforeach
        </select>
        <button class="boton" type="submit">Continuar</button>
    </form>

    @if ($tipo)
        <form method="GET" action="{{ route('carrito.elegir', $proyecto) }}" class="tarjeta">
            <input type="hidden" name="tipo_producto_id" value="{{ $tipo->id }}">
            <label for="producto_id">Paso 2 · Producto de tipo «{{ $tipo->nombre }}»</label>
            <select id="producto_id" name="producto_id">
                <option value="">— Elegir —</option>
                @foreach ($productos as $p)
                    <option value="{{ $p->id }}" @selected($producto?->id === $p->id)>
                        {{ $p->nombre }} · {{ $p->proveedor->nombre }} ·
                        {{ \App\Services\CalculadoraPrecios::euros($p->tarifa_centimos) }}/{{ $p->unidad }}
                    </option>
                @endforeach
            </select>
            <button class="boton" type="submit">Continuar</button>
        </form>
    @endif

    @if ($producto)
        <form method="POST" action="{{ route('carrito.store', $proyecto) }}" class="tarjeta">
            @csrf
            <input type="hidden" name="producto_id" value="{{ $producto->id }}">

            <p><strong>Paso 3 · {{ $producto->nombre }}</strong>
               <span class="etiqueta">{{ $producto->modalidad->etiqueta() }}</span></p>

            @if ($regiones->isEmpty())
                <p class="aviso mal">Este producto no está disponible en ninguna región ahora mismo.</p>
            @else
                <div class="fila">
                    <div>
                        <label for="region_id">Región</label>
                        <select id="region_id" name="region_id">
                            @foreach ($regiones as $region)
                                <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>
                                    {{ $region->nombre }} ({{ $region->codigo }}) —
                                    {{ \App\Services\CalculadoraPrecios::euros($region->pivot->tarifa_centimos ?? $producto->tarifa_centimos) }}
                                </option>
                            @endforeach
                        </select>
                        @error('region_id') <p class="error-campo">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cantidad">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" max="500"
                               value="{{ old('cantidad', 1) }}">
                        @error('cantidad') <p class="error-campo">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Campos distintos según la modalidad y el tipo del producto --}}
                @if ($producto->modalidad === \App\Enums\Modalidad::Consumo)
                    <label for="consumo_estimado">Consumo estimado al mes ({{ $producto->unidad }})</label>
                    <input type="number" id="consumo_estimado" name="consumo_estimado" min="0"
                           value="{{ old('consumo_estimado') }}">
                    <p class="pie">Tope mensual del producto:
                        {{ $producto->tope_mensual_centimos
                            ? \App\Services\CalculadoraPrecios::euros($producto->tope_mensual_centimos)
                            : 'sin tope' }}</p>
                    @error('consumo_estimado') <p class="error-campo">{{ $message }}</p> @enderror
                @endif

                @if ($producto->tipo->codigo === 'computo')
                    <div class="fila">
                        <div>
                            <label for="vcpu">vCPU por unidad</label>
                            <input type="number" id="vcpu" name="vcpu" min="0" max="256" value="{{ old('vcpu') }}">
                        </div>
                        <div>
                            <label for="ram_gb">RAM por unidad (GB)</label>
                            <input type="number" id="ram_gb" name="ram_gb" min="0" max="4096" value="{{ old('ram_gb') }}">
                        </div>
                    </div>
                @elseif (in_array($producto->tipo->codigo, ['almacenamiento', 'bbdd', 'backup']))
                    <label for="almacenamiento_gb">Capacidad por unidad (GB)</label>
                    <input type="number" id="almacenamiento_gb" name="almacenamiento_gb" min="0" max="100000"
                           value="{{ old('almacenamiento_gb') }}">
                @else
                    <p class="pie">Este tipo de servicio no necesita configuración adicional.</p>
                @endif

                <button class="boton" type="submit">Añadir a la selección</button>
            @endif

            <a class="boton gris" href="{{ route('proyectos.show', $proyecto) }}">Volver al proyecto</a>
        </form>
    @endif
@endsection
