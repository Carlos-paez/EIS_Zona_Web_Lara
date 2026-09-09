# Guía de Preparación para la Defensa de Proyecto

> **Proyecto:** EIS System - Sistema de Gestión Integral (PHP)  
> **Versión:** 1.0

---

## Índice

1. [Introducción](#introducción)
2. [Preguntas sobre Arquitectura y Framework](#2-preguntas-sobre-arquitectura-y-framework)
3. [Preguntas sobre el Patrón MVC](#3-preguntas-sobre-el-patrón-mvc)
4. [Preguntas sobre Seguridad](#4-preguntas-sobre-seguridad)
5. [Preguntas sobre Base de Datos](#5-preguntas-sobre-base-de-datos)
6. [Preguntas sobre el Funcionamiento CRUD](#6-preguntas-sobre-el-funcionamiento-crud)
7. [Preguntas sobre Módulos Específicos](#7-preguntas-sobre-módulos-específicos)
8. [Preguntas sobre Renderizado y Layout](#8-preguntas-sobre-renderizado-y-layout)
9. [Preguntas sobre Mejoras y Proyección](#9-preguntas-sobre-mejoras-y-proyección)
10. [Consejos Finales](#10-consejos-finales)
11. [Notas Rápidas (Cheat Sheet)](#11-notas-rápidas-cheat-sheet)

---

## Introducción

Este documento reúne las preguntas que un profesor tiene más probabilidad de hacer durante la defensa de un proyecto basado en **EIS System**, un sistema de gestión integral desarrollado en **PHP con un framework custom (propio)** bajo el patrón **Front Controller + MVC**.

Cada pregunta incluye:
- **La pregunta** que podrían hacerte.
- **Por qué la preguntan** (qué evaluador quiere comprobar).
- **Cómo responderla** (guion con la respuesta correcta).
- **Punto clave** (el detalle que demuestra dominio).

---

## 2. Preguntas sobre Arquitectura y Framework

### 2.1 ¿Qué framework utilizan?

**Por qué la preguntan:** Confirman que entiendes la base tecnológica de tu proyecto.

**Cómo responder:**
- "EIS System está desarrollado con un **framework custom**, es decir, un framework propio construido desde cero para este proyecto. No utiliza Laravel, CodeIgniter ni ningún framework externo."
- "Solo usamos **Composer** para el autoloading de clases mediante el estándar **PSR-4**, que mapea el namespace `App\` a la carpeta `src/app/`."

**Punto clave:** Solo Composer es externo; todo el resto de la arquitectura es código propio, lo que demuestra comprensión profunda.

### 2.2 ¿Por qué no usaste un framework popular como Laravel?

**Por qué la preguntan:** Evalúan tu criterio técnico y si fue una decisión consciente.

**Cómo responder:**
- "Por varias razones: (1) **Aprendizaje profundo**: construir el router, el modelo y el controlador desde cero me permitió entender cómo funcionan internamente los frameworks. (2) **Ligereza y rendimiento**: al no cargar cientos de bibliotecas que no uso, la aplicación arranca más rápido. (3) **Control total**: tengo control absoluto sobre la seguridad, el enrutamiento y la estructura. (4) **Cero dependencias**: es más fácil de desplegar en cualquier servidor con PHP y Apache."
- "Reconozco que para proyectos grandes Laravel ofrece más herramientas listas, pero para las necesidades de este sistema la solución propia es más que suficiente."

**Punto clave:** No menosprecies los frameworks; reconoce sus ventajas pero justifica tu decisión.

### 2.3 ¿Cómo funciona el sistema de rutas?

**Por qué la preguntan:** Es el corazón del front controller, la pregunta más probable de la defensa.

**Cómo responder:**
- "El sistema se basa en **query string**: la URL lleva un parámetro `?pagina=X`."
- "El archivo **.htaccess** en `src/` reescribe las URL. Cuando el usuario escribe `/clientes` en el navegador, Apache internamente lo convierte a `index.php?pagina=clientes` sin que el usuario lo vea."
- "Todo pasa por el **front controller** `index.php`, que instancia la clase `Router` y llama a su método `handle()`."

**Punto clave:** Mencionar las dos formas de acceder: URL amigable (`/clientes`) y query string (`?pagina=clientes`).

### 2.4 ¿Cómo agregarías un nuevo módulo al sistema?

**Por qué la preguntan:** Verifican que entiendes la estructura y la extensibilidad.

**Cómo responder:**
- "Son 6 pasos: (1) Creo la tabla en MySQL. (2) Creo el **Modelo** en `Models/NuevoModulo.php`. (3) Creo el **Controlador** en `Controllers/NuevoModuloController.php`. (4) Creo la **Vista** en `Views/nuevomodulo.php`. (5) Registro la página en el Router: agrego el título en `PAGE_TITLES` y el controlador en la constante `CONTROLLERS`, además del `use` de la clase. (6) Agrego el enlace en el sidebar del `layout.php`."

**Punto clave:** Mencionar que el router valida el nombre con regex `/^[a-zA-Z0-9_-]+$/` antes de procesarlo.

### 2.5 ¿Qué es el Front Controller?

**Por qué la preguntan:** Concepto básico de arquitectura web.

**Cómo responder:**
- "Es un patrón donde **todas las solicitudes entran por un único punto** (`index.php`). El front controller centraliza la lógica común: iniciar sesión, generar CSRF, resolver la página solicitada, verificar autenticación y despachar al controlador o la vista correspondiente. Así no hay que repetir ese código en cada página."

**Punto clave:** Centralización = mantenible y seguro.

---

## 3. Preguntas sobre el Patrón MVC

### 3.1 ¿Explica qué es MVC y cómo se aplica en tu proyecto?

**Por qué la preguntan:** Pregunta de concepto clásica y casi obligatoria.

**Cómo responder:**
- "MVC separa la aplicación en tres capas: **Modelo** (datos y lógica de negocio), **Vista** (interfaz de usuario) y **Controlador** (maneja las peticiones y orquesta)."
- "En EIS: el **Modelo** (carpeta `Models/`) se conecta a la BD con PDO y valida los datos en sus setters. El **Controlador** (carpeta `Controllers/`) recibe la acción del usuario, valida entrada, verifica CSRF y responde JSON. La **Vista** (carpeta `Views/`) genera el HTML y se inyecta dentro del `layout.php`."

**Punto clave:** La relación Modelo↔Controlador↔Vista: nunca se hablan directamente, siempre pasan por el controlador.

### 3.2 ¿Qué función tiene el Modelo en tu sistema?

**Por qué la preguntan:** Verifican que entiendes la capa de datos.

**Cómo responder:**
- "El modelo encapsula: (1) **Conexión** a la base de datos heredada de `App\Core\Model` que usa un singleton PDO. (2) **Validación** de datos en los setters, por ejemplo `setCedula()` valida formato, longitud y caracteres. (3) **Consultas SQL**: todos los métodos CRUD usan prepared statements con `bindParam()`. (4) **Sanitización**: cada valor se limpia con `htmlspecialchars()` antes de guardarse."

**Punto clave:** La validación vive constantemente en el modelo; así ningún controlador puede crear datos inválidos.

### 3.3 ¿Qué función tiene el Controlador?

**Por qué la preguntan:** Verifican el flujo de entrada.

**Cómo responder:**
- "El controlador es la capa que **recibe las peticiones AJAX**. Su método `handle()` lee la variable `$_GET['action']` y usa un `match` para despachar a la acción correspondiente: `listar`, `detalle`, `crear`, `actualizar`, `eliminar`, `kpis`. Antes de cada operación de escritura valida el **CSRF** y los campos con el `Validator`. Siempre responde en formato **JSON**."

**Punto clave:** Un solo método de entrada (`handle()`) = estructura uniforme en todos los módulos.

### 3.4 ¿Cómo se relacionan el modelo y la vista?

**Por qué la preguntan:** Trampa común de MVC mal entendido.

**Cómo responder:**
- "En este sistema, **la vista nunca contacta al modelo directamente**. La vista solo renderiza HTML. Los datos llegan por dos vías: en la carga inicial, el Router inyecta variables (`$pageTitle`, `$headerExtra`, `$contentView`); para operaciones dinámicas, el **JavaScript** de la vista hace peticiones AJAX al controlador, que a su vez consulta al modelo y devuelve JSON."

**Punto clave:** El flujo siempre es: Vista (JS) → Controlador → Modelo → BD → JSON → Vista.

### 3.5 ¿Qué es el modelo base `App\Core\Model`?

**Por qué la preguntan:** Demuestra que heredaste comportamiento reutilizable.

**Cómo responder:**
- "Es una clase **abstracta** que todos los modelos extienden. Provee: la conexión PDO (`$this->db` via `Database::getConnection()`), y métodos de validación/sanitización reutilizables como `sanitizeString()`, `sanitizeInt()`, `validateNotEmpty()`, `validateFecha()`, `validateEnLista()`, `validateMax()`, etc. Esto evita duplicar código en los 13 modelos."

**Punto clave:** Herencia = DRY (Don't Repeat Yourself).

---

## 4. Preguntas sobre Seguridad

### 4.1 ¿Cómo evitas la inyección SQL?

**Por qué la preguntan:** Es LA pregunta de seguridad más importante. Te van a preguntar sí o sí.

**Cómo responder:**
- "Uso **prepared statements** con `bindParam()` en todas las consultas. Ejemplo del modelo Cliente:"

```php
$stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bindParam(1, $id, PDO::PARAM_INT);
$stmt->execute();
```

- "Además, todo dato de entrada pasa por sanitización (`sanitizeInt`, `sanitizeString`) y validación en los setters antes de llegar a la consulta."

**Punto clave:** Nunca concateno el valor del usuario directamente en la cadena SQL.

### 4.2 ¿Cómo evitas el ataque XSS (Cross-Site Scripting)?

**Por qué la preguntan:** Seguridad del lado del cliente.

**Cómo responder:**
- "El método `sanitizeString()` del modelo base aplica `htmlspecialchars($input, ENT_QUOTES, 'UTF-8')`, que convierte caracteres como `<`, `>`, `"`, `'` en entidades HTML, inutilizables como código."
- "También al inyectar el contenido en el layout, se usa `htmlspecialchars()` al imprimir variables."

**Punto clave:** No solo se valida la entrada; se escapa la salida.

### 4.3 ¿Por qué usas tokens CSRF en los formularios?

**Por qué la preguntan:** Seguridad contra ataques del lado del servidor.

**Cómo responder:**
- "El token **CSRF** protege contra falsificación de peticiones entre sitios (Cross-Site Request Forgery). El Router genera un token aleatorio con `bin2hex(random_bytes(32))` al iniciar sesión y lo guarda en `$_SESSION['csrf_token']`."
- "En el layout se inyecta en `window.EIS.csrfToken` y jQuery lo adjunta automáticamente a cada petición POST mediante `$.ajaxSetup()`."
- "En el servidor, cada acción de escritura (`crear`, `actualizar`, `eliminar`) verifica que el token recibido coincida con el de la sesión usando `hash_equals()`."

**Punto clave:** Verificación en servidor con comparación segura (`hash_equals` previene ataques de tiempo).

### 4.4 ¿Cómo controlas el acceso a las páginas?

**Por qué la preguntan:** Autenticación y autorización.

**Cómo responder:**
- "El Router mantiene una constante `PUBLIC_PAGES` con las páginas públicas: `login` y `login_validate`."
- "Todas las demás páginas son privadas. Si el usuario no tiene `$_SESSION['logged_in']` y pide una página privada, se redirige automáticamente a `login`."
- "Además, el sistema cuenta con un módulo de **Roles y Permisos** (RBAC) para control de acceso más fino."

**Punto clave:** Doble capa: sesión global + permisos por rol.

### 4.5 ¿Qué pasa si el usuario cierra sesión?

**Por qué la preguntan:** Manejo correcto de sesiones.

**Cómo responder:**
- "El método `logout()` del Router hace: (1) `session_regenerate_id(true)` para **invalidar la sesión antigua y evitar session fixation**. (2) Vacía el array `$_SESSION`. (3) Destruye la sesión con `session_destroy()`. (4) Redirige al login."

**Punto clave:** `session_regenerate_id` es la parte que el 90% de los proyectos estudiantiles omite, mide mérito.

### 4.6 ¿Cómo manejas los datos de los formularios?

**Por qué la preguntan:** Validación de entrada.

**Cómo responder:**
- "Tengo una clase `Validator` con métodos especializados: `Validator::id()` para enteros, `Validator::cedula()`, `Validator::telefono()`, `Validator::texto()`, `Validator::requeridos()` que verifica campos obligatorios."
- "Y en los controladores nunca uso `$_POST` directo; siempre valido primero, luego paso los datos al modelo."

**Punto clave:** Capa de validación separada = limpieza y seguridad.

### 4.7 ¿Qué haces si se intenta escribir una acción inválida en la URL?

**Por qué la preguntan:** Manejo de errores.

**Cómo responder:**
- "Si el usuario escribe `?pagina=clientes&action=borrarTodo`, el `match` del controlador cae en el caso `default` y retorna `{ success: false, error: "Acción no válida" }`."
- "Si la página no existe, el Router responde un **404** con un mensaje y un enlace de vuelta al dashboard."
- "Además, el nombre de página se valida con regex antes de cualquier despacho."

**Punto clave:** Sin acciones ocultas no permitidas; todo está controlado.

---

## 5. Preguntas sobre Base de Datos

### 5.1 ¿Cómo te conectas a la base de datos?

**Por qué la preguntan:** Conexión y manejo de recursos.

**Cómo responder:**
- "Uso un **patrón Singleton** en la clase `Database`. Solo existe UNA instancia de la conexión PDO, con credenciales de MySQL. Los modelos acceden a ella a través de `Database::getConnection()`."
- "PDO me da prepared statements nativas y manejo multi-base de datos (aunque uso MySQL)."

**Punto clave:** Singleton = una sola conexión reutilizada; evita abrir cientos de conexiones.

### 5.2 ¿Cómo manejas las Foreign Keys?

**Por qué la preguntan:** Integridad referencial.

**Cómo responder:**
- "Las tablas usan claves foráneas. Por ejemplo, `facturacion.cliente_id` referencia `clientes.id`."
- "Cuando intentas borrar un registro que tiene dependencias, MySQL lanza un error de **constraint**. Los controladores detectan la cadena 'foreign key constraint' en el `PDOException` y muestran un mensaje amigable al usuario: *'No se puede eliminar: el registro tiene dependencias'*, en lugar de un error técnico."

**Punto clave:** Los errores SQL crudos NUNCA llegan al usuario.

### 5.3 ¿Qué tipos de validación haces en la base de datos?

**Por qué la preguntan:** Coherencia Vistas/Modelo/BD.

**Cómo responder:**
- "Triple capa: (1) En la vista: `required`, `pattern` en HTML y validación en JS. (2) En el modelo: setters que lanzan `InvalidArgumentException` si un dato no cumple (longitud, formato, valores permitidos). (3) En el controlador: `Validator::requeridos()` antes de operaciones de escritura."

**Punto clave:** La validación nunca depende de una sola capa.

### 5.4 ¿Cómo consultas las ventas del POS?

**Por qué la preguntan:** Consultas complejas.

**Cómo responder:**
- "Usando consultas con `JOIN`. Por ejemplo, para listar ventas se une la tabla ventas con clientes y usuarios para obtener nombres en vez de solo IDs, y se agregan cálculos con funciones de agregación (SUM, COUNT) para los KPIs del dashboard."

**Punto clave:** Diferenciar consultas simples (SELECT con WHERE) y complejas (JOIN + agregaciones).

---

## 6. Preguntas sobre el Funcionamiento CRUD

### 6.1 ¿Cómo funciona el listado de registros?

**Por qué la preguntan:** El ciclo completo de datos.

**Cómo responder:**
- "El front-end (DataTables) hace una petición AJAX `GET ?pagina=clientes&action=listar`. El Router despacha al controlador. `ClienteController::listar()` llama a `$this->model->obtenerClientes()` que ejecuta un SELECT. El resultado se envía como `{ success: true, data: [...] }`. DataTables recibe el array y lo pinta en la tabla con ordenamiento, búsqueda y paginación automáticas."

**Punto clave:** Mencionar que el renderizado de tablas lo maneja DataTables en el front-end.

### 6.2 ¿Cómo funciona el crear/actualizar?

**Por qué la preguntan:** El flujo POST completo.

**Cómo responder:**
- "El formulario de la vista se serializa con jQuery y se envía por POST a `?pagina=clientes&action=crear` (o `actualizar`). jQuery adjunta automáticamente el `csrf_token` gracias a `$.ajaxSetup()`."
- "El controlador: (1) verifica CSRF con `Router::verifyCsrfToken()`, (2) valida campos obligatorios con `Validator::requeridos()`, (3) valida cada campo con reglas específicas (cédula, teléfono, etc.), (4) llama al método del modelo que valida de nuevo en los setters, (5) ejecuta el INSERT/UPDATE con prepared statements, (6) responde `{ success: true, message: '...' }` o el error."
- "Si la creación de datos es incorrecta, se muestran errores básicos en el front-end (Materialize toast)."

**Punto clave:** Doble validación (controlador y modelo), CSRF y respuesta JSON uniforme.

### 6.3 ¿Cómo funciona el eliminar?

**Por qué la preguntan:** Integridad y seguridad.

**Cómo responder:**
- "Un modal de confirmación pregunta al usuario. Al confirmar, se envía POST con el `id` (y el `csrf_token`). El controlador valida CSRF, valida el ID con `Validator::id()`, y el modelo ejecuta un `DELETE` con prepared statement."
- "Si el registro tiene dependencias (por FK), se captura el `PDOException` y se devuelve el mensaje de error amigable."

**Punto clave:** Confirmación en front-end + control de FK en back-end.

### 6.4 ¿Cómo obtienes el detalle de un registro?

**Por qué la preguntan:** Consulta por identificador.

**Cómo responder:**
- "Se hace una petición `GET ?pagina=X&action=detalle&id=5`. El ID se valida con `Validator::id($_GET['id'])`. El modelo ejecuta `SELECT ... WHERE id = ?` con `bindParam(1, $id, PDO::PARAM_INT)` y devuelve el registro o `false`. Si no existe, se responde `{ success: false, error: 'Registro no encontrado' }`."

**Punto clave:** Validar el ID antes de usarlo en la consulta.

---

## 7. Preguntas sobre Módulos Específicos

### 7.1 ¿Cómo funciona el punto de venta (POS)?

**Por qué la preguntan:** El módulo más complejo del sistema.

**Cómo responder:**
- "El POS tiene acciones: agregar productos al carrito, ajustar cantidades, cobrar, y generar el ticket."
- "Al **cobrar**, se valida el total, se verifica CSRF y el modelo inicia una **transacción** para insertar la venta, los ítems vendidos y **descontar el inventario** de forma atómica: si algo falla, `rollback`. Si todo está bien, `commit`."
- "Después se genera el comprobante usando `PdfBuilder`."

**Punto clave:** Transacciones SQL (BEGIN/COMMIT/ROLLBACK) = no quedar inventario descontado sin venta registrada.

### 7.2 ¿Cómo funciona el módulo de Cyber (control de estaciones)?

**Por qué la preguntan:** Lógica en tiempo real.

**Cómo responder:**
- "El módulo mantiene el estado de las estaciones (disponible/ocupada) y controla el tiempo de uso del cliente."
- "Las estadísticas de estaciones disponibles/ocupadas se muestran en el header mediante `PAGE_EXTRA_HEADERS` y se actualizan vía AJAX."

**Punto clave:** Estado en la BD + eventos AJAX para actualizar el header en vivo.

### 7.3 ¿Cómo funciona el Dashboard?

**Por qué la preguntan:** Consultas de agregación.

**Cómo responder:**
- "El `DashboardController` consulta los KPIs (ventas totales, clientes, productos, etc.) a través de consultas con funciones de agregación como `SUM()`, `COUNT()`, `AVG()` sobre las tablas."
- "Cada módulo expone también un KPI propio (ej. `totalClientes()`, `totalRegistros()`) que el dashboard consolida."

**Punto clave:** Las métricas vienen de la BD, no son valores fijos en la vista.

### 7.4 ¿Cómo generas los reportes?

**Por qué la preguntan:** Utilidad real del sistema.

**Cómo responder:**
- "El módulo de reportes usa `Reporte` para consultas agregadas (ventas por período, top clientes, etc.) y genera archivos con la clase `Exporter` (CSV/Excel) y `PdfBuilder` (PDF)."
- "Así el usuario puede exportar los resultados en el formato que necesita."

**Punto clave:** Exportación a múltiples formatos es un diferenciador.

---

## 8. Preguntas sobre Renderizado y Layout

### 8.1 ¿Cómo se construye el layout de la página?

**Por qué la preguntan:** Renderizado y componentes comunes.

**Cómo responder:**
- "`Router::render()` prepara las variables `$pageTitle`, `$headerExtra`, `$contentView` (ruta a la vista) y `$pagina`, y hace `require` de `template/layout.php`."
- "El layout contiene: el **sidebar** con los enlaces a `?pagina=X` (marcando como 'active' la página actual), la **navbar** con título, reloj y notificaciones, y el `<main>` que hace `<?php require $contentView; ?>` para inyectar el contenido de la página."

**Punto clave:** Una sola plantilla, el contenido cambia, la estructura no.

### 8.2 ¿Cómo haces que el enlace del menú se marque como activo?

**Por qué la preguntan:** Detalle de UX.

**Cómo responder:**
- "El layout compara la variable `$pagina` (inyectada por el Router) con el nombre de página de cada enlace. Si coincide, imprime la clase `active` sobre el elemento, y el CSS de Materialize lo resalta:"

```php
<li><a href="?pagina=clientes" class="sidenav-link<?php echo $pagina === 'clientes' ? ' active' : ''; ?>">...
```

**Punto clave:** La condición PHP se evalúa en el servidor, no con JS.

### 8.3 ¿Qué son los headers extra (`PAGE_EXTRA_HEADERS`)?

**Por qué la preguntan:** Personalización de la navbar.

**Cómo responder:**
- "Es un mapa opcional en el Router que agrega elementos HTML a la barra de navegación por página. Por ejemplo, en `ciberControl` muestra chips con el número de estaciones disponibles/ocupadas."
- "En el layout, si `$headerExtra` no está vacío, se imprime en la navbar: `<?php if (!empty($headerExtra)): ?>...`"

**Punto clave:** Extensiensibilidad sin tocar el layout por cada módulo.

### 8.4 ¿Cómo manejas las vistas públicas (login)?

**Por qué la preguntan:** Diferencia entre páginas públicas y privadas.

**Cómo responder:**
- "El Router, para páginas públicas (login), hace `require` de la vista **directamente, sin layout**."
- "Para el resto, renderiza la vista **dentro del layout** con sidebar y navbar."

**Punto clave:** El login no muestra sidebar ni navbar.

---

## 9. Preguntas sobre Mejoras y Proyección

### 9.1 ¿Qué le mejorarías o qué le agregarías?

**Por qué la preguntan:** Pensamiento crítico y proyección.

**Cómo responder:**
- "A nivel de seguridad: **RBAC más granulado** (permisos por acción y no solo por módulo) y **hasheo más fuerte** si fuera necesario."
- "A nivel funcional: **API REST** con tokens JWT para abrir el sistema a apps móviles, **paginación real** en todas las acciones `listar`, y más **reportes estadísticos** con gráficos."
- "A nivel técnico: **sesiones en cache (Redis)** para escalar a varios servidores, y **test automatizados** (PHPUnit) para las validaciones del modelo."

**Punto clave:** Que reconozcas limitaciones actuales y propongas soluciones concretas, no genéricas.

### 9.2 ¿El sistema escalaría o se puede implementar en producción?

**Por qué la preguntan:** Viabilidad real.

**Cómo responder:**
- "Sí. La arquitectura es portable: PHP y MySQL son estándar. El código está modularizado con PSR-4, sesiones únicas con regeneración, encoding UTF-8 y está listo para subirse a cualquier hosting Apache."
- "Con mejoras de seguridad en producción se podría implementar en un negocio real (hosting con HTTPS, backups automáticos, y credenciales en variables de entorno)."

**Punto clave:** Honestidad + viabilidad.

### 9.3 ¿Cómo saltaría el sistema a miles de usuarios?

**Por qué la preguntan:** Escalabilidad.

**Cómo responder:**
- "La BD MySQL soporta transacciones y consultas preparadas. Con carga alta, se podría: (1) pasar sesiones a Redis para **múltiples servidores**, (2) agregar **cache de consultas** en Redis, (3) optimizar consultas con índices adicionales, (4) particionar la BD."

**Punto clave:** Reconocer el límite y proponer soluciones.

---

## 10. Consejos Finales

### Durante la defensa

1. **Sé honesto.** Si no sabes algo, di "no lo implementé, pero sé cómo se haría...". Eso demuestra madurez.
2. **Explica, no te limites a describir.** Cuando muestres una consulta, no digas solo "esto consulta datos"; explica *por qué* usa prepared statements y *qué pasa* si el usuario envía `' OR 1=1`.
3. **Usa el manual como guion.** Repasa el flujo del Router (`handle()`, `resolvePagina()`, `dispatchAction()`, `render()`) del archivo `MANUAL_NUEVOS_MODULOS.md`.
4. **Muestra código.** Abre `router.php`, `ClienteController.php` y `Cliente.php` en la defensa y señala las líneas clave (CSRF, `bindParam`, `match`).
5. **Si te piden agregar un módulo en vivo**, sigue el checklist de 6 pasos sin dudar.

### Temas que SIEMPRE preguntan (memoriza estos 5)

| Orden tema | Concepto | Ubicación en código |
|---|---|---|
| 1 | Inyección SQL | `bindParam()` en todos los modelos |
| 2 | CSRF | `Router::verifyCsrfToken()` |
| 3 | Front Controller | `index.php` + `Router::handle()` |
| 4 | MVC | `Models/`, `Controllers/`, `Views/` |
| 5 | Control de acceso | `PUBLIC_PAGES` + `$_SESSION['logged_in']` |

---

## 11. Notas Rápidas (Cheat Sheet)

### Datos del sistema

| Dato | Valor |
|---|---|
| Framework | Custom (propio) |
| Patrón | Front Controller + MVC |
| Autoloading | Composer PSR-4 (`App\` → `src/app/`) |
| BD | MySQL vía PDO singleton |
| UI | Materialize CSS + jQuery + DataTables |
| PWA | Service Worker (`sw.js`) |
| Sessions | Única con regeneración en logout |
| CSRF | `bin2hex(random_bytes(32))` por sesión |

### Flujo de una petición (resumen de 1 línea)

```
Navegador → /clientes → .htaccess → index.php?pagina=clientes → Router::handle()
→ validar sesión → ¿action? SÍ → controlador → JSON | NO → render() → layout.php → vista
```

### Estructura de respuesta JSON

```json
{ "success": true, "data": [...] }
{ "success": false, "error": "Mensaje amigable" }
```

### Cómo responder 3 preguntas rápidas

| Pregunta | Respuesta de una línea |
|---|---|
| "¿Cómo evitas inyecciones SQL?" | "Prepared statements con `bindParam()` en PDO; nunca concateno SQL." |
| "¿Cómo proteges las operaciones?" | "Token CSRF por sesión verificado con `hash_equals()` en cada POST." |
| "¿Por qué un framework custom?" | "Control total, aprendizaje profundo, ligereza y cero dependencias." |
| "¿Cómo agrego un módulo?" | "Tabla → Modelo → Controlador → Vista → registrar en Router → link en layout." |
| "¿Qué es el Front Controller?" | "Todo entra por `index.php`, centraliza sesión, CSRF y despacho." |