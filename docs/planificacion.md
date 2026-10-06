# Planificación

**Proyecto:** CloudForge · Generación de infraestructuras Cloud con IA
**Módulo:** Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027
**Centro:** CIFP Villa de Agüimes
**Entrega UT1:** 14 de octubre de 2026

---

Este documento recoge la planificación del proyecto. Contiene el calendario de la UT1 con sus
hitos y su camino crítico, la previsión del trabajo para el resto del curso organizada por
unidades, el backlog inicial de tareas derivado del mapa funcional, y los riesgos identificados
con su plan de respuesta. No es un calendario definitivo: se revisa al cerrar cada unidad y las
desviaciones se anotan en el registro de decisiones.

---

## 1. Planificación de la UT1

Nueve días, del martes 6 al miércoles 14 de octubre.

### Semana 1 — Entorno y base del análisis

| Día | Fecha | Hito | Tareas |
|---|---|---|---|
| **D1** | Mar 6 oct | **Arranque** | Reunión de los cuatro: confirmar nombre, stack y roles, y leer la rúbrica juntos. Antonio: crear `develop` y `test`, proteger `main`, invitar a los tres y al profesor, abrir las issues. Alejandro: empezar la máquina virtual e instalar Ubuntu. Todos: preguntar las dudas al profesor |
| **D2** | Mié 7 oct | Entorno I | Alejandro: VS Code, PHP 8.5 y extensiones, Composer, Git y acceso a GitHub. Yassine: visitar las webs de referencia y redactar problema, valor y objetivos. Yeray: módulos del mapa funcional y catálogo de productos |
| **D3** | Jue 8 oct | Entorno II | Alejandro: Node y npm, MariaDB, Laravel 13, configuración del `.env` y migraciones. Yassine: los cuatro actores y los requisitos funcionales y no funcionales. Yeray: modelo conceptual y primeros diagramas. Antonio: `.gitignore`, `.env.example` y README |
| **D4** | Vie 9 oct | **Hito 1 · Entorno cerrado** | Alejandro y Yeray: página de comprobación del ciclo MVC. Los otros tres: empezar su propia máquina virtual siguiendo `entorno.md`. Antonio: revisar los primeros Pull Requests. Yassine: reglas de negocio |
| **D5–D6** | Sáb 10 – Dom 11 oct | Reproducibilidad | Los tres restantes terminan su máquina virtual y anotan cada paso que falle, lo que alimenta los apartados de reproducibilidad e incidencias. Cada uno redacta sus tres casos de uso. Yeray cierra los tres diagramas |

### Semana 2 — Documentos, evidencias y exposición

| Día | Fecha | Hito | Tareas |
|---|---|---|---|
| **D7** | Lun 12 oct | Documentos en limpio | Sesión conjunta para poner en común los doce casos de uso, unificar formato y detectar solapes. Todos: volcar su parte a las plantillas A y B. Yassine: comprobar que cada módulo tiene requisito y que cada caso de uso se rastrea a un requisito funcional |
| **D8** | Mar 13 oct | **Hito 2 · Todo cerrado** | Todos: capturar las doce evidencias técnicas con su explicación. Revisión cruzada: Yassine revisa a Yeray y a la inversa; Antonio revisa a Alejandro. Antonio: montar los dos PDF y completar la tabla de participación. Todos: diapositivas y ensayo cronometrado de diez minutos |
| **D9** | Mié 14 oct | **Entrega y exposición** | Comprobación final, exportación de los dos PDF desde LibreOffice, última auditoría de secretos, verificación de que el profesor accede al repositorio, entrega de los tres entregables y exposición |

### Camino crítico

```
Alejandro monta la VM ──► Laravel arranca ──► Página MVC ──► Evidencias ──► Plantilla A
      D1–D2 (6–7)              D3 (8)            D4 (9)        D8 (13)        D8 (13)
```



### Respuesta ante desviaciones

