# Arquitectura: módulos, dominio Cloud, modelo de datos y Laravel

**Proyecto:** CloudForge · Generación de infraestructuras Cloud con IA
**Módulo:** Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027
**Centro:** CIFP Villa de Agüimes
**Responsable:** Yeray (Arquitecto de dominio y datos)

---

En este documento explicamos cómo vamos a organizar CloudForge por dentro: qué módulos tiene, cómo
funciona el catálogo Cloud, qué datos hay que guardar y cómo se reparte el trabajo entre las
carpetas de Laravel. Es un diseño inicial. Todavía no hay código: se programará a partir de UT2.

Los códigos RF, RNF y RN que aparecen son los de [`requisitos.md`](requisitos.md).

---

## 1. Mapa funcional

Hemos dividido la aplicación en 15 módulos, agrupados en 4 bloques según la parte del proceso que
cubren:

| Bloque | Módulos |
|---|---|
| Acceso | MOD-01 Usuarios · MOD-02 Auditoría |
| Catálogo | MOD-03 Catálogo de productos · MOD-04 Proveedores y regiones · MOD-05 Precios |
| Diseño | MOD-06 Proyectos · MOD-07 Requerimientos · MOD-08 Recomendaciones IA · MOD-09 Arquitecturas · MOD-10 Ítems (productos configurados) |
| Contratación | MOD-11 Carrito · MOD-12 Presupuestos · MOD-13 Pedidos · MOD-14 Pagos · MOD-15 Despliegues |

El recorrido normal es de izquierda a derecha: el administrador prepara el catálogo, el cliente
crea un proyecto y obtiene una arquitectura, y después la contrata. El bloque de Acceso está en
todos los pasos, porque todo pasa por un usuario identificado y todo lo importante queda en
auditoría.

Diagrama: [`diagramas/mapa-funcional.drawio`](diagramas/mapa-funcional.drawio)

### Bloque Acceso

#### MOD-01 Usuarios

Se encarga del registro, el inicio de sesión y los perfiles.

- Actores: todos. El administrador además gestiona usuarios y asigna roles.
- Datos principales: nombre, correo, contraseña cifrada y rol (cliente, arquitecto, auditor o
  administrador).
- Operaciones: registrarse, iniciar y cerrar sesión, cambiar el rol de un usuario.
- Relación con otros módulos: todos los demás lo usan para saber quién hace cada cosa. El
  arquitecto asignado a un proyecto (MOD-06) es un usuario de este módulo.
- Reglas: cada usuario tiene un solo rol (RF-03). Un cliente solo ve lo suyo (RN-07). Las
  contraseñas se guardan con hash (RNF-01).
- Riesgos: que un usuario vea datos de otro cliente cambiando el id en la URL. Hay que
  comprobar el dueño en cada consulta, no solo que haya sesión iniciada.

#### MOD-02 Auditoría

Guarda un registro de quién hizo qué y cuándo, para poder reconstruir lo que pasó.

- Actores: el auditor lo consulta y añade observaciones. El administrador también puede
  consultarlo.
- Datos principales: usuario, acción, entidad afectada, fecha. Observaciones y marcas de
  incumplimiento del auditor.
- Operaciones: registrar acciones (lo hace la aplicación sola), consultar y filtrar el registro,
  añadir una observación.
- Relación con otros módulos: recibe eventos de casi todos: cambios de catálogo, arquitecturas
  aprobadas, presupuestos aceptados, pagos, despliegues y decisiones sobre la IA.
- Reglas: las entradas no se modifican ni se borran (RN-11, RF-37). El auditor no cambia datos
  operativos (RN-08).
- Riesgos: olvidarse de registrar alguna acción importante. Por eso lo haremos desde un único
  servicio al que llaman los demás, y no copiando código en cada controlador.

### Bloque Catálogo

#### MOD-03 Catálogo de productos

Contiene los productos Cloud que se pueden contratar y qué se puede configurar de cada uno.

- Actores: el administrador lo mantiene. Cliente y arquitecto lo consultan.
- Datos principales: tipo de producto, atributos configurables de cada tipo, producto
  (nombre, descripción, proveedor, estado).
- Operaciones: alta, modificación y retirada de productos (RF-07), listado filtrable (RF-05),
  ver los atributos configurables y sus valores (RF-06).
- Relación con otros módulos: cada producto es de un proveedor y está disponible en algunas
  regiones (MOD-04) con un precio (MOD-05). Lo usan los ítems de las arquitecturas y del carrito
  (MOD-10).
- Reglas: un producto retirado no se puede añadir al carrito, pero sigue valiendo en
  presupuestos y pedidos antiguos (RN-13). Por eso los productos no se borran, se retiran.
- Riesgos: que el catálogo crezca y el listado vaya lento. Lo mostramos paginado (RNF-04).

#### MOD-04 Proveedores y regiones

Guarda las empresas que ofrecen los productos y sus regiones, es decir, dónde tienen centros de
datos. Son proveedores simulados: no nos conectamos a sus APIs reales. Los juntamos en un módulo
porque una región no existe sin su proveedor (Proveedor → Regiones).

- Actores: el administrador los gestiona y define en qué regiones está cada producto. El cliente
  filtra el catálogo por proveedor y región. El auditor comprueba dónde están los recursos.
- Datos principales: proveedor (nombre, país de la sede, web) y región (código como
  `eu-south-1`, nombre, país y zona geográfica: UE, EEUU o Asia).
- Operaciones: alta, modificación y baja de proveedores y regiones, marcar en qué regiones está
  cada producto (RF-08).
- Relación con otros módulos: cada producto es de un proveedor (MOD-03). La región del proyecto
  (MOD-06) limita qué se puede añadir al carrito (MOD-11).
- Reglas: solo se puede añadir un producto si está disponible en la región del proyecto
  (RN-01). Si el proyecto exige datos en la UE, no vale ninguna región de fuera (RN-10). Un
  proveedor con productos en presupuestos o pedidos no se puede borrar.
