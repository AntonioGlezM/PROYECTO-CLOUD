{{--
    Mensajes de resultado de la última operación. Se usa en todas las
    pantallas desde el layout, así que el formato está en un solo sitio.
--}}
@if (session('exito'))
    <p class="aviso ok">{{ session('exito') }}</p>
@endif

@if (session('error'))
    <p class="aviso mal">{{ session('error') }}</p>
@endif

@if (session('bloqueos'))
    <div class="aviso mal">
        <strong>Reglas que impiden emitir el presupuesto:</strong>
        <ul>
            @foreach (session('bloqueos') as $mensaje)
                <li>{{ $mensaje }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($errors->any())
    <div class="aviso mal">
        <strong>Revisa el formulario:</strong>
        <ul>
            @foreach ($errors->all() as $mensaje)
                <li>{{ $mensaje }}</li>
            @endforeach
        </ul>
    </div>
@endif
