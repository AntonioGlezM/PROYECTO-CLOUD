@extends('layouts.app')
@section('titulo', 'PHP embebido frente a Blade')

@section('contenido')
    <h1>PHP embebido frente a Blade</h1>
    <p>Esta pantalla compara las dos formas de escribir el mismo resultado y
       muestra por qué <code>{{ '{{ }}' }}</code> evita mostrar HTML inseguro.</p>

    @php
        // Dato tal y como podría llegar desde un formulario: contiene etiquetas.
        $nombreCliente = '<b onmouseover="alert(1)">Academia Lumen</b>';
        $unidades      = 3;
        $tarifa        = 4550;   // céntimos
    @endphp

    <h2>1. Mismo cálculo, dos sintaxis</h2>
    <table>
        <tr><th style="width:30%">Forma</th><th style="width:35%">Código</th><th>Resultado</th></tr>
        <tr>
            <td>PHP embebido</td>
            <td><code>&lt;?= $unidades * $tarifa ?&gt;</code></td>
            <td><?= $unidades * $tarifa ?> céntimos</td>
        </tr>
        <tr>
            <td>Blade</td>
            <td><code>{{ '{{ $unidades * $tarifa }}' }}</code></td>
            <td>{{ $unidades * $tarifa }} céntimos</td>
        </tr>
    </table>
    <p class="pie">El resultado es idéntico: Blade se compila a PHP. La diferencia está en el escape.</p>

    <h2>2. El mismo dato con etiquetas HTML dentro</h2>
    <table>
        <tr><th style="width:30%">Forma</th><th>Qué se ve</th></tr>
        <tr>
            <td><code>{{ '{{ $nombreCliente }}' }}</code><br><span class="etiqueta">seguro</span></td>
            <td>{{ $nombreCliente }}</td>
        </tr>
        <tr>
            <td><code>{{ '{!! $nombreCliente !!}' }}</code><br><span class="etiqueta">peligroso</span></td>
            <td>{!! $nombreCliente !!}</td>
        </tr>
    </table>

    <div class="aviso amarillo">
        <strong>Por qué importa.</strong>
        Con <code>{{ '{{ }}' }}</code> Blade aplica <code>htmlspecialchars</code> y el texto se
        muestra tal cual se escribió: se lee la etiqueta, no se ejecuta. Con
        <code>{{ '{!! !!}' }}</code> el navegador interpreta el HTML, de modo que cualquiera que
        pueda escribir el nombre de un cliente podría inyectar código en la página de los demás.
        En CloudForge sólo se usa la primera forma; la segunda aparece aquí
        únicamente para enseñar la diferencia.
    </div>

    <h2>3. Condición y bucle sobre datos reales</h2>
    @php $regiones = ['eu-madrid' => true, 'us-east' => false, 'eu-paris' => true]; @endphp
    <ul>
        @foreach ($regiones as $codigo => $disponible)
            <li>
                {{ $codigo }} —
                @if ($disponible) disponible @else <em>no disponible</em> @endif
            </li>
        @endforeach
    </ul>
@endsection