- Riesgos: equivocarse con la zona de una región y dejar pasar datos fuera de la UE. Afecta
  al RGPD (RNF-11).

#### MOD-05 Precios

Calcula cuánto cuesta cada producto según la región y la configuración elegida.

- Actores: el administrador actualiza los precios. Los demás los ven.
- Datos principales: precio base mensual, precio único (pago de alta), precio por unidad de
  algunos atributos (por ejemplo por GB) y moneda. Todo ello por producto y región.
- Operaciones: actualizar precios, calcular el precio de una configuración (RF-09).
- Relación con otros módulos: lo usan el carrito (MOD-11) y los presupuestos (MOD-12), y la
  IA para buscar alternativas más baratas (MOD-08).
- Reglas: el precio siempre se calcula en el servidor y nunca se acepta un importe que venga
  del navegador (RNF-02, RN-06). Los importes se calculan con precisión decimal (RNF-05).
- Riesgos: errores de redondeo si se usan números con decimales normales (float) para el
  dinero. En PHP, `0.1 + 0.2` no da exactamente `0.3`.

### Bloque Diseño

#### MOD-06 Proyectos

Un proyecto es la necesidad del cliente, por ejemplo "web para mi academia con 500 alumnos".

- Actores: el cliente lo crea y lo gestiona. El administrador le asigna un arquitecto. El
  arquitecto ve los proyectos que tiene asignados.
- Datos principales: nombre, descripción en lenguaje natural, presupuesto máximo mensual,
  usuarios esperados, nivel de seguridad, región preferida y estado (RF-10).
- Operaciones: crear, editar y consultar proyectos, asignar arquitecto.
- Relación con otros módulos: de un proyecto salen sus requerimientos (MOD-07), sus
  arquitecturas (MOD-09), su carrito (MOD-11) y sus presupuestos (MOD-12).
- Reglas: un cliente puede tener varios proyectos (M-02). El arquitecto lo asigna el
  administrador (M-06). Cada cliente solo ve sus proyectos (RN-07).
- Riesgos: que la descripción sea tan vaga que no salgan requerimientos útiles. El cliente
  siempre puede revisarlos a mano (RF-12).

#### MOD-07 Requerimientos

Convierte la descripción del proyecto en condiciones concretas que se pueden comprobar, por
ejemplo `usuarios_concurrentes >= 500` o `residencia_datos = UE`.

- Actores: la IA los propone, el cliente los revisa y el arquitecto los consulta.
- Datos principales: atributo, operador, valor, unidad, si es obligatorio u opcional, y origen
  (cliente, IA o norma) (RF-11, RF-13, RF-14).
- Operaciones: generar la propuesta, revisar, añadir, modificar y eliminar requerimientos
  (RF-12).
- Relación con otros módulos: pertenecen a un proyecto (MOD-06) y son lo que comprueba el
  validador de arquitecturas (MOD-09).
- Reglas: un requerimiento obligatorio que no se cumple deja la arquitectura como no válida
  (RN-12).
- Riesgos: que la IA deduzca un requerimiento que el cliente no pidió. Por eso guardamos el
  origen y el cliente los confirma antes de usarlos.

#### MOD-08 Recomendaciones IA

Pide a la IA una propuesta de arquitectura o una alternativa más barata, y guarda lo que respondió.

- Actores: el cliente y el arquitecto la solicitan. El auditor revisa las decisiones.
- Datos principales: lo que se envió a la IA, lo que respondió, la decisión que tomó la
  aplicación (aceptada, corregida o descartada) y el motivo (RN-09).
- Operaciones: solicitar recomendación (RF-15), pedir alternativa más económica (RF-21).
- Relación con otros módulos: lee el catálogo (MOD-03) y los requerimientos (MOD-07). Lo que
  propone pasa siempre por el validador de MOD-09 antes de mostrarse.
- Reglas: se descarta cualquier producto que no exista en el catálogo (RF-16). No se envían
  datos personales del cliente (RNF-10). Si la IA no responde, la aplicación sigue funcionando por
  el camino manual (RNF-13).
- Riesgos: que la IA se invente productos o precios, o que tarde mucho en responder.

#### MOD-09 Arquitecturas

Una arquitectura es el conjunto de productos configurados que resuelve un proyecto.

- Actores: la IA o el cliente la proponen. El arquitecto la revisa, la modifica y la aprueba o
  la rechaza.
- Datos principales: versión, origen, estado, coste mensual estimado y observaciones. Sus
  ítems, cada uno con su configuración y cantidad.
- Operaciones: crear, validar (RF-17), mostrar los errores (RF-18), modificar creando una
  versión nueva (RF-19), aprobar o rechazar (RF-20), pasar al carrito.
- Relación con otros módulos: pertenece a un proyecto (MOD-06), se comprueba contra sus
  requerimientos (MOD-07), está formada por ítems (MOD-10) y, una vez aprobada, se pasa al
  carrito (MOD-11).
- Reglas: una arquitectura que incumple un obligatorio no se presenta como válida (RN-12). No se
  admite un backup sin algo que respaldar (RN-02). No se admiten recursos fuera de la UE si el
  proyecto lo exige (RN-10).
- Riesgos: que el validador sea muy complejo. Empezaremos con pocas reglas y las iremos
  ampliando.

#### MOD-10 Ítems (productos configurados)

Un ítem es cada producto que forma parte de una arquitectura o del carrito, con su configuración,
su región y su cantidad. Por ejemplo: "2 máquinas de computación con 4 vCPU y 8 GB de RAM en
Milán, como servidores web".

- Actores: el cliente y el arquitecto los añaden, cambian y quitan. La IA los propone.
- Datos principales: producto, región, valores elegidos para cada atributo del tipo, cantidad y
  función dentro de la arquitectura.
- Operaciones: añadir un ítem, cambiar su configuración o su cantidad, quitarlo, copiar los
  ítems de una arquitectura aprobada al carrito.
