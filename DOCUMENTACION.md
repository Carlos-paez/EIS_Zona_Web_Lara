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

**EIS System** es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con interfaz **Material Design** (Materialize CSS 1.0.0) y **jQuery 3.7.1**. El proyecto simula un sistema completo para administrar un negocio que incluye: cybercafe, ventas POS, inventario, proveedores, activos y reportes.

**NOTA IMPORTANTE**: A pesar del nombre "eis_zona_web_lara", este proyecto **NO es Laravel**. Es PHP puro con arquitectura MVC basica.

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
| src/index.php | 3 | Punto de entrada |
| src/Config/database.php | 25 | Configuracion BD |
| src/app/core/router.php | 50 | Enrutamiento + layout |
| src/app/Models/crud_users.php | 38 | Funciones CRUD (no usado) |
| src/app/template/layout.php | 70 | Layout maestro Materialize |
| src/app/Views/login.php | 86 | Pagina login |
| src/app/Views/login_validate.php | 19 | Validacion login |
| src/app/Views/dashboard.php | 135 | Panel principal |
| src/app/Views/menu.php | 133 | Menu navegacion |
| src/app/Views/inventario.php | 110 | Gestion inventario |
| src/app/Views/ventas.php | 110 | Punto de venta |
| src/app/Views/proveedores.php | 98 | Solicitudes |
| src/app/Views/reportes.php | 132 | Reportes |
| src/app/Views/activos.php | 185 | Activos fijos |
| src/app/Views/ciberControl.php | 158 | Control cyber |
| src/Public/css/styles.css | 404 | Estilos personalizados |
| src/Public/css/login.css | 58 | Estilos login |
| src/Public/js/app.js | 362 | JS comun con jQuery |
| src/Database/mian.sql | 138 | Esquema BD |
| src/Database/seed.sql | 102 | Datos prueba |

**Total lineas de codigo**: ~2,200 lineas (PHP + CSS + JS + SQL)

---

## Explicacion Detallada por Archivo

### 1. `src/index.php` (3 lineas)

```php
<?php

    require_once __DIR__.'/app/core/router.php';
```

**Explicacion**:
- `<?php` - Apertura de codigo PHP
- `require_once __DIR__.'/app/core/router.php'` - Incluye y ejecuta el archivo router.php una sola vez
- `__DIR__` - Constante magica que apunta al directorio actual (src/)

---

### 2. `src/app/core/router.php` (50 lineas) - EL CEREBRO DE LA APLICACION

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
| 6 | `!empty($_GET["pagina"])` | Verifica que el parametro GET no este vacio |
| 7 | `$pagina = $_GET["pagina"]` | Asigna el valor del query string |
| 11 | `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` | Valida solo letras, numeros, guiones |
| 15 | `$public_pages` | Array con paginas publicas (login, login_validate) |
| 16 | `!isset($_SESSION['logged_in'])` | Verifica si NO esta autenticado |
| 24-43 | **NOVEDAD**: Layout system | Si es pagina publica carga directa, si no, usa layout.php con titulos dinamicos |

**Cambio clave respecto a version anterior**: El router ahora distingue entre paginas publicas (carga directa) y autenticadas (usa layout.php). Define arrays `$titulos` y `$extraHeaders` para pasar datos al layout.

---

### 3. `src/app/template/layout.php` (70 lineas) - LAYOUT MAESTRO

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
        <li><div class="user-view">
            <div class="background indigo darken-4"></div>
            <span class="white-text name">EIS System</span>
            <span class="white-text email">Sistema de Gestion Integral</span>
        </div></li>
        <li><a href="?pagina=dashboard"><i class="material-icons left">dashboard</i>Dashboard</a></li>
        <li><a href="?pagina=inventario"><i class="material-icons left">inventory_2</i>Inventario</a></li>
        <li><a href="?pagina=ventas"><i class="material-icons left">shopping_cart</i>Ventas (POS)</a></li>
        <li><a href="?pagina=proveedores"><i class="material-icons left">request_quote</i>Solicitudes</a></li>
        <li><a href="?pagina=ciberControl"><i class="material-icons left">computer</i>Cyber</a></li>
        <li><a href="?pagina=reportes"><i class="material-icons left">bar_chart</i>Reportes</a></li>
        <li><a href="?pagina=activos"><i class="material-icons left">build</i>Activos</a></li>
        <li><div class="divider"></div></li>
        <li><a href="?pagina=login"><i class="material-icons left">logout</i>Cerrar Sesion</a></li>
        <li><a id="themeToggle" style="cursor:pointer;">
            <i class="material-icons left" id="themeIcon">dark_mode</i>
            <span id="themeLabel">Modo Oscuro</span>
        </a></li>
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
| `sidenav` | Sidebar fijo con Materialize Sidenav (14 items) |
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

