# Problema, objetivos, actores y requisitos

**Proyecto:** CloudForge · Generación de infraestructuras Cloud con IA
**Módulo:** Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027
**Centro:** CIFP Villa de Agüimes
**Entrega UT1:** 14 de octubre de 2026

---

Este documento recoge el análisis funcional de la aplicación: el problema que resuelve y el valor
que aporta, sus objetivos y su alcance, los cuatro actores del sistema, y los requisitos funcionales,
no funcionales y reglas de negocio que debe cumplir.

---

## 1. Problema que se pretende resolver

Cuando una persona o una pequeña empresa necesita poner una aplicación en producción, sabe
describir **qué quiere conseguir** —«una web para mi academia, con unos 500 alumnos, que no se
caiga y que no me cueste una fortuna»— pero no sabe traducir eso a **qué debe contratar**.

La dificultad no está en que haya muchos productos, sino en que las decisiones están entrelazadas.
Para configurar una infraestructura mínimamente razonable hay que resolver a la vez:

| Decisión | Por qué no se puede tomar aislada |
|---|---|
| CPU y RAM | Dependen de los usuarios concurrentes esperados, que el cliente rara vez sabe estimar |
| Almacenamiento | Condiciona el tipo de disco, y este el rendimiento de la base de datos |
| Región | Limita qué productos existen, a qué precio, y determina bajo qué legislación quedan los datos |
| Disponibilidad | Subir de un servidor a dos multiplica el coste y obliga a añadir balanceo |
| Seguridad | Un firewall o un certificado pueden ser obligatorios según el tipo de aplicación |
| Backup | Necesita saber antes qué recurso hay que respaldar y con qué frecuencia |
| Base de datos | El motor condiciona la versión, la replicación y el tamaño necesario |
| Presupuesto | Es la restricción que afecta a todas las anteriores a la vez |
| Monitorización | Suele olvidarse hasta que hay una caída |
| Servicios de IA | Facturan por uso, con un coste difícil de prever |

A esto se añade que **el precio real solo aparece al final del proceso**: las calculadoras de los
grandes proveedores obligan a configurar todo el detalle antes de mostrar una cifra. El resultado
habitual es uno de estos dos:

- **Sobredimensionar «por si acaso»**, pagando durante meses recursos que no se usan.
- **Infradimensionar**, y descubrir el problema cuando la aplicación empieza a fallar en producción.

En ambos casos el usuario no tiene forma de saber si lo que ha elegido es adecuado hasta que ya lo
ha contratado. Falta una pieza intermedia entre «sé lo que necesito conseguir» y «sé lo que tengo
que comprar».

## 2. Valor aportado por la aplicación

Comparación con una tienda online convencional:

| Aspecto | Tienda convencional | CloudForge |
|---|---|---|
| Unidad de venta | Un producto cerrado | Un producto **configurable**: «VM» no es un artículo, es una familia de combinaciones de CPU, RAM y disco |
| Precio | Fijo por artículo | Depende de la **configuración y de la región**, y es **recurrente** |
| Carrito | Suma de precios independientes | Conjunto de elementos con **dependencias entre sí** (un backup necesita algo que respaldar) |
| Validación | Comprobar stock | Comprobar **compatibilidad, disponibilidad regional y cumplimiento de requisitos** |
| Resultado | Un paquete que se envía | Una **arquitectura** que debe sostenerse en conjunto |
| Ayuda al usuario | Buscador y filtros | **Recomendación** a partir de una necesidad descrita en lenguaje natural |
| Tras la compra | Envío y seguimiento | **Despliegue simulado** con recursos que tienen ciclo de vida |

De esas diferencias salen las cinco funciones que justifican la plataforma: **configuración guiada,
validación, cálculo de precios, recomendación y simulación del ciclo completo de contratación.**

### 2.1 Fuentes investigadas