| Situación | Respuesta acordada |
|---|---|
| Alejandro se atasca con la máquina virtual el día 2 | Yeray deja los módulos y le apoya. Los módulos se recuperan el fin de semana |
| Alguien no tiene su máquina virtual el día 6 | Se monta en sesión conjunta el lunes por la mañana, aunque suponga perder la puesta en común de los casos de uso |
| Faltan casos de uso el día 7 | Se cubren con los dos de reserva previstos y se reparten entre quien vaya más holgado |

---

## 2. Previsión para el resto del curso

Organización del trabajo futuro en grandes bloques. Es una previsión inicial, no un compromiso
cerrado, y se revisará al terminar cada unidad.

| Fase | Contenido | Depende de |
|---|---|---|
| **UT1** | Entorno reproducible y análisis funcional | — |
| **UT2** | Modelo de datos, migraciones, *seeders* y CRUD del catálogo | Modelo conceptual de UT1 |
| **UT3** | Autenticación, roles y autorización por perfiles | Entidad USUARIO y actores de UT1 |
| **UT4** | Proyectos, requerimientos y motor de validación | UT2 y UT3 |
| **UT5** | Carrito, cálculo de precios y presupuestos | Catálogo y precios de UT2 |
| **UT6** | Pedidos, pago simulado y despliegues simulados | UT5 |
| **UT7** | Integración de inteligencia artificial para recomendaciones | Validador de UT4 y catálogo de UT2 |
| **UT8** | API REST, auditoría, monitorización y cierre | Todo lo anterior |

---

## 3. Backlog inicial

Cada módulo identificado en el mapa funcional se convierte en una tarea grande.

| # | Tarea | Módulo | Fase prevista | Prioridad |
|---|---|---|---|---|
| 1 | Gestión de usuarios, roles y autenticación | USUARIOS | UT3 | Alta |
| 2 | Catálogo de productos con especificación por tipo | CATÁLOGO | UT2 | Alta |
| 3 | Proveedores, regiones y disponibilidad | PROVEEDORES / REGIONES | UT2 | Alta |
| 4 | Precios por producto, región y configuración | PRECIOS | UT2 | Alta |
| 5 | Proyectos y requerimientos estructurados | PROYECTOS / REQUERIMIENTOS | UT4 | Alta |
| 6 | Motor de validación de arquitecturas | ARQUITECTURAS | UT4 | Alta |
| 7 | Carrito con configuraciones | CARRITO / ITEMS | UT5 | Media |
| 8 | Generación y gestión de presupuestos | PRESUPUESTOS | UT5 | Alta |
| 9 | Pedidos y pago simulado | PEDIDOS / PAGOS | UT6 | Media |
| 10 | Despliegues y recursos simulados | DESPLIEGUES / RECURSOS | UT6 | Media |
| 11 | Integración de IA para recomendaciones | RECOMENDACIONES | UT7 | Media |
| 12 | Registro de auditoría | AUDITORÍA | UT8 | Media |
| 13 | API REST | — | UT8 | Baja |
| 14 | Servicios de soporte e incidencias | SOPORTE | UT8 | Baja |

---

## 4. Riesgos de la planificación

| Riesgo | Probabilidad | Impacto | Plan de respuesta |
|---|---|---|---|
| El entorno no está listo el día 4 | Media | Muy alto | Es el camino crítico. Se empieza el día 1 |
| Alguien no monta su máquina virtual antes del día 6 | Media | Alto | Sesión conjunta el lunes por la mañana |
| Las capturas se dejan para el último día | Alta | Alto | Hito 2 fijado el día 8; el día 9 solo queda exportar |
| Commits concentrados en una sola persona | Media | Alto | Revisión de `git shortlog -sn --all` en las reuniones del día 4 y del día 8 |
| Documentación desactualizada respecto al código | Media | Medio | Revisión de `docs/` en el cierre de cada unidad |
