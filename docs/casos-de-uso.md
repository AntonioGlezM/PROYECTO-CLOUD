# Casos de uso

**Proyecto:** CloudForge · Generación de infraestructuras Cloud con IA
**Módulo:** Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027
**Centro:** CIFP Villa de Agüimes
**Entrega UT1:** 14 de octubre de 2026

---

Este documento recoge los doce casos de uso del sistema. Cada uno indica su código, nombre, actor,
descripción, precondiciones, flujo principal, flujos alternativos y resultado, y se relaciona con los
requisitos funcionales y reglas de negocio que lo sustentan.

---

## Reparto y trazabilidad

| Código | Nombre | Actor | Redacta | Requisitos |
|---|---|---|---|---|
| CU01 | Registrarse y acceder a la plataforma | Cliente | Yassine | RF-01, RF-02, RNF-01 |
| CU02 | Crear un proyecto describiendo una necesidad | Cliente | Yassine | RF-10 |
| CU03 | Convertir la descripción en requerimientos estructurados | Cliente / Sistema | Yassine | RF-11, RF-12, RF-13, RF-14 |
| CU04 | Solicitar una recomendación de arquitectura a la IA | Cliente | Yeray | RF-15, RF-16, RF-21 |
| CU05 | Validar una arquitectura contra los requerimientos | Sistema | Yeray | RF-17, RF-18, RN-10, RN-12 |
| CU06 | Configurar un producto del catálogo | Cliente | Yeray | RF-05, RF-06, RF-09 |
| CU07 | Añadir productos configurados al carrito | Cliente | Alejandro | RF-22, RF-23, RN-01, RN-02 |
| CU08 | Generar un presupuesto a partir del carrito | Cliente | Alejandro | RF-25, RF-26, RF-27, RN-05, RN-06 |
| CU09 | Aceptar un presupuesto y confirmar el pedido | Cliente | Alejandro | RF-28, RF-29, RF-31, RN-03 |
| CU10 | Registrar el pago simulado de un pedido | Cliente | Antonio | RF-30, RNF-06, RN-04 |
| CU11 | Revisar y modificar una arquitectura propuesta | Arquitecto | Antonio | RF-19, RF-20 |
| CU12 | Consultar el registro de auditoría de un pedido | Auditor | Antonio | RF-35, RF-36, RF-37, RN-08, RN-11 |

**De reserva**, si sobra tiempo: *CU13 — Dar de alta un producto en el catálogo* (Administrador,
RF-07/RF-08) y *CU14 — Generar un despliegue simulado a partir de un pedido pagado* (Sistema,
RF-32/RF-33).

### Reglas de redacción aplicadas

1. Cada paso empieza por el **sujeto que actúa**: «El cliente…», «El sistema…».
2. Sin lenguaje de implementación: no aparece «se hace un `SELECT`» ni «se llama al método».
3. Cada caso se rastrea hasta al menos un requisito funcional de `requisitos.md`.
4. Los flujos alternativos se separan del principal.

---

## CU01 — Registrarse y acceder a la plataforma

| Campo | Contenido |
|---|---|
| **Código** | CU01 |
| **Nombre** | Registrarse y acceder a la plataforma |
| **Actor** | Cliente |
| **Descripción** | Un usuario nuevo crea su cuenta en CloudForge y accede por primera vez para poder empezar a trabajar con proyectos |
| **Precondiciones** | El usuario no tiene cuenta previa · dispone de una dirección de correo válida |
| **Resultado** | Existe un usuario con perfil de cliente, con la sesión iniciada y acceso a su área de proyectos |

**Flujo principal**

1. El usuario accede al formulario de registro.
2. El usuario introduce nombre, correo electrónico y contraseña.
3. El sistema comprueba que el correo no está ya registrado.
4. El sistema comprueba que la contraseña cumple los requisitos mínimos de seguridad.
5. El sistema crea la cuenta con perfil de cliente y almacena la contraseña cifrada.
6. El sistema registra el alta en auditoría.
7. El sistema inicia la sesión y muestra el área de proyectos, vacía.

**Flujos alternativos**

