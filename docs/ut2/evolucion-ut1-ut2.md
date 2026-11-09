# Evolución de UT1 a UT2

Este documento recoge qué ha cambiado en el análisis entre la primera y la
segunda fase, por qué ha cambiado y dónde se ve el efecto en el código o en
las pruebas. Es la base del apartado 1 de la memoria de UT2.

La mayoría de los cambios nacen de la corrección de la Parte B de UT1. El
profesorado señaló un patrón repetido: el análisis estaba bien explicado pero
en abstracto, sin apuntar a nada comprobable. Los cambios de esta fase van
dirigidos a eso.

---

## 1. Cambios y confirmaciones

### C-01 · Los requisitos ahora apuntan a la prueba que los comprueba

**Antes (UT1).** El documento de requisitos listaba 37 RF y 13 RNF bien
redactados, pero no decía quién los verificaba.

**Ahora (UT2).** Cada requisito que la aplicación implementa en esta fase
tiene asociada una regla de negocio o un test automatizado. La tabla
requisito → comprobación está en el apartado 1.2 de este documento.

**Por qué.** Corrección de UT1, criterio «Requisitos y reglas»: *«Falta
indicar qué caso de uso comprueba cada requisito. Añadid una tabla sencilla
con requisito, caso de uso, comprobación.»*

**Efecto en código y pruebas.** `app/Services/MotorReglas.php` implementa seis
reglas con identificador propio (R01–R06); `tests/Unit/MotorReglasTest.php`
comprueba cada una por separado, distinguiendo las que bloquean de las que
sólo avisan.

---

### C-02 · Las decisiones abiertas del modelo de datos quedan cerradas y razonadas

**Antes (UT1).** El apartado 11.4 listaba seis decisiones de modelo pendientes
(M-01 a M-06) sin inclinarse por ninguna.

**Ahora (UT2).** Las dos que condicionaban la implementación están decididas y
documentadas como ADR:

- **ADR-001 · Disponibilidad por región.** Se descarta guardar la región
  directamente en el producto y se usa una tabla de disponibilidad
  `producto_region` con `disponible` y `tarifa_centimos` propios. Un producto
  puede ofrecerse en varias regiones a precios distintos y no ofrecerse en
  otras, que es justo el caso negativo que el enunciado pide demostrar.
- **ADR-002 · Configuración de un elemento.** Los atributos de cada elemento
  (vCPU, RAM, capacidad) se guardan como JSON en lugar de crear una columna
  por atributo, porque cada tipo de producto tiene atributos distintos y una
  tabla con columnas fijas quedaría medio vacía.

**Por qué.** Corrección de UT1, criterio «Modelo conceptual»: *«Algunas
decisiones del diseño quedan abiertas sin explicar por qué.»*

**Efecto en código y pruebas.** Migraciones
`2026_11_08_100500_create_producto_region_table.php` y
`2026_11_08_100900_create_elementos_arquitectura_table.php`, ambas con el
motivo comentado en el propio archivo.
`tests/Feature/CatalogoTest::test_t07_la_relacion_producto_region_guarda_disponibilidad_y_tarifa`
comprueba que el pivote guarda tarifa propia y que
`test_existe_un_producto_no_disponible_en_alguna_region` cubre el caso negativo.

---

### C-03 · Los riesgos de los módulos pasan de genéricos a fallos concretos

**Antes (UT1).** El mapa funcional describía los quince módulos con un riesgo
asociado a cada uno, redactado en términos amplios («pérdida de integridad»,
«inconsistencia de datos»).

**Ahora (UT2).** Los riesgos de los módulos implementados se expresan como un
fallo que puede reproducirse, con la medida que lo evita:

| Módulo | Fallo concreto | Medida y evidencia |
|---|---|---|
| Presupuestos | Se emite un presupuesto, el administrador sube la tarifa de un producto y el presupuesto emitido pasa a mostrar otro importe | Las líneas copian nombre, proveedor, región y tarifa al emitir. Test T09 |
| Presupuestos | Falla la escritura de la tercera línea y queda una cabecera con dos líneas y el carrito ya vaciado | Toda la emisión va en `DB::transaction`. Test T10 |
| Catálogo | Se selecciona un producto en una región donde el proveedor no lo ofrece | Comprobación en el servidor al añadir al carrito y regla R01. Test T03 |
| Requerimientos | Se guarda un requisito con un operador imposible (`region >= eu-madrid`) y el motor de reglas revienta al evaluarlo | Validación cruzada tipo/operador en `GuardarRequerimientoRequest`. Test T06 |
| Ficheros | Se escribe el archivo en disco, falla el registro en BD y queda un archivo huérfano | El controlador borra el archivo si la escritura en BD falla |
| Precios | Sumar importes con decimales hace que el total no cuadre con la suma de las líneas | Todos los importes son enteros en céntimos. Test T01 |

**Por qué.** Corrección de UT1, criterio «Mapa funcional y módulos»: *«En
algún caso, el riesgo indicado es demasiado general; explicad un fallo
concreto que podría producirse.»*

**Efecto en código y pruebas.** Los seis fallos de la tabla tienen su medida
implementada y cinco de ellos tienen test.

---

### C-04 · El cálculo de precios y las reglas salen de los modelos y de las vistas

**Antes (UT1).** El análisis de arquitectura decía que la lógica de negocio
iría en servicios, pero no concretaba cuáles ni qué haría cada uno.

**Ahora (UT2).** Existen tres servicios con una responsabilidad cada uno:

- `CalculadoraPrecios` · único sitio autorizado a calcular importes.
- `MotorReglas` · inventario de las seis reglas de negocio.
- `EmisorPresupuestos` · coordina validación, transacción y copia histórica.

Ninguno depende de Eloquent salvo el emisor, de modo que precios y reglas se
pueden probar sin base de datos. La traducción entre la base de datos y el
formato que entienden las reglas vive en un único punto,
`ConstructorEvaluable`.

**Por qué.** Corrección de UT1, criterio «Arquitectura Laravel inicial»:
*«Para mejorar, poned un ejemplo real de una tarea que hará cada parte de la
aplicación.»*

**Efecto en código y pruebas.** `app/Services/`. Las pruebas T01–T04 son
unitarias y no tocan la base de datos, lo que sólo es posible gracias a esta
separación.

---

### C-05 · Los actores se concretan en lo que pueden y lo que no pueden hacer

**Antes (UT1).** Los cuatro actores (cliente, arquitecto, auditor,
administrador) estaban descritos con su objetivo y sus funcionalidades.

**Ahora (UT2).** Se añade la columna de lo prohibido para cada uno, con la
advertencia honesta de que **en UT2 no hay autenticación**: la matriz describe
el diseño que se implementará en UT3, no algo que el código impida hoy.

| Actor | Puede | No puede |
|---|---|---|
| Cliente | Crear proyectos, requisitos y selecciones propias; emitir presupuestos | Editar el catálogo; ver proyectos de otros clientes |
| Arquitecto | Revisar y modificar arquitecturas; aprobarlas o rechazarlas | Cambiar precios del catálogo; borrar presupuestos emitidos |
| Auditor | Consultar todo el registro de auditoría y los presupuestos | Modificar ningún dato operativo |
| Administrador | Gestionar catálogo, proveedores, regiones, precios y reglas | Alterar un presupuesto ya emitido |

**Por qué.** Corrección de UT1, criterio «Actores»: *«Falta concretar qué
acciones puede hacer cada uno y cuáles le deben estar prohibidas.»*

**Efecto en código y pruebas.** Parcial. Lo único que el código impide hoy es
modificar un presupuesto emitido, porque sus líneas no tienen formulario de
edición y son copias, no referencias. El resto queda documentado como diseño
de UT3 y aparece así en el apartado de limitaciones.

---

### C-06 · Cada tarea de la planificación tiene una condición de terminada

