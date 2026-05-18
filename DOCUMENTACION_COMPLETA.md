# Documentación Completa: EIS System — Aplicación PHP Procedural

> **Propósito:** Este documento explica el funcionamiento de cada archivo y cada línea de la aplicación EIS System, un sistema de gestión empresarial integral (inventario, ventas POS, cybercafé, proveedores, activos, asesoría legal, reportes). Está diseñado para ser ingresado a NotebookLM y generar material de estudio.

---

## Índice

1. [Estructura del proyecto](#1-estructura-del-proyecto)
2. [Configuración del servidor web (.htaccess)](#2-htaccess)
3. [Punto de entrada (index.php)](#3-indexphp)
4. [Núcleo del sistema (Router)](#4-router)
5. [Configuración (Config/database.php)](#5-databasephp)
6. [Vistas (Views)](#6-views)
7. [Modelos (Models)](#7-models)
8. [Assets frontend](#8-assets-frontend)
9. [composer.json](#9-composerjson)
10. [Flujo de navegación completo](#10-flujo-de-navegacion-completo)

---

## 1. Estructura del proyecto

```
eis_zona_web_lara/
├── .htaccess                  # Redirige al directorio src/
├── composer.json              # Configuración de dependencias PHP
├── README.md                  # Documentación general
├── docs/                      # Documentos de diseño de BD
├── vendor/                    # Dependencias instaladas (Composer)
└── src/
    ├── .htaccess              # Reescribe URLs al front controller
    ├── index.php              # PUNTO DE ENTRADA (Front Controller)
    ├── Config/
    │   └── database.php       # Conexión a MySQL con PDO
    ├── app/
    │   ├── core/
    │   │   └── router.php     # Enrutador procedural (68 líneas)
    │   ├── template/
    │   │   └── layout.php     # Layout HTML principal (sidebar + header)
    │   ├── Views/
    │   │   ├── login.php      # Formulario de inicio de sesión
    │   │   ├── login_validate.php # Procesador de autenticación
    │   │   ├── dashboard.php  # Panel de control con métricas
    │   │   ├── inventario.php # Tabla de productos
    │   │   ├── ventas.php     # POS con catálogo y carrito modal
    │   │   ├── proveedores.php# Solicitudes a proveedores
    │   │   ├── reportes.php   # Generador de reportes
    │   │   ├── ciberControl.php # Estaciones de cybercafé
    │   │   ├── activos.php    # Activos fijos (equipos, licencias)
    │   │   ├── asesorias.php  # Asesoría legal con validación
    │   │   └── menu.php       # Página de menú independiente
    │   └── Models/
    │       ├── crud_users.php # CRUD de usuarios (8 funciones, bcrypt)
    │       └── crud_asesorias.php # CRUD de asesorías (8 funciones)
    └── Public/
        ├── css/
        │   ├── styles.css     # Estilos principales (587 líneas, tema claro/oscuro)
        │   └── login.css      # Estilos específicos del login
        └── js/
            └── app.js         # Lógica frontend con jQuery (525 líneas)
```

---

## 2. .htaccess

### Raíz del proyecto (`/.htaccess`)

```
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ src/$1 [L]

RewriteRule ^$ src/ [L,R=301]
```

- `RewriteEngine On` - Activa el motor de reescritura de URLs de Apache (mod_rewrite).
- `RewriteCond %{REQUEST_FILENAME} !-f` - Solo aplica si el archivo NO existe físicamente (permite servir CSS, JS, imágenes).
- `RewriteCond %{REQUEST_FILENAME} !-d` - Solo aplica si el directorio NO existe físicamente.
- `RewriteRule ^(.*)$ src/$1 [L]` - Cualquier ruta se redirige internamente a src/.
- `RewriteRule ^$ src/ [L,R=301]` - Si la URL es exactamente la raíz, redirige permanentemente (301) a src/.

### Dentro de `src/` (`src/.htaccess`)

```
Options All -Indexes
RewriteEngine On

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

- `Options All -Indexes` - Bloquea el listado de directorios (seguridad).
- `RewriteRule ^$ index.php [L,QSA]` - Si la URL es la raíz (src/), redirige a index.php.
- `RewriteCond %{REQUEST_FILENAME} !-f` - Solo aplica si NO es un archivo real (CSS, JS, imágenes pasan directo).
- `RewriteCond %{REQUEST_FILENAME} !-d` - Solo aplica si NO es un directorio real.
- `RewriteRule ^(.*)$ index.php [QSA,L]` - Cualquier otra ruta se envía a index.php (Front Controller Pattern).

---

## 3. index.php (Front Controller)

```
Línea 1:  <?php
           - Apertura del bloque PHP.

Línea 2-3: Comentarios explicando que este es el punto de entrada único.

Línea 5:  require_once __DIR__.'/app/core/router.php';
           - Incluye el router procedural que maneja toda la lógica de navegación y autenticación.
           - __DIR__ apunta a src/ (el directorio actual del archivo).
           - NO hay session_start() aquí; se hace dentro de router.php.
           - NO hay autoloader de Composer; el sistema usa includes directos.
```

---

## 4. Router

### `src/app/core/router.php` (68 líneas)

El corazón de la aplicación. Es un enrutador procedural (NO orientado a objetos).

```
Línea 2:  session_start()
           - Inicia o reanuda la sesión del usuario.
           - Debe ejecutarse antes de cualquier salida al navegador.

Línea 5:  $pagina = "login";
           - Valor por defecto: muestra la página de login.

Línea 7-9: if(!empty($_GET["pagina"])){ $pagina = $_GET["pagina"]; }
           - Si la URL contiene ?pagina=nombre, usa ese nombre.
           - Ej: ?pagina=dashboard carga la vista dashboard.php.

Línea 13: preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)
           - Validación de seguridad: solo permite caracteres alfanuméricos, guiones y guiones bajos.
           - Evita path traversal o inyección de rutas.

Línea 18: $public_pages = ['login', 'login_validate'];
           - Array con páginas que NO requieren autenticación.

Línea 20-23: if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages))
             - Si NO hay sesión Y la página NO es pública: redirige al login.

Línea 27: $rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';
           - Construye la ruta absoluta al archivo de vista.

Línea 30-59: if(is_file($rutaVista))
             - Si el archivo de vista existe:
               - Páginas públicas: include directo.
               - Páginas autenticadas: define $pageTitle, $headerExtra, $contentView
                 e incluye layout.php, que a su vez incluye la vista.

Línea 38: $titulos = [ ... 'asesorias' => 'Asesoría Legal' ... ]
           - Array asociativo que mapea cada página a su título.

Línea 50: $extraHeaders = [ 'ciberControl' => '<span class="chip">...</span>' ]
           - HTML adicional para insertar en el header de páginas específicas.

Línea 61-66: else { http_response_code(404); ... }
             - Si el archivo no existe, muestra error 404 con enlace de retorno.
```

**Seguridad implementada:**
1. Validación regex del parámetro `pagina` (solo alfanumérico + guiones).
2. No se construyen rutas basadas en input del usuario (solo se verifica existencia de archivo).
3. Las páginas protegidas requieren sesión activa o son redirigidas.
4. 404 para archivos de vista inexistentes.

---

## 5. database.php

```
Línea 4-8:  $host, $db, $user, $pass, $charset
             - Configuración de conexión a MySQL.
             - db = "zwl" (Zona Web Lara).
             - charset = "utf8mb4" (soporta emojis y caracteres especiales).

Línea 10:  $dns = "mysql:host=$host;dbname=$db;charset=$charset"
            - Data Source Name para PDO.

Línea 12-16: $options = [ PDO::ATTR_ERRMODE => EXCEPTION,
                           PDO::ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC,
                           PDO::ATTR_EMULATE_PREPARES => false ]
             - Configuración de PDO:
               - EXCEPTION: los errores SQL lanzan excepciones.
               - FETCH_ASSOC: resultados como array asociativo.
               - EMULATE_PREPARES false: consultas preparadas nativas (más seguras).

Línea 18-27: try { $pdo = new PDO($dns, $user, $pass, $options); echo "Conexión exitosa"; }
              catch (\PDOException $e) { throw new \PDOException($e->getMessage(), ...); }
             - Intenta la conexión. Si falla, relanza la excepción.
             - NOTA: el echo "Conexión exitosa" rompería respuestas JSON en producción.
```

---

## 6. Vistas

### 6.1 layout.php (128 líneas)

Layout principal que envuelve todas las páginas internas (autenticadas). Usa Materialize CSS.

```
Línea 13-26: <head>
              - Meta tags (charset, viewport).
              - <title> dinámico: "$pageTitle - EIS System".
              - Material Icons (Google Fonts).
              - Materialize CSS 1.0.0 (CDN).
              - styles.css (estilos propios).
              - jQuery 3.7.1 (CDN) en el <head>.

Línea 31-67: Sidebar (menú lateral izquierdo):
              - <ul id="slide-out" class="sidenav sidenav-fixed">.
              - user-view: logo y nombre del sistema.
              - Enlaces a módulos: Dashboard, Inventario, Ventas, Solicitudes, Cyber,
                Reportes, Activos, Asesoría Legal.
              - Divisor, Theme Toggle (oscuro/claro), Cerrar Sesión.

Línea 70-106: Header (barra superior):
              - <nav class="nav-extended indigo darken-3">.
              - sidenav-trigger (botón hamburguesa en móviles).
              - brand-logo: $pageTitle.
              - Reloj: <span id="clock"> (actualizado por JS cada 1s).
              - headerExtra: HTML extra (ej: badges de cyber).
              - Campana de notificaciones con badge rojo "3".
              - Badge de usuario: "Admin".

Línea 108-114: <main> - Contenido principal:
              - <div class="container"> con padding y max-width 1400px.
              - <?php require $contentView; ?> incluye la vista específica.

Línea 117-120: Botón "Volver arriba" (#backToTop), oculto inicialmente.

Línea 122-125: Scripts:
              - materialize.min.js (CDN).
              - app.js (lógica frontend propia).
```

### 6.2 login.php (123 líneas)

Página completa (sin layout) del formulario de inicio de sesión.

```
- DOCTYPE, meta tags, Material Icons, Materialize CSS, login.css.
- Tarjeta de login centrada (.login-card.z-depth-4).
- Logo: ⚡ con gradiente.
- Mensaje de error: si ?error=1, muestra "Credenciales incorrectas".
- Formulario POST a ?pagina=login_validate.
- Campos: usuario (person) y contraseña (lock) con íconos Material.
- Enlace "Olvidaste tu contraseña?" (no funcional).
- Botones sociales: Google y GitHub (SVG, no funcionales).
- Botón flotante de cambio de tema (themeToggle).
- jQuery + Materialize JS + script inline para tema oscuro/claro con localStorage.
```

### 6.3 dashboard.php (130 líneas)

Vista del panel de control, se inyecta dentro del layout.

```
- Banner de bienvenida con gradiente índigo.
- 4 tarjetas métricas (metric-card):
  1. Ventas Hoy: $1,245.50 (icono payments, 23 transacciones)
  2. Stock Crítico: 4 (icono warning, rojo)
  3. Sesiones Cyber: 7 (icono desktop_windows, naranja)
  4. Solicitudes Pend.: 3 (icono assignment, azul)
- Tabla Horas Pico: 3 franjas horarias con tendencias (↑↓).
- Tabla Productos Sin Stock: Resma A4, Tóner Negro, Cable USB-C.
- Actividad Reciente: 3 eventos con íconos (venta, stock, cyber).
```

### 6.4 inventario.php (129 líneas)

Vista de gestión de inventario.

```
- Barra de herramientas: buscador (#searchProducto), filtro por estado (select), botón "Nuevo Producto".
- Tabla de 3 productos con columnas: ID, Producto, Precio, Stock, Mínimo, Estado, Acciones.
- Estados: Crítico (rojo), OK (verde), Bajo (naranja).
- Acciones: ver movimientos (inventory), editar (edit).
- Paginación: 3 páginas (solo UI).
```

### 6.5 ventas.php (130 líneas)

Vista del Punto de Venta (POS) con carrito modal.

```
- Header: título y resumen del carrito (total + badge).
- Catálogo de 5 productos con data-name y data-price:
  Teclado Mecánico ($45), Mouse USB ($12.50), Auriculares ($35), Monitor 24" ($189), Cable USB-C ($8).
- Buscador (#posSearch) con debounce 200ms.
- Cada producto tiene botón "+" flotante (hover).
- Modal del carrito (#posCartModal) con Materialize:
  - Lista dinámica de productos.
  - Total, botón "Vaciar", botón "Procesar Venta".
- Lógica del carrito en app.js (posCart array, actualizarPosUI, confirmación).
```

### 6.6 proveedores.php (115 líneas)

Vista de solicitudes a proveedores.

```
- Barra de herramientas: buscador (#searchProveedor), filtro por estado, botón "Nueva Solicitud".
- Tabla de 3 solicitudes: #SOL-089 (Pendiente), #SOL-088 (Recibida), #SOL-087 (Cancelada).
- Paginación: 3 páginas.
```

### 6.7 reportes.php (139 líneas)

Vista de reportes y estadísticas.

```
- 4 tarjetas métricas mensuales: Ventas ($34,580), Productos (245), Horas Cyber (1,240), Solicitudes (28).
- Generador de Reportes: tipo (select), fechas (date), formato (radio PDF/Excel/CSV), botón generar.
- Lista de 4 reportes recientes con botón de descarga.
- Submit simulado con toast (app.js).
```

### 6.8 ciberControl.php (133 líneas)

Vista de control de estaciones de cybercafé.

**NOVEDAD: Datos generados desde PHP** con un array $zonas que define 3 zonas (Zona A: 4 estaciones, Zona B: 3, Zona C: 3).

```
- 4 contadores resumen calculados con PHP (array_filter): Disponibles, Ocupadas, Mantenimiento, Total.
- Filtros: Todas, Disponibles, Ocupadas, Mantenimiento.
- Grid de 10 estaciones con estructura interna (.station-inner > .station-header/.body/.footer).
- Estados: disponible (verde, check_circle), ocupada (naranja, timelapse + precio), mantenimiento (rojo, build).
- Toggle de estados con confirmación y animación.
```

### 6.9 activos.php (207 líneas)

Vista de gestión de activos fijos.

```
- Barra de herramientas: buscador (#searchActivo), filtro por categoría, botón "Nuevo Activo".
- Equipos (3): Impresora HP (Activo), Proyector Epson (Mantenimiento), Router Cisco (Activo).
- Licencias (2): Windows 11 Pro (Vencida), Office 365 (Activa).
- Herramientas (2 de 4): Kit Destornilladores (Disponible), Multímetro Digital (Disponible).
- Resumen: Activos Totales (9), En Mantenimiento (1), Requieren Atención (1).
```

### 6.10 asesorias.php (128 líneas) — NUEVO MÓDULO

Vista de asesoría legal con validación de documentos.

```
- Banner con ícono gavel y título "Asesoría Legal".
- Formulario de registro: nombre, cédula, tipo de documento (con datalist de 14 sugerencias),
  descripción, botón "Validar y Registrar".
- Catálogo de 11 documentos permitidos (chips verdes).
- Validación en tiempo real: al escribir el documento, el botón cambia:
  - Documento permitido: botón indigo "Validar y Registrar".
  - Documento no permitido: botón rojo "Derivar a Oficina Oficial".
- Historial de asesorías registradas (en memoria, no en BD).
- Buscador en historial con debounce.
- Eliminación de registros con confirmación.
- Documentos NO permitidos: Juicio/Litigio, Demanda Formal, Apelación, Recurso de Amparo,
  Divorcio Contencioso, Herencia/Sucesión, Penal/Delito → requieren derivación.
```

### 6.11 menu.php (158 líneas)

Página independiente (sin layout) con diseño tipo tarjeta.

```
- Fondo degradado oscuro.
- Tarjeta central con enlaces a módulos (Dashboard, Inventario, Cyber, Proveedores, Reportes, Ventas, Activos).
- Cada enlace con ícono Material y flecha animada (hover).
- Toggle de tema oscuro/claro con localStorage.
- Muestra usuario "admin" en el footer.
- CSS embebido específico para esta página.
```

---

## 7. Models

### 7.1 crud_users.php (54 líneas)

Modelo CRUD para la tabla 'usuarios'. Usa funciones sueltas (no clase).

```
Línea 3:  require_once __DIR__.'/../../Config/database.php'
           - Carga la conexión PDO.

--- crearUsuario($pdo, $username, $password, $nombre, $email, $telefono, $rol_id) ---
           - Genera hash bcrypt con password_hash().
           - INSERT en usuarios con prepared statements.

--- obtenerUsuarios($pdo) ---
           - SELECT con JOIN a roles para obtener el nombre del rol.

--- obtenerUsuarioPorId($pdo, $id) ---
           - SELECT * con WHERE id = ?

--- obtenerUsuarioPorUsername($pdo, $username) ---
           - SELECT solo usuarios activos (activo = TRUE).

--- autenticarUsuario($pdo, $username, $password) ---
           - Busca usuario por username.
           - Verifica contraseña con password_verify().
           - Actualiza ultimo_acceso con NOW().
           - Retorna el usuario o false.

--- actualizarUsuario($pdo, $id, $nombre, $email, $telefono, $rol_id, $activo) ---
           - UPDATE con COALESCE para mantener valores existentes.

--- actualizarPassword($pdo, $id, $password) ---
           - Genera nuevo hash y actualiza.

--- eliminarUsuario($pdo, $id) ---
           - DELETE por ID.
```

### 7.2 crud_asesorias.php (49 líneas)

Modelo CRUD para la tabla 'asesorias'.

```
--- crearAsesoria($pdo, $ciudadano, $cedula, $documento, $descripcion, $estado, $usuario_id) ---
           - INSERT con fecha_registro = NOW().

--- obtenerAsesorias($pdo) ---
           - SELECT con LEFT JOIN a usuarios, ORDER BY fecha_registro DESC.

--- obtenerAsesoriasPorEstado($pdo, $estado) ---
           - Filtrado por estado.

--- obtenerAsesoriaPorId($pdo, $id) ---
           - SELECT con JOIN a usuarios.

--- buscarAsesoriasPorCedula($pdo, $cedula) ---
           - Búsqueda con LIKE %cedula%.

--- actualizarAsesoria($pdo, $id, $ciudadano, $cedula, $documento, $descripcion, $estado) ---
           - Si el estado es Finalizada o Archivada, establece fecha_cierre automáticamente.

--- eliminarAsesoria($pdo, $id) ---
           - DELETE por ID.

--- contarAsesoriasPorEstado($pdo) ---
           - SELECT COUNT(*) GROUP BY estado.
```

**NOTA**: Ambos archivos existen y son funcionalmente correctos, pero NINGUNA vista actual los utiliza. Son código preparado para la futura implementación de backend.

---

## 8. Assets Frontend

### 8.1 styles.css (587 líneas)

Hoja de estilos principal. Define el tema visual completo.

```
Línea 1-22: Variables CSS (:root) para tema claro:
             22 variables: --primary, --success, --warning, --danger, --info,
             --bg, --surface, --text, --text-muted, --border, --shadow, --radius.

Línea 24-43: [data-theme="dark"]: sobrescribe 22 variables para tema oscuro.

Línea 45-50: Estilos base del body.

Línea 52-59: Header y main con padding-left: 300px (sidebar).
             Media query ≤992px: padding-left: 0.

Línea 61-72: Sidebar activo con borde derecho.

Línea 74-76: Tarjetas con borde redondeado y sombra.

Línea 77-87: Welcome banner con gradiente.

Línea 89-141: Metric cards con indicador de color superior (::before).
             Efecto hover: translateY(-4px).

Línea 142-189: Station cards con borde superior según estado.
             Estados: disponible (verde), ocupada (naranja), mantenimiento (rojo).

Línea 191-215: Cart items con hover highlight y botón eliminar.

Línea 217-240: Activity items con icono circular.

Línea 242-265: Adaptaciones de sidebar y formularios para tema oscuro.

Línea 267-304: POS product cards con hover, selected, animación pulse.

Línea 306-363: Botón volver arriba, notificaciones, toasts, paginación.

Línea 365-387: Más adaptaciones de tema oscuro.

Línea 389-495: Estructura interna de estaciones (station-inner, header, body, footer,
             badge, price), zone-divider, filter buttons.

Línea 497-538: Modal del carrito POS, botón "+" flotante.

Línea 540-587: Estilos de asesoría legal: .legal-permitido (verde), .legal-denegado (rojo),
             #documentValidationResult con borde izquierdo de color.
```

### 8.2 login.css (65 líneas)

Estilos específicos para la página de login.

```
Línea 1-17: Variables CSS para tema claro y oscuro.
Línea 19-27: Body con flexbox centrado y gradiente.
Línea 29-31: Tema oscuro: gradiente más oscuro.
Línea 33-43: Tarjeta de login con animación slideUp.
Línea 45-56: Logo con gradiente y sombra.
Línea 58-65: Adaptaciones para tema oscuro (inputs, labels).
```

### 8.3 app.js (525 líneas)

Lógica frontend completa con jQuery.

```
Línea 1: var EIS = {}; — Objeto global para utilidades.

Línea 3-16: $(function () { ... }) — jQuery ready:
             Inicializa Materialize: .sidenav(), .formSelect(), .tooltip(), .modal(),
             .dropdown(), .tabs(), .collapsible(), .materialbox(), .parallax(),
             .pushpin(), .scrollSpy().

Línea 18-27: Reloj en tiempo real:
             - actualizarReloj() con toLocaleTimeString() y toLocaleDateString().
             - setInterval cada 1 segundo.

Línea 29-35: Sistema de notificaciones Toast:
             - EIS.toast(msg, color, icon) usa M.toast() de Materialize.

Línea 37-54: Tema oscuro/claro:
             - updateThemeUI(theme) actualiza icono y texto.
             - Persiste en localStorage.
             - Event delegation en #themeToggle.

Línea 56-58: Transición de página con fadeIn (400ms).

Línea 60-83: Animación de contadores:
             - animarContadores() con $.animate() de 0 a valor final.
             - Soporta formato moneda ($1,245.50) y enteros.

Línea 85-127: Búsqueda en tablas con debounce:
             - debounce(fn, delay) — técnica que limita ejecución.
             - filtrarTabla(input, table, colIndex).
             - Inputs: #searchProducto, #searchProveedor, #searchActivo, #posSearch.

Línea 129-145: Filtro por estado (select):
             - #filterEstado, #filterEstadoProv filtran por badge de estado.

Línea 147-234: Sistema POS (carrito de compras):
             - posCart = [], posTotal = 0.
             - Click en .pos-product → agrega al carrito.
             - actualizarPosUI(), actualizarMiniTotal(), actualizarCarritoModal().
             - Procesar venta con confirmación y toast.
             - Vaciar carrito con confirmación.

Línea 236-291: Cybercafé:
             - actualizarCyberContadores() cuenta estaciones por estado.
             - Click en .station-card: toggle disponible ↔ ocupada.
             - Filtro de estaciones con slideDown/hide.
             - Animación scale en station-status.

Línea 293-302: Reportes:
             - Submit de #formReporte: previene envío, simula generación con setTimeout.

Línea 304-314: Botones de acción:
             - [data-confirm]: confirmación antes de acción.
             - .btn-nuevo: toast "demo".

Línea 316-325: Paginación: cambia clase active.

Línea 327-330: Descarga: toast "Descargando archivo...".

Línea 332-336: Tooltips: preserva títulos en hover.

Línea 338-499: Asesoría Legal:
             - allowedDocs: array con 11 documentos permitidos.
             - asesoriasRegistradas: array de objetos.
             - documentoPermitido(doc): validación contra allowedDocs.
             - actualizarHistorial(): renderiza tabla de asesorías.
             - mostrarValidacion(): muestra resultado (permitido/denegado).
             - Submit del formulario: valida, registra y actualiza historial.
             - Input en #documento: cambia color del botón según validación.
             - Eliminación de asesorías del historial.
             - Búsqueda en historial con debounce.

Línea 501-509: Notificaciones demo (campana):
             - Click en #notifBell: 3 toasts + oculta badge.

Línea 512-523: Botón volver arriba:
             - Scroll > 400px: fadeIn.
             - Click: animate scrollTop a 0 (400ms).
```

---

## 9. composer.json

```
Línea 2:  "name": "carlospez/clase"
Línea 3-7: "autoload": { "psr-4": { "App\\": "src/" } }
           - Namespace App\ mapeado a src/.
           - Por ahora solo el autoloader está instalado (vendor/).
           - Las clases con namespace no existen aún en src/.
Línea 8-12: Autores: Carlos Páez.
Línea 14: "require": {} — Sin dependencias externas.
```

---

## 10. Flujo de navegación completo

### 10.1 Diagrama de flujo de una petición

```
Usuario escribe: http://localhost/eis_zona_web_lara/src/?pagina=dashboard

1. Apache recibe la petición
   └── src/.htaccess  →  RewriteCond !-f, !-d  →  RewriteRule ^(.*)$ index.php [QSA,L]
       └── src/index.php  ←  Petición llega aquí

2. index.php:
   └── require_once __DIR__.'/app/core/router.php'

3. router.php:
   ├── session_start()
   ├── $pagina = $_GET['pagina'] ?? 'login'  →  "dashboard"
   ├── preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)  →  OK
   ├── $_SESSION['logged_in'] === true?  →  Sí
   ├── $rutaVista = __DIR__ . '/../Views/dashboard.php'
   ├── is_file($rutaVista)  →  Sí
   ├── $pageTitle = 'Panel de Control'
   ├── $contentView = $rutaVista
   └── require __DIR__ . '/../template/layout.php'
       ├── <html><head><title>Panel de Control - EIS System</title>
       ├── Sidebar (sidenav con enlaces a módulos)
       ├── Header con reloj, notificaciones, headerExtra
       ├── <main>
       │   └── require $contentView → dashboard.php
       │       ├── Welcome banner
       │       ├── 4 metric cards
       │       ├── Tabla horas pico
       │       ├── Tabla productos sin stock
       │       └── Actividad reciente
       ├── Botón volver arriba
       ├── Materialize JS
       └── app.js (inicializa componentes, reloj, animaciones)

4. Respuesta HTML enviada al navegador
```

### 10.2 Resumen de la arquitectura

| Capa | Rol | Archivos |
|------|-----|----------|
| **Front Controller** | Punto de entrada único | `src/index.php` |
| **Router** | Enrutamiento y autenticación | `src/app/core/router.php` |
| **Vista (Layout)** | Template maestro HTML | `src/app/template/layout.php` |
| **Vistas** | Contenido específico | `src/app/Views/*.php` |
| **Modelos** | CRUD con PDO (preparados) | `src/app/Models/*.php` |
| **Config** | Conexión a BD | `src/Config/database.php` |
| **Frontend JS** | Lógica cliente con jQuery | `src/Public/js/app.js` |
| **Frontend CSS** | Estilos personalizados | `src/Public/css/*.css` |

### 10.3 Mapa de páginas

| Parámetro | Archivo | Autenticación | Título | Descripción |
|-----------|---------|---------------|--------|-------------|
| `login` | `login.php` | No | Login - EIS System | Formulario de inicio de sesión |
| `login_validate` | `login_validate.php` | No | — | Procesa autenticación |
| `dashboard` | `dashboard.php` | Sí | Panel de Control | Métricas y actividad |
| `inventario` | `inventario.php` | Sí | Gestión de Inventario | Productos y stock |
| `ventas` | `ventas.php` | Sí | Punto de Venta (POS) | Carrito de compras |
| `ciberControl` | `ciberControl.php` | Sí | Control de Cybercafé | Estaciones cyber |
| `proveedores` | `proveedores.php` | Sí | Solicitudes a Proveedores | Solicitudes de compra |
| `reportes` | `reportes.php` | Sí | Reportes y Estadísticas | Generador de reportes |
| `activos` | `activos.php` | Sí | Gestión de Activos | Activos fijos |
| `asesorias` | `asesorias.php` | Sí | Asesoría Legal | Validación documentos |
| `menu` | `menu.php` | Sí | — | Menú alternativo |

### 10.4 Tecnologías utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | 7.4+ | Lenguaje backend (procedural) |
| MySQL | 8.0+ | Base de datos (19 tablas, no implementada en vistas) |
| Apache | - | Servidor web con mod_rewrite |
| Composer | - | Autoloading PSR-4 |
| Materialize CSS | 1.0.0 | Framework CSS Material Design |
| jQuery | 3.7.1 | Manipulación del DOM y eventos |
| Material Icons | - | Iconografía |
| PDO | - | Conexión segura a base de datos |

---

## Notas importantes

1. **Autenticación**: Las credenciales actuales son `admin` / `1234` (hardcodeadas en `login_validate.php`). En producción deberían almacenarse en base de datos con contraseñas hasheadas.

2. **Datos estáticos**: Todas las vistas internas muestran datos de ejemplo estáticos. No hay conexión a base de datos en las vistas actuales. Los modelos `crud_users.php` y `crud_asesorias.php` están preparados pero no se usan en la UI.

3. **Frontend jQuery**: Toda la interactividad se maneja con jQuery en el lado del cliente. No hay framework frontend.

4. **Modo oscuro**: Persiste la preferencia del usuario en `localStorage` con la clave `theme`.

5. **Asesoría Legal**: Módulo nuevo con validación frontend de documentos permitidos. Los datos no persisten entre recargas de página.

6. **Esquema BD v2.0**: 19 tablas con 26 índices, 3 vistas, 1 función, 2 procedimientos, 2 triggers y 1 evento programado.

7. **Arquitectura actual**: 100% procedural (sin clases PHP). El archivo `composer.json` tiene configurado PSR-4 para futura migración a MVC.
