# Registro de decisiones del proyecto

**Proyecto:** CloudForge · Generación de infraestructuras Cloud con IA
**Módulo:** Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027
**Centro:** CIFP Villa de Agüimes

Este registro se completa desde el primer día, no al final. Cada vez que el equipo toma una
decisión que podría haberse resuelto de otra forma, la anotamos aquí junto con la alternativa
descartada y el motivo. Formato: una fila por decisión. No se borran filas; si una decisión se
revierte, se añade una nueva que lo indique.

---

| # | Fecha | Decisión | Alternativas consideradas | Motivo | Quién |
|---|---|---|---|---|---|
| D-01 | 2026-10-06 | Nombre de la aplicación: **CloudForge** | InfraMind, NimbusPlan, CloudCraft, Stratus, ArquiCloud | El verbo «forjar» describe lo que hace la aplicación: de una necesidad sale una arquitectura construida. Encaja con el nombre del repositorio y es claro en español y en inglés | Equipo |
| D-02 | 2026-10-06 | Sistema operativo: **Ubuntu 26.04 LTS** sobre VirtualBox | Ubuntu nativo, WSL2 | Es la última LTS y el enunciado pide «Ubuntu en su última versión». En máquina virtual porque permite que los cuatro tengamos entornos idénticos y desechables | Equipo |
| D-03 | 2026-10-06 | Base de datos: **MariaDB** | MySQL, PostgreSQL | Está en los repositorios oficiales de Ubuntu, es compatible con el driver `mysql` de PHP que utiliza Laravel y no requiere licencia. **Confirmado por el profesor como motor para todo el grupo-clase** | Equipo |
| D-04 | 2026-10-06 | PHP **8.5 desde los repositorios oficiales de Ubuntu**, sin repositorio externo | Repositorio `packages.sury.org` para fijar otra versión | Ubuntu 26.04 ya incluye PHP 8.5 y Laravel 13 admite de 8.3 a 8.5. Menos dependencias de terceros y un procedimiento más sencillo de reproducir | Alejandro |
| D-05 | 2026-10-06 | Framework: **Laravel 13** | Laravel 12 | Versión mayor actual, publicada en marzo de 2026, con correcciones de errores hasta el tercer trimestre de 2027 | Equipo |
| D-06 | 2026-10-06 | Gestor de paquetes de frontend: **npm** | pnpm, yarn | La plantilla pide expresamente la salida de `npm --version` y la función de `package.json`, y Laravel genera sus scripts para npm | Equipo |
| D-07 | 2026-10-06 | Juego de caracteres de la base de datos: **utf8mb4 / utf8mb4_unicode_ci** | utf8 de tres bytes | Es el único que almacena correctamente todo Unicode. Con `utf8` el texto se guarda truncado sin aviso, lo que produce errores difíciles de diagnosticar | Alejandro |
| D-08 | 2026-10-06 | Flujo de ramas `main` / `develop` / `test` más ramas `feature/*`, con Pull Request obligatorio | Commits directos sobre `main` | Lo exige el enunciado y permite que las aportaciones de cada integrante sean identificables | Antonio |
| D-09 | 2026-10-06 | Catálogo: **separar la tabla general de la especificación por tipo** | Una única tabla con columnas opcionales para todos los tipos | Con una tabla ancha la mayoría de columnas quedarían a `NULL`, no se podría declarar obligatoriedad por tipo, los índices se degradarían y, lo decisivo, añadir un tipo nuevo exigiría modificar el esquema en lugar de insertar datos. Razonamiento completo en `arquitectura.md` | Yeray |
| D-10 | 2026-10-06 | El **precio depende de producto y región**, y en algunos casos también de la configuración | Precio como atributo del producto | Un mismo producto no cuesta lo mismo en todas las regiones ni en todas sus configuraciones | Yeray |
| D-11 | 2026-10-06 | La lógica de negocio vive en **servicios**, no en los controladores | Toda la lógica en los controladores | La misma regla se invoca desde el controlador web, una futura API, comandos de consola y trabajos en segundo plano. Además, el principio «la IA recomienda y la aplicación decide» necesita un lugar concreto donde esa decisión esté escrita | Yeray |
| D-12 | 2026-10-06 | La propuesta de la IA **pasa siempre por el validador de la aplicación** antes de mostrarse | Mostrar la respuesta de la IA directamente | Evita que lleguen al cliente productos inexistentes, precios inventados o combinaciones incompatibles | Yeray |
| D-13 | 2026-10-06 | Nombre de la carpeta del proyecto Laravel: `cloudforge` | `proyecto_laravel`, el genérico del enunciado | El enunciado pide «un nombre de proyecto acorde a lo que vamos a crear» | Alejandro |
| D-14 | 2026-10-06 | Las líneas de presupuesto y de pedido **copian** producto, configuración y precio, en lugar de referenciarlos | Referenciar al catálogo vivo | Un presupuesto o un pedido es un documento histórico: debe poder leerse dentro de dos años aunque el catálogo haya cambiado por completo | Yeray |
| D-15 | 2026-10-06 | Modificar una arquitectura genera una **versión nueva** en lugar de sobrescribir la anterior | Sobrescribir la existente | Permite comparar qué propuso la IA con qué aprobó finalmente una persona, que es lo que el auditor necesita poder revisar | Yeray |
| D-16 | 2026-10-06 | Composer se instala **verificando el hash** del instalador | Descargar y ejecutar el instalador directamente | No se ejecuta a ciegas un script descargado de internet con permisos de instalación en el sistema | Alejandro |
| D-17 | 2026-10-06 | Visual Studio Code desde el **repositorio oficial de Microsoft**, no desde Snap | Paquete Snap | Se actualiza mediante `apt` junto con el resto del sistema y arranca más rápido | Alejandro |
| D-18 | 2026-10-06 | Usuario de base de datos propio (`cloudforge_dev`), no `root` | Utilizar `root` para la aplicación | Permisos acotados a una única base de datos: limita el daño si las credenciales se filtran | Alejandro |
| D-19 | 2026-10-06 | Cada integrante sube **sus propios ficheros** desde su rama | Que el coordinador suba toda la documentación | El enunciado rechaza expresamente que «una única persona realice todo el proyecto y posteriormente los demás hagan un único commit». El historial de Git deja constancia de quién escribió cada parte | Antonio |
| D-20 | 2026-10-06 | Los doce casos de uso van en una rama compartida, con tres por persona | Un fichero por persona, o que los escriba una sola | Es un entregable único que debe quedar uniforme, pero cada integrante debe tener en él una aportación identificable | Antonio |

---

## Decisiones pendientes

Cuestiones que todavía no hemos resuelto. Al cerrarse, pasan a la tabla anterior.

- ¿Cuántos días dura un presupuesto antes de caducar?
- ¿Puede un cliente tener varios proyectos activos a la vez?
- ¿Se permite modificar un pedido confirmado, o solo cancelarlo y rehacerlo desde un presupuesto nuevo?
- ¿La configuración de un producto es una entidad propia o son atributos de la línea?
- ¿Una arquitectura es una versión del proyecto o una entidad independiente?
- ¿Quién asigna un arquitecto a un proyecto: el administrador, el cliente, o es automático?