**Antes (UT1).** La planificación repartía tareas y fechas, pero no decía
cuándo una tarea estaba acabada.

**Ahora (UT2).** Cada tarea se da por terminada cuando pasa una prueba
concreta, no cuando quien la hace lo considera. La correspondencia está en
`docs/ut2/reparto-ut2.md`: por ejemplo, «cálculo de precios» no está
terminada hasta que `CalculadoraPreciosTest` pasa en verde.

**Por qué.** Corrección de UT1, criterio «Planificación inicial»: *«Faltó
decir cómo sabréis que cada tarea está terminada: qué debe funcionar o qué
prueba habrá que superar.»*

**Efecto en código y pruebas.** Las diez pruebas T01–T10 son la definición de
terminado de los diez bloques funcionales de UT2.

---

### C-07 · Las versiones mínimas quedan fijadas y bloqueadas

**Antes (UT1).** El documento de entorno indicaba las versiones instaladas por
el equipo, pero no un mínimo exigible ni cómo evitar que las máquinas se
separasen.

**Ahora (UT2).** Se fija PHP 8.3 como mínimo (Laravel 13 admite de 8.3 a 8.5),
y la coincidencia entre máquinas se garantiza con `composer.lock` subido al
repositorio y `.env.example` como única fuente de las claves de configuración.
El README recoge la tabla de versiones y el procedimiento.

**Por qué.** Corrección de UT1, criterio «Tecnologías y justificación»:
*«Faltó indicar qué versiones mínimas vais a usar y cómo evitaréis diferencias
entre los equipos del grupo.»*

**Efecto en código y pruebas.** `composer.lock`, `.env.example` y el apartado 1
del `README.md`.

---

## 2. Qué se mantiene igual y por qué

- **El principio «la IA recomienda y la lógica de aplicación decide»** sigue
  intacto. En UT2 no hay IA conectada, pero la arquitectura ya lo respeta: el
  motor de reglas valida cualquier propuesta venga de donde venga, y el campo
  `origen` de un requerimiento admite el valor `ia` precisamente para poder
  distinguir después qué propuso la máquina.
- **MariaDB como motor**, confirmado por el profesorado para todo el
  grupo-clase. Sin cambios.
- **El flujo de ramas** `main` / `develop` / `test` con Pull Request
  obligatorio. Sin cambios.
- **Los cuatro actores y los quince módulos** del análisis de UT1 siguen
  siendo válidos; UT2 implementa un subconjunto y no redefine el alcance.

---

## 3. Requisitos de UT1 y su estado en UT2

| Requisito UT1 | Prioridad | Estado en UT2 | Evidencia o motivo |
|---|---|---|---|
| Catálogo de productos con tipos y atributos | Alta | Hecho | `CatalogoController`, `CatalogoTest` |
| Disponibilidad y precio por región | Alta | Hecho | Tabla `producto_region`, test T07 |
| Proyectos con presupuesto máximo | Alta | Hecho | `ProyectoController` |
| Requerimientos estructurados | Alta | Hecho | Tests T05 y T06 |
| Arquitecturas asociadas a un proyecto | Alta | Hecho | `ArquitecturaController` |
| Cálculo de precios en varias modalidades | Alta | Hecho | Tests T01 y T02 |
| Reglas de negocio con error y aviso | Alta | Hecho | Tests T03 y T04 |
| Selección persistente | Alta | Hecho | Tabla `lineas_carrito` |
| Presupuesto histórico inmutable | Alta | Hecho | Test T09 |
| Documentación adjunta a un proyecto | Media | Hecho | Test T08 |
| Registro de auditoría | Media | Parcial | Se registran las operaciones principales; no hay pantalla de consulta |
| Autenticación y permisos por rol | Alta | Futuro | No exigido en UT2 |
| Recomendación mediante IA | Alta | Futuro | No exigido en UT2 |
| Pagos y despliegue simulado | Media | Futuro | No exigido en UT2 |
| API REST | Baja | Futuro | No exigido en UT2 |