- *3a. El correo ya existe:* el sistema informa de que esa dirección ya tiene cuenta y ofrece
  iniciar sesión. No se crea nada.
- *4a. La contraseña es débil:* el sistema indica qué requisito no cumple y mantiene el resto de
  datos del formulario.

---

## CU02 — Crear un proyecto describiendo una necesidad

| Campo | Contenido |
|---|---|
| **Código** | CU02 |
| **Nombre** | Crear un proyecto describiendo una necesidad |
| **Actor** | Cliente |
| **Descripción** | El cliente da de alta un proyecto describiendo en lenguaje natural qué quiere conseguir, junto con sus restricciones de presupuesto, volumen y región |
| **Precondiciones** | El cliente está autenticado |
| **Resultado** | Existe un proyecto asociado al cliente, en estado borrador, listo para deducir requerimientos |

**Flujo principal**

1. El cliente pulsa «Nuevo proyecto».
2. El cliente introduce el nombre del proyecto y una descripción en lenguaje natural.
3. El cliente indica el presupuesto máximo mensual, los usuarios esperados, el nivel de seguridad
   deseado y la región o zona preferida.
4. El sistema comprueba que los campos obligatorios están presentes y que el presupuesto es un
   importe positivo.
5. El sistema crea el proyecto en estado borrador y lo asocia al cliente.
6. El sistema registra la creación en auditoría.
7. El sistema muestra el proyecto y ofrece deducir sus requerimientos.

**Flujos alternativos**

- *4a. Falta un campo obligatorio o el presupuesto no es válido:* el sistema señala el campo
  concreto y conserva lo ya escrito.

---

## CU03 — Convertir la descripción en requerimientos estructurados

| Campo | Contenido |
|---|---|
| **Código** | CU03 |
| **Nombre** | Convertir la descripción en requerimientos estructurados |
| **Actor** | Cliente (principal) · Sistema |
| **Descripción** | A partir de la descripción en lenguaje natural del proyecto, el sistema propone un conjunto de condiciones verificables que el cliente revisa y confirma |
| **Precondiciones** | El proyecto existe, pertenece al cliente y tiene descripción · el proyecto está en estado borrador |
| **Resultado** | El proyecto tiene un conjunto de requerimientos confirmados, cada uno con atributo, operador, valor, unidad, carácter obligatorio y origen |

**Flujo principal**

1. El cliente solicita deducir los requerimientos de su proyecto.
2. El sistema envía la descripción y los datos del proyecto al servicio de recomendación.
3. El sistema recibe una propuesta de requerimientos y descarta los que no correspondan a atributos
   conocidos del catálogo.
4. El sistema añade los requerimientos impuestos por norma que correspondan, por ejemplo residencia
   de datos en la Unión Europea.
5. El sistema muestra la lista propuesta, indicando el origen de cada requerimiento.
6. El cliente revisa la lista: modifica valores, marca requerimientos como obligatorios u
   opcionales, añade los que falten y elimina los que no procedan.
7. El cliente confirma la lista.
8. El sistema guarda los requerimientos asociados al proyecto y registra la operación en auditoría.

**Flujos alternativos**

- *2a. El servicio de recomendación no responde:* el sistema informa de la incidencia y ofrece al
  cliente introducir los requerimientos manualmente. El proyecto sigue siendo utilizable.
- *6a. El cliente define requerimientos contradictorios:* el sistema advierte del conflicto —por
  ejemplo, memoria mínima alta con presupuesto muy bajo— y pide revisarlos antes de confirmar.

---

## CU04 — Solicitar una recomendación de arquitectura a la IA

| Campo | Contenido |
|---|---|
| **Código** | CU04 |
| **Nombre** | Solicitar una recomendación de arquitectura a la IA |
| **Actor** | Cliente |
| **Descripción** | El cliente pide una propuesta de infraestructura para su proyecto. La IA la genera, pero la aplicación la filtra y la valida antes de mostrarla |
| **Precondiciones** | El proyecto tiene requerimientos confirmados · existe catálogo con productos activos |
| **Resultado** | Existe una arquitectura propuesta, validada, asociada al proyecto, con su explicación y con la consulta registrada |

**Flujo principal**