- Relación con otros módulos: cada ítem apunta a un producto del catálogo (MOD-03) en una
  región (MOD-04). Forman las arquitecturas (MOD-09) y las líneas del carrito (MOD-11). Su
  precio lo calcula MOD-05.
- Reglas: los valores tienen que ser de los admitidos por el tipo de producto y estar todos los
  obligatorios (RF-06). El producto tiene que estar disponible en esa región (RN-01). Un ítem de
  backup necesita otro ítem al que respaldar (RN-02).
- Riesgos: que llegue desde el navegador una configuración que no existe (por ejemplo 3 GB de
  RAM si solo se admiten 2 o 4). Se comprueba siempre en el servidor.

### Bloque Contratación

#### MOD-11 Carrito

Es la lista de lo que el cliente va a pedir que le presupuesten.

- Actores: el cliente.
- Datos principales: líneas, cada una con su configuración (producto, región y valores) y su
  cantidad.
- Operaciones: añadir (RF-22), cambiar cantidad o configuración, eliminar (RF-24), cargar los
  ítems de una arquitectura aprobada.
- Relación con otros módulos: sus líneas son ítems (MOD-10) y usa los precios (MOD-05). De él
  sale el presupuesto (MOD-12).
- Reglas: no se añade un producto que no esté en la región del proyecto (RN-01, RF-23) ni un
  producto retirado (RN-13).
- Riesgos: que el navegador mande un precio manipulado. El carrito no guarda precios: se
  calculan siempre en el servidor.

#### MOD-12 Presupuestos

Convierte el carrito en un documento con precios cerrados y fecha de caducidad.

- Actores: el cliente lo genera, lo acepta o lo rechaza. El auditor lo consulta.
- Datos principales: código, fechas de creación y caducidad, moneda, subtotal, impuestos,
  total mensual y total de pagos únicos (RF-25, RF-26). Sus líneas, con los datos copiados.
- Operaciones: generar, consultar, aceptar y rechazar (RF-28).
- Relación con otros módulos: sale del carrito (MOD-11). Al aceptarse genera un pedido
  (MOD-13).
- Reglas: caduca a los 30 días (M-01, RN-03). Conserva los precios del momento en que se
  generó (RN-05, RF-27). Una sola moneda por presupuesto (RN-14).
- Riesgos: que un cambio de precios en el catálogo cambie un presupuesto ya enviado. Lo
  evitamos copiando los datos en las líneas (ver 3.3).

#### MOD-13 Pedidos

Es el compromiso de compra que se crea al aceptar un presupuesto.

- Actores: el cliente lo confirma. El auditor y el administrador lo consultan.
- Datos principales: código, fecha, estado, total y líneas copiadas del presupuesto.
- Operaciones: crear al aceptar un presupuesto (RF-29), consultar, cancelar.
- Relación con otros módulos: viene de un presupuesto (MOD-12), recibe un pago (MOD-14) y da
  lugar a un despliegue (MOD-15).
- Reglas: no se puede confirmar dos veces (RF-31). Un pedido confirmado no se modifica: solo
  se cancela y se rehace desde un presupuesto nuevo (M-03, RN-15).
- Riesgos: pedidos duplicados si el cliente pulsa dos veces el botón. Se controla con el
  estado del pedido.

#### MOD-14 Pagos

Registra un pago simulado. No hay pasarela real.

- Actores: el cliente.
- Datos principales: importe, método simulado, referencia, estado y fecha.
- Operaciones: registrar el pago de un pedido (RF-30).
- Relación con otros módulos: pertenece a un pedido (MOD-13). Sin pago no hay despliegue
  (MOD-15).
- Reglas: confirmar el pedido y registrar el pago es una operación atómica (RNF-06): o se
  guardan las dos cosas o ninguna. En Laravel esto se hace con una transacción de base de datos.
  Un pedido solo se despliega si está pagado (RN-04).
- Riesgos: que falle a mitad y quede un pedido pagado sin registrar, o al revés. Lo evita la
  transacción.

#### MOD-15 Despliegues

Simula que la infraestructura se pone en marcha.

- Actores: el cliente lo consulta. El administrador lo supervisa. El auditor comprueba dónde
  están los recursos.
- Datos principales: estado del despliegue y sus recursos, cada uno con nombre, región, zona y
  estado (RF-33).
- Operaciones: generar el despliegue de un pedido pagado (RF-32), crear un recurso por cada
  línea del pedido, consultar estados (RF-34).
- Relación con otros módulos: sale de un pedido pagado (MOD-13, MOD-14).
- Reglas: solo con pago registrado (RN-04). Cada recurso guarda su región para saber dónde
  están los datos (RNF-11).
- Riesgos: que el cliente crea que el despliegue es real. Lo indicaremos claramente en la
  pantalla.

---

## 2. Dominio Cloud

### 2.1 Catálogo de productos

El catálogo tiene los 10 tipos de producto que pide el enunciado. Cada tipo necesita una
configuración distinta:

| Tipo | Qué es | Configuración que necesita | Cómo se cobra |
|---|---|---|---|
| Computación | Servidores virtuales | CPU (vCPU), RAM (GB), disco (GB), tipo de disco (HDD/SSD), sistema operativo | Mensual, según la configuración |
| Almacenamiento | Espacio para guardar ficheros | Tipo (objetos, bloque o archivos), capacidad (GB), clase (estándar o archivo) | Mensual por GB |
| Bases de datos | Un motor de base de datos que mantiene el proveedor | Motor (MySQL, PostgreSQL, MariaDB...), versión, almacenamiento (GB), tipo (relacional o NoSQL), replicación (número de réplicas) | Mensual |
| Redes | Red privada, IPs públicas y reparto de tráfico | Rango de IPs, número de IPs públicas, transferencia mensual (GB), balanceador de carga sí/no | Mensual |
| Seguridad | Protección del tráfico y de las conexiones | Firewall (puertos y reglas permitidos), certificado SSL sí/no, protección DDoS sí/no | Mensual |
| Backup | Copias de seguridad de otro recurso | Recurso a respaldar, frecuencia (diaria, semanal), días de retención | Mensual |
| Monitorización | Vigila los recursos y avisa si fallan | Métricas, intervalo de comprobación, canal de alertas, días que se guardan los logs | Mensual |
| Computación IA | Servidores con tarjeta gráfica (GPU) para entrenar o ejecutar modelos propios | Modelo de GPU, número de GPUs, RAM (GB), disco (GB) | Mensual o por horas de uso |
| Servicio IA | Modelos de IA ya hechos que se usan por API | Tipo de servicio (texto, imagen, voz), límite de peticiones al mes | Por uso |
| Soporte | Ayuda técnica del proveedor | Nivel (básico, estándar, premium), tiempo de respuesta, horario | Mensual, y pago único si incluye instalación |