### 4. `src/Config/database.php` (25 lineas)

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
| `$pass` | string | Vacia en desarrollo (XAMPP/WAMP) |
| `$charset` | string | "utf8mb4" - soporta emojis y acentos |
| `$dns` | string | Data Source Name para PDO |
| `$options` | array | ATTR_ERRMODE, FETCH_ASSOC, EMULATE_PREPARES |

**NOTA**: La ruta cambio de `src/app/Config/database.php` a `src/Config/database.php`.

---

### 5. `src/app/Models/crud_users.php` (38 lineas) - CRUD USUARIOS (NO usado)

Contiene 5 funciones CRUD para la tabla `usuarios`:
- `crearUsuario($pdo, $nombre, $email)` - INSERT
- `obtenerUsuarios($pdo)` - SELECT ALL
- `obtenerUsuarioPorId($pdo, $id)` - SELECT ONE
- `actualizarUsuario($pdo, $id, $nombre, $email)` - UPDATE
- `eliminarUsuario($pdo, $id)` - DELETE

**NOTA**: El archivo se renombro de `crud.php` a `crud_users.php`. Sigue sin ser utilizado por ninguna vista.

---

### 6. `src/app/Views/login.php` (86 lineas)

Pagina de login con diseno Material Design. NO usa el layout (pagina publica).

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Meta tags, Google Fonts, Font Awesome -->
    <link rel="stylesheet" href="Public/css/login.css">
</head>
<body>
    <div class="form-container">
        <!-- Logo con gradiente y emoji -->
        <h1 class="title">EIS System</h1>
        <p class="subtitle">Ingresa tus credenciales para continuar</p>

        <!-- Mensaje de error (se muestra si ?error=1) -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">Credenciales incorrectas</div>
        <?php endif; ?>

        <!-- Formulario de login -->
        <form action="?pagina=login_validate" method="post">
            <input type="text" name="username" placeholder="Usuario" required autofocus>
            <input type="password" name="password" placeholder="Contrasena" required>
            <button type="submit">Iniciar Sesion</button>
        </form>

        <!-- Redes sociales (no funcional) -->
        <div class="social-icons">
            <button onclick="alert('No disponible')">Google</button>
            <button onclick="alert('No disponible')">GitHub</button>
        </div>
    </div>
    <!-- jQuery para theme toggle -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" ...></script>
</body>
</html>
```

**Elementos clave**:
- `$_GET['error']` - Muestra mensaje si hay error de autenticacion
- form action="?pagina=login_validate" - Envio POST al validador
- jQuery incluido para el theme toggle en login

---

### 7. `src/app/Views/login_validate.php` (19 lineas)

```php
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    $valid_username = "admin";
    $valid_password = "1234";

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header("Location: ?pagina=dashboard");
        exit;
    } else {
        header("Location: ?pagina=login&error=1");
        exit;
    }
}
header("Location: ?pagina=login");
exit;
```

**Variables superglobales**:

| Variable | Descripcion |
|----------|-------------|
| `$_SERVER["REQUEST_METHOD"]` | Metodo HTTP: "GET", "POST" |
| `$_POST["username"]` | Usuario enviado desde el formulario |
| `$_POST["password"]` | Contrasena enviada desde el formulario |
| `$_SESSION['logged_in']` | Flag de autenticacion |
| `$_SESSION['username']` | Nombre de usuario en sesion |

---

### 8. Vistas Autenticadas (Contenido Solo)

Todas las vistas autenticadas ahora son solo **fragmentos HTML** sin estructura completa de pagina. El layout maestro provee el HTML comun.

#### 8.1 `dashboard.php` (135 lineas)

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
    <!-- ... mas metricas ... -->
</div>

<!-- Tablas: Horas Pico y Productos Sin Stock -->
<div class="row">
    <div class="col s12 l6">
        <div class="card"><table>...</table></div>
    </div>
    <div class="col s12 l6">
        <div class="card"><table>...</table></div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="card">
    <div class="card-content">
        <span class="card-title">Actividad Reciente</span>
        <div class="activity-item">...</div>
    </div>
</div>
```