1. El cliente pulsa «Proponer arquitectura» en su proyecto.
2. El sistema reúne el contexto: requerimientos, presupuesto, región preferida y **el catálogo real
   de productos disponibles con sus precios**.
3. El sistema envía ese contexto al servicio de IA, sin incluir datos personales identificables del
   cliente.
4. El sistema recibe una propuesta estructurada: lista de productos con su configuración y cantidad,
   más una explicación en lenguaje llano.
5. El sistema **descarta de la propuesta todo producto cuyo código no exista en el catálogo**.
6. El sistema calcula el precio de la propuesta a partir de los precios del catálogo, nunca de los
   que pudiera haber devuelto la IA.
7. El sistema valida la propuesta contra los requerimientos del proyecto (CU05).
8. El sistema registra la entrada enviada, la propuesta recibida y la decisión tomada.
9. El sistema muestra la arquitectura con su coste mensual, su coste inicial y la explicación.

**Flujos alternativos**

- *4a. El servicio de IA no responde o agota el tiempo de espera:* el sistema informa y ofrece
  configurar la arquitectura manualmente desde el catálogo.
- *5a. Tras descartar productos inexistentes la propuesta queda incompleta:* el sistema lo indica y
  ofrece completarla manualmente o volver a solicitarla.
- *7a. La propuesta no supera la validación:* el sistema muestra qué requerimientos incumple y
  ofrece solicitar una nueva propuesta o corregirla a mano. **No se presenta como válida.**

> Este caso es el que materializa la frase «la IA recomienda y la lógica de aplicación decide».
> Los pasos 5, 6 y 7 son exactamente el punto donde decide Laravel.

---

## CU05 — Validar una arquitectura contra los requerimientos del proyecto

| Campo | Contenido |
|---|---|
| **Código** | CU05 |
| **Nombre** | Validar una arquitectura contra los requerimientos del proyecto |
| **Actor** | Sistema |
| **Descripción** | El sistema comprueba de forma automática si una arquitectura cumple los requerimientos del proyecto y si sus elementos son compatibles entre sí |
| **Precondiciones** | Existe una arquitectura con al menos un elemento · el proyecto tiene requerimientos confirmados |
| **Resultado** | La arquitectura queda marcada como válida o no válida; si no lo es, con el detalle de qué incumple |

**Flujo principal**

1. El sistema recupera los requerimientos del proyecto y los elementos de la arquitectura.
2. El sistema comprueba que todos los productos existen y están activos.
3. El sistema comprueba que todos los productos están disponibles en la región del proyecto.
4. El sistema comprueba las dependencias entre elementos: por ejemplo, que un servicio de backup
   tenga un recurso al que respaldar.
5. El sistema evalúa cada **requerimiento obligatorio** contra la arquitectura.
6. El sistema evalúa los **requerimientos opcionales** y anota cuáles se cumplen, sin descartar.
7. El sistema calcula el coste mensual total y lo compara con el presupuesto máximo del proyecto.
8. El sistema marca la arquitectura como válida si todos los obligatorios se cumplen, o como no
   válida en caso contrario.
9. El sistema registra el resultado de la validación.

**Flujos alternativos**

- *3a. Un producto no está disponible en la región:* la arquitectura se marca como no válida y se
  indica el producto y la región afectados.
- *5a. Un requerimiento obligatorio no se cumple:* la arquitectura se marca como no válida y se
  detalla el requerimiento, el valor exigido y el valor obtenido.
- *7a. El coste supera el presupuesto máximo:* si el presupuesto se definió como requerimiento
  obligatorio, la arquitectura se marca como no válida; si era orientativo, se avisa pero se
  permite continuar.

---

## CU06 — Configurar un producto del catálogo

| Campo | Contenido |
|---|---|
| **Código** | CU06 |
| **Nombre** | Configurar un producto del catálogo |
| **Actor** | Cliente |
| **Descripción** | El cliente ajusta los atributos de un producto —por ejemplo CPU, memoria y disco de una máquina virtual— y obtiene el precio correspondiente a esa configuración en esa región |
| **Precondiciones** | El cliente está autenticado · el producto existe, está activo y disponible en la región seleccionada |
| **Resultado** | Existe una configuración concreta del producto, válida, con su precio calculado |

