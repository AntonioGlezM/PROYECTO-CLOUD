# CloudForge

Plataforma web orientada al backend para diseñar, configurar, presupuestar y contratar de forma
simulada una infraestructura Cloud.

---

## 1. Descripción

CloudForge resuelve un problema concreto: un cliente sabe **qué quiere conseguir** —«una web para
una academia con unos 500 alumnos, que no se caiga y que no cueste una fortuna»— pero no sabe
traducir eso a **qué debe contratar**. Para configurar una infraestructura razonable hay que
decidir a la vez sobre CPU, memoria, almacenamiento, región, disponibilidad, seguridad, copias de
seguridad, base de datos, monitorización y presupuesto, y cada decisión condiciona a las demás.

En lugar de obligar al usuario a seleccionar productos uno a uno, la aplicación le permite
describir una **necesidad tecnológica** en lenguaje natural y le propone una infraestructura
completa: servidores virtuales, bases de datos, almacenamiento, sistemas de backup, redes,
cortafuegos, monitorización, servicios de inteligencia artificial y soporte. Después podrá
configurarla, validarla, presupuestarla y contratarla de forma simulada.

El principio que gobierna el diseño es **«la IA recomienda y la lógica de aplicación decide»**: la
inteligencia artificial propone arquitecturas y alternativas más económicas, pero el cálculo de
precios, la validación de compatibilidades y la decisión final permanecen siempre bajo el control
de la aplicación Laravel.

## 2. Integrantes

