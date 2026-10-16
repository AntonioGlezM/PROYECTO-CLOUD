{{-- Vista de comprobación del entorno (UT1). No se evalúa el diseño gráfico. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $nombreApp }}</title>
    <style>
        body  { font-family: system-ui, sans-serif; margin: 3rem auto; max-width: 40rem; color: #16222e; }
        h1    { color: #1f5f8b; margin-bottom: .25rem; }
        .ok   { color: #1b6b43; font-weight: 600; }
        table { border-collapse: collapse; margin-top: 1.5rem; }
        th,td { text-align: left; padding: .4rem 1.5rem .4rem 0; border-bottom: 1px solid #d4dde4; }
    </style>
</head>
<body>
    {{-- Las variables llegan del controlador; Blade las escapa automáticamente --}}
    <h1>{{ $nombreApp }}</h1>
    <p class="ok">Entorno de desarrollo preparado correctamente.</p>

    <table>
        <tr><th>Equipo</th><td>{{ implode(', ', $equipo) }}</td></tr>
        <tr><th>Laravel</th><td>{{ $laravel }}</td></tr>
        <tr><th>PHP</th><td>{{ $php }}</td></tr>
    </table>
</body>
</html>