**Flujo principal**

1. El cliente selecciona un producto del catálogo.
2. El sistema muestra los atributos configurables de su tipo, con los valores admitidos y los que
   son obligatorios.
3. El cliente selecciona la región y asigna un valor a cada atributo.
4. El sistema comprueba que todos los atributos obligatorios tienen valor y que cada valor está
   dentro de los admitidos.
5. El sistema calcula el precio correspondiente a esa combinación de producto, configuración y
   región.
6. El sistema muestra el resumen de la configuración con su precio y su periodicidad.

**Flujos alternativos**

- *3a. El producto no está disponible en la región elegida:* el sistema lo indica y ofrece las
  regiones donde sí lo está.
- *4a. Falta un atributo obligatorio o un valor no es admisible:* el sistema señala el atributo
  concreto y no permite continuar.

---

## CU07 — Añadir productos configurados al carrito

| Campo | Contenido |
|---|---|
| **Código** | CU07 |
| **Nombre** | Añadir productos configurados al carrito |
| **Actor** | Cliente |
| **Descripción** | El cliente incorpora al carrito de su proyecto un producto ya configurado, con su región y cantidad |
| **Precondiciones** | El cliente está autenticado y el proyecto es suyo · existe una configuración válida del producto (CU06) |
| **Resultado** | El carrito del proyecto contiene una línea más, con su configuración, región, cantidad y precio aplicado |

**Flujo principal**

1. El cliente pulsa «Añadir al carrito» sobre un producto configurado.
2. El cliente indica la cantidad.
3. El sistema localiza el carrito activo del proyecto, o lo crea si no existe.
4. El sistema comprueba que el producto sigue activo y disponible en la región del proyecto.
5. El sistema comprueba las dependencias: si el producto es de tipo backup, que exista en el carrito
   o en la arquitectura un recurso al que respaldar.
6. El sistema recupera el precio vigente de esa configuración en esa región.
7. El sistema añade la línea al carrito con el precio recuperado.
8. El sistema recalcula el total mensual y el total de pagos únicos, y los muestra por separado.

**Flujos alternativos**

- *4a. El producto ha sido retirado del catálogo:* el sistema informa y no lo añade.
- *5a. No existe un recurso al que asociar el backup:* el sistema explica la dependencia y sugiere
  añadir antes el recurso correspondiente.
- *7a. La línea ya existe con la misma configuración y región:* el sistema incrementa la cantidad en
  lugar de duplicar la línea.

> El precio se toma **siempre del catálogo**, nunca de un valor enviado por el cliente.

---

## CU08 — Generar un presupuesto a partir del carrito

| Campo | Contenido |
|---|---|
| **Código** | CU08 |
| **Nombre** | Generar un presupuesto a partir del carrito |
| **Actor** | Cliente |
| **Descripción** | El cliente convierte el contenido de su carrito en un presupuesto formal, con precios cerrados y fecha de caducidad |
| **Precondiciones** | El cliente está autenticado · el carrito pertenece a un proyecto suyo y contiene al menos una línea · todos los productos siguen activos y disponibles en la región del proyecto |
| **Resultado** | Existe un presupuesto en estado pendiente, con código, fecha de caducidad y precios congelados, que el cliente puede aceptar o rechazar |

**Flujo principal**

1. El cliente pulsa «Generar presupuesto» desde su carrito.
2. El sistema comprueba que todas las líneas siguen siendo válidas.
3. El sistema recupera el precio vigente de cada producto para su configuración y región.
4. El sistema calcula subtotal, impuestos, total recurrente mensual y total de pagos únicos.
5. El sistema crea el presupuesto en estado pendiente, le asigna código y fecha de caducidad, y
   **copia** en sus líneas los productos, configuraciones y precios aplicados.
6. El sistema registra la operación en auditoría.
7. El sistema muestra el presupuesto con su desglose por líneas y sus dos totales.

**Flujos alternativos**

- *2a. Un producto ha sido retirado o ha dejado de estar disponible:* el sistema lo señala y pide
  retirar o sustituir esa línea antes de generar el presupuesto.
