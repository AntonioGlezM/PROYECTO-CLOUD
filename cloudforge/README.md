# CloudForge · UT2

Aplicación Laravel para diseñar, presupuestar y contratar infraestructura Cloud
simulada. Esta versión corresponde a la **fase UT2**: catálogo, proyectos,
requisitos, arquitecturas manuales, cálculo de precios, reglas de negocio,
selección y presupuestos históricos.

Repositorio: https://github.com/AntonioGlezM/PROYECTO-CLOUD

---

## 1. Requisitos de la máquina

| Componente | Versión usada por el equipo |
|---|---|
| Ubuntu | 26.04 LTS |
| PHP | 8.5.4 (mínimo 8.3) |
| Composer | 2.10.3 |
| MariaDB | 11.8.6 |
| Node y npm | v24.22.0 / 11.19.1 (opcional, ver nota) |

Extensiones de PHP necesarias: mbstring, xml, curl, zip, gd, intl, pdo_mysql,
fileinfo, tokenizer.

> **Nota sobre Node.** La interfaz de UT2 no usa JavaScript ni Vite: los
> estilos van en la plantilla y los formularios dinámicos se resuelven en el
> servidor. No hace falta `npm install` para que la aplicación funcione.

## 2. Instalación

Desde la carpeta del proyecto:

composer install

cp .env.example .env

php artisan key:generate

## 3. Base de datos

Crear la base y el usuario en MariaDB:

CREATE DATABASE cloudforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'cloudforge_dev'@'localhost' IDENTIFIED BY 'la contraseña local';

GRANT ALL PRIVILEGES ON cloudforge.* TO 'cloudforge_dev'@'localhost';

FLUSH PRIVILEGES;

Rellenar en el `.env` únicamente los valores locales:

DB_DATABASE=cloudforge

DB_USERNAME=cloudforge_dev

DB_PASSWORD=la contraseña local

Crear las tablas y cargar los datos de ejemplo:

php artisan migrate --seed

Los seeders dejan 2 proveedores, 6 regiones, 6 tipos, 15 productos y un
proyecto de ejemplo con su selección preparada.

> `php artisan migrate:fresh --seed` rehace la base desde cero y **borra todos
> los datos** de la base indicada en el `.env`.

## 4. Arrancar la aplicación

php artisan serve

Abrir http://127.0.0.1:8000

## 5. Ejecutar las pruebas

php artisan test

Las pruebas usan SQLite en memoria (configurado en `phpunit.xml`), así que no
tocan la base de datos de desarrollo. Los ficheros se prueban con
`Storage::fake()`, de modo que tampoco se escribe nada en disco.

| Prueba | Qué comprueba |
|---|---|
| T01 · T02 | Cálculo de precios en las tres modalidades y el tope mensual |
| T03 | Reglas que bloquean la emisión (presupuesto superado, región no disponible) |
| T04 | Reglas que sólo avisan (sin copia de seguridad, sin redundancia) |
| T05 | Alta válida de requisito que se guarda |
| T06 | Alta inválida (operador incompatible con el tipo) que no se guarda |
| T07 | CRUD de producto y relación N:M producto–región con datos del pivote |
| T08 | Subida, rechazo y sustitución de ficheros con Storage simulado |
| T09 | El presupuesto emitido no cambia al modificar el catálogo |
| T10 | Un fallo a mitad de la emisión revierte toda la operación |

## 6. Recorrido de la aplicación

1. **Catálogo** · listado con filtros por nombre, tipo, proveedor y región.
2. **Proyectos** · alta del proyecto con su presupuesto máximo mensual.
3. **Requisitos** · formulario en dos pasos sin JavaScript; el tipo de dato de
   la clave decide qué operadores se ofrecen.
4. **Selección** · formulario en tres pasos sin JavaScript: tipo → producto →
   región y configuración.
5. **Reglas** · se evalúan seis reglas; cuatro bloquean y dos sólo avisan.
6. **Presupuesto** · se emite dentro de una transacción y congela nombres,
   tarifas y regiones.

La pantalla «PHP vs Blade» del menú compara la salida de PHP embebido con la de
Blade y muestra por qué `{{ }}` evita inyectar HTML.

## 7. Qué funciona y qué queda para UT3/UT4

| Módulo | Estado en UT2 |
|---|---|
| Catálogo, proveedores y regiones | Hecho |
| Proyectos y requisitos | Hecho |
| Arquitecturas manuales | Hecho |
| Precios, reglas, selección y presupuestos | Hecho |
| Ficheros adjuntos | Hecho |
| Registro de auditoría | Básico: se registran las operaciones principales |
| Inicio de sesión y permisos | No exigido en UT2 — pendiente |
| API REST y Sanctum | No exigido en UT2 — pendiente |
| IA conectada | No exigido en UT2 — pendiente |
| Pagos y despliegue real | No exigido en UT2 — pendiente |

## 8. Seguridad

- Todos los formularios llevan `@csrf`.
- Toda la validación se hace en el servidor con Form Requests.
- La salida se escapa siempre con `{{ }}`.
- Los modelos declaran `$fillable` para evitar asignación masiva.
- Los precios se calculan **siempre** en el servidor: ningún importe llega
  desde el navegador.
- El `.env` no se sube al repositorio ni al ZIP; `.env.example` no lleva
  ningún valor secreto.