**Datos estaticos** (deberian venir de BD):
- Ventas Hoy: $1,245.50
- Stock Critico: 4 productos
- Sesiones Cyber: 7
- Solicitudes Pendientes: 3

#### 8.2 `ventas.php` (110 lineas) - PUNTO DE VENTA (POS)

```html
<!-- Header con total carrito y boton -->
<div class="row">
    <div class="col s12 m7">
        <span>Punto de Venta</span>
    </div>
    <div class="col s12 m5">
        <div>Total: <span id="posMiniTotal">$0.00</span></div>
        <button id="openCartBtn">Carrito <span id="cartCountBadge">0</span></button>
    </div>
</div>

<!-- Catalogo de productos -->
<div class="card">
    <div class="card-content">
        <input type="text" id="posSearch" placeholder="Buscar...">
        <div id="posProducts" class="row">
            <!-- Productos con data-name y data-price -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product"
                     data-name="Teclado Mecanico"
                     data-price="45.00">
                    <i class="material-icons">keyboard</i>
                    <h6>Teclado Mecanico</h6>
                    <span>$45.00</span>
                </div>
            </div>
            <!-- ... mas productos ... -->
        </div>
    </div>
</div>

<!-- Modal del carrito (Materialize Modal) -->
<div id="posCartModal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4>Carrito de Compras</h4>
        <div id="posCartItems">...</div>
    </div>
    <div class="modal-footer">
        <span id="posTotal">$0.00</span>
        <button id="vaciarCarrito">Vaciar</button>
        <button id="procesarVenta">Procesar Venta</button>
    </div>
</div>
```

**Funcionalidad JavaScript (en app.js)**:
- `posCart` - Array de objetos `{name, price}`
- `posAddItem` via evento click en `.pos-product` (data-name, data-price)
- `actualizarPosUI()` - Actualiza mini-total y modal
- `actualizarCarritoModal()` - Renderiza items del carrito
- `procesarVenta` - Simula venta con EIS.toast()

#### 8.3 `ciberControl.php` (158 lineas) - CONTROL DE CYBERCAFE

```html
<!-- Contadores resumen -->
<div class="row">
    <div class="col s6 m3">
        <div id="countDisponibles">5</div>
        <div>Disponibles</div>
    </div>
    <!-- ... ocupadas, mantenimiento, total ... -->
</div>

<!-- Filtros -->
<div class="card">
    <a class="filter-btn active" data-filter="all">Todas</a>
    <a class="filter-btn" data-filter="disponible">Disponibles</a>
    <a class="filter-btn" data-filter="ocupada">Ocupadas</a>
    <a class="filter-btn" data-filter="mantenimiento">Mantenimiento</a>
</div>

<!-- Grid de estaciones -->
<div class="row" id="cyberGrid">
    <div class="col s6 m4 l3 xl2">
        <div class="station-card disponible" data-status="disponible">
            <div class="station-icon"><i class="material-icons">check_circle</i></div>
            <div class="station-number">#1</div>
            <div class="station-status">Disponible</div>
        </div>
    </div>
    <!-- ... 10 estaciones ... -->
</div>
```

**Funcionalidad jQuery (en app.js)**:
- `actualizarCyberContadores()` - Actualiza contadores dinamicos
- Toggle de estaciones con animaciones (`.animate()`)
- Filtro visual con `.slideDown()` / `.hide()`
- Toast notifications en cada accion

#### 8.4 `inventario.php` (110 lineas)

```html
<!-- Barra de busqueda + filtro + boton nuevo -->
<div class="card">
    <div class="input-field">
        <input type="text" id="searchProducto" placeholder="Buscar...">
        <select id="filterEstado">
            <option value="">Todos</option>
            <option value="ok">Stock OK</option>
            <option value="critico">Critico</option>
            <option value="sin stock">Sin stock</option>
        </select>
    </div>
    <button class="btn-nuevo" data-tipo="producto">Nuevo Producto</button>
</div>

<!-- Tabla de productos -->
<div class="card">
    <table class="responsive-table striped">
        <thead>
            <tr><th>ID</th><th>Producto</th><th>Precio</th><th>Stock</th><th>Minimo</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>#1042</td>
                <td>Mouse Inalambrico</td>
                <td>$12.50</td>
                <td>5</td>
                <td>10</td>
                <td><span class="new badge red">Critico</span></td>
                <td>
                    <button class="btn-floating tooltipped" data-tooltip="Ver movimientos">
                        <i class="material-icons">inventory</i>
                    </button>
                    <button class="btn-floating tooltipped" data-tooltip="Editar">
                        <i class="material-icons">edit</i>
                    </button>
                </td>
            </tr>
            <!-- ... mas productos ... -->
        </tbody>
    </table>
    <!-- Paginacion -->
    <ul class="pagination">
        <li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>
        <li class="active indigo"><a>1</a></li>
        <li class="waves-effect"><a>2</a></li>
        <li class="waves-effect"><a>3</a></li>
        <li class="waves-effect"><a><i class="material-icons">chevron_right</i></a></li>
    </ul>
</div>
```