La diferencia entre Computación IA y Servicio IA es quién pone el modelo. En Computación IA el
cliente alquila la máquina con GPU y ejecuta en ella lo que quiera. En Servicio IA el cliente no
ve ninguna máquina: manda peticiones a un modelo del proveedor y paga por cada una.

**Qué es común a todos:** nombre, descripción, tipo, proveedor, estado (activo o retirado), las
regiones en las que está disponible, el precio en cada región, la moneda y la forma de cobro.
Lo general va en PRODUCTO, la forma de cobro en TIPO_PRODUCTO, y la disponibilidad y el precio en
PRODUCTO_REGION.

**Qué cambia según el tipo:** la configuración. Un producto de computación necesita CPU y RAM y
un backup no. Un backup necesita saber qué recurso respalda y uno de computación no.

**Por qué no hacemos una sola tabla con 50 columnas opcionales.** Si metiéramos todos los atributos
de los 10 tipos en una tabla, cada fila tendría casi todas las columnas vacías (`NULL`): un backup
tendría vacías la CPU, la RAM, el motor de base de datos... Además no podríamos obligar a que un
producto de computación tenga RAM, porque la columna tendría que admitir vacíos para el resto
de tipos. Y cada vez que el administrador quisiera añadir un tipo nuevo habría que cambiar la
tabla, con una migración nueva y tocando código.

**Cómo lo separamos.** Hemos decidido separar la parte general de la específica (D-09):

- PRODUCTO guarda lo común.
- TIPO_PRODUCTO y ATRIBUTO_TIPO describen qué hay que configurar en cada tipo: nombre del
  atributo, unidad, si es obligatorio y qué valores admite. Por ejemplo, el tipo "Computación"
  tiene el atributo `ram_gb`, obligatorio, con valores 1, 2, 4, 8 o 16.
- CONFIGURACION guarda los valores que eligió el cliente para un producto concreto (M-04).

**Ventajas de separarlo así:**

- No hay columnas vacías: cada producto solo tiene lo que le corresponde.
- Se puede marcar qué es obligatorio en cada tipo.
- Añadir un tipo nuevo es añadir filas en TIPO_PRODUCTO y ATRIBUTO_TIPO desde el panel del
  administrador, sin migraciones ni código nuevo.
- La pantalla de configurar un producto se puede generar a partir de sus atributos, con un único
  formulario para todos los tipos.

### 2.2 Proveedores y regiones

**Relación.** Un proveedor tiene muchas regiones y cada región es de un solo proveedor
(PROVEEDOR 1:N REGION). Una región es un sitio físico con centros de datos, por ejemplo
"Europa (Milán)". Cada región tiene una zona geográfica: UE, EEUU o Asia. Con el ejemplo del
enunciado:

```
NebulaCloud
├── Madrid      (UE)
├── Frankfurt   (UE)
└── Virginia    (EEUU)
```

**Disponibilidad.** No todos los productos están en todas las regiones. La relación entre PRODUCTO
y REGION es N:M: un producto puede estar en varias regiones y en una región hay muchos productos.
Esa relación la guardamos en PRODUCTO_REGION, que además lleva el precio en esa región. Si no hay
fila en PRODUCTO_REGION, el producto no está disponible allí y no se puede añadir al carrito
(RN-01).

**Por qué un producto está en una región y no en otra.** Los motivos más habituales son:

- El proveedor no ha instalado ese hardware allí. Por ejemplo, los productos de Computación IA
  necesitan tarjetas gráficas (GPU) que solo hay en algunas regiones.
- Hay poca demanda en esa zona y al proveedor no le compensa ofrecerlo.
- Hay leyes locales que lo limitan.
- Es un servicio nuevo que se lanza primero en unas pocas regiones.

**Residencia de datos.** Es el lugar físico donde quedan guardados los datos, y de eso depende qué
ley se les aplica. Por eso el proyecto puede tener el requerimiento "datos en la UE", y entonces el
validador rechaza cualquier recurso de una región fuera de la UE (RN-10). También guardamos la
región de cada recurso desplegado para que el auditor pueda comprobarlo (RNF-11).

**Diferencias entre zonas:**

| Zona | Qué hay que tener en cuenta |
|---|---|
| UE | Se aplica el RGPD. Los datos personales no pueden salir de la UE sin garantías. Es la opción normal para un cliente español |
| EEUU | Suele tener más productos y precios algo más bajos. Pero las empresas americanas pueden estar obligadas a entregar datos a su gobierno (CLOUD Act), lo que choca con el RGPD si son datos personales de europeos |
| Asia | Tiene sentido si los usuarios están allí, porque la latencia (lo que tardan en llegar los datos) es menor. Cada país tiene su propia ley de protección de datos |

### 2.3 Precios

El precio no es un atributo del producto, porque el mismo producto cuesta distinto según la región
y según cómo se configure (D-10). Por eso el precio está en PRODUCTO_REGION:

- `precio_base_mensual`: lo que cuesta al mes el producto en esa región.
- `precio_unico`: un pago de alta, si lo hay.
- `precios_por_unidad`: lo que se suma por cada unidad de algunos atributos, por ejemplo 2 € por
  cada GB de RAM o 0,10 € por cada GB de disco.
- `moneda`.

El precio mensual de una línea se calcula así:

```
precio mensual = (precio base + suma de (valor del atributo × precio por unidad)) × cantidad
```

Ejemplo: una máquina virtual en `eu-south-1` con precio base de 5 €, 4 GB de RAM a 2 €/GB y 50 GB
de disco a 0,10 €/GB cuesta 5 + 8 + 5 = 18 € al mes. Si se piden 2, cuesta 36 €.

Este cálculo lo hace siempre el servidor, en un servicio llamado `CalculadoraPrecios`. El
navegador solo envía el producto, la región, los valores y la cantidad. Nunca envía un precio
(RNF-02).

---

## 3. Modelo conceptual de datos

Es un diseño inicial: entidades, sus atributos principales y cómo se relacionan. No ponemos tipos
de datos SQL. Eso se decidirá al hacer las migraciones en UT2.

Diagrama: [`diagramas/modelo-conceptual.drawio`](diagramas/modelo-conceptual.drawio)

### 3.1 Entidades

| Bloque | Entidad | Para qué sirve | Atributos principales |
|---|---|---|---|
| Acceso | USUARIO | Cualquier persona que entra en la aplicación | nombre, email, password, rol |
| Acceso | REGISTRO_AUDITORIA | Una acción importante que ha pasado | usuario, acción, entidad, id de la entidad, detalle, fecha |
| Acceso | OBSERVACION_AUDITORIA | Una nota del auditor sobre algo que ha revisado | auditor, entidad, id de la entidad, texto, es_incumplimiento, fecha |
| Catálogo | PROVEEDOR | Empresa que ofrece los productos | nombre, país_sede, web |
| Catálogo | REGION | Lugar con centros de datos de un proveedor | código, nombre, país, zona (UE, EEUU, Asia) |
| Catálogo | TIPO_PRODUCTO | Los 10 tipos del catálogo | nombre, descripción, forma de cobro |
| Catálogo | ATRIBUTO_TIPO | Lo que hay que configurar en cada tipo | clave, nombre, unidad, obligatorio, valores_admitidos |
| Catálogo | PRODUCTO | Un producto concreto del catálogo | nombre, descripción, estado (activo o retirado) |
| Catálogo | PRODUCTO_REGION | Disponibilidad y precio de un producto en una región | precio_base_mensual, precio_unico, precios_por_unidad, moneda |
| Catálogo | REGLA_COMPATIBILIDAD | Qué tipo necesita a otro para poder contratarse | tipo origen, tipo requerido, descripción |
| Diseño | PROYECTO | La necesidad del cliente | nombre, descripción, presupuesto_max_mensual, usuarios_esperados, nivel_seguridad, estado |
| Diseño | REQUERIMIENTO | Una condición que se puede comprobar | atributo, operador, valor, unidad, obligatorio, origen |
| Diseño | RECOMENDACION_IA | Una petición a la IA y lo que se decidió con ella | petición enviada, respuesta, decisión, motivo, fecha |
| Diseño | ARQUITECTURA | Una propuesta de infraestructura para un proyecto | versión, origen, estado, coste_mensual_estimado, observaciones |
| Diseño | ARQUITECTURA_ITEM | Cada pieza de una arquitectura | cantidad, función (por ejemplo "servidor web") |
| Diseño | CONFIGURACION | Un producto en una región con unos valores elegidos | valores (por ejemplo vCPU 4, RAM 8, disco SSD) |
| Contratación | CARRITO | Lo que el cliente va a presupuestar | estado |
| Contratación | LINEA_CARRITO | Cada producto configurado del carrito | cantidad |
| Contratación | PRESUPUESTO | Documento con precios cerrados | código, fecha_creacion, fecha_caducidad, moneda, subtotal, impuestos, total_mensual, total_unico, estado |
| Contratación | LINEA_PRESUPUESTO | Cada línea del presupuesto, con los datos copiados | nombre_producto, tipo, proveedor, región, configuración copiada, cantidad, precio_mensual, precio_unico, subtotal |
| Contratación | PEDIDO | La compra hecha al aceptar un presupuesto | código, fecha, estado, total |
| Contratación | LINEA_PEDIDO | Cada línea del pedido, copiada de la del presupuesto | mismos datos que LINEA_PRESUPUESTO |
| Contratación | PAGO | El pago simulado del pedido | importe, método, referencia, estado, fecha |
| Contratación | DESPLIEGUE | La puesta en marcha simulada | estado, fecha_inicio, fecha_fin |
| Contratación | RECURSO | Cada cosa desplegada | nombre, región, zona, estado |

En el diagrama y en esta tabla no ponemos las claves ajenas (por ejemplo `proyecto_id`) como
atributos: se ven en las relaciones de 3.2.

### 3.2 Relaciones