- *3a. El precio ha cambiado desde que se añadió al carrito:* el sistema aplica el precio vigente y
  advierte expresamente al cliente del cambio antes de confirmar.

> Las líneas **copian** los datos en lugar de referenciar al catálogo: un presupuesto debe poder
> leerse dentro de dos años aunque el catálogo sea irreconocible (RN-05, decisión M-04).

---

## CU09 — Aceptar un presupuesto y confirmar el pedido

| Campo | Contenido |
|---|---|
| **Código** | CU09 |
| **Nombre** | Aceptar un presupuesto y confirmar el pedido |
| **Actor** | Cliente |
| **Descripción** | El cliente acepta un presupuesto vigente, lo que genera el pedido correspondiente |
| **Precondiciones** | El presupuesto existe, pertenece a un proyecto del cliente, está en estado pendiente y **no ha caducado** · no existe ya un pedido asociado |
| **Resultado** | El presupuesto queda aceptado y existe un pedido en estado confirmado, con los precios del presupuesto |

**Flujo principal**

1. El cliente abre un presupuesto pendiente y revisa su desglose.
2. El cliente pulsa «Aceptar presupuesto».
3. El sistema comprueba que el presupuesto no ha caducado.
4. El sistema comprueba que el presupuesto no tiene ya un pedido asociado.
5. El sistema cambia el estado del presupuesto a aceptado y congela definitivamente sus precios.
6. El sistema crea el pedido en estado confirmado, copiando las líneas y los totales.
7. El sistema registra la aceptación y la creación del pedido en auditoría.
8. El sistema muestra el pedido y ofrece proceder al pago.

**Flujos alternativos**

- *3a. El presupuesto ha caducado:* el sistema lo indica, no lo acepta y ofrece generar uno nuevo
  con los precios actuales.
- *4a. Ya existe un pedido para ese presupuesto:* el sistema muestra el pedido existente en lugar de
  crear otro.
- *2a. El cliente rechaza el presupuesto:* el estado pasa a rechazado, se registra en auditoría y no
  se crea ningún pedido.

> Los pasos 5 y 6 forman una **única operación atómica**: o se completan los dos o no se hace
> ninguno (RNF-06).

---

## CU10 — Registrar el pago simulado de un pedido

| Campo | Contenido |
|---|---|
| **Código** | CU10 |
| **Nombre** | Registrar el pago simulado de un pedido |
| **Actor** | Cliente |
| **Descripción** | El cliente completa el pago simulado de un pedido confirmado, lo que habilita su despliegue. No interviene ninguna pasarela de pago real |
| **Precondiciones** | El pedido existe, es del cliente y está en estado confirmado · no tiene todavía un pago válido registrado |
| **Resultado** | El pedido queda en estado pagado, con un pago asociado, y es apto para desplegarse |

**Flujo principal**

1. El cliente abre un pedido confirmado y pulsa «Pagar».
2. El sistema muestra el importe a abonar, separando el total inicial del coste mensual.
3. El cliente selecciona un método de pago simulado y confirma.
4. El sistema comprueba que el pedido sigue en estado confirmado y que no tiene ya un pago válido.
5. El sistema registra el pago con su importe, fecha, método y referencia.
6. El sistema cambia el estado del pedido a pagado.
7. El sistema registra la operación en auditoría.
8. El sistema confirma el pago y ofrece generar el despliegue.

**Flujos alternativos**

- *4a. El pedido ya tiene un pago válido:* el sistema no registra un segundo pago y muestra el
  existente.
- *5a. El registro del pago falla:* el sistema revierte la operación completa, el pedido vuelve a
  estado confirmado y se deja constancia del intento fallido en auditoría.
- *3a. El cliente cancela:* el pedido permanece en estado confirmado, sin pago.

> Los pasos 5 y 6 son **atómicos**. El escenario a evitar es un pedido marcado como pagado sin pago
> registrado, o al revés (RNF-06, RN-04).

---

## CU11 — Revisar y modificar una arquitectura propuesta

