<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'CloudForge')</title>
    {{--
        La interfaz usa CSS propio servido aquí mismo. No se usa Vite ni
        JavaScript: el enunciado de UT2 pide que los formularios funcionen
        sin JS, así que no hay nada que compilar para que la web funcione.
    --}}
    <style>
        :root{--tinta:#16222e;--azul:#1f5f8b;--claro:#eef4f9;--linea:#d4dde4;
              --gris:#5b6b78;--rojo:#b3261e;--ambar:#8a6d00;--verde:#1b6b43}
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;
             color:var(--tinta);background:#f4f6f8;line-height:1.5}
        header{background:var(--tinta);color:#fff;padding:.9rem 1.5rem;
               display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap}
        header a{color:#cfe3f1;text-decoration:none;font-size:.95rem}
        header a:hover,header a.activo{color:#fff;text-decoration:underline}
        header .marca{font-weight:700;font-size:1.15rem;color:#fff;margin-right:auto}
        main{max-width:1100px;margin:0 auto;padding:1.5rem}
        h1{font-size:1.6rem;margin:0 0 1rem}
        h2{font-size:1.2rem;margin:1.6rem 0 .6rem;color:var(--azul)}
        table{width:100%;border-collapse:collapse;background:#fff;margin:.5rem 0 1rem;
              border:1px solid var(--linea);font-size:.92rem}
        th{background:var(--azul);color:#fff;text-align:left;padding:.5rem .6rem;font-weight:600}
        td{padding:.45rem .6rem;border-top:1px solid var(--linea);vertical-align:top}
        tr:nth-child(even) td{background:#fafcfd}
        .tarjeta{background:#fff;border:1px solid var(--linea);border-radius:6px;
                 padding:1rem 1.2rem;margin-bottom:1rem}
        label{display:block;font-weight:600;font-size:.88rem;margin:.7rem 0 .2rem}
        input,select,textarea{width:100%;padding:.45rem .55rem;border:1px solid var(--linea);
                              border-radius:4px;font:inherit;background:#fff}
        .error-campo{color:var(--rojo);font-size:.85rem;margin-top:.2rem}
        .boton{display:inline-block;background:var(--azul);color:#fff;border:0;border-radius:4px;
               padding:.5rem .9rem;font:inherit;cursor:pointer;text-decoration:none;margin-top:.8rem}
        .boton.gris{background:var(--gris)}
        .boton.rojo{background:var(--rojo)}
        .aviso{padding:.7rem .9rem;border-radius:4px;margin:.6rem 0;font-size:.92rem}
        .aviso.ok{background:#edf7f1;border-left:4px solid var(--verde)}
        .aviso.mal{background:#fdecea;border-left:4px solid var(--rojo)}
        .aviso.amarillo{background:#fff8e6;border-left:4px solid var(--ambar)}
        .fila{display:flex;gap:1rem;flex-wrap:wrap}
        .fila>*{flex:1;min-width:220px}
        .pie{color:var(--gris);font-size:.85rem}
        .etiqueta{display:inline-block;padding:.1rem .45rem;border-radius:3px;
                  font-size:.78rem;background:var(--claro);color:var(--azul);font-weight:600}
        .derecha{text-align:right}
        nav.paginas{margin:.5rem 0}
        nav.paginas a,nav.paginas span{margin-right:.4rem}
    </style>
</head>
<body>
<header>
    <span class="marca">CloudForge</span>
    <a href="{{ route('inicio') }}">Inicio</a>
    <a href="{{ route('catalogo.index') }}">Catálogo</a>
    <a href="{{ route('proyectos.index') }}">Proyectos</a>
    <a href="{{ route('presupuestos.index') }}">Presupuestos</a>
    <a href="{{ route('diagnostico.escape') }}">PHP vs Blade</a>
</header>

<main>
    {{-- Parcial reutilizable: aparece en todas las pantallas --}}
    <x-alertas />

    @yield('contenido')
</main>
</body>
</html>