| Fuente | Qué observar | Observación del equipo |
|---|---|---|
| DigitalOcean Marketplace | Categorías, fichas de servicio, filtros, despliegue en un clic | `Catálogo claro, organizado por categorías (bases de datos, CMS, desarrollo, etc.) con buscador y filtros. Cada ficha resume qué incluye, versión, requisitos y un único botón de despliegue. Aplicable: fichas cortas y homogéneas, y un flujo de despliegue de pocos pasos que reduzca la fricción para usuarios sin perfil técnico.` |
| Google Cloud Pricing Calculator | Cómo se configura un recurso y cómo se presenta el desglose mensual | `El usuario añade un producto, ajusta parámetros (tipo de máquina, región, uso) y el coste se recalcula al instante. Muestra el total mensual y el detalle por recurso. Aplicable: cálculo en tiempo real mientras se configura, sin esperar a un paso final.` |
| Azure Pricing Calculator | Selección de productos, regiones, cantidades y resumen del presupuesto | `Flujo basado en buscar un producto, añadirlo a una estimación y editar región, nivel y cantidad. El resumen agrupa todo y permite exportar. Aplicable: separar “elegir recurso” de “ajustar cantidades” y ofrecer un resumen exportable del presupuesto.` |
| AWS Pricing Calculator | Agrupación de recursos por arquitectura; coste inicial frente a mensual | `Permite agrupar servicios en una estimación y distingue coste inicial (upfront) de coste mensual y anual. Aplicable: mostrar por separado lo que se paga una vez y lo recurrente, y agrupar recursos por arquitectura o proyecto.` |
| AWS Architecture Center | Diagramas, casos de uso y criterios de validación | `Biblioteca de diagramas de referencia por caso de uso, con descripción, servicios implicados y buenas prácticas (Well-Architected). Aplicable: acompañar cada arquitectura de un diagrama, un caso de uso típico y una lista de criterios que la validen.` |
| Azure Architecture Center | Arquitecturas de referencia, patrones y decisiones tecnológicas | `Combina arquitecturas de referencia con guías de patrones de diseño y comparativas para elegir tecnología (por ejemplo, qué servicio de cómputo usar). Aplicable: incluir una breve justificación de por qué se elige cada componente.` |
| Google Cloud Architecture Center | Reglas de validación: seguridad, eficiencia, resiliencia, costes, topologías regionales | `Organiza las guías en pilares (seguridad, fiabilidad, coste, rendimiento, excelencia operativa) y describe topologías zonales, regionales y multirregión. Aplicable: base para nuestras reglas de validación, por ejemplo “recurso público sin firewall” o “base de datos sin copia de seguridad”.` |
| AWS Service Catalog | Catálogo controlado de recursos desplegables sin tocar la infraestructura | `Los administradores definen productos aprobados y los usuarios solo despliegan lo permitido, sin acceso directo a la infraestructura subyacente. Aplicable: separar el rol que define arquitecturas aprobadas del rol que solo las despliega, con parámetros limitados.` |
| Coolify | Organización por proyectos, entornos, servidores y recursos; estados y cancelación | `Jerarquía proyecto → entorno → recursos, con servidores asociados. Muestra estados de cada recurso (en ejecución, detenido, en despliegue, error) y permite ver logs y cancelar un despliegue en curso. Aplicable: modelo de organización y estados visibles del despliegue, incluida la cancelación.` |
| Terraform | Cómo una arquitectura aprobada se convierte en infraestructura gestionable | `Describe la infraestructura de forma declarativa y el flujo plan → apply permite previsualizar cambios antes de ejecutarlos. El estado guarda lo desplegado. Aplicable: traducir cada arquitectura aprobada a una plantilla reproducible y mostrar un resumen de cambios antes de confirmar.` |

## 3. Objetivo principal

> Permitir que un usuario sin conocimientos profundos de Cloud describa una necesidad tecnológica
> en lenguaje natural y obtenga una infraestructura configurada, validada y presupuestada que pueda
> contratar de forma simulada, manteniendo siempre la decisión final en la lógica de la aplicación
> y no en la inteligencia artificial.

## 4. Objetivos secundarios

Los objetivos secundarios son:

| # | Objetivo | Cubre |
|---|---|---|
| OS-01 | Ofrecer un catálogo de productos y servicios Cloud configurable por tipo, proveedor y región | Configuración |
| OS-02 | Convertir una descripción en lenguaje natural en requerimientos estructurados y verificables | Configuración |
| OS-03 | Proponer arquitecturas que respondan a los requerimientos de un proyecto | Recomendaciones |
| OS-04 | Validar que una arquitectura es coherente, compatible y cumple los requerimientos obligatorios | Validación |
| OS-05 | Controlar el acceso mediante autenticación y autorización por perfiles | Seguridad |
| OS-06 | Calcular precios de forma íntegra en el servidor, con desglose recurrente y único | Precios |
| OS-07 | Generar presupuestos con pre]cios congelados y fecha de caducidad | Presupuestos |
| OS-08 | Gestionar el ciclo carrito → presupuesto → pedido → pago simulado | Contratación |
| OS-09 | Transformar un pedido pagado en un despliegue simulado con sus recursos y estados | Infraestructura simulada |
| OS-10 | Registrar en auditoría las acciones relevantes para poder reconstruir qué ocurrió | Seguridad |