**Funcionalidad jQuery**:
- `filtrarTabla()` - Filtro por texto con debounce (300ms)
- `filterEstado` - Filtro por estado via select
- `result-count` - Muestra cuantos resultados visibles de total
- `pagination` - Navegacion con toasts

#### 8.5 Otras Vistas

| Vista | Lineas | Descripcion |
|------|--------|-------------|
| **proveedores.php** | 98 | Tabla de solicitudes con busqueda y filtro |
| **reportes.php** | 132 | Formulario de generacion con selects y radios |
| **activos.php** | 185 | Tarjetas de activos por categoria con busqueda |
| **menu.php** | 133 | Menu alternativo estilo card-based |

---

## JavaScript Central (app.js)

**Archivo**: `src/Public/js/app.js` (362 lineas)

### Estructura General

```javascript
var EIS = {};  // Namespace global

$(function () {
    // 1. Inicializar componentes Materialize
    $('.sidenav').sidenav();
    $('select').formSelect();
    $('.tooltipped').tooltip();
    $('.modal').modal();
    $('.dropdown-trigger').dropdown();
    $('.tabs').tabs();
    $('.collapsible').collapsible();
    $('.materialboxed').materialbox();
    $('.parallax').parallax();

    // 2. Reloj en tiempo real
    function actualizarReloj() {...}
    setInterval(actualizarReloj, 1000);

    // 3. Sistema de notificaciones (Toast)
    EIS.toast = function (msg, color, icon) {...};

    // 4. Tema oscuro/claro
    function updateThemeUI(theme) {...}
    var currentTheme = localStorage.getItem('theme') || 'light';
    $('html').attr('data-theme', currentTheme);

    // 5. Animacion de pagina
    $('main').hide().fadeIn(400);

    // 6. Animacion de contadores
    function animarContadores() {...}

    // 7. Busqueda en tablas con debounce
    function debounce(fn, delay) {...}
    function filtrarTabla(input, table, colIndex) {...}

    // 8. Sistema POS
    var posCart = [];
    var posTotal = 0;
    // eventos click en .pos-product, .cart-item-remove, #openCartBtn, #procesarVenta

    // 9. Cyber control
    function actualizarCyberContadores() {...}
    // eventos click en .station-card, .filter-btn

    // 10. Reportes
    // evento submit en #formReporte

    // 11. Botones de accion
    // eventos click en [data-confirm], .btn-nuevo, .pagination

    // 12. Notificaciones demo
    // evento click en #notifBell

    // 13. Boton volver arriba
    // evento scroll en window, click en #backToTop
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

---

## Base de Datos

### Esquema General

**Base de datos**: `zwl` (Zona Web Lara)
**Motor**: InnoDB
**Charset**: utf8mb4

### Tablas (10 total)

| # | Tabla | Proposito |
|---|-------|-----------|
| 1 | `usuarios` | Usuarios del sistema |
| 2 | `productos` | Catalogo de productos (campos: codigo, codigo_barras, nombre, marca, categoria, stock, precio, IVA, etc.) |
| 3 | `ventas` | Registro de ventas |
| 4 | `detalle_ventas` | Detalle de productos vendidos |
| 5 | `proveedores` | Proveedores |
| 6 | `solicitudes` | Pedidos a proveedores |
| 7 | `activos` | Activos fijos |
| 8 | `estaciones_cyber` | Estaciones de cybercafe |
| 9 | `sesiones_cyber` | Sesiones de uso |
| 10 | `movimientos_stock` | Historial de inventario |

### Indices (9 total)

```sql
CREATE INDEX idx_productos_categoria ON productos(categoria);
CREATE INDEX idx_ventas_fecha ON ventas(fecha);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_detalle_ventas_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_ventas_producto ON detalle_ventas(producto_id);
CREATE INDEX idx_solicitudes_proveedor ON solicitudes(proveedor_id);
CREATE INDEX idx_solicitudes_fecha ON solicitudes(fecha);
CREATE INDEX idx_sesiones_estacion ON sesiones_cyber(estacion_id);
CREATE INDEX idx_movimientos_producto ON movimientos_stock(producto_id);
CREATE INDEX idx_movimientos_fecha ON movimientos_stock(fecha);
```

Para documentacion completa de la base de datos, consultar:
- `docs/database-conceptual-design.md` (346 lineas)
- `docs/database-logical-design.md` (497 lineas)
- `docs/database-physical-design.md` (189 lineas)

---

## CSS y Estilos

### `src/Public/css/styles.css` (404 lineas)

Estilos personalizados que complementan Materialize CSS. Incluye:

**Variables CSS (Custom Properties)** en `:root`:

```css
:root {
    --primary: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --bg: #f1f5f9;
    --surface: #ffffff;
    --text: #1e293b;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --sidebar-width: 280px;
    --radius: 12px;
}
```

**Tema oscuro** con `[data-theme="dark"]`:

```css
[data-theme="dark"] {
    --bg: #0f172a;
    --surface: #1e293b;
    --text: #e2e8f0;
    --text-muted: #94a3b8;
    --border: #334155;
}
```

**Clases principales**:

| Clase | Proposito |
|-------|-----------|
| `.metric-card` | Tarjetas de metricas con borde colorido |
| `.station-card` | Tarjetas de estaciones cyber (disponible/ocupada/mantenimiento) |
| `.pos-product` | Tarjetas de productos en POS |
| `.cart-item` | Items del carrito de compras |
| `.activity-item` | Items de actividad reciente |
| `.welcome-banner` | Banner de bienvenida en dashboard |
| `.result-count` | Contador de resultados de busqueda |

### `src/Public/css/login.css` (58 lineas)

Estilos especificos para la pagina de login (independiente).

---

## jQuery

### Integracion

jQuery 3.7.1 se carga via CDN con integridad SRI:

```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
```

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
- **Seguridad**: Validacion de metodo POST, sesiones PHP
- **Credenciales**: admin / 1234 (hardcodeadas)

### Dashboard (UI Estatica)
- **Archivo**: `dashboard.php`
- **Contenido**: 4 metricas, tablas de horas pico y productos sin stock, actividad reciente
- **Problema**: Datos estaticos, deberian venir de consultas SQL

### Inventario (UI Estatica con filtros)
- **Archivo**: `inventario.php`
- **Contenido**: Tabla con busqueda (debounce), filtro por estado, paginacion
- **Problema**: No conecta a BD, datos simulados

### Punto de Venta (Semi-funcional con jQuery)
- **Archivo**: `ventas.php`
- **Funcionalidad**: Carrito completo en jQuery con modal Materialize
- **Problema**: No persiste en BD, venta simulada con toast

### Cyber Control (Interactivo con jQuery)
- **Archivo**: `ciberControl.php`
- **Funcionalidad**: Toggle de estados con animaciones, filtros visuales, contadores
- **Problema**: Cambios no persisten en BD

### Solicitudes (UI Estatica)
- **Archivo**: `proveedores.php`
- **Contenido**: Tabla de solicitudes con busqueda
- **Problema**: No conecta a BD

### Reportes (Simulado)
- **Archivo**: `reportes.php`
- **Contenido**: Formulario generador con jQuery (submit simulado)
- **Problema**: No genera archivos reales

### Activos (UI Estatica)
- **Archivo**: `activos.php`
- **Contenido**: Tarjetas por categoria con busqueda
- **Problema**: No conecta a BD

---

## Conclusiones y Recomendaciones

### Estado Actual
El proyecto es un **prototipo de UI** con:
- Diseno Material Design con Materialize CSS
- Navegacion funcional con sidebar responsivo
- Sistema de login basico
- Tema oscuro/claro con persistencia
- Carrito POS funcional (frontend)
- Control de cyber interactivo
- Busquedas y filtros con debounce

### Para hacerlo funcional se requiere:
1. **Crear controladores** en `Controllers/` (logica de negocio)
2. **Expandir modelos** para todas las tablas
3. **Conectar vistas con BD** (usar PDO en AJAX)
4. **Implementar CRUD** real (insert, update, delete)
5. **Agregar seguridad** (CSRF, password hashing, sanitizacion)

---

**Documentacion generada**: Mayo 2026
**Version**: 1.1
**Autor**: Carlos Paez Guerra
