@extends('layouts.app')
@section('titulo', 'Catálogo')

@section('contenido')
    <h1>Catálogo de servicios</h1>

    {{-- Filtros por GET: la URL resultante se puede guardar y compartir --}}
    <form method="GET" action="{{ route('catalogo.index') }}" class="tarjeta">
        <div class="fila">
            <div>
                <label for="buscar">Buscar por nombre o SKU</label>
                <input type="text" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}">
            </div>
            <div>
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t->id }}" @selected(($filtros['tipo'] ?? null) == $t->id)>{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="proveedor">Proveedor</label>
                <select id="proveedor" name="proveedor">
                    <option value="">Todos</option>
                    @foreach ($proveedores as $p)
                        <option value="{{ $p->id }}" @selected(($filtros['proveedor'] ?? null) == $p->id)>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="region">Disponible en región</label>
                <select id="region" name="region">
                    <option value="">Cualquiera</option>
                    @foreach ($regiones as $r)
                        <option value="{{ $r->id }}" @selected(($filtros['region'] ?? null) == $r->id)>
                            {{ $r->nombre }} ({{ $r->codigo }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <button class="boton" type="submit">Filtrar</button>
        <a class="boton gris" href="{{ route('catalogo.index') }}">Quitar filtros</a>
        <a class="boton" href="{{ route('catalogo.create') }}">Nuevo producto</a>
    </form>

    <p class="pie">{{ $productos->total() }} producto(s) encontrados.</p>

    <table>
        <thead>
        <tr>
            <th>SKU</th><th>Producto</th><th>Tipo</th><th>Proveedor</th>
            <th>Modalidad</th><th class="derecha">Tarifa</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse ($productos as $producto)
            <tr>
                <td><code>{{ $producto->sku }}</code></td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->tipo->nombre }}</td>
                <td>{{ $producto->proveedor->nombre }}</td>
                <td>{{ $producto->modalidad->etiqueta() }}</td>
                <td class="derecha">
                    <x-importe :centimos="$producto->tarifa_centimos" />
                    <br><span class="pie">{{ $producto->unidad }}</span>
                </td>
                <td><a href="{{ route('catalogo.show', $producto) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="7">Ningún producto coincide con esos filtros.</td></tr>
        @endforelse
        </tbody>
    </table>

    <nav class="paginas">{{ $productos->links() }}</nav>
@endsection