| Campo | Contenido |
|---|---|
| **Código** | CU11 |
| **Nombre** | Revisar y modificar una arquitectura propuesta |
| **Actor** | Arquitecto |
| **Descripción** | El arquitecto revisa técnicamente una arquitectura, sustituye o ajusta elementos si lo considera necesario, y la aprueba o la rechaza con una observación |
| **Precondiciones** | El arquitecto está autenticado y tiene asignado el proyecto · existe una arquitectura en estado propuesta o en revisión |
| **Resultado** | La arquitectura queda en una versión nueva, revalidada, con estado aprobada o rechazada y una observación técnica asociada |

**Flujo principal**

1. El arquitecto abre un proyecto asignado y selecciona la arquitectura a revisar.
2. El sistema muestra la arquitectura, los requerimientos del proyecto, el resultado de la
   validación y, si procede, la explicación generada por la IA.
3. El arquitecto sustituye productos, ajusta configuraciones, cambia cantidades o elimina elementos.
4. El sistema crea una versión nueva de la arquitectura conservando la anterior.
5. El sistema revalida la arquitectura modificada (CU05) y recalcula su coste.
6. El arquitecto redacta una observación técnica justificando los cambios.
7. El arquitecto aprueba o rechaza la arquitectura.
8. El sistema registra la revisión, los cambios y la decisión en auditoría.
9. El sistema notifica al cliente que su arquitectura ha sido revisada.

**Flujos alternativos**

- *5a. La arquitectura modificada no supera la validación:* el sistema lo indica y no permite
  aprobarla hasta que cumpla los requerimientos obligatorios.
- *3a. El arquitecto no cambia nada:* no se crea versión nueva; solo se registra la aprobación o el
  rechazo con su observación.
- *1a. La arquitectura ya está aceptada por el cliente:* el sistema la muestra en modo lectura y no
  permite modificarla.

> Modificar genera **una versión nueva** en lugar de sobrescribir: así se puede comparar qué propuso
> la IA con qué aprobó finalmente una persona, que es justo lo que el auditor necesita ver.

---

## CU12 — Consultar el registro de auditoría de un pedido

| Campo | Contenido |
|---|---|
| **Código** | CU12 |
| **Nombre** | Consultar el registro de auditoría de un pedido |
| **Actor** | Auditor |
| **Descripción** | El auditor reconstruye el histórico completo de un pedido: quién hizo cada acción, cuándo, con qué precios y en qué regiones quedaron los recursos |
| **Precondiciones** | El auditor está autenticado con perfil de auditor · el pedido existe |
| **Resultado** | El auditor dispone del histórico completo del pedido y puede dejar constancia de un incumplimiento si lo detecta. **No se modifica ningún dato operativo** |

**Flujo principal**

1. El auditor accede al módulo de auditoría y busca el pedido por su código.
2. El sistema muestra todas las entradas relacionadas con ese pedido, ordenadas cronológicamente:
   generación del presupuesto, aceptación, confirmación, pago y despliegue.
3. El sistema muestra, para cada entrada, el usuario que la originó, la fecha y los valores antes y
   después del cambio.
4. El auditor consulta los precios congelados del presupuesto y los compara con los del catálogo en
   esa fecha.
5. El auditor consulta las regiones de los recursos desplegados y comprueba si respetan los
   requerimientos de residencia de datos del proyecto.
6. El auditor consulta, si existe, la recomendación de IA asociada y la decisión que tomó la
   aplicación sobre ella.
7. El auditor marca un incumplimiento y redacta una observación si detecta una irregularidad.
8. El sistema registra la consulta y la observación del auditor.

**Flujos alternativos**

- *2a. No existen entradas para ese pedido:* el sistema lo indica. La ausencia de registro es en sí
  misma un hallazgo de auditoría y debe poder señalarse.
- *5a. Un recurso está fuera de la zona exigida:* el auditor marca el incumplimiento, que queda
  asociado al pedido y visible para el administrador.
- *7a. El auditor intenta modificar un dato operativo:* el sistema lo impide. El perfil de auditor
  solo lee (RN-08).

> Las entradas de auditoría **no se modifican ni se borran** (RN-11). Un registro alterable no sirve
> como prueba de nada.