## 5. Alcance inicial y fuera de alcance

| Incluido en el alcance | Fuera de alcance / fases posteriores |
|---|---|
| Catálogo de productos y servicios Cloud con configuración por tipo | Despliegue real de máquinas en AWS, Azure, Google u otros proveedores |
| Proveedores y regiones, con disponibilidad y precio por región | Integración con las APIs reales de los proveedores |
| Proyectos con descripción, presupuesto y restricciones | Pasarela de pago real |
| Requerimientos estructurados y verificables | Facturación fiscal, impuestos reales o contabilidad |
| Arquitecturas propuestas y validadas | Contratos jurídicos con proveedores |
| Carrito, presupuestos, pedidos y pago **simulado** | Gestión real de incidencias con soporte humano |
| Despliegues y recursos **simulados** con sus estados | Monitorización real de recursos en funcionamiento |
| Perfiles de usuario, autenticación y autorización | Aplicación móvil |
| Registro de auditoría | Internacionalización a varios idiomas |
| Recomendaciones de IA **con validación posterior obligatoria** | Que la IA tome decisiones vinculantes |

---

## 6. Actores del sistema

### 6.1 CLIENTE

Cliente de la plataforma. Es el actor principal y el que recorre el ciclo completo.

| Campo | Contenido |
|---|---|
| **Objetivo** | Obtener una infraestructura que cubra su necesidad real dentro de su presupuesto, sin necesitar conocimientos profundos de Cloud |
| **Funcionalidades** | Registrarse y acceder · crear y gestionar sus proyectos · describir necesidades en lenguaje natural · revisar los requerimientos estructurados que se deducen · solicitar recomendaciones · configurar productos del catálogo · gestionar el carrito · solicitar presupuestos · aceptar o rechazar un presupuesto · confirmar pedidos · registrar el pago simulado · consultar sus despliegues y recursos |
| **Información consultada** | Catálogo de productos, proveedores, regiones y precios · sus propios proyectos, requerimientos y arquitecturas · sus presupuestos, pedidos, pagos y despliegues |
| **Información modificada** | Sus propios proyectos, requerimientos y carrito · el estado de sus presupuestos y pedidos · sus datos de cuenta |

### 6.2 ARQUITECTO

Perfil técnico encargado de revisar o modificar arquitecturas.

| Campo | Contenido |
|---|---|
| **Objetivo** | Garantizar que lo que se va a contratar es técnicamente correcto antes de que el cliente se comprometa |
| **Funcionalidades** | Consultar los proyectos asignados · revisar arquitecturas propuestas, vengan de la IA o del propio cliente · sustituir productos por alternativas · ajustar configuraciones · comprobar compatibilidades · aprobar o rechazar una arquitectura · dejar observaciones técnicas justificadas |
| **Información consultada** | Proyectos y sus requerimientos · catálogo completo con precios y disponibilidad regional · reglas de compatibilidad · propuestas y explicaciones generadas por la IA · histórico de versiones de la arquitectura |
| **Información modificada** | Composición de las arquitecturas (productos, configuraciones, cantidades) · estado de revisión · observaciones técnicas |

### 6.3 AUDITOR

Perfil especializado en seguridad y revisión.

| Campo | Contenido |
|---|---|
| **Objetivo** | Verificar que lo contratado y desplegado cumple las normas de seguridad y de residencia de datos, y poder reconstruir qué ocurrió y quién lo hizo |
| **Funcionalidades** | Consultar el registro de auditoría · revisar pedidos, pagos y despliegues · comprobar en qué regiones están los recursos · verificar niveles de seguridad y políticas de backup · emitir informes de revisión · marcar incumplimientos |
| **Información consultada** | Registro de auditoría completo · pedidos, presupuestos y despliegues · regiones y zonas de los recursos · configuraciones de seguridad · decisiones registradas de la IA |
| **Información modificada** | Únicamente observaciones de auditoría y marcas de incumplimiento. **No modifica ningún dato operativo del sistema** |

