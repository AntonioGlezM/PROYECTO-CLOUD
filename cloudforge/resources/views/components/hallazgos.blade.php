{{--
    Lista los resultados del motor de reglas. Se reutiliza en la ficha del
    proyecto y en la de una arquitectura guardada (dos pantallas distintas).
--}}
@props(['hallazgos'])

@forelse ($hallazgos as $hallazgo)
    <p class="aviso {{ $hallazgo->bloquea() ? 'mal' : 'amarillo' }}">
        <strong>{{ $hallazgo->regla }}</strong>
        <span class="etiqueta">{{ $hallazgo->bloquea() ? 'bloquea' : 'sólo avisa' }}</span><br>
        {{ $hallazgo->mensaje }}
    </p>
@empty
    <p class="aviso ok">Ninguna regla de negocio se incumple.</p>
@endforelse