| Integrante | GitHub |
|---|---|
| Antonio González | [@AntonioGlezM](https://github.com/AntonioGlezM) |
| Alejandro | [@AacostaA-1327](https://github.com/AacostaA-1327) |
| Yassine | [@yassinesayar](https://github.com/yassinesayar) |
| Yeray | [@yeeeray09](https://github.com/yeeeray09) |

## 3. Roles

Roles iniciales. Rotarán en las siguientes unidades y todos los integrantes conocen el conjunto
del proyecto.

| Integrante | Rol | Responsabilidades principales |
|---|---|---|
| Antonio | Coordinación técnica + Calidad y seguridad | Repositorio, ramas, Pull Requests, issues, plazos, seguridad y revisión de entregables |
| Alejandro | Responsable del entorno de desarrollo | Ubuntu, PHP, Composer, Node, MariaDB, Laravel, documentación reproducible y evidencias técnicas |
| Yassine | Analista funcional | Problema, objetivos, actores, requisitos, reglas de negocio y coordinación de los casos de uso |
| Yeray | Arquitecto de dominio y datos | Módulos del sistema, catálogo Cloud, modelo conceptual, arquitectura Laravel y análisis de IA |

El detalle completo, con las normas internas de trabajo y la estrategia de Git, está en
[`docs/roles-equipo.md`](docs/roles-equipo.md).

## 4. Tecnologías

| Componente | Versión | Función en el proyecto |
|---|---|---|
| Ubuntu | 26.04 LTS | Sistema operativo de la máquina de desarrollo, sobre VirtualBox |
| Visual Studio Code | — | Editor con soporte PHP y Blade, terminal integrada y control de versiones |
| PHP | 8.5 | Lenguaje de servidor. Es la versión que incluyen los repositorios oficiales de Ubuntu 26.04 |
| Composer | 2.x | Gestor de dependencias de PHP y generación del autoload PSR-4 |
| Laravel | 13.x | Framework MVC sobre el que se construye la aplicación. Admite PHP 8.3 a 8.5 |
| MariaDB | Repositorios de Ubuntu | Servidor de base de datos de desarrollo |
| Node.js | LTS | Ejecución de las herramientas de construcción de frontend |
| npm | Incluido con Node.js | Gestor de dependencias de frontend |
| Vite | Incluido en Laravel | Compilación y recarga de los recursos estáticos |
| Git y GitHub | — | Control de versiones y trabajo colaborativo |

> La base de datos **MariaDB** es la base de datos *de desarrollo de esta aplicación*. No debe
> confundirse con las bases de datos que la aplicación ofrecerá como *producto* dentro de su
> catálogo Cloud: son dos conceptos distintos.

La justificación de cada elección está en [`docs/decisiones.md`](docs/decisiones.md).

## 5. Requisitos previos

- Máquina con Ubuntu 26.04 LTS, física o virtual. Recomendado: 4 GB de RAM, 2 CPU y 40 GB de disco.
- Acceso a internet para descargar paquetes y dependencias.
- Cuenta de GitHub con acceso a este repositorio.

El procedimiento completo de preparación de la máquina, paso a paso y reproducible, está en
[`docs/entorno.md`](docs/entorno.md).

## 6. Instalación

```bash
git clone https://github.com/AntonioGlezM/PROYECTO-CLOUD.git
cd PROYECTO-CLOUD/cloudforge

composer install
npm install
```

`composer install` reconstruye la carpeta `vendor/` a partir de `composer.lock`, lo que garantiza
que todos los integrantes trabajen con exactamente las mismas versiones de cada dependencia. Ni
`vendor/` ni `node_modules/` se versionan.

## 7. Configuración

```bash
cp .env.example .env
```

Edita el fichero `.env` con los datos locales de la base de datos (apartado 8) y después:

```bash
php artisan key:generate
php artisan migrate
```

> El fichero `.env` **nunca se sube al repositorio**. Contiene credenciales reales y la `APP_KEY`,
> que es la clave con la que Laravel cifra sesiones y cookies, y además cambia en cada máquina. Lo
> que se versiona es `.env.example`, con las claves de configuración pero sin ningún valor.

## 8. Base de datos

| Dato | Valor |
|---|---|
| Motor | MariaDB |
| Base de datos | `cloudforge` |
| Usuario de desarrollo | `cloudforge_dev` |
| Puerto | 3306 |
| Juego de caracteres | `utf8mb4` / `utf8mb4_unicode_ci` |

Creación:

```sql
CREATE DATABASE cloudforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cloudforge_dev'@'localhost' IDENTIFIED BY '<contraseña local>';
GRANT ALL PRIVILEGES ON cloudforge.* TO 'cloudforge_dev'@'localhost';
FLUSH PRIVILEGES;
```

Cada integrante elige su propia contraseña en su máquina y no se escribe en ningún documento del
repositorio. No se utiliza el usuario `root` para la aplicación: un usuario con permisos acotados a
una única base de datos limita el daño en caso de filtración.

En `.env` la conexión se declara como `mysql`, porque MariaDB comparte protocolo y driver con
MySQL.

Se utiliza `utf8mb4` y no `utf8` porque el `utf8` de MySQL y MariaDB almacena como máximo tres
bytes por carácter, insuficiente para parte de Unicode. Con `utf8` el texto se guardaría truncado
sin aviso.

## 9. Ejecución

```bash
php artisan serve
```

Abrir <http://127.0.0.1:8000> en el navegador. Se muestra la página de comprobación del entorno con
el nombre de la aplicación, el equipo y las versiones de Laravel y PHP en ejecución.

> `php artisan serve` levanta el servidor de desarrollo incluido en PHP, que atiende una petición
> cada vez. No es un servidor de producción: en producción la aplicación iría detrás de Nginx o
> Apache con PHP-FPM.

## 10. Estructura de la documentación

```
PROYECTO-CLOUD/
├── docs/
│   ├── entorno.md         Preparación reproducible del entorno de desarrollo
│   ├── requisitos.md      Problema, objetivos, actores, requisitos y reglas de negocio
│   ├── arquitectura.md    Módulos, dominio Cloud, modelo conceptual, arquitectura Laravel e IA
│   ├── casos-de-uso.md    Los doce casos de uso del sistema
│   ├── roles-equipo.md    Organización del equipo y estrategia de Git
│   ├── planificacion.md   Planificación de la UT1 y previsión del resto del curso
│   ├── decisiones.md      Registro de decisiones del proyecto con su justificación
│   └── diagramas/         Mapa funcional, modelo conceptual y arquitectura Laravel
│
├── cloudforge/            Proyecto Laravel 13
│   ├── .env.example
│   ├── .gitignore
│   ├── composer.json
│   ├── composer.lock
│   └── package.json
│
└── README.md
```

## 11. Estado actual del proyecto

**UT1 — Fase 1: preparación del entorno de desarrollo y análisis funcional.**

El repositorio contiene el procedimiento documentado del entorno de desarrollo, el proyecto Laravel
inicial con una página de comprobación que demuestra el ciclo MVC, y la documentación completa del
análisis funcional de la aplicación: problema y objetivos, actores, mapa funcional de módulos,
requisitos y reglas de negocio, doce casos de uso, modelo conceptual de datos, arquitectura Laravel
propuesta y análisis del uso previsto de inteligencia artificial.

**Las funcionalidades de la aplicación todavía no están implementadas.** No existe catálogo, ni
carrito, ni presupuestos, ni pedidos, ni integración con IA. Esa implementación corresponde a las
siguientes unidades, según la previsión recogida en
[`docs/planificacion.md`](docs/planificacion.md).

---

*Módulo de Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027*
*CIFP Villa de Agüimes · Entrega UT1: 14 de octubre de 2026*