### 6.4 ADMINISTRADOR

Administrador de la plataforma.

| Campo | Contenido |
|---|---|
| **Objetivo** | Mantener la plataforma operativa y su catálogo actualizado y coherente |
| **Funcionalidades** | Gestionar usuarios y asignar roles · alta, modificación y retirada de productos · gestionar tipos de producto y sus atributos · gestionar proveedores y regiones · definir disponibilidad por región · actualizar precios · definir reglas de compatibilidad · supervisar despliegues · consultar auditoría |
| **Información consultada** | Todo el sistema |
| **Información modificada** | Catálogo, tipos y atributos · proveedores y regiones · precios y disponibilidad · reglas de compatibilidad · usuarios y roles |

### 6.5 Relaciones entre actores

```
                 ┌──────────────────┐
                 │  ADMINISTRADOR   │
                 │ mantiene el      │
                 │ catálogo         │
                 └────────┬─────────┘
                          │ provee productos, precios y reglas
                          ▼
┌───────────┐  crea   ┌──────────────┐  revisa   ┌─────────────┐
│  CLIENTE  │────────▶│  PROYECTO /  │◀──────────│ ARQUITECTO  │
│           │         │ ARQUITECTURA │           │             │
└───────────┘         └──────┬───────┘           └─────────────┘
                             │ se contrata y despliega
                             ▼
                      ┌─────────────┐   observa   ┌──────────┐
                      │   PEDIDO /  │────────────▶│ AUDITOR  │
                      │  DESPLIEGUE │   (solo     │          │
                      └─────────────┘   lectura)  └──────────┘
```

**el cliente crea**, el **arquitecto revisa**, el **administrador provee** lo que
ambos usan, y el **auditor observa** el resultado de los tres sin alterarlo.

---

## 7. Requisitos funcionales (RF)

Los requisitos funcionales son:

### Usuarios y acceso

| Código | Requisito | Prioridad |
|---|---|---|
| RF-01 | El sistema permitirá a un usuario registrarse aportando nombre, correo electrónico y contraseña | Alta |
| RF-02 | El sistema permitirá a un usuario registrado iniciar y cerrar sesión | Alta |
| RF-03 | El sistema asignará a cada usuario un único perfil: cliente, arquitecto, auditor o administrador | Alta |
| RF-04 | El sistema impedirá que un usuario acceda a recursos que no le corresponden según su perfil | Alta |

### Catálogo

| Código | Requisito | Prioridad |
|---|---|---|
| RF-05 | El sistema mostrará el catálogo de productos filtrable por tipo, proveedor y región | Alta |
| RF-06 | El sistema mostrará, para cada producto, sus atributos configurables y los valores admitidos | Alta |
| RF-07 | El sistema permitirá al administrador dar de alta, modificar y retirar productos del catálogo | Alta |
| RF-08 | El sistema permitirá al administrador definir en qué regiones está disponible cada producto y a qué precio | Alta |
| RF-09 | El sistema mostrará el precio de un producto correspondiente a la región y configuración seleccionadas | Alta |

### Proyectos y requerimientos

| Código | Requisito | Prioridad |
|---|---|---|
| RF-10 | El sistema permitirá a un cliente crear un proyecto con nombre, descripción en lenguaje natural, presupuesto máximo mensual, usuarios esperados, nivel de seguridad y región preferida | Alta |
| RF-11 | El sistema generará, a partir de la descripción del proyecto, una propuesta de requerimientos estructurados con atributo, operador, valor y unidad | Alta |
| RF-12 | El sistema permitirá al cliente revisar, modificar, añadir y eliminar requerimientos antes de confirmarlos | Alta |
| RF-13 | El sistema distinguirá entre requerimientos obligatorios y opcionales | Alta |
| RF-14 | El sistema registrará el origen de cada requerimiento: cliente, deducido por la IA, o impuesto por norma | Media |

### Arquitecturas y recomendación

| Código | Requisito | Prioridad |
|---|---|---|
| RF-15 | El sistema permitirá solicitar una recomendación de arquitectura a partir de los requerimientos del proyecto | Alta |
| RF-16 | El sistema descartará de la propuesta recibida cualquier producto que no exista en el catálogo | Alta |
| RF-17 | El sistema validará toda arquitectura contra los requerimientos obligatorios del proyecto antes de mostrarla | Alta |
| RF-18 | El sistema informará, cuando una arquitectura no sea válida, de qué requerimiento incumple y por qué | Alta |
| RF-19 | El sistema permitirá al arquitecto modificar una arquitectura propuesta y volver a validarla | Alta |
| RF-20 | El sistema permitirá al arquitecto aprobar o rechazar una arquitectura dejando una observación | Media |
| RF-21 | El sistema propondrá alternativas más económicas que sigan cumpliendo los requerimientos obligatorios | Media |

### Carrito, presupuestos y pedidos

| Código | Requisito | Prioridad |
|---|---|---|
| RF-22 | El sistema permitirá añadir al carrito productos con una configuración concreta, una región y una cantidad | Alta |
| RF-23 | El sistema impedirá añadir al carrito un producto no disponible en la región del proyecto | Alta |
| RF-24 | El sistema permitirá modificar la cantidad y la configuración de una línea del carrito, y eliminarla | Alta |
| RF-25 | El sistema generará un presupuesto a partir del carrito, con subtotal, impuestos, total recurrente mensual y total de pagos únicos | Alta |
| RF-26 | El sistema asignará a cada presupuesto un código, una fecha de creación y una fecha de caducidad | Alta |
| RF-27 | El sistema conservará en el presupuesto los precios vigentes en el momento de su generación | Alta |
| RF-28 | El sistema permitirá al cliente aceptar o rechazar un presupuesto no caducado | Alta |
| RF-29 | El sistema generará un pedido al aceptarse un presupuesto | Alta |
| RF-30 | El sistema registrará un pago simulado asociado a un pedido | Alta |
| RF-31 | El sistema impedirá confirmar dos veces el mismo pedido | Alta |

### Despliegues y auditoría

| Código | Requisito | Prioridad |
|---|---|---|
| RF-32 | El sistema generará un despliegue simulado a partir de un pedido pagado | Alta |
| RF-33 | El sistema creará un recurso simulado por cada línea del pedido, con su estado y su región | Alta |
| RF-34 | El sistema permitirá al cliente consultar el estado de sus despliegues y recursos | Media |
| RF-35 | El sistema registrará en auditoría el usuario, la acción, la entidad afectada y la fecha de toda operación relevante | Alta |
| RF-36 | El sistema permitirá al auditor consultar y filtrar el registro de auditoría | Alta |
| RF-37 | El sistema impedirá modificar o eliminar entradas del registro de auditoría | Alta |

## 8. Requisitos no funcionales (RNF)

| Código | Requisito | Categoría |
|---|---|---|
| RNF-01 | Las contraseñas se almacenarán cifradas mediante una función de hash resistente, nunca en texto plano | Seguridad |
| RNF-02 | Toda la lógica de cálculo de precios se ejecutará en el servidor; el sistema no aceptará ningún importe enviado desde el cliente | Seguridad |
| RNF-03 | El cálculo de un presupuesto de hasta 20 líneas se resolverá en menos de 2 segundos | Rendimiento |
| RNF-04 | El listado del catálogo se mostrará paginado y responderá en menos de 1 segundo con hasta 1.000 productos | Rendimiento |
| RNF-05 | Los importes monetarios se calcularán con aritmética de precisión decimal, nunca en coma flotante | Fiabilidad |
| RNF-06 | La operación de confirmar un pedido y registrar su pago será atómica: o se completa entera o no deja rastro parcial | Fiabilidad |
| RNF-07 | La aplicación será utilizable desde navegador en escritorio sin instalar software adicional | Usabilidad |
| RNF-08 | Los mensajes de error indicarán qué ha fallado y qué puede hacer el usuario, sin exponer detalles internos del sistema | Usabilidad |
| RNF-09 | La lógica de negocio residirá en servicios independientes de los controladores, para poder reutilizarse y probarse de forma aislada | Mantenibilidad |
| RNF-10 | El sistema no enviará al proveedor de IA datos personales identificables del cliente | Privacidad |
| RNF-11 | El sistema permitirá identificar en qué zona geográfica queda alojado cada recurso de un despliegue | Privacidad / RGPD |
| RNF-12 | El código fuente no contendrá credenciales, tokens ni claves; la configuración sensible se cargará desde variables de entorno | Seguridad |
| RNF-13 | El sistema seguirá siendo operativo, con el camino manual de configuración, si el proveedor de IA no responde | Disponibilidad |

