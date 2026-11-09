{{--
    Muestra un importe guardado en céntimos. El formateo está aquí y no en
    cada vista para que todos los precios se vean igual.
--}}
@props(['centimos', 'sufijo' => null])

<span>{{ \App\Services\CalculadoraPrecios::euros((int) $centimos) }}@if ($sufijo) <span class="pie">{{ $sufijo }}</span>@endif</span>
