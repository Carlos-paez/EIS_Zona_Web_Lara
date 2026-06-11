# DOCUMENTACION TECNICA DETALLADA - EIS_Zona_Web_Lara

## Indice
1. [Descripcion General](#descripcion-general)
2. [Flujo de la Aplicacion](#flujo-de-la-aplicacion)
3. [Analisis de Codigo Fuente](#analisis-de-codigo-fuente)
4. [Explicacion Detallada por Archivo](#explicacion-detallada-por-archivo)
5. [Layout Maestro](#layout-maestro)
6. [JavaScript Modular](#javascript-modular)
7. [Base de Datos](#base-de-datos)
8. [Offline y PWA](#offline-y-pwa)
9. [CSS y Estilos](#css-y-estilos)

---

## Descripcion General

**EIS System** es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con interfaz **Material Design** (Materialize CSS 1.0.0) y **jQuery 3.7.1**. Utiliza un patron **Front Controller** con enrutador procedural y layout maestro.

El proyecto simula un sistema completo para administrar un negocio que incluye: cybercafe, ventas POS, inventario, proveedores, activos, asesoria legal, reportes y usuarios.

**Caracteristicas tecnicas destacadas**:
- Assets 100% locales (sin dependencia de CDN)
- JavaScript modular en 7 archivos especializados
- Service Worker para funcionamiento offline
- PWA con manifest.json
- Tema oscuro/claro con persistencia en localStorage
- Motor de busqueda con debounce en tablas

---

## Flujo de la Aplicacion

```
1. Usuario accede a index.php
   |
2. index.php carga router.php
   |
3. router.php inicia sesion (session_start())
   |
4. Obtiene parametro "pagina" de la URL (?pagina=X)
   |
5. Valida que el nombre sea alfanumerico (seguridad regex)
   |
6. Verifica si el usuario esta logueado
   |
7. Si no esta logueado Y la pagina no es publica -> Redirige a login
   |
8. Si la pagina es publica (login/login_validate) -> Carga directa
   |
9. Si es pagina autenticada -> Carga layout.php que incluye:
   |  - Sidebar con Materialize Sidenav
   |  - Header con nav, reloj y notificaciones
   |  - Contenido especifico de la vista
   |  - Scripts: Materialize JS + modulos JS segun pagina
   |  - Service Worker registration
   |
10. Si el archivo no existe -> Muestra error 404
```

---

## Analisis de Codigo Fuente

### Estadisticas del Proyecto

| Archivo | Lineas | Proposito |
|---------|--------|----------|
| src/index.php | 6 | Punto de entrada |
| src/Config/database.php | 27 | Configuracion BD |
| src/app/core/router.php | 75 | Enrutamiento + layout + ruta AJAX inventario |
| src/app/template/layout.php | 162 | Layout maestro con JS condicional |
| src/app/Controllers/inventarioController.php | 247 | Controlador AJAX inventario (10 acciones) |
| src/app/Models/crud_inventario.php | 265 | CRUD inventario (15+ funciones) |
| src/app/Models/crud_users.php | 54 | CRUD usuarios (8 funciones) |
| src/app/Models/crud_asesorias.php | 49 | CRUD asesorias (8 funciones) |
| src/app/Views/login.php | 134 | Pagina login |
| src/app/Views/login_validate.php | 30 | Validacion login |
| src/app/Views/dashboard.php | 130 | Panel principal |
| src/app/Views/menu.php | 170 | Menu navegacion |
| src/app/Views/inventario.php | 474 | Gestion inventario (conectado a BD) |
| src/app/Views/ventas.php | 130 | Punto de venta |
| src/app/Views/proveedores.php | 115 | Solicitudes |
| src/app/Views/reportes.php | 139 | Reportes |
| src/app/Views/activos.php | 207 | Activos fijos |
| src/app/Views/ciberControl.php | 133 | Control cyber |
| src/app/Views/asesorias.php | 128 | Asesoria legal |
| src/app/Views/usuarios.php | — | Gestion usuarios |
| src/Public/css/styles.css | 1105 | Estilos personalizados |
| src/Public/css/login.css | 138 | Estilos login |
| src/Public/css/material-icons.css | 14 | Estilos Material Icons |
| src/Public/css/materialize.min.css | — | Materialize CSS (local) |
| src/Public/js/app.core.js | 31 | Funciones compartidas |
| src/Public/js/app.init.js | 80 | Inicializacion Materialize |
| src/Public/js/app.tables.js | 47 | Busqueda en tablas |
| src/Public/js/app.ui.js | 45 | UI notificaciones |
| src/Public/js/app.pos.js | 117 | Sistema POS |
| src/Public/js/app.cyber.js | 78 | Estaciones cyber |
| src/Public/js/app.legal.js | 208 | Asesoria legal |
| src/Public/js/app.inventario.js | 450 | CRUD inventario via AJAX |
| src/manifest.json | 15 | Manifiesto PWA |
| src/sw.js | 83 | Service Worker |
| src/offline.php | 43 | Pagina offline |
| src/Database/estructura.sql | 528 | Esquema BD v2.0 |
| src/Database/datos_prueba.sql | 229 | Datos prueba |

---

## Explicacion Detallada por Archivo

### 1. `src/index.php` (6 lineas)

```php
<?php
    require_once __DIR__.'/app/core/router.php';
?>
```

Punto de entrada unico (Front Controller). Todas las solicitudes pasan por aqui gracias a las reglas de reescritura de Apache (.htaccess).

---

### 2. `src/app/core/router.php` (75 lineas) - EL CEREBRO DE LA APLICACION

```php
<?php
session_start();

$pagina = "login";

if(!empty($_GET["pagina"])){
    $pagina = $_GET["pagina"];
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
    $pagina = "login";
}

$public_pages = ['login', 'login_validate'];
if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
    header("Location: ?pagina=login");
    exit;
}

// RUTA PARA CONTROLADOR DE INVENTARIO (AJAX)
if ($pagina === 'inventario' && isset($_GET['action'])) {
    require __DIR__ . '/../Controllers/inventarioController.php';
    exit;
}

$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';

if(is_file($rutaVista)){
    if (in_array($pagina, $public_pages)) {
        require $rutaVista;
    } else {
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestion de Inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafe',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadisticas',
            'activos'      => 'Gestion de Activos',
            'asesorias'    => 'Asesoria Legal',
            'usuarios'     => 'Gestion de Usuarios',
        ];
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text" style="...">5 Disponibles</span><span class="chip orange white-text" style="...">4 Ocupadas</span>',
        ];
        $pageTitle = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';
        $contentView = $rutaVista;
        require __DIR__ . '/../template/layout.php';
    }
} else {
    http_response_code(404);
    echo "<h1>Error 404: Pagina no encontrada</h1>";
    echo "<p>La pagina <strong>{$pagina}</strong> no existe.</p>";
    echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
}
?>
```

**Funciones y variables**:

| Linea | Codigo | Explicacion |
|--------|---------|-------------|
| 2 | `session_start()` | Inicia o reanuda una sesion PHP |
| 4 | `$pagina = "login"` | Valor por defecto "login" |
| 7-8 | `$_GET["pagina"]` | Toma el nombre de la pagina desde la URL |
| 13 | `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` | Valida solo letras, numeros, guiones (seguridad) |
| 18 | `$public_pages` | Array con paginas publicas (login, login_validate) |
| 20 | `!isset($_SESSION['logged_in'])` | Verifica si NO esta autenticado |
| 38 | `$titulos` | Array con titulos para cada pagina |
| 51 | `$extraHeaders` | HTML extra para el header (badges de cyber) |
| 56 | `$contentView` | Ruta de la vista a incluir dentro del layout |

La diferencia clave: si es pagina publica carga directa la vista; si no, usa `layout.php` con titulos dinamicos y carga condicional de JavaScript.

---

### 3. `src/app/template/layout.php` (159 lineas) - LAYOUT MAESTRO

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a237e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.json">
    <title><?php echo $pageTitle; ?> - EIS System</title>
    <!-- Material Icons (local) -->
    <link rel="stylesheet" href="Public/css/material-icons.css">
    <!-- Materialize CSS (local) -->
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="Public/css/styles.css">
    <!-- jQuery (local) -->
    <script src="Public/js/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- Sidebar con Sidenav de Materialize -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <!-- 10 modulos + theme toggle + cerrar sesion -->
    </ul>
    <!-- Header con navegacion -->
    <header>
        <nav class="nav-extended indigo darken-3">
            <!-- Titulo, reloj, notificaciones, usuario -->
        </nav>
    </header>
    <!-- Contenido principal -->
    <main>
        <div class="container">
            <?php require $contentView; ?>
        </div>
    </main>
    <!-- Boton volver arriba -->
    <div id="backToTop" class="btn-floating indigo">...</div>

    <!-- Scripts globales -->
    <script src="Public/js/materialize.min.js"></script>
    <script src="Public/js/app.core.js"></script>
    <script src="Public/js/app.init.js"></script>
    <script src="Public/js/app.tables.js"></script>
    <script src="Public/js/app.ui.js"></script>

    <!-- Scripts condicionales por pagina -->
    <?php if ($pagina === 'ventas'): ?>
    <script src="Public/js/app.pos.js"></script>
    <?php endif; ?>
    <?php if ($pagina === 'ciberControl'): ?>
    <script src="Public/js/app.cyber.js"></script>
    <?php endif; ?>
    <?php if ($pagina === 'asesorias'): ?>
    <script src="Public/js/app.legal.js"></script>
    <?php endif; ?>
    <?php if ($pagina === 'inventario'): ?>
    <script src="Public/js/app.inventario.js"></script>
    <?php endif; ?>

    <!-- Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js');
    }
    </script>
</body>
</html>
```

**Componentes del layout**:

| Componente | Descripcion |
|-----------|-------------|
| `sidenav` | Sidebar fijo con Materialize Sidenav (11 items) |
| `nav` | Barra superior con titulo dinamico, reloj, notificaciones |
| `container` | Contenedor central donde se renderiza `$contentView` |
| `backToTop` | Boton flotante para volver arriba |
| `Materialize JS` | Framework UI (local) |
| Modulos JS | 4 archivos base + 3 condicionales por pagina |
| `sw.js` | Service Worker para cache offline |

**Variables PHP pasadas desde router.php**:

| Variable | Proposito |
|----------|-----------|
| `$pageTitle` | Titulo de la pagina (ej: "Panel de Control") |
| `$headerExtra` | HTML extra en el header (ej: badges de cyber) |
| `$contentView` | Ruta al archivo de vista a incluir |
| `$pagina` | Nombre de la pagina actual (para carga condicional de JS) |

---

### 4. `src/Config/database.php` (27 lineas)

Configuracion de conexion PDO a MySQL con:
- Host: localhost
- Base de datos: zwl
- Charset: utf8mb4
- Modo error: PDO::ERRMODE_EXCEPTION
- Fetch mode: PDO::FETCH_ASSOC
- Prepared statements reales (EMULATE_PREPARES = false)

---

### 5. `src/app/Models/crud_users.php` (54 lineas)

Contiene 8 funciones CRUD para la tabla `usuarios`:
- `crearUsuario()` - INSERT con password_hash bcrypt
- `obtenerUsuarios()` - SELECT ALL con JOIN a roles
- `obtenerUsuarioPorId()` - SELECT ONE
- `obtenerUsuarioPorUsername()` - SELECT por username (solo activos)
- `autenticarUsuario()` - Autenticacion con password_verify
- `actualizarUsuario()` - UPDATE
- `actualizarPassword()` - UPDATE password con bcrypt
- `eliminarUsuario()` - DELETE

**NOTA**: Las funciones existen pero no son utilizadas por las vistas actuales.

### 5b. `src/app/Models/crud_asesorias.php` (49 lineas)

Contiene 8 funciones CRUD para la tabla `asesorias`:
- `crearAsesoria()`, `obtenerAsesorias()`, `obtenerAsesoriasPorEstado()`
- `obtenerAsesoriaPorId()`, `buscarAsesoriasPorCedula()`
- `actualizarAsesoria()`, `eliminarAsesoria()`, `contarAsesoriasPorEstado()`

**NOTA**: Las funciones existen pero no son utilizadas por las vistas actuales.

---

### 6. `src/app/Views/login.php` (134 lineas)

Pagina de login con diseno Material Design. NO usa el layout (pagina publica).

**Caracteristicas**:
- Formulario con campos usuario y contraseña
- Tema oscuro/claro con localStorage
- Botones sociales (Google/GitHub) no funcionales
- Mensaje de error si `?error=1`
- Material Icons y Materialize CSS locales
- Carga `app.core.js` para el namespace EIS

---

### 7. `src/app/Views/login_validate.php` (30 lineas)

Procesa la autenticacion con credenciales hardcodeadas (admin/1234).
- Si exito: `$_SESSION['logged_in'] = true`, redirige a dashboard
- Si falla: redirige a `?pagina=login&error=1`

---

### 8. Vistas Autenticadas (Contenido Solo)

Todas las vistas autenticadas son solo fragmentos HTML sin estructura completa. El layout maestro provee el HTML comun.

#### 8.1 `dashboard.php` (130 lineas)
Panel principal con banner de bienvenida, 4 metricas (Ventas Hoy, Stock Critico, Sesiones Cyber, Solicitudes Pendientes), tablas de horas pico y productos sin stock, y actividad reciente.

#### 8.2 `ventas.php` (130 lineas) - PUNTO DE VENTA (POS)
Catalogo de 5 productos con carrito modal. Toda la logica en `app.pos.js`.
- Click en producto agrega al carrito (data-name, data-price)
- Modal del carrito con total, eliminar, vaciar y procesar
- Busqueda de productos con debounce 200ms

#### 8.3 `ciberControl.php` (133 lineas) - CONTROL DE CYBERCAFE
10 estaciones organizadas en 3 zonas (Gamer, Estandar, VIP) con estados disponible/ocupada/mantenimiento. Los datos de estaciones se generan desde PHP con un array `$zonas`. Contadores calculados con PHP (`array_filter`).

#### 8.4 `inventario.php` (474 lineas) - GESTION DE INVENTARIO (FUNCIONAL CON BD)
Modulo completo conectado a la base de datos via `crud_inventario.php`. Incluye:
- **KPIs**: 4 tarjetas con total de productos, stock critico, stock bajo y valor total (desde BD)
- **Tabla de productos**: Listado completo con estado, stock, precios (desde BD)
- **Filtros**: Busqueda por texto con debounce + filtro por estado (OK, Critico, Sin stock)
- **CRUD completo**: Crear, editar y eliminar productos via AJAX
- **Movimientos de stock**: Entrada y salida con actualizacion de stock y bitacora
- **Historial**: Visualizacion de movimientos por producto
- **3 modales**: Producto (crear/editar), Movimientos (historial), Stock (entrada/salida)
- **JS**: `app.inventario.js` (450 lineas) para todas las operaciones AJAX

#### 8.5 `proveedores.php` (115 lineas)
Tabla de solicitudes con 3 ejemplos, busqueda y filtro por estado.

#### 8.6 `reportes.php` (139 lineas)
Formulario de generacion con selectores (tipo, fechas, formato), 4 metricas mensuales, listado de reportes recientes.

#### 8.7 `activos.php` (207 lineas)
Activos agrupados por categoria (Equipos 3, Licencias 2, Herramientas 4) con resumen de totales.

#### 8.8 `asesorias.php` (128 lineas) - ASESORIA LEGAL
Formulario de registro con validacion de documentos permitidos (11 tipos). Toda la logica en `app.legal.js`.
- Validacion en tiempo real: boton cambia de color segun tipo de documento
- Historial de asesorias registradas en la sesion
- Busqueda en el historial con debounce
- Eliminacion de registros

#### 8.9 `menu.php` (170 lineas)
Pagina alternativa con diseno tipo tarjeta, estilo independiente, enlaces a 7 modulos.

#### 8.10 `usuarios.php`
Gestion de usuarios (UI estatica basica).

---

## JavaScript Modular

### Arquitectura

El monolito `app.js` original se dividio en **8 archivos modulares** organizados por funcionalidad:

| Archivo | Lineas | Proposito | Carga |
|---------|--------|-----------|-------|
| `app.core.js` | 31 | Namespace EIS, debounce, filtrarTabla, EIS.toast | Siempre |
| `app.init.js` | 80 | Init Materialize, reloj, tema, animaciones | Siempre |
| `app.tables.js` | 47 | Busqueda en tablas, filtro estado, paginacion | Siempre |
| `app.ui.js` | 45 | Notificaciones, botones, reportes, tooltips | Siempre |
| `app.pos.js` | 117 | Sistema carrito POS | Solo ventas |
| `app.cyber.js` | 78 | Gestion estaciones cyber | Solo ciberControl |
| `app.legal.js` | 208 | Validacion documentos legales | Solo asesorias |
| `app.inventario.js` | 450 | CRUD inventario via AJAX | Solo inventario |

### app.core.js - Funciones Compartidas

```javascript
var EIS = {};  // Namespace global

function debounce(fn, delay) { ... }
function filtrarTabla(inputSelector, tableSelector, colIndex) { ... }
EIS.toast = function (msg, color, icon) { ... }
```

### app.init.js - Inicializacion

```javascript
$(function () {
    // Componentes Materialize: sidenav, select, tooltips, modal, tabs, etc.
    // Reloj digital: setInterval cada 1s
    // Tema oscuro/claro con localStorage
    // Animacion de transicion (fadeIn)
    // Animacion de contadores numericos
    // Boton volver arriba (scroll)
});
```

### app.tables.js - Busqueda y Filtros

```javascript
$(function () {
    // #searchProducto - Tabla inventario (col 1)
    // #searchProveedor - Tabla proveedores (col 1)
    // #searchActivo - Tabla activos (col 0)
    // #filterEstado, #filterEstadoProv - Filtro por estado
    // .pagination - Paginacion
});
```

### app.ui.js - Interacciones UI

```javascript
$(function () {
    // #notifBell - Notificaciones demo
    // #formReporte - Generador de reportes simulado
    // [data-confirm] - Botones con confirmacion
    // .btn-nuevo - Boton nuevo elemento
    // .btn-download - Descarga simulada
    // .btn-floating, .tooltip-me - Tooltips mejorados
});
```

### app.pos.js - Sistema POS

```javascript
$(function () {
    var posCart = [];
    var posTotal = 0;

    $(document).on('click', '.pos-product', function () { ... });
    // actualizarPosUI(), actualizarMiniTotal(), actualizarCarritoModal()
    // .cart-item-remove, #openCartBtn, #procesarVenta, #vaciarCarrito
    // #posSearch - Busqueda productos con debounce 200ms
});
```

### app.cyber.js - Estaciones Cyber

```javascript
$(function () {
    function actualizarCyberContadores() { ... }
    // Click en .station-card: toggle disponible/ocupada con confirmacion
    // Click en .filter-btn: filtrar por estado
});
```

### app.legal.js - Asesoria Legal

```javascript
$(function () {
    var allowedDocs = [ /* 11 tipos permitidos */ ];
    var asesoriasRegistradas = [];

    function normalizarDoc(texto) { ... }
    function documentoPermitido(doc) { ... }
    function actualizarHistorial() { ... }
    function mostrarValidacion(tipo, mensaje, esPermitido) { ... }

    // #asesoriaForm - Submit: validacion y registro
    // #documento - Input: validacion en tiempo real
    // .btn-eliminar-asesoria - Eliminar registro
    // #searchAsesoria - Busqueda en historial
});
```

---

## Base de Datos

### Esquema General

- **Base de datos**: `zwl` (Zona Web Lara)
- **Motor**: InnoDB
- **Charset**: utf8mb4
- **Version**: 2.2

### Tablas (19 total)

| # | Tabla | Proposito |
|---|-------|-----------|
| 1 | `roles` | Catalogo de roles de usuario |
| 2 | `categorias` | Catalogo de categorias de productos |
| 3 | `marcas` | Catalogo de marcas de productos |
| 4 | `tipos_activo` | Catalogo de tipos de activos |
| 5 | `tarifas_cyber` | Tarifas de estaciones de cybercafe |
| 6 | `tipos_pago` | Catalogo de metodos de pago |
| 7 | `usuarios` | Usuarios del sistema (con bcrypt) |
| 8 | `productos` | Catalogo de productos |
| 9 | `proveedores` | Proveedores |
| 10 | `producto_proveedor` | Relacion M:N productos-proveedores |
| 11 | `ventas` | Registro de ventas |
| 12 | `detalle_ventas` | Detalle de productos vendidos |
| 13 | `solicitudes` | Pedidos a proveedores |
| 14 | `detalle_solicitudes` | Detalle de productos solicitados |
| 15 | `activos` | Activos fijos |
| 16 | `estaciones_cyber` | Estaciones de cybercafe |
| 17 | `sesiones_cyber` | Sesiones de uso |
| 18 | `movimientos_stock` | Historial de inventario |
| 19 | `asesorias` | Casos de asesoria legal |

### Indices (26 total)
Indices en columnas clave: rol_id, codigo_barras, categoria_id, marca_id, ventas.fecha, ventas.usuario_id, proveedor_id, estacion_id, producto_id, etc.

### Vistas (3)
- `v_productos_stock` - Productos con estado de stock calculado
- `v_ventas_diarias` - Agregacion de ventas diarias
- `v_sesiones_activas` - Sesiones de cyber activas con costo estimado

### Objetos de BD
- `fn_estado_stock` - FUNCTION: calcula estado del stock
- `sp_registrar_movimiento_stock` - PROCEDURE: registro transaccional
- `sp_cerrar_sesion_cyber` - PROCEDURE: cierre de sesion cyber
- `trg_actualizar_totales_venta` - TRIGGER: AFTER INSERT actualiza totales
- `trg_auditar_precio_producto` - TRIGGER: BEFORE UPDATE registro cambio precio
- `ev_vencer_licencias` - EVENT: diario vence licencias expiradas

---

## Offline y PWA

### Assets Locales

Todos los recursos que antes se cargaban desde CDN ahora son locales:

| Recurso | Antes (CDN) | Ahora (Local) |
|---------|-------------|---------------|
| Materialize CSS | cdnjs.cloudflare.com | `Public/css/materialize.min.css` |
| Materialize JS | cdnjs.cloudflare.com | `Public/js/materialize.min.js` |
| jQuery 3.7.1 | code.jquery.com | `Public/js/jquery-3.7.1.min.js` |
| Material Icons | fonts.googleapis.com | `Public/css/material-icons.css` + `Public/fonts/MaterialIcons-Regular.ttf` |

### Service Worker (`sw.js`)

```javascript
var CACHE_NAME = 'eis-cache-v1';
var STATIC_ASSETS = [
  'Public/css/material-icons.css', 'Public/css/materialize.min.css',
  'Public/css/styles.css', 'Public/css/login.css',
  'Public/js/jquery-3.7.1.min.js', 'Public/js/materialize.min.js',
  'Public/js/app.core.js', 'Public/js/app.init.js',
  'Public/js/app.tables.js', 'Public/js/app.ui.js',
  'Public/js/app.pos.js', 'Public/js/app.cyber.js', 'Public/js/app.legal.js',
  'Public/fonts/MaterialIcons-Regular.ttf',
  'manifest.json', 'offline.php'
];
```

**Estrategia de cache**:
- **Cache First** para assets estaticos (CSS, JS, fuentes)
- **Network First** con fallback a `offline.php` para navegacion PHP

### Manifest (`src/manifest.json`)

```json
{
  "name": "EIS System",
  "short_name": "EIS",
  "display": "standalone",
  "background_color": "#1a237e",
  "theme_color": "#1a237e",
  "icons": []
}
```

### Pagina Offline (`src/offline.php`)
Pagina de fallo que se muestra cuando el usuario esta sin conexion e intenta navegar. Incluye:
- Icono `cloud_off` de Material Icons
- Mensaje "Sin Conexion"
- Boton "Reintentar" que recarga la pagina

---

## CSS y Estilos

### `src/Public/css/styles.css` (1105 lineas)

Estilos personalizados que complementan Materialize CSS. Incluye:

**Variables CSS (Custom Properties)**: 22 variables para tema claro y 22 para tema oscuro (`[data-theme="dark"]`).

**Clases principales**:

| Clase | Proposito |
|-------|-----------|
| `.metric-card` | Tarjetas de metricas con borde colorido |
| `.station-card` | Tarjetas de estaciones cyber |
| `.pos-product` | Tarjetas de productos en POS |
| `.cart-item` | Items del carrito de compras |
| `.activity-item` | Items de actividad reciente |
| `.welcome-banner` | Banner de bienvenida en dashboard |
| `.result-count` | Contador de resultados de busqueda |
| `.legal-permitido` | Badge verde para documentos permitidos |
| `.legal-denegado` | Badge rojo para documentos no permitidos |
| `.zone-divider` | Separador de zonas en cybercafe |
| `.zone-title` | Titulo de zona en cybercafe |
| `.station-inner/header/body/footer` | Estructura interna de estaciones |
| `.pos-add-btn` | Boton "+" flotante en productos POS |
| `.filter-btn` | Botones de filtro en cybercafe |

### `src/Public/css/login.css` (138 lineas)
Estilos especificos para la pagina de login (independiente del layout).

### `src/Public/css/material-icons.css` (14 lineas)
Hoja de estilos local para Material Icons con referencia a la fuente TTF local.

---

## Resumen de Funcionalidad por Modulo

### Login System (Funcional)
- **Archivos**: `login.php`, `login_validate.php`
- **Flujo**: Formulario -> Validacion -> Sesion -> Dashboard
- **Credenciales**: admin / 1234 (hardcodeadas)

### Dashboard (UI Estatica)
- **Archivo**: `dashboard.php` (130 lineas)
- **Contenido**: 4 metricas, tablas de horas pico y productos sin stock, actividad reciente

### Inventario (UI Estatica con filtros)
- **Archivo**: `inventario.php` (129 lineas)
- **JS**: `app.tables.js` - busqueda con debounce, filtro por estado, paginacion

### Punto de Venta (Semi-funcional con jQuery)
- **Archivo**: `ventas.php` (130 lineas)
- **JS**: `app.pos.js` - Carrito completo, modal Materialize, busqueda productos

### Cyber Control (Interactivo con jQuery)
- **Archivo**: `ciberControl.php` (133 lineas)
- **JS**: `app.cyber.js` - Toggle de estados, filtros visuales, contadores dinamicos PHP

### Solicitudes (UI Estatica)
- **Archivo**: `proveedores.php` (115 lineas)
- **JS**: `app.tables.js` - busqueda y filtro por estado

### Reportes (Simulado)
- **Archivo**: `reportes.php` (139 lineas)
- **JS**: `app.ui.js` - Formulario generador simulado con toasts

### Activos (UI Estatica)
- **Archivo**: `activos.php` (207 lineas)
- **JS**: `app.tables.js` - busqueda por nombre

### Asesoria Legal (Semi-funcional con jQuery)
- **Archivo**: `asesorias.php` (128 lineas)
- **JS**: `app.legal.js` - Validacion documentos en tiempo real, registro historial local

### Usuarios (UI Estatica)
- **Archivo**: `usuarios.php`
- Configuracion basica de usuarios

---

## Conclusiones y Recomendaciones

### Estado Actual
El proyecto es un **prototipo de UI** con:
- Diseno Material Design con Materialize CSS
- Navegacion funcional con sidebar responsivo
- Sistema de login basico
- Tema oscuro/claro con persistencia
- Carrito POS funcional (frontend)
- Control de cyber interactivo con datos PHP
- Busquedas y filtros con debounce
- Validacion de asesoria legal en frontend
- **JavaScript modular** en 8 archivos especializados
- **Assets 100% locales** (sin dependencia de CDN)
- **Service Worker** para funcionamiento offline
- **PWA** con manifest.json
- Esquema de BD completo v2.0 (19 tablas)
- Modelos CRUD preparados (inventario 15+ funcs, usuarios 8, asesorias 8)
- Controlador AJAX de inventario con 10 acciones
- Modulo de inventario completamente funcional con BD

### Para hacerlo funcional se requiere:
1. **Conectar vistas con BD** (usar PDO en AJAX)
2. **Implementar CRUD** real (insert, update, delete) via backend
3. **Agregar seguridad** (CSRF, password hashing, sanitizacion)
4. **Migrar a MVC** (controladores con clases, namespaces)

---

**Documentacion generada**: Junio 2026
**Version**: 2.2
**Autor**: Carlos Paez Guerra

