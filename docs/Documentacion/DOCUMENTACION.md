# DOCUMENTACION TECNICA DETALLADA - EIS_Zona_Web_Lara

## Indice
1. [Descripcion General](#descripcion-general)
2. [Flujo de la Aplicacion](#flujo-de-la-aplicacion)
3. [Analisis de Codigo Fuente](#analisis-de-codigo-fuente)
4. [Explicacion Detallada por Archivo](#explicacion-detallada-por-archivo)
5. [Layout Maestro](#layout-maestro)
6. [JavaScript Central (app.js)](#javascript-central-appjs)
7. [Base de Datos](#base-de-datos)
8. [CSS y Estilos](#css-y-estilos)
9. [jQuery](#jquery)

---

## Descripcion General

**EIS System** es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con interfaz **Material Design** (Materialize CSS 1.0.0) y **jQuery 3.7.1**. El proyecto simula un sistema completo para administrar un negocio que incluye: cybercafe, ventas POS, inventario, proveedores, activos, asesoria legal y reportes.

**NOTA IMPORTANTE**: A pesar del nombre "eis_zona_web_lara", este proyecto **NO es Laravel**. Es PHP puro con arquitectura procedural.

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
   |  - Scripts (Materialize JS + app.js con jQuery)
   |
10. Si el archivo no existe -> Muestra error 404
```

---

## Analisis de Codigo Fuente

### Estadisticas del Proyecto

| Archivo | Lineas | Proposito |
|---------|---------|----------|
| src/index.php | 6 | Punto de entrada |
| src/Config/database.php | 27 | Configuracion BD |
| src/app/core/router.php | 68 | Enrutamiento + layout |
| src/app/Models/crud_users.php | 54 | CRUD usuarios (8 funciones) |
| src/app/Models/crud_asesorias.php | 49 | CRUD asesorias (8 funciones) |
| src/app/template/layout.php | 128 | Layout maestro Materialize |
| src/app/Views/login.php | 123 | Pagina login |
| src/app/Views/login_validate.php | 30 | Validacion login |
| src/app/Views/dashboard.php | 130 | Panel principal |
| src/app/Views/menu.php | 158 | Menu navegacion |
| src/app/Views/inventario.php | 129 | Gestion inventario |
| src/app/Views/ventas.php | 130 | Punto de venta |
| src/app/Views/proveedores.php | 115 | Solicitudes |
| src/app/Views/reportes.php | 139 | Reportes |
| src/app/Views/activos.php | 207 | Activos fijos |
| src/app/Views/ciberControl.php | 133 | Control cyber |
| src/app/Views/asesorias.php | 128 | Asesoria legal |
| src/Public/css/styles.css | 587 | Estilos personalizados |
| src/Public/css/login.css | 65 | Estilos login |
| src/Public/js/app.js | 525 | JS comun con jQuery |
| src/Database/estructura.sql | 526 | Esquema BD v2.0 |
| src/Database/datos_prueba.sql | 229 | Datos prueba |

**Total lineas de codigo**: ~2,800 lineas (PHP + CSS + JS + SQL)

---

## Explicacion Detallada por Archivo

### 1. `src/index.php` (6 lineas)

```php
<?php
// Punto de entrada unico de la aplicacion (Front Controller).
// Todas las solicitudes HTTP pasan por aqui gracias a las reglas de reescritura de Apache (.htaccess).

    require_once __DIR__.'/app/core/router.php'; // Incluye el router, que maneja la logica de navegacion y autenticacion
?>
```

**Explicacion**:
- `<?php` - Apertura de codigo PHP
- `require_once __DIR__.'/app/core/router.php'` - Incluye y ejecuta el archivo router.php una sola vez
- `__DIR__` - Constante magica que apunta al directorio actual (src/)

---

### 2. `src/app/core/router.php` (68 lineas) - EL CEREBRO DE LA APLICACION

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
        ];
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text">7 Disponibles</span><span class="chip orange white-text">3 Ocupadas</span>',
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

**Funciones y variables explicadas**:

| Linea | Codigo | Explicacion |
|--------|---------|-------------|
| 2 | `session_start()` | Inicia o reanuda una sesion PHP |
| 4 | `$pagina = "login"` | Valor por defecto "login" |
| 7-8 | `$_GET["pagina"]` | Toma el nombre de la pagina desde la URL |
| 13 | `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` | Valida solo letras, numeros, guiones |
| 18 | `$public_pages` | Array con paginas publicas (login, login_validate) |
| 20 | `!isset($_SESSION['logged_in'])` | Verifica si NO esta autenticado |
| 38 | `$titulos` | Array con titulos para cada pagina (incluye 'asesorias') |
| 51 | `$extraHeaders` | HTML extra para el header (badges de cyber) |
| 56 | `$contentView` | Ruta de la vista a incluir dentro del layout |

La diferencia clave: si es pagina publica carga directa, si no usa layout.php con titulos dinamicos.

---

### 3. `src/app/template/layout.php` (128 lineas) - LAYOUT MAESTRO

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - EIS System</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="Public/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" ...></script>
</head>
<body>

    <!-- Sidebar con Sidenav de Materialize -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <li><div class="user-view">...</div></li>
        <li><a href="?pagina=dashboard"><i class="material-icons left">dashboard</i>Dashboard</a></li>
        <li><a href="?pagina=inventario"><i class="material-icons left">inventory_2</i>Inventario</a></li>
        <li><a href="?pagina=ventas"><i class="material-icons left">shopping_cart</i>Ventas (POS)</a></li>
        <li><a href="?pagina=proveedores"><i class="material-icons left">request_quote</i>Solicitudes</a></li>
        <li><a href="?pagina=ciberControl"><i class="material-icons left">computer</i>Cyber</a></li>
        <li><a href="?pagina=reportes"><i class="material-icons left">bar_chart</i>Reportes</a></li>
        <li><a href="?pagina=activos"><i class="material-icons left">build</i>Activos</a></li>
        <li><a href="?pagina=asesorias"><i class="material-icons left">gavel</i>Asesoria Legal</a></li>
        <li><div class="divider"></div></li>
        <li><a id="themeToggle" style="cursor:pointer;">
            <i class="material-icons left" id="themeIcon">dark_mode</i>
            <span id="themeLabel">Modo Oscuro</span>
        </a></li>
        <li><a href="?pagina=login"><i class="material-icons left">logout</i>Cerrar Sesion</a></li>
    </ul>

    <!-- Header con navegacion Materialize -->
    <header>
        <nav class="nav-extended indigo darken-3">
            <div class="nav-wrapper">
                <a href="#" data-target="slide-out" class="sidenav-trigger">
                    <i class="material-icons">menu</i>
                </a>
                <span class="brand-logo"><?php echo $pageTitle; ?></span>
                <ul id="nav-mobile" class="right">
                    <li><span id="clock">Cargando...</span></li>
                    <?php if (!empty($headerExtra)): ?>
                        <li><?php echo $headerExtra; ?></li>
                    <?php endif; ?>
                    <li><a id="notifBell"><i class="material-icons">notifications</i>
                        <span id="notifBadge" class="new badge red">3</span></a></li>
                    <li><span class="badge indigo lighten-2 white-text">Admin</span></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main>
        <div class="container">
            <?php require $contentView; ?>
        </div>
    </main>

    <!-- Boton volver arriba -->
    <div id="backToTop" class="btn-floating indigo">
        <i class="material-icons">keyboard_arrow_up</i>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="Public/js/app.js"></script>
</body>
</html>
```

**Componentes del layout**:

| Componente | Descripcion |
|-----------|-------------|
| `sidenav` | Sidebar fijo con Materialize Sidenav (15 items, incluye Asesoria Legal) |
| `nav` | Barra superior con titulo dinamico, reloj, notificaciones |
| `container` | Contenedor central donde se renderiza `$contentView` |
| `backToTop` | Boton flotante para volver arriba (jQuery) |
| `Materialize JS` | Framework UI (sidenav, modals, selects, tooltips, etc.) |
| `app.js` | Script central con jQuery para toda la app |

**Variables PHP pasadas desde router.php**:

| Variable | Proposito |
|----------|-----------|
| `$pageTitle` | Titulo de la pagina (ej: "Panel de Control") |
| `$headerExtra` | HTML extra en el header (ej: badges de cyber) |
| `$contentView` | Ruta al archivo de vista a incluir |

---

### 4. `src/Config/database.php` (27 lineas)

```php
<?php
    $host = "localhost";
    $db = "zwl";
    $user = "root";
    $pass = "";
    $charset = 'utf8mb4';

    $dns = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dns, $user, $pass, $options);
        echo "Conexion exitosa";  // ERROR: rompe respuestas JSON
    }catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
?>
```

**Variables de configuracion**:

| Variable | Tipo | Explicacion |
|---------|------|-------------|
| `$host` | string | "localhost" - servidor MySQL local |
| `$db` | string | "zwl" - Zona Web Lara |
| `$user` | string | "root" - usuario MySQL default |
| `$pass` | string | Vacia en desarrollo (XAMPP/WAMP/Laragon) |
| `$charset` | string | "utf8mb4" - soporta emojis y acentos |
| `$dns` | string | Data Source Name para PDO |
| `$options` | array | ATTR_ERRMODE, FETCH_ASSOC, EMULATE_PREPARES |

---

### 5. `src/app/Models/crud_users.php` (54 lineas) - CRUD USUARIOS

Contiene 8 funciones CRUD para la tabla `usuarios` con autenticacion bcrypt:
- `crearUsuario($pdo, $username, $password, $nombre, $email, $telefono, $rol_id)` - INSERT con password_hash bcrypt
- `obtenerUsuarios($pdo)` - SELECT ALL con JOIN a roles
- `obtenerUsuarioPorId($pdo, $id)` - SELECT ONE
- `obtenerUsuarioPorUsername($pdo, $username)` - SELECT por username (solo activos)
- `autenticarUsuario($pdo, $username, $password)` - Autenticacion con password_verify + actualiza ultimo_acceso
- `actualizarUsuario($pdo, $id, $nombre, $email, $telefono, $rol_id, $activo)` - UPDATE
- `actualizarPassword($pdo, $id, $password)` - UPDATE password con bcrypt
- `eliminarUsuario($pdo, $id)` - DELETE

**NOTA**: El archivo contiene funciones preparadas pero no es utilizado por ninguna vista actual.

### 5b. `src/app/Models/crud_asesorias.php` (49 lineas) - CRUD ASESORIAS

Contiene 8 funciones CRUD para la tabla `asesorias`:
- `crearAsesoria($pdo, $ciudadano, $cedula, $documento, $descripcion, $estado, $usuario_id)` - INSERT
- `obtenerAsesorias($pdo)` - SELECT ALL con JOIN a usuarios
- `obtenerAsesoriasPorEstado($pdo, $estado)` - SELECT filtrado por estado
- `obtenerAsesoriaPorId($pdo, $id)` - SELECT ONE
- `buscarAsesoriasPorCedula($pdo, $cedula)` - Busqueda por cedula (LIKE)
- `actualizarAsesoria($pdo, $id, $ciudadano, $cedula, $documento, $descripcion, $estado)` - UPDATE con fecha_cierre automatica
- `eliminarAsesoria($pdo, $id)` - DELETE
- `contarAsesoriasPorEstado($pdo)` - Agregacion GROUP BY estado

**NOTA**: El archivo contiene funciones preparadas pero no es utilizado por ninguna vista actual.

---

### 6. `src/app/Views/login.php` (123 lineas)

Pagina de login con diseno Material Design. NO usa el layout (pagina publica).

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Meta tags, Material Icons, Materialize CSS -->
    <link rel="stylesheet" href="Public/css/login.css">
</head>
<body>
    <div class="card login-card z-depth-4 white">
        <!-- Logo EIS System con emoji ⚡ -->
        <h4 class="card-title">EIS System</h4>
        <!-- Mensaje de error si ?error=1 -->
        <?php if (isset($_GET['error'])): ?>
            <div class="card-panel red lighten-4 red-text">Credenciales incorrectas</div>
        <?php endif; ?>
        <!-- Formulario POST a ?pagina=login_validate -->
        <form action="?pagina=login_validate" method="post">
            <input type="text" name="username" required autofocus>
            <input type="password" name="password" required>
            <button type="submit">Iniciar Sesion</button>
        </form>
        <!-- Botones sociales (Google/GitHub) no funcionales -->
    </div>
    <!-- jQuery + Materialize JS + Theme toggle script -->
</body>
</html>
```

---

### 7. `src/app/Views/login_validate.php` (30 lineas)

Procesa la autenticacion con credenciales hardcodeadas (admin/1234).

**NOVEDAD**: A diferencia de versiones anteriores, este archivo actualmente SI se usa para validar credenciales. No hay controlador separado.

---

### 8. Vistas Autenticadas (Contenido Solo)

Todas las vistas autenticadas son solo **fragmentos HTML** sin estructura completa de pagina. El layout maestro provee el HTML comun.

#### 8.1 `dashboard.php` (130 lineas)

```html
<!-- Welcome Banner -->
<div class="welcome-banner">
    <h2>Bienvenido de nuevo!</h2>
    <p>Gestiona tu negocio de manera eficiente con EIS System</p>
</div>

<!-- Metricas (4 tarjetas con Materialize grid) -->
<div class="row">
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <div class="metric-icon"><i class="material-icons">payments</i></div>
            <div class="metric-label">Ventas Hoy</div>
            <div class="metric-value">$1,245.50</div>
        </div>
    </div>
    <!-- Stock Critico, Sesiones Cyber, Solicitudes Pend. -->
</div>

<!-- Tablas: Horas Pico y Productos Sin Stock -->
<div class="row">
    <div class="col s12 l6"><div class="card"><table>...</table></div></div>
    <div class="col s12 l6"><div class="card"><table>...</table></div></div>
</div>

<!-- Actividad Reciente -->
<div class="card">
    <div class="card-content">
        <span class="card-title">Actividad Reciente</span>
        <div class="activity-item">...</div>
    </div>
</div>
```

#### 8.2 `ventas.php` (130 lineas) - PUNTO DE VENTA (POS)

Catalogo de 5 productos (Teclado Mecanico, Mouse USB, Auriculares, Monitor 24, Cable USB-C) con carrito modal. Toda la logica en app.js.

**Funcionalidad JavaScript**:
- `posCart` - Array de objetos `{name, price}`
- Click en `.pos-product` (data-name, data-price) agrega al carrito
- Modal del carrito con total, vaciar y procesar venta
- `procesarVenta` simula venta con confirmacion y toast

#### 8.3 `ciberControl.php` (133 lineas) - CONTROL DE CYBERCAFE

10 estaciones organizadas en 3 zonas, con estados disponibles/ocupada/mantenimiento.

**NOVEDAD**: Los datos de estaciones se generan desde PHP con un array `$zonas` que define 3 zonas con estaciones especificas. Los contadores se calculan con PHP nativo (`array_filter`, `array_merge`).

#### 8.4 `inventario.php` (129 lineas)

Tabla con 3 productos de ejemplo, busqueda con debounce, filtro por estado, paginacion.

#### 8.5 `proveedores.php` (115 lineas)

Tabla de solicitudes con 3 ejemplos, busqueda y filtro por estado.

#### 8.6 `reportes.php` (139 lineas)

Formulario de generacion con selectores (tipo, fechas, formato), 4 metricas mensuales, listado de reportes recientes.

#### 8.7 `activos.php` (207 lineas)

Activos agrupados por categoria (Equipos 3, Licencias 2, Herramientas 4) con resumen de totales.

#### 8.8 `asesorias.php` (128 lineas) - ASESORIA LEGAL

**NUEVO MODULO**. Formulario de registro con validacion de documentos permitidos:

- Catalogo con 11 tipos de documentos permitidos (Consulta Laboral, Civil, Familiar, etc.)
- Validacion en tiempo real: si el documento es permitido, boton "Validar y Registrar" (indigo); si no, "Derivar a Oficina Oficial" (rojo)
- Historial de asesorias registradas en la sesion
- Busqueda en el historial
- Eliminacion de registros
- Documentos no permitidos (juicios, demandas, apelaciones, etc.) requieren derivacion

Toda la logica es frontend (en app.js), sin persistencia en BD.

#### 8.9 `menu.php` (158 lineas)

Pagina alternativa con diseño tipo tarjeta, estilo independiente, enlaces a modulos.

---

## JavaScript Central (app.js)

**Archivo**: `src/Public/js/app.js` (525 lineas)

### Estructura General

```javascript
var EIS = {};  // Namespace global

$(function () {
    // 1. Inicializar componentes Materialize
    // 2. Reloj en tiempo real (setInterval cada 1s)
    // 3. Sistema de notificaciones EIS.toast()
    // 4. Tema oscuro/claro con localStorage
    // 5. Animacion de transicion de pagina (fadeIn)
    // 6. Animacion de contadores numericos
    // 7. Busqueda en tablas con debounce
    // 8. Sistema POS (carrito de compras)
    // 9. Control de estaciones cyber
    // 10. Reportes (formulario simulado)
    // 11. Botones de accion generales
    // 12. Asesoria Legal (validacion de documentos)
    // 13. Notificaciones demo (campana)
    // 14. Boton volver arriba
});
```

### Funciones Clave

| Funcion | Descripcion |
|---------|-------------|
| `EIS.toast()` | Muestra notificacion tipo toast con Materialize |
| `actualizarReloj()` | Reloj digital en tiempo real (cada 1s) |
| `updateThemeUI()` | Actualiza icono y texto del theme toggle |
| `animarContadores()` | Animacion progresiva de valores numericos |
| `debounce()` | Limita frecuencia de ejecucion (busqueda) |
| `filtrarTabla()` | Filtra filas de tabla por texto |
| `actualizarPosUI()` | Actualiza interfaz del carrito POS |
| `actualizarCyberContadores()` | Actualiza contadores de estaciones |
| `documentoPermitido()` | Valida si tipo de documento es permitido en asesoria |
| `actualizarHistorial()` | Renderiza tabla de historial de asesorias |
| `mostrarValidacion()` | Muestra resultado de validacion de documento |

### Novedades en app.js

**Asesoria Legal** (lineas 338-499):
- `allowedDocs` - Array con 11 tipos de documentos permitidos
- `asesoriasRegistradas` - Array de objetos con datos de asesorias
- Validacion en tiempo real al escribir el documento (evento input)
- Boton de registro cambia de color segun el tipo de documento
- Historial con capacidad de eliminacion
- Busqueda en historial con debounce

---

## Base de Datos

### Esquema General

**Base de datos**: `zwl` (Zona Web Lara)
**Motor**: InnoDB
**Charset**: utf8mb4
**Version**: 2.0

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

| Vista | Descripcion |
|-------|-------------|
| `v_productos_stock` | Productos con estado de stock calculado (OK, Critico, Sin stock) |
| `v_ventas_diarias` | Agregacion de ventas diarias |
| `v_sesiones_activas` | Sesiones de cyber activas con costo estimado |

### Objetos de BD

| Objeto | Tipo | Descripcion |
|--------|------|-------------|
| `fn_estado_stock` | FUNCTION | Calcula estado del stock |
| `sp_registrar_movimiento_stock` | PROCEDURE | Registro transaccional de movimiento |
| `sp_cerrar_sesion_cyber` | PROCEDURE | Cierre de sesion cyber |
| `trg_actualizar_totales_venta` | TRIGGER | AFTER INSERT actualiza totales |
| `trg_auditar_precio_producto` | TRIGGER | BEFORE UPDATE registra cambio de precio |
| `ev_vencer_licencias` | EVENT | Diario: vence licencias expiradas |

Para documentacion completa de la base de datos, consultar:
- `docs/database-conceptual-design.md` (581 lineas)
- `docs/database-logical-design.md` (448 lineas)
- `docs/database-physical-design.md` (268 lineas)

---

## CSS y Estilos

### `src/Public/css/styles.css` (587 lineas)

Estilos personalizados que complementan Materialize CSS. Incluye:

**Variables CSS (Custom Properties)** en `:root` con 22 variables para tema claro.
**Tema oscuro** con `[data-theme="dark"]` que sobrescribe 22 variables.

**Clases principales** (actualizado):

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

### `src/Public/css/login.css` (65 lineas)

Estilos especificos para la pagina de login (independiente).

---

## jQuery

### Integracion

jQuery 3.7.1 se carga via CDN con integridad SRI en layout.php (autenticadas), login.php y menu.php.

### Donde se usa

1. **layout.php** - Cargado en `<head>` para todas las paginas autenticadas
2. **login.php** - Cargado al final del `<body>` para theme toggle
3. **menu.php** - Cargado al final del `<body>` para theme toggle

### Funcionalidades jQuery

| Funcionalidad | Metodo jQuery |
|---------------|---------------|
| Selectores | `$('#id')`, `$('.class')`, `$(element)` |
| Atributos | `.attr('data-theme', value)` |
| Clases CSS | `.addClass()`, `.removeClass()`, `.toggleClass()` |
| Eventos | `.on('click', fn)`, `.on('input', fn)`, `.on('submit', fn)` |
| Animaciones | `.fadeIn()`, `.hide()`, `.slideDown()`, `.animate()` |
| Contenido | `.text()`, `.html()` |
| DOM | `.closest()`, `.find()`, `.each()`, `.is()` |
| Event delegation | `$(document).on('event', 'selector', fn)` |

Para documentacion detallada de la migracion a jQuery, consultar `DOCUMENTACION_JQUERY.md`.

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
- **Contenido**: Tabla con busqueda (debounce), filtro por estado, paginacion

### Punto de Venta (Semi-funcional con jQuery)
- **Archivo**: `ventas.php` (130 lineas)
- **Funcionalidad**: Carrito completo en jQuery con modal Materialize
- **Problema**: No persiste en BD, venta simulada con toast

### Cyber Control (Interactivo con jQuery)
- **Archivo**: `ciberControl.php` (133 lineas)
- **Funcionalidad**: Toggle de estados con animaciones, filtros visuales, contadores dinamicos PHP

### Solicitudes (UI Estatica)
- **Archivo**: `proveedores.php` (115 lineas)
- **Contenido**: Tabla de solicitudes con busqueda

### Reportes (Simulado)
- **Archivo**: `reportes.php` (139 lineas)
- **Contenido**: Formulario generador con jQuery (submit simulado) + 4 metricas mensuales

### Activos (UI Estatica)
- **Archivo**: `activos.php` (207 lineas)
- **Contenido**: Tarjetas por categoria con busqueda y resumen

### Asesoria Legal (Semi-funcional con jQuery)
- **Archivo**: `asesorias.php` (128 lineas)
- **Funcionalidad**: Validacion de documentos en tiempo real, registro en historial local
- **Problema**: No persiste en BD (solo en memoria del navegador)

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
- Esquema de BD completo v2.0 (19 tablas)
- Modelos CRUD preparados (usuarios, asesorias)

### Para hacerlo funcional se requiere:
1. **Conectar vistas con BD** (usar PDO en AJAX)
2. **Implementar CRUD** real (insert, update, delete) via backend
3. **Migrar a MVC** (controladores con clases, Request, Router)
4. **Agregar seguridad** (CSRF, password hashing, sanitizacion)

---

**Documentacion generada**: Mayo 2026
**Version**: 1.2
**Autor**: Carlos Paez Guerra