## 9. Reglas de negocio (RN)

Restricciones del dominio que se cumplen siempre, las implemente quien las implemente.

| Código | Regla | Afecta a |
|---|---|---|
| RN-01 | Un producto solo puede añadirse al carrito si está disponible en la región del proyecto | RF-23 |
| RN-02 | No puede contratarse un servicio de backup sin un recurso al que respaldar | RF-22 |
| RN-03 | Un presupuesto caduca transcurridos los días establecidos y, una vez caducado, no puede aceptarse | RF-26, RF-28 |
| RN-04 | Un pedido solo puede desplegarse si su pago simulado está registrado | RF-32 |
| RN-05 | Un presupuesto aceptado conserva los precios aplicados en el momento de su generación, aunque el catálogo cambie después | RF-27 |
| RN-06 | El total de un presupuesto se calcula siempre en el servidor a partir de los precios del catálogo | RNF-02 |
| RN-07 | Un cliente solo accede a sus propios proyectos, presupuestos, pedidos y despliegues | RF-04 |
| RN-08 | El auditor nunca modifica datos operativos del sistema | RF-36 |
| RN-09 | Toda recomendación de la IA queda registrada junto con la propuesta generada y la decisión que tomó la aplicación | RF-35 |
| RN-10 | Si un proyecto exige residencia de datos en la Unión Europea, ninguna arquitectura con recursos fuera de la UE puede validarse | RF-17 |
| RN-11 | Las entradas del registro de auditoría no se modifican ni se borran | RF-37 |
| RN-12 | Una arquitectura que incumpla un requerimiento obligatorio no puede presentarse como válida al cliente | RF-17, RF-18 |
| RN-13 | Un producto retirado del catálogo deja de poder añadirse al carrito, pero sigue siendo válido en presupuestos y pedidos anteriores | RF-07 |
| RN-14 | La moneda es única por presupuesto: no se mezclan divisas en un mismo documento | RF-25 |
| RN-15 | Un pedido confirmado no puede modificarse; solo cancelarse y rehacerse desde un nuevo presupuesto | RF-31 |

> **Diferencia entre RF y RN**, por si cae en la exposición: un **requisito funcional** describe algo
> que el sistema *hace* y podría implementarse de varias formas. Una **regla de negocio** describe
> una restricción del dominio que es cierta con independencia de cómo se implemente, e incluso de si
> hay un sistema informático. RN-05 seguiría siendo cierta si los presupuestos se hicieran en papel.

## 10. Ambigüedades y decisiones pendientes

| # | Cuestión abierta | Por qué importa | Propuesta de partida |
|---|---|---|---|
| A-01 | ¿Cuántos días dura un presupuesto antes de caducar? | Condiciona RN-03 y la experiencia del cliente | 30 días naturales |
| A-02 | ¿Puede un cliente tener varios proyectos activos a la vez? | Afecta a la relación USUARIO–PROYECTO del modelo | Sí, sin límite |
| A-03 | ¿Se permite modificar un pedido confirmado? | Afecta a RN-15 y a la atomicidad | No: solo cancelar y rehacer |
| A-04 | ¿Quién asigna un arquitecto a un proyecto: el administrador, el cliente, o es automático? | Define el flujo de revisión | Pendiente |
| A-05 | ¿La validación de una arquitectura es bloqueante o solo informativa? | Decide si el cliente puede contratar algo que incumple un opcional | Bloqueante para obligatorios, informativa para opcionales |
| A-06 | ¿Los impuestos son un porcentaje fijo o dependen de la región? | Afecta al cálculo del presupuesto | Porcentaje fijo configurable en esta fase |
| A-07 | ¿Un cliente puede ser también arquitecto? | Decide si el rol es único o múltiple | Rol único (RF-03) |

> Reconocer lo que aún no está decidido **no resta nota**: el apartado 8.4 de la plantilla lo pide
> expresamente. Lo que resta es fingir que todo está cerrado y luego no saber responder.

