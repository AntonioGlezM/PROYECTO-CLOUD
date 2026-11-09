@extends('layouts.app')
@section('titulo', 'Añadir requisito')

@section('contenido')
    <h1>Añadir requisito a «{{ $proyecto->nombre }}»</h1>

    {{--
        Formulario dinámico SIN JavaScript, en dos pasos.
        Paso 1: se elige la clave y se envía por GET.
        Paso 2: el servidor devuelve esta misma vista, ya sabiendo el tipo
        de dato de esa clave, y muestra sólo los operadores compatibles.
    --}}
    <form method="GET" action="{{ route('requerimientos.create', $proyecto) }}" class="tarjeta">
        <label for="clave">Paso 1 · ¿Qué se quiere exigir?</label>
        <select id="clave" name="clave">
            <option value="">— Elegir —</option>
            @foreach (\App\Models\Requerimiento::CLAVES as $codigo => $titulo)
                <option value="{{ $codigo }}" @selected($clave === $codigo)>{{ $titulo }}</option>
            @endforeach
        </select>
        <button class="boton" type="submit">Continuar</button>
    </form>

    @if ($clave)
        @php $tipoDato = \App\Enums\TipoDato::from($tipo); @endphp

        <form method="POST" action="{{ route('requerimientos.store', $proyecto) }}" class="tarjeta">
            @csrf
            <input type="hidden" name="clave" value="{{ $clave }}">

            <p><strong>Paso 2 · {{ \App\Models\Requerimiento::CLAVES[$clave] }}</strong>
               <span class="etiqueta">tipo {{ $tipo }}</span></p>

            <div class="fila">
                <div>
                    <label for="operador">Operador</label>
                    <select id="operador" name="operador">
                        {{-- Sólo los operadores que admite este tipo de dato --}}
                        @foreach ($tipoDato->operadores() as $operador)
                            <option value="{{ $operador }}" @selected(old('operador') === $operador)>
                                {{ $operador }}</option>
                        @endforeach
                    </select>
                    @error('operador') <p class="error-campo">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="valor">Valor</label>
                    @if ($tipo === 'booleano')
                        <select id="valor" name="valor">
                            <option value="si" @selected(old('valor') === 'si')>Sí</option>
                            <option value="no" @selected(old('valor') === 'no')>No</option>
                        </select>
                    @elseif ($tipo === 'numero')
                        <input type="number" id="valor" name="valor" value="{{ old('valor') }}">
                    @else
                        <input type="text" id="valor" name="valor" value="{{ old('valor') }}"
                               placeholder="{{ $clave === 'region' ? 'eu-madrid,eu-paris' : '' }}">
                    @endif
                    @error('valor') <p class="error-campo">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="origen">Origen</label>
                    <select id="origen" name="origen">
                        <option value="cliente">Lo pide el cliente</option>
                        <option value="equipo">Lo añade el equipo</option>
                        <option value="ia">Sugerencia de IA</option>
                    </select>
                </div>
            </div>

            <label><input type="checkbox" name="obligatorio" value="1" style="width:auto"
                          @checked(old('obligatorio', true))>
                   Obligatorio (si no se cumple, no se puede emitir presupuesto)</label>

            <button class="boton" type="submit">Guardar requisito</button>
            <a class="boton gris" href="{{ route('proyectos.show', $proyecto) }}">Cancelar</a>
        </form>
    @endif
@endsection