| # | Relación | Cardinalidad | Qué significa |
|---|---|---|---|
| R-01 | USUARIO – PROYECTO (como cliente) | 1:N | Un cliente tiene muchos proyectos (M-02) |
| R-02 | USUARIO – PROYECTO (como arquitecto) | 1:N | Un arquitecto lleva muchos proyectos; un proyecto tiene uno o ninguno todavía (M-06) |
| R-03 | USUARIO – REGISTRO_AUDITORIA | 1:N | Un usuario genera muchas entradas de auditoría |
| R-04 | USUARIO – OBSERVACION_AUDITORIA | 1:N | Un auditor escribe muchas observaciones |
| R-05 | PROVEEDOR – REGION | 1:N | Un proveedor tiene muchas regiones |
| R-06 | PROVEEDOR – PRODUCTO | 1:N | Un proveedor ofrece muchos productos |
| R-07 | TIPO_PRODUCTO – PRODUCTO | 1:N | Cada producto es de un tipo |
| R-08 | TIPO_PRODUCTO – ATRIBUTO_TIPO | 1:N | Cada tipo define sus atributos configurables |
| R-09 | TIPO_PRODUCTO – TIPO_PRODUCTO (por REGLA_COMPATIBILIDAD) | N:M | Un tipo puede necesitar otros. Por ejemplo, un backup necesita un producto de computación o una base de datos (RN-02) |
| R-10 | PRODUCTO – REGION (por PRODUCTO_REGION) | N:M | Un producto está en varias regiones y una región tiene muchos productos, cada uno con su precio |
| R-11 | REGION – PROYECTO | 1:N | Cada proyecto tiene una región preferida |
| R-12 | PROYECTO – REQUERIMIENTO | 1:N | Un proyecto tiene muchos requerimientos |
| R-13 | PROYECTO – ARQUITECTURA | 1:N | Un proyecto tiene varias arquitecturas, una por versión (M-05) |
| R-14 | PROYECTO – RECOMENDACION_IA | 1:N | Un proyecto puede pedir varias recomendaciones |
| R-15 | RECOMENDACION_IA – ARQUITECTURA | 1:1 (opcional) | Si la propuesta de la IA se acepta, genera una arquitectura. Si se descarta, no genera ninguna |
| R-16 | ARQUITECTURA – ARQUITECTURA_ITEM | 1:N | Una arquitectura tiene muchos ítems |
| R-17 | ARQUITECTURA_ITEM – CONFIGURACION | 1:1 | Cada ítem tiene su configuración |
| R-18 | PRODUCTO – CONFIGURACION | 1:N | Un producto puede configurarse de muchas formas |
| R-19 | REGION – CONFIGURACION | 1:N | Cada configuración es para una región |
| R-20 | PROYECTO – CARRITO | 1:1 | Cada proyecto tiene un carrito |
| R-21 | CARRITO – LINEA_CARRITO | 1:N | Un carrito tiene muchas líneas |
| R-22 | LINEA_CARRITO – CONFIGURACION | 1:1 | Cada línea del carrito tiene su configuración |
| R-23 | PROYECTO – PRESUPUESTO | 1:N | Un proyecto puede tener varios presupuestos |
| R-24 | PRESUPUESTO – LINEA_PRESUPUESTO | 1:N | Un presupuesto tiene muchas líneas |
| R-25 | PRODUCTO – LINEA_PRESUPUESTO | 1:N (solo referencia) | La línea guarda de qué producto viene, pero los datos que se muestran y se cobran son los copiados |
| R-26 | PRESUPUESTO – PEDIDO | 1:1 (opcional) | Un presupuesto aceptado genera un pedido. Si no se acepta, no hay pedido |
| R-27 | PEDIDO – LINEA_PEDIDO | 1:N | Un pedido tiene muchas líneas |
| R-28 | PEDIDO – PAGO | 1:1 | Cada pedido tiene un pago simulado |
| R-29 | PEDIDO – DESPLIEGUE | 1:1 (opcional) | Un pedido pagado genera un despliegue |
| R-30 | DESPLIEGUE – RECURSO | 1:N | Un despliegue tiene muchos recursos |
| R-31 | LINEA_PEDIDO – RECURSO | 1:1 | Cada línea del pedido crea un recurso (RF-33) |

REGISTRO_AUDITORIA y OBSERVACION_AUDITORIA guardan el nombre de la entidad y su id (por ejemplo
`PEDIDO` y `15`) en lugar de una relación fija, porque pueden referirse a cualquier tabla. Por eso
en el diagrama solo aparecen unidas a USUARIO.

### 3.3 Explicación del modelo

**¿Por qué existen los ARQUITECTURA_ITEM?** Una arquitectura tiene muchos productos y un producto
puede estar en muchas arquitecturas, así que es una relación N:M. Pero no basta con decir "esta
arquitectura usa una máquina virtual". Hay que saber cuántas, con qué configuración, en qué
región y para qué sirve cada una. Por ejemplo, la misma arquitectura puede tener una máquina
virtual pequeña como servidor web y otra grande para procesos pesados. Esos datos no son ni de la
arquitectura ni del producto, son de la unión entre los dos. Por eso la tabla intermedia es una
entidad con sus propios datos: ARQUITECTURA_ITEM.

La cadena completa es:

```
ARQUITECTURA ──1:N──► ARQUITECTURA_ITEM ──1:1──► CONFIGURACION ──N:1──► PRODUCTO
                      (cantidad, función)        (región, valores)       (lo común)
```

**¿Por qué separar producto y configuración?** El producto es lo que vende el proveedor, por
ejemplo "Servidor virtual estándar", que es de tipo Computación. La configuración es cómo lo
quiere el cliente: 4 vCPU, 8 GB de RAM y SSD en Milán. Un mismo producto se configura de muchas
formas distintas, y no queremos crear un producto nuevo por cada combinación.

**¿Por qué las líneas del presupuesto copian los datos?** Un presupuesto es un documento: lo que
se le dijo al cliente no puede cambiar después. Si la línea solo guardara el id del producto y el
precio se leyera del catálogo, al subir un precio o retirar un producto cambiarían también los
presupuestos antiguos. Por eso, al generar el presupuesto, copiamos en cada línea el nombre del
producto, el tipo, el proveedor, la región, la configuración y el precio de ese momento (RN-05,
D-14). Guardamos también el `producto_id`, pero solo para saber de dónde viene la línea. Con el
pedido pasa lo mismo: sus líneas se copian de las del presupuesto.

**¿Por qué el carrito no guarda precios?** Porque el carrito todavía no es un documento. Mientras
el cliente lo prepara, el precio se calcula en cada momento a partir del catálogo. Los precios
solo se congelan cuando se genera el presupuesto.

**¿Cómo pasa una arquitectura al carrito?** Cuando una arquitectura está aprobada, el cliente
puede cargarla en el carrito. Se crea una línea por cada ítem, con una copia de su configuración y
su cantidad. La arquitectura no cambia aunque el cliente toque después el carrito.

### 3.4 Decisiones tomadas

Al hacer el modelo nos salieron seis dudas que cambiaban cómo se guardan los datos. Las hemos
cerrado eligiendo en cada caso lo más sencillo que sigue siendo correcto, porque todo esto lo
tenemos que programar en UT2 con Laravel.

| # | Duda | Decisión |
|---|---|---|
| M-01 | ¿Cuántos días dura un presupuesto? | 30 días naturales |
| M-02 | ¿Puede un cliente tener varios proyectos activos? | Sí, sin límite |
| M-03 | ¿Se puede modificar un pedido confirmado? | No, solo cancelarlo y rehacerlo desde un presupuesto nuevo |
| M-04 | ¿La configuración es una entidad propia o atributos de la línea? | Entidad propia: CONFIGURACION |
| M-05 | ¿La arquitectura es una versión del proyecto o una entidad independiente? | Entidad independiente: ARQUITECTURA, con número de versión |
| M-06 | ¿Quién asigna un arquitecto a un proyecto? | El administrador |

**M-01. Caducidad del presupuesto.** Al crear un presupuesto guardamos su `fecha_caducidad`,
que es la fecha de creación más 30 días. Los 30 días van en un fichero de configuración y no
escritos a mano en el código, para poder cambiarlos sin tocar la lógica. Para cumplir RN-03 basta
con comparar la fecha de hoy con `fecha_caducidad` antes de aceptar.

**M-02. Varios proyectos por cliente.** En el modelo es una relación USUARIO 1:N PROYECTO y no hace
falta ninguna regla más. Poner un límite complicaría el código y ningún requisito lo pide.

**M-03. Pedido confirmado.** Un pedido confirmado no se modifica: solo cambia de estado, por
ejemplo a `cancelado`. Si el cliente quiere otra cosa, pide un presupuesto nuevo. Es lo que dice
RN-15, y así el pedido queda como documento histórico igual que el presupuesto.

**M-04. Configuración como entidad.** Una CONFIGURACION guarda un producto, una región y los
valores elegidos (por ejemplo 4 vCPU, 8 GB de RAM y disco SSD). La usan tanto los ítems de una
arquitectura como las líneas del carrito, y es lo que comprueba el validador. Si fueran columnas
de cada línea tendríamos que repetirlas en varios sitios. Los valores los guardamos en un campo
JSON, que Laravel convierte en un array de PHP, y un Service comprueba que cada valor está
permitido según los atributos del tipo de producto. Así no hace falta una tabla por cada tipo.

**M-05. Arquitectura como entidad.** Un proyecto puede tener varias arquitecturas
(PROYECTO 1:N ARQUITECTURA). Cada una tiene `version`, `origen` (IA, cliente o arquitecto) y
`estado`. Cuando alguien la modifica no se sobrescribe: se crea una fila nueva con la versión
siguiente (D-15). Así se puede comparar lo que propuso la IA con lo que aprobó el arquitecto.

**M-06. Asignación del arquitecto.** El administrador elige qué usuario con perfil ARQUITECTO
revisa cada proyecto. En el modelo, PROYECTO tiene un campo `arquitecto_id` que apunta a USUARIO
y que puede estar vacío mientras no se haya asignado nadie. Un arquitecto puede llevar muchos
proyectos, así que es una relación USUARIO 1:N PROYECTO (R-02). La asignación
automática nos obligaría a programar un reparto de carga que nadie pide, y el cliente no conoce a
los arquitectos para elegir uno.

### 3.5 Pendiente

Hay cosas que todavía no podemos cerrar porque dependen de unidades que no hemos dado o de otra
parte del análisis:

- **Impuestos.** Si son un porcentaje fijo o dependen de la región. Está abierto en `requisitos.md`
  (A-06). Si acaban dependiendo de la región, habría que añadir el porcentaje a REGION.
- **Cómo hacer las cuentas con decimales exactos en PHP.** Sabemos que no podemos usar `float`
  (RNF-05). Elegiremos entre trabajar en céntimos con números enteros o usar una librería de
  decimales cuando lleguemos a los precios en UT5.
- **Recursos con cantidad mayor que 1.** RF-33 dice un recurso por línea del pedido. Si una línea
  pide 3 máquinas virtuales, puede interesar crear 3 recursos para ver el estado de cada una. Lo
  revisaremos en UT6.

---

## 4. Arquitectura Laravel

Laravel sigue el patrón MVC, pero en una aplicación como esta con modelo, vista y controlador no
basta. Hay mucha lógica (calcular precios, validar arquitecturas, hablar con la IA) que no es ni
del modelo ni del controlador. Por eso añadimos una capa de servicios.

Diagrama: [`diagramas/arquitectura-laravel.drawio`](diagramas/arquitectura-laravel.drawio)

### 4.1 Función de cada carpeta

| Carpeta | Qué hace | Ejemplo en CloudForge |
|---|---|---|
| `routes/` | Dice qué URL llama a qué método de qué controlador. `web.php` para las páginas y `api.php` para la API | `POST /proyectos/{proyecto}/presupuestos` llama a `PresupuestoController@store` |
| `app/Http/Controllers/` | Recibe la petición, llama al servicio que toca y devuelve la respuesta. No hace cálculos | `PresupuestoController` pide al servicio que genere el presupuesto y devuelve la vista o el JSON |
| `app/Http/Requests/` | Clases que validan los datos que llegan del formulario antes de que entren al controlador (en Laravel se llaman FormRequest) | `AnadirAlCarritoRequest` comprueba que llegan producto, región y una cantidad mayor que 0 |
| `app/Services/` | Aquí está la lógica de negocio. Laravel no crea esta carpeta, la creamos nosotros | `CalculadoraPrecios`, `ValidadorArquitectura`, `RecomendadorIA`, `GeneradorPresupuesto` |
| `app/Models/` | Un modelo Eloquent por tabla. Representa los datos y sus relaciones (`hasMany`, `belongsTo`...) | `Proyecto` con `hasMany(Arquitectura::class)` |
| `app/Enums/` | Listas cerradas de valores posibles | `EstadoPresupuesto`: pendiente, aceptado, rechazado, caducado |
| `app/Http/Resources/` | Transforman un modelo en el JSON que se devuelve, eligiendo qué campos se enseñan | `PresupuestoResource` devuelve código, totales y líneas, pero no datos internos |
| `app/Policies/` | Dicen si un usuario puede hacer algo con un registro concreto | `ProyectoPolicy` impide que un cliente vea el proyecto de otro (RN-07) |

