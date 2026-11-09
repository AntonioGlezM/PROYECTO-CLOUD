@extends('layouts.app')
@section('titulo', $proyecto->nombre)

@section('contenido')
    <h1>{{ $proyecto->nombre }}</h1>
    <p class="pie">{{ $proyecto->cliente }} · máximo
        <x-importe :centimos="$proyecto->presupuesto_max_centimos" sufijo="al mes" />
        @if ($proyecto->regionPreferida) · región preferida {{ $proyecto->regionPreferida->nombre }} @endif
    </p>

    @if ($proyecto->descripcion)
        <div class="tarjeta">{{ $proyecto->descripcion }}</div>
    @endif

    <a class="boton gris" href="{{ route('proyectos.edit', $proyecto) }}">Editar proyecto</a>
    <form method="POST" action="{{ route('proyectos.destroy', $proyecto) }}" style="display:inline">
        @csrf @method('DELETE')
        <button class="boton rojo" type="submit">Borrar proyecto</button>
    </form>

    {{-- ------------------------------------------------------ requisitos --}}
    <h2>Requisitos del cliente</h2>
    <table>
        <thead>
        <tr><th>Requisito</th><th>Tipo</th><th>¿Obligatorio?</th><th>Origen</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($proyecto->requerimientos as $requisito)
            <tr>
                <td>{{ $requisito->descripcion() }}</td>
                <td>{{ $requisito->tipo->value }}</td>
                <td>{{ $requisito->obligatorio ? 'Obligatorio' : 'Preferencia' }}</td>
                <td>{{ $requisito->origen }}</td>
                <td>
                    <form method="POST" action="{{ route('requerimientos.destroy', [$proyecto, $requisito]) }}">
                        @csrf @method('DELETE')
                        <button class="boton rojo" style="margin:0;padding:.2rem .5rem" type="submit">Quitar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Sin requisitos todavía.</td></tr>
        @endforelse
        </tbody>
    </table>
    <a class="boton" href="{{ route('requerimientos.create', $proyecto) }}">Añadir requisito</a>

    {{-- --------------------------------------------------------- carrito --}}
    <h2>Selección actual</h2>
    @if ($proyecto->lineasCarrito->isEmpty())
        <p>No hay nada seleccionado.</p>
    @else
        <table>
            <thead>
            <tr><th>Producto</th><th>Tipo</th><th>Región</th><th>Cantidad</th>
                <th>Consumo</th><th>Configuración</th><th></th></tr>
            </thead>
            <tbody>
            @foreach ($proyecto->lineasCarrito as $linea)
                <tr>
                    <td>{{ $linea->producto->nombre }}</td>
                    <td>{{ $linea->producto->tipo->nombre }}</td>
                    <td><code>{{ $linea->region->codigo }}</code></td>
                    <td>
                        <form method="POST" action="{{ route('carrito.update', [$proyecto, $linea->id]) }}">
                            @csrf @method('PATCH')
                            <input type="number" name="cantidad" min="1" max="500"
                                   value="{{ $linea->cantidad }}" style="width:5rem">
                            <input type="hidden" name="consumo_estimado" value="{{ $linea->consumo_estimado }}">
                            <button class="boton" style="margin:0;padding:.2rem .5rem" type="submit">Cambiar</button>
                        </form>
                    </td>
                    <td>{{ $linea->consumo_estimado ?? '—' }}</td>
                    <td class="pie">
                        @forelse ($linea->configuracion ?? [] as $campo => $valor)
                            {{ $campo }}: {{ $valor }}<br>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>
                        <form method="POST" action="{{ route('carrito.destroy', [$proyecto, $linea->id]) }}">
                            @csrf @method('DELETE')
                            <button class="boton rojo" style="margin:0;padding:.2rem .5rem" type="submit">Quitar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="tarjeta">
            <strong>Coste calculado en el servidor</strong>
            <table>
                <tr><td>Gasto mensual recurrente</td>
                    <td class="derecha"><x-importe :centimos="$evaluable->totalMensualCentimos" /></td></tr>
                <tr><td>Presupuesto máximo del proyecto</td>
                    <td class="derecha"><x-importe :centimos="$evaluable->presupuestoMaxCentimos" /></td></tr>
            </table>
            <p class="pie">Los importes los calcula <code>CalculadoraPrecios</code> en el servidor.
               La página no recibe ningún precio desde el navegador.</p>
        </div>
    @endif

    <a class="boton" href="{{ route('carrito.elegir', $proyecto) }}">Añadir servicio</a>

    {{-- ---------------------------------------------------------- reglas --}}
    @if ($proyecto->lineasCarrito->isNotEmpty())
        <h2>Comprobación de reglas de negocio</h2>
        <x-hallazgos :hallazgos="$hallazgos" />

        <form method="POST" action="{{ route('arquitecturas.store', $proyecto) }}" class="tarjeta">
            @csrf
            <label for="nombre">Guardar esta selección como arquitectura</label>
            <input type="text" id="nombre" name="nombre" placeholder="Arquitectura base" value="{{ old('nombre') }}">
            <button class="boton" type="submit">Guardar arquitectura</button>
        </form>

        <form method="POST" action="{{ route('presupuestos.store', $proyecto) }}">
            @csrf
            <button class="boton" type="submit" @disabled($bloqueado)>Emitir presupuesto</button>
            @if ($bloqueado)
                <p class="pie">Hay reglas incumplidas que impiden emitir. Corrige la selección.</p>
            @endif
        </form>
    @endif

    {{-- ----------------------------------------------------- presupuestos --}}
    <h2>Presupuestos emitidos</h2>
    <table>
        <tr><th>Número</th><th>Emitido</th><th class="derecha">Mensual</th>
            <th class="derecha">Pago único</th><th></th></tr>
        @forelse ($proyecto->presupuestos as $presupuesto)
            <tr>
                <td><code>{{ $presupuesto->numero }}</code></td>
                <td>{{ $presupuesto->emitido_en->format('d/m/Y H:i') }}</td>
                <td class="derecha"><x-importe :centimos="$presupuesto->total_mensual_centimos" /></td>
                <td class="derecha"><x-importe :centimos="$presupuesto->total_unico_centimos" /></td>
                <td><a href="{{ route('presupuestos.show', $presupuesto) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Todavía no se ha emitido ninguno.</td></tr>
        @endforelse
    </table>

    {{-- --------------------------------------------------------- ficheros --}}
    <h2>Documentación adjunta</h2>
    <table>
        <tr><th>Archivo</th><th>Tipo</th><th class="derecha">Tamaño</th><th></th></tr>
        @forelse ($proyecto->ficheros as $fichero)
            <tr>
                <td>{{ $fichero->nombre_original }}</td>
                <td class="pie">{{ $fichero->mime }}</td>
                <td class="derecha">{{ $fichero->tamanoLegible() }}</td>
                <td>
                    <a href="{{ route('ficheros.show', [$proyecto, $fichero]) }}">Descargar</a>
                    <form method="POST" action="{{ route('ficheros.destroy', [$proyecto, $fichero]) }}"
                          style="display:inline">
                        @csrf @method('DELETE')
                        <button class="boton rojo" style="margin:0;padding:.2rem .5rem" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Sin documentos.</td></tr>
        @endforelse
    </table>

    <form method="POST" action="{{ route('ficheros.store', $proyecto) }}"
          enctype="multipart/form-data" class="tarjeta">
        @csrf
        <label for="documento">Subir documento (PDF, PNG, JPG, TXT o MD · máximo 2 MB)</label>
        <input type="file" id="documento" name="documento">
        @error('documento') <p class="error-campo">{{ $message }}</p> @enderror
        <button class="boton" type="submit">Subir</button>
    </form>
@endsection
