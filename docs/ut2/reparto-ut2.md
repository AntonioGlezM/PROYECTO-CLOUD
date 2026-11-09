# Reparto del trabajo · UT2

Cuatro personas, cuatro bloques. Cada uno sube **sus** archivos desde **su**
cuenta de GitHub, en **su** rama, y abre un Pull Request a `develop`.

---

## Orden de integración

El bloque de Alejandro (migraciones y modelos) es la base de todo lo demás.
Conviene que su Pull Request se revise y se integre primero; los otros tres
podemos trabajar en paralelo.

---

## Antonio · rama `feature/ut2-catalogo`

**Archivos**

    routes/web.php
    app/Http/Controllers/InicioController.php
    app/Http/Controllers/CatalogoController.php
    app/Http/Requests/GuardarProductoRequest.php
    resources/views/layouts/app.blade.php
    resources/views/components/alertas.blade.php
    resources/views/components/importe.blade.php
    resources/views/components/hallazgos.blade.php
    resources/views/inicio.blade.php
    resources/views/catalogo/index.blade.php
    resources/views/catalogo/show.blade.php
    resources/views/catalogo/form.blade.php
    resources/views/diagnostico/escape.blade.php
    tests/Feature/CatalogoTest.php
    README.md
    docs/ut2/evolucion-ut1-ut2.md
    docs/ut2/reparto-ut2.md

**Terminado cuando** `php artisan test --filter=CatalogoTest` pasa en verde y
el catálogo se puede filtrar por nombre, tipo, proveedor y región.

---

## Alejandro · rama `feature/ut2-modelo-datos`

**Archivos**

    database/migrations/              (las 14)
    app/Models/                       (los 13)
    database/seeders/CatalogoSeeder.php
    database/seeders/ProyectoDemoSeeder.php
    database/seeders/DatabaseSeeder.php
    app/Services/EmisorPresupuestos.php
    app/Services/PresupuestoBloqueado.php
    app/Http/Controllers/PresupuestoController.php
    resources/views/presupuestos/index.blade.php
    resources/views/presupuestos/show.blade.php
    tests/Feature/PresupuestoTest.php
    .env.example

**Terminado cuando** `php artisan migrate:fresh --seed` deja la base poblada
sin errores y `php artisan test --filter=PresupuestoTest` pasa en verde.

---

## Yassine · rama `feature/ut2-proyectos-requisitos`

**Archivos**

    app/Http/Controllers/ProyectoController.php
    app/Http/Controllers/RequerimientoController.php
    app/Http/Controllers/CarritoController.php
    app/Http/Controllers/ArquitecturaController.php
    app/Http/Requests/GuardarProyectoRequest.php
    app/Http/Requests/GuardarRequerimientoRequest.php
    resources/views/proyectos/index.blade.php
    resources/views/proyectos/show.blade.php
    resources/views/proyectos/form.blade.php
    resources/views/requerimientos/form.blade.php
    resources/views/carrito/elegir.blade.php
    resources/views/arquitecturas/show.blade.php
    tests/Feature/RequerimientoTest.php

**Terminado cuando** `php artisan test --filter=RequerimientoTest` pasa en
verde y se puede recorrer: crear proyecto → añadir requisito → añadir servicio
al carrito.

---

## Yeray · rama `feature/ut2-precios-reglas`

Este bloque es el corazón de la aplicación y es el que está probado con más
detalle: 13 comprobaciones de precios y 23 de reglas.

**Archivos**

    app/Enums/Modalidad.php
    app/Enums/Severidad.php
    app/Enums/TipoDato.php
    app/Enums/EstadoArquitectura.php
    app/Services/CalculadoraPrecios.php
    app/Services/MotorReglas.php
    app/Services/Hallazgo.php
    app/Services/ArquitecturaEvaluable.php
    app/Services/ConstructorEvaluable.php
    app/Http/Controllers/FicheroController.php
    app/Http/Requests/SubirFicheroRequest.php
    tests/Unit/CalculadoraPreciosTest.php
    tests/Unit/MotorReglasTest.php
    tests/Feature/FicheroTest.php

**Terminado cuando** `php artisan test --filter=Unit` y
`php artisan test --filter=FicheroTest` pasan en verde.

---

## Antes de entregar

- [ ] Las cuatro ramas integradas en `develop`
- [ ] `php artisan test` pasa entero
- [ ] `git shortlog -sn --all` muestra a los cuatro con commits
- [ ] `git check-ignore -v cloudforge/.env` confirma que el `.env` está ignorado
- [ ] El ZIP no lleva `vendor/`, `node_modules/`, `.git/` ni `.env`
- [ ] La etiqueta `ut2-entrega` creada y apuntando al commit que se entrega
