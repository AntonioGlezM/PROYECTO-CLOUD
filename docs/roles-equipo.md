# Organización del equipo

**Proyecto:** CloudForge · Generación de infraestructuras Cloud con IA
**Módulo:** Desarrollo Web en Entorno Servidor · 2.º DAW · Curso 2026/2027
**Centro:** CIFP Villa de Agüimes
**Entrega UT1:** 14 de octubre de 2026

---

## 1. Integrantes y roles

El enunciado define cinco roles y somos cuatro, así que el Rol 5 lo repartimos entre dos
personas, tal y como permite el propio documento. Los roles son **iniciales**: rotarán en las
siguientes unidades, y todos conocemos el conjunto del proyecto con independencia del que nos
haya tocado ahora.

| Integrante | Rol inicial | Responsabilidades principales |
|---|---|---|
| **Antonio González** | Rol 1 · Coordinación técnica<br>+ Rol 5 · Calidad y seguridad | Coordinar las tareas del equipo y las reuniones, comprobar el cumplimiento de los plazos, controlar las issues, coordinar ramas y Pull Requests, resolver con el equipo los conflictos de integración, mantener la seguridad del repositorio y revisar los entregables |
| **Alejandro** | Rol 2 · Responsable del entorno de desarrollo | Preparación de Ubuntu, PHP y sus extensiones, Composer, Node.js y npm, servidor de base de datos, Laravel, configuración inicial, documentación de la instalación, comprobación de versiones y resolución de los problemas del entorno |
| **Yassine** | Rol 3 · Analista funcional | Estudio del problema, identificación de actores, recopilación de requisitos, casos de uso, funcionalidades, restricciones, reglas iniciales, flujos de usuario y alcance del proyecto |
| **Yeray** | Rol 4 · Arquitecto de dominio y datos | Identificación de entidades y relaciones, información que será necesario almacenar, propuesta inicial del modelo de datos, separación de responsabilidades e identificación de los módulos del sistema |

**Rol 5 · Calidad, seguridad y documentación (compartido):** yo me ocupo de la estructura
documental, la revisión del README, las normas de Git, el control del `.gitignore`, la
comprobación de que no se publican secretos y la revisión de los entregables. Alejandro lleva las
evidencias técnicas. Yassine y Yeray hacen la revisión cruzada de sus respectivos documentos antes
de la entrega. La preparación de la presentación es de los cuatro.

La coordinación técnica no es la jefatura del grupo: su función es facilitar que el resto pueda
trabajar sin bloqueos.

### 1.1 Lo que hacemos todos

Al margen del rol asignado, cada integrante:

- Monta su propia máquina virtual Ubuntu siguiendo el procedimiento documentado en
  `docs/entorno.md`, lo que además sirve como prueba de reproducibilidad.
- Realiza commits propios e identificables en varias ramas `feature/`, repartidos a lo largo de
  las dos semanas.
- Revisa al menos dos Pull Requests de compañeros.
- Redacta tres de los doce casos de uso.
- Captura sus propias evidencias técnicas.
- Interviene en la exposición.

Ninguno de nosotros puede justificar desconocer una parte del proyecto alegando que la hizo otro
compañero. El reparto organiza quién redacta, no quién entiende.

---

## 2. Normas internas de trabajo

| Asunto | Norma acordada |
|---|---|
| Reparto de tareas | Cada tarea se abre como *issue* en GitHub y se asigna a una persona. Quien la termina la cierra |
| Toma de decisiones | Por consenso en la reunión. Si no lo hay, se vota. La decisión y su motivo se anotan en `docs/decisiones.md` |
| Reuniones | **Diarias**, de 15 minutos: qué hice, qué haré, qué me bloquea. Con solo nueve días hasta la entrega no hay margen para detectar un bloqueo cuarenta y ocho horas tarde |
| Comunicación | Grupo de WhatsApp para lo urgente. Las decisiones técnicas se escriben como *issue* o en el registro de decisiones, para que quede traza |
| Idioma | Documentación y mensajes de commit en español. Código y nombres de fichero en inglés |
| Retrasos | Se detectan en la reunión diaria y se redistribuye la carga ese mismo día |
| Conflictos internos | Se resuelven dentro del grupo. Si una discrepancia bloquea el trabajo, se expone en la reunión y se vota. Solo acudiríamos al profesor en un caso extremo |
| Formato de entrega | Las plantillas se completan en LibreOffice Writer y se exportan a PDF. La copia en Markdown vive en `docs/` |

---

## 3. Estrategia de Git y GitHub

**Repositorio:** <https://github.com/AntonioGlezM/PROYECTO-CLOUD>

### 3.1 Ramas

| Rama | Finalidad | Quién la toca |
|---|---|---|
| `main` | Versión estable y entregable. Solo recibe código revisado y funcionando. Protegida | Solo por Pull Request desde `develop`, con aprobación |
| `develop` | Integración del trabajo de los cuatro. Es la rama por defecto y la del día a día | Todos, por Pull Request |
| `test` | Pruebas conjuntas antes de promocionar a `main`: aquí se verifica que una instalación limpia funciona | Antonio y Alejandro |
| `feature/...` | Una rama por tarea concreta, corta y con un solo propósito | Quien tenga asignada la issue |