**Enums.** Un Enum es un tipo de PHP (desde la versión 8.1) que solo admite unos valores fijos. Si
el estado de un presupuesto fuera un texto libre, alguien podría escribir `"acetado"` y nadie se
daría cuenta. Con un Enum, PHP da error si el valor no es uno de los permitidos, y el editor nos
sugiere los válidos. Los que tenemos previstos son: `Rol`, `ZonaGeografica`, `OrigenRequerimiento`,
`OrigenArquitectura`, `EstadoArquitectura`, `EstadoPresupuesto`, `EstadoPedido`, `EstadoPago`,
`EstadoDespliegue` y `EstadoRecurso`.

**Resources.** Cuando la aplicación devuelve JSON (la API o las partes de la web que se cargan
con JavaScript), no queremos devolver el modelo tal cual, porque saldrían todos los campos de la
tabla, incluidos los internos. El Resource decide qué campos salen y con qué nombre. Si un día
cambiamos una columna de la tabla, solo tocamos el Resource y la API sigue respondiendo igual.
Cuando la respuesta es una vista Blade, el controlador devuelve la vista y no se usa Resource.

### 4.2 Recorrido de una petición

Ejemplo: el cliente pulsa "Generar presupuesto".

1. El navegador envía `POST /proyectos/7/presupuestos`.
2. `routes/web.php` dirige la petición a `PresupuestoController@store`.
3. Laravel comprueba que hay sesión iniciada y la `ProyectoPolicy` comprueba que el proyecto 7 es
   de ese cliente.
4. El controlador llama a `GeneradorPresupuesto`.
5. El servicio lee el carrito con los modelos, pide a `CalculadoraPrecios` el precio de cada línea,
   copia los datos en las líneas del presupuesto, pone la fecha de caducidad (M-01) y lo guarda
   todo.
6. El servicio avisa al servicio de auditoría para que lo registre.
7. El controlador devuelve el presupuesto: como vista, o como JSON a través de
   `PresupuestoResource`.

### 4.3 Por qué no ponemos toda la lógica en los Controllers

Si toda la lógica estuviera en los controladores, funcionaría al principio, pero en CloudForge nos
daría estos problemas:

- **Repetiríamos código.** El precio de una configuración se calcula en el carrito, en el
  presupuesto y cuando la IA busca alternativas baratas. Si el cálculo está en un controlador, hay
  que copiarlo en los otros. Si está en `CalculadoraPrecios`, todos llaman al mismo sitio y un
  cambio se hace una sola vez.
- **No se podría usar desde fuera de la web.** La misma lógica la usarán la API (UT8) y tareas
  que se ejecutan solas, como marcar presupuestos caducados. Esas no pasan por un controlador web.
- **Sería difícil de probar.** Un servicio se puede probar pasándole datos y mirando el resultado.
  Un controlador necesita simular una petición completa.
- **Controladores enormes.** Un controlador con cientos de líneas es difícil de leer y de
  repartir entre los cuatro.

Por eso RNF-09 pide la lógica en servicios (D-11). La idea es que el controlador sea corto:
recibe, llama al servicio y responde.

---

## 5. Papel de la inteligencia artificial

La idea principal del proyecto es que **la IA recomienda y Laravel decide**. La IA ayuda a pasar
de una descripción en lenguaje natural a una propuesta, pero no tiene la última palabra en nada
que se guarde o se cobre.

| La IA propone | Laravel calcula y decide |
|---|---|
| Requerimientos a partir de la descripción del proyecto | Que el cliente los revise y confirme antes de usarlos |
| Una arquitectura: qué productos, cuántos y con qué configuración | Si esos productos existen en el catálogo (RF-16) y están en la región del proyecto (RN-01) |
| Alternativas más baratas | El precio real de cada opción, con `CalculadoraPrecios` (RNF-02) |
| Una explicación de por qué ha elegido cada pieza | Si la arquitectura cumple todos los requerimientos obligatorios (RF-17, RN-12) |
|  | Si respeta la residencia de datos (RN-10) y las reglas de compatibilidad (RN-02) |
|  | El estado final: válida o no válida, y qué falla (RF-18) |

El recorrido es este:

1. `RecomendadorIA` prepara la petición con los requerimientos del proyecto y la lista de
   productos disponibles en su región. No envía nombre, correo ni ningún dato personal del cliente
   (RNF-10).
2. La IA responde con una propuesta en un formato fijo (JSON) con ids de producto, valores y
   cantidades.
3. `ValidadorArquitectura` comprueba la propuesta: quita los productos que no existen, comprueba
   disponibilidad y compatibilidades, y compara con cada requerimiento obligatorio.
4. `CalculadoraPrecios` calcula el coste. Si la IA ha dicho un precio, se ignora.
5. Se guarda una RECOMENDACION_IA con la petición, la respuesta y lo que decidió la aplicación
   (RN-09). Si se acepta, se crea una ARQUITECTURA con origen IA.
6. Solo entonces se le muestra al cliente, y el arquitecto la revisa.

Si la IA no responde o tarda demasiado, el cliente puede montar la arquitectura a mano desde el
catálogo, y pasa por el mismo validador (RNF-13). Por eso la IA ayuda pero la aplicación funciona
sin ella.

---

## 6. Diagramas

Los tres diagramas están en formato draw.io y se abren en <https://app.diagrams.net>.

| Diagrama | Fichero |
|---|---|
| Mapa funcional: 15 módulos en 4 bloques | [`diagramas/mapa-funcional.drawio`](diagramas/mapa-funcional.drawio) |
| Modelo conceptual de datos | [`diagramas/modelo-conceptual.drawio`](diagramas/modelo-conceptual.drawio) |
| Arquitectura Laravel y recorrido de una petición | [`diagramas/arquitectura-laravel.drawio`](diagramas/arquitectura-laravel.drawio) |