Ramas de trabajo previstas en esta unidad:

| Rama | Contenido | Responsable |
|---|---|---|
| `feature/organizacion-equipo` | Organización, planificación, decisiones y README | Antonio |
| `feature/entorno` | Documentación del entorno de desarrollo | Alejandro |
| `feature/entorno-laravel` | Proyecto Laravel inicial | Alejandro |
| `feature/pagina-comprobacion` | Página que demuestra el ciclo MVC | Alejandro y Yeray |
| `feature/requisitos` | Problema, objetivos, actores, requisitos y reglas | Yassine |
| `feature/arquitectura` | Módulos, dominio Cloud, modelo de datos, arquitectura e IA | Yeray |
| `feature/casos-de-uso` | Los doce casos de uso, tres por persona | Los cuatro |
| `feature/seguridad-repo` | `.gitignore`, `.env.example` y auditoría de secretos | Antonio |

### 3.2 Política de commits

Formato: `tipo(ámbito): descripción en imperativo`

Tipos que usamos: `docs` documentación · `feat` funcionalidad nueva · `fix` corrección ·
`chore` mantenimiento y configuración.

```
docs(entorno): documentar instalación de PHP 8.5 y extensiones
docs(requisitos): añadir RF-01 a RF-08 del módulo de proyectos
docs(casos-uso): redactar CU10, CU11 y CU12
feat(pagina): añadir EstadoController y vista de comprobación
chore(repo): añadir .env.example sin valores
fix(readme): corregir el comando de ejecución del proyecto
```

Preferimos commits pequeños y frecuentes a uno grande al final: el historial se lee mejor, los
conflictos son menores y la aportación de cada uno queda más visible.

### 3.3 Pull Requests

Nada se fusiona en `develop` sin Pull Request y **al menos una revisión** de otro compañero.
Quien abre el PR no lo aprueba ni lo fusiona.

Cada PR indica qué incluye, qué apartado de la rúbrica cubre y qué conviene revisar, y antes de
aprobarse se comprueba que no contiene secretos, que está revisado ortográficamente y que las
figuras están numeradas.

### 3.4 Resolución de conflictos de integración

Un conflicto aparece cuando dos personas modifican las mismas líneas del mismo fichero. Para
reducirlos, cada documento de `docs/` tiene un único responsable y hacemos `pull` de `develop`
antes de empezar cada sesión de trabajo. Cuando aun así aparece uno, lo resuelve quien abrió la
rama, nunca con `--force`, y deja constancia en el mensaje de commit.

### 3.5 Seguridad del repositorio

- El fichero `.env` está ignorado y se verifica con `git check-ignore -v`.
- Se mantiene un `.env.example` con las claves de configuración pero **sin valores**.
- El repositorio no contiene contraseñas, tokens, API keys, credenciales, claves privadas ni
  secretos de conexión.
- Antes de cada entrega se revisa también el **historial**, no solo los ficheros actuales: borrar
  un secreto en un commit posterior no lo elimina del repositorio.
- La contraseña de la base de datos de desarrollo la elige cada integrante en su propia máquina y
  no se escribe en ningún documento ni aparece en ninguna captura.

### 3.6 Evidencia de participación

Esta tabla se completa el 13 de octubre, a partir de la salida de `git shortlog -sn --all` y de
la pestaña *Insights → Contributors* del repositorio, cuando el trabajo esté terminado y los datos
sean reales.

| Integrante | Usuario de GitHub | Commits | Ramas en las que ha trabajado | Aportación identificable |
|---|---|---|---|---|
| Antonio González | AntonioGlezM | | | |
| Alejandro | AacostaA-1327| | | |
| Yassine | yassinesayar| | | |
| Yeray | Yeeeray09| | | |

---

## 4. Reparto de la exposición

Diez minutos. Intervenimos los cuatro. El profesor puede hacer preguntas individuales sobre
cualquier parte del proyecto, no solo sobre el tramo de cada uno.

| Minuto | Quién | Contenido |
|---|---|---|
| 0:00 – 2:00 | Yassine | Problema que se pretende resolver y requisitos |
| 2:00 – 4:00 | Alejandro | Entorno preparado, tecnologías y justificación, demostración de la página de comprobación |
| 4:00 – 5:30 | Antonio | Organización del equipo y funcionamiento de Git: ramas, Pull Requests y contribuciones |
| 5:30 – 8:00 | Yeray | Arquitectura cliente-servidor, módulos principales, modelo de datos conceptual y arquitectura propuesta |
| 8:00 – 9:00 | Antonio | Planificación y estado actual del proyecto |
| 9:00 – 10:00 | Los cuatro | Cierre y preguntas |

---

## 5. Control de versiones de este documento

| Versión | Fecha | Autores | Cambios |
|---|---|---|---|
| 1.0 | 2026-10-06 | Los cuatro | Organización del equipo, normas internas y estrategia de Git para la UT1 |