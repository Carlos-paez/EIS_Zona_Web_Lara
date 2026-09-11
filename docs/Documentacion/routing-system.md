# Sistema de Enrutamiento — Documentacion Tecnica

## Arquitectura Actual (Front Controller OOP)

```
src/
├── index.php                    ← Front Controller (punto de entrada, autoloader)
├── .htaccess                    ← Reglas de reescritura Apache
├── manifest.json                ← Manifiesto PWA
├── sw.js                        ← Service Worker
├── offline.php                  ← Pagina offline
├── Config/
│   └── database.php             ← Conexion PDO MySQL (legacy)
├── app/
│   ├── core/
│   │   ├── Database.php         ← Conexion PDO Singleton
│   │   ├── Exporter.php         ← Exportaciones CSV/Excel/PDF
│   │   ├── Model.php            ← Clase base abstracta con helpers de validacion
│   │   ├── Validator.php        ← Clase final con reglas de validacion estaticas
│   │   ├── PdfBuilder.php       ← Generacion de PDF para reportes
│   │   └── router.php           ← Enrutador OOP (clase Router, mapa CONTROLLERS, dispatchAction, CSRF)
│   ├── Controllers/             ← 13 controladores con namespace
│   │   ├── AuthController.php   ← Login/logout con sesiones + CSRF + session_regenerate_id
│   │   ├── ClienteController.php← Controlador AJAX clientes
│   │   ├── InventarioController.php ← Controlador AJAX inventario
│   │   ├── ProveedorController.php  ← Controlador AJAX proveedores (solicitudes)
│   │   ├── ProveedorGestionController.php ← Controlador AJAX proveedores (gestion)
│   │   ├── RolController.php    ← Controlador AJAX roles/permisos
│   │   ├── ActivoController.php ← Controlador AJAX activos
│   │   ├── AsesoriaController.php← Controlador AJAX asesorias
│   │   ├── CiberController.php  ← Controlador AJAX cybercafe
│   │   ├── DashboardController.php← Controlador AJAX dashboard
│   │   ├── ReporteController.php← Controlador AJAX reportes
│   │   ├── VentaController.php  ← Controlador AJAX ventas (POS)
│   │   └── UsuarioController.php← Controlador AJAX usuarios
│   ├── Models/                  ← 15 modelos (12 POO + 3 legacy)
│   │   ├── Cliente.php          ← Modelo POO clientes
│   │   ├── Inventario.php       ← Modelo POO inventario (namespace)
│   │   ├── Usuario.php          ← Modelo POO usuarios
│   │   ├── Proveedor.php        ← Modelo POO proveedores (solicitudes)
│   │   ├── ProveedorGestion.php ← Modelo POO proveedores (gestion)
│   │   ├── Rol.php              ← Modelo POO roles/permisos
│   │   ├── Asesoria.php         ← Modelo POO asesorias
│   │   ├── Activo.php           ← Modelo POO activos
│   │   ├── Venta.php            ← Modelo POO ventas
│   │   ├── Reporte.php          ← Modelo POO reportes
│   │   ├── Dashboard.php        ← Modelo POO dashboard
│   │   ├── CiberControl.php     ← Modelo POO control cyber
│   │   ├── CiberModel.php       ← Modelo POO sesiones cyber
│   │   ├── crud_users.php       ← CRUD usuarios legacy
│   │   └── crud_asesorias.php   ← CRUD asesorias legacy
│   ├── template/
│   │   └── layout.php           ← Layout maestro con CSRF token + JS condicional
│   └── Views/
│       ├── login.php            ← Formulario de inicio de sesion
│       ├── login_validate.php   ← Validacion de credenciales (legacy)
│       ├── dashboard.php        ← Panel de control
│       ├── inventario.php       ← Gestion de inventario (conectado a BD)
│       ├── ventas.php           ← Punto de venta (POS)
│       ├── proveedores.php      ← Solicitudes a proveedores (conectado a BD)
│       ├── clientes.php         ← Gestion de clientes (conectado a BD)
│       ├── reportes.php         ← Reportes y estadisticas
│       ├── activos.php          ← Activos fijos
│       ├── ciberControl.php     ← Control de cybercafe
│       ├── asesorias.php        ← Asesoria legal
│       ├── menu.php             ← Menu de navegacion
│       ├── usuarios.php         ← Gestion de usuarios (conectado a BD)
│       └── roles.php            ← Gestion de roles/permisos (conectado a BD)
└── Public/
    ├── css/                     ← Estilos (locales)
    ├── js/                      ← JavaScript modular (15 modulos + jQuery/Materialize/DataTables)
    └── fonts/                   ← Material Icons (local)
```

---

## 1. `src/.htaccess` — URLs con Query String + URLs Limpias Parciales

```apache
Options All -Indexes
RewriteEngine on

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([\w-]+)$ index.php?pagina=$1 [L,QSA]
```

| Linea | Explicacion |
|-------|-------------|
| `Options All -Indexes` | Bloquea el listado de directorios por seguridad. |
| `RewriteEngine On` | Activa el modulo de reescritura de Apache. |
| `RewriteRule ^$ index.php [L,QSA]` | La raiz `/` se redirige internamente a `index.php`. |
| `RewriteCond %{REQUEST_FILENAME} !-f` | Solo aplica si el archivo NO existe fisicamente. |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Solo aplica si el directorio NO existe. |
| `RewriteRule ^([\w-]+)$ index.php?pagina=$1 [L,QSA]` | Convierte `/nombre` en `?pagina=nombre`. Soporta URLs limpias parciales. |

Actualmente las URLs soportan dos formatos: `?pagina=nombre` (query string) y `/nombre` (URL limpia gracias al .htaccess).

---

## 2. `src/index.php` — Front Controller

El archivo `index.php` (135 lineas) no solo carga el autoloader y ejecuta el Router: tambien configura un **manejo global de errores** completo que evita que los errores tecnicos se filtren al usuario final.

```
src/index.php (135 lineas)
├── require_once vendor/autoload.php   ← Autoloader Composer PSR-4
├── use App\Core\Logger, Router
├── error_reporting(E_ALL) + ini_set('display_errors','0')
├── ob_start()                         ← Buffer de salida para errores fatales
├── es_peticion_ajax()                 ← Helper: detecta peticiones AJAX
├── limpiar_buffer_salida()            ← Helper: descarta buffer previo
├── mostrar_error_generico()           ← HTML/JSON generico (sin detalles tecnicos)
├── set_error_handler()                ← Warnings/notices → Logger::error()
├── set_exception_handler()            ← Excepciones no capturadas → Logger::error() + 500
├── register_shutdown_function()       ← Errores fatales → Logger::error() + 500
├── $router = new Router()
└── $router->handle()
```

| Seccion | Explicacion |
|---------|-------------|
| `require_once __DIR__ . '/../vendor/autoload.php'` | Carga el autoloader de Composer (PSR-4). |
| `use App\Core\Logger` / `Router` | Importa clases del namespace `App\Core`. |
| `ini_set('display_errors', '0')` | Los errores PHP nunca se muestran al navegador. |
| `ob_start()` | Activa buffer de salida para reemplazar respuesta parcial en errores fatales. |
| `set_error_handler(...)` | Captura warnings/notices y los registra via `Logger::error()` (en `src/logs/errores.md`). |
| `set_exception_handler(...)` | Captura excepciones no manejadas, las registra y muestra error 500 generico. |
| `register_shutdown_function(...)` | Detecta errores fatales (`E_ERROR`, `E_PARSE`, etc.) al finalizar el script. |
| `$router = new Router()` | Crea instancia: inicia sesion y resuelve pagina. |
| `$router->handle()` | Procesa la solicitud (AJAX, auth, o vista). |

El flujo es: `index.php` -> configura manejo de errores -> `new Router()` -> `Router::handle()`.

---

## 3. `src/app/core/router.php` — Enrutador OOP (Clase Router)

### Mapa de rutas

El enrutamiento usa la clase `Router` en namespace `App\Core`. Centraliza los controladores en un mapa `CONTROLLERS` (`pagina => clase`) y usa `dispatchAction()` para las peticiones AJAX:
1. **AJAX** (`?pagina=X&action=Y`) cuando `X` esta en `CONTROLLERS` -> `dispatchAction()` instancia el controlador y ejecuta `handle()`
2. **Auth actions** -> `login_validate` (POST) -> `AuthController::login()`; `pagina=login` con `logout=1` -> `Router::logout()`
3. **Vistas normales** -> `render()` -> `layout.php` + vista

El mapa `CONTROLLERS` incluye 12 controladores AJAX: `clientes`, `inventario`, `ventas`, `roles`, `proveedores`, `proveedores-gestion`, `asesorias`, `ciberControl`, `activos`, `dashboard`, `reportes`, `usuarios`.

### Estructura de la clase

```php
<?php
namespace App\Core;

class Router
{
    private string $pagina;
    private const CONTROLLERS = [
        'clientes'          => \App\Controllers\ClienteController::class,
        'inventario'        => \App\Controllers\InventarioController::class,
        'ventas'            => \App\Controllers\VentaController::class,
        'roles'             => \App\Controllers\RolController::class,
        'proveedores'       => \App\Controllers\ProveedorController::class,
        'proveedores-gestion' => \App\Controllers\ProveedorGestionController::class,
        'asesorias'         => \App\Controllers\AsesoriaController::class,
        'ciberControl'      => \App\Controllers\CiberController::class,
        'activos'           => \App\Controllers\ActivoController::class,
        'dashboard'         => \App\Controllers\DashboardController::class,
        'reportes'          => \App\Controllers\ReporteController::class,
        'usuarios'          => \App\Controllers\UsuarioController::class,
    ];

    private const PUBLIC_PAGES = ['login', 'login_validate'];

    private const PAGE_TITLES = [
        'dashboard'           => 'Panel de Control',
        'inventario'          => 'Gestión de inventario',
        'ventas'              => 'Punto de Venta (POS)',
        'ciberControl'        => 'Control de Cybercafé',
        'proveedores'         => 'Solicitudes a Proveedores',
        'proveedores-gestion' => 'Gestión de Proveedores',
        'clientes'            => 'Gestión de Clientes',
        'reportes'            => 'Reportes y Estadísticas',
        'activos'             => 'Gestión de Activos',
        'asesorias'           => 'Asesoría Legal',
        'usuarios'            => 'Configuración de Usuarios',
        'roles'               => 'Roles y Permisos',
    ];

    private const PAGE_EXTRA_HEADERS = [
        'ciberControl' => '<span id="hdrDisponibles" class="chip green white-text">Disponibles</span><span id="hdrOcupadas" class="chip orange white-text">Ocupadas</span>',
    ];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function handle(): void
    {
        $this->pagina = $this->resolvePagina();

        // Página de cierre de sesión (GET ?pagina=login con intención de logout).
        if (
            $this->pagina === 'login'
            && isset($_GET['logout'])
            && isset($_SESSION['logged_in'])
        ) {
            $this->logout();
        }

        // Control de acceso: las páginas privadas requieren sesión.
        if (
            !isset($_SESSION['logged_in'])
            && !in_array($this->pagina, self::PUBLIC_PAGES, true)
        ) {
            $this->redirect('login');
        }

        // Despacho de peticiones AJAX de los módulos (?pagina=X&action=Y).
        if (array_key_exists($this->pagina, self::CONTROLLERS) && isset($_GET['action'])) {
            $this->dispatchAction();
        }

        // Flujo de inicio de sesión (POST ?pagina=login_validate).
        if ($this->pagina === 'login_validate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new \App\Controllers\AuthController())->login();
            return;
        }

        $this->render();
    }

    private function resolvePagina(): string
    {
        $pagina = $_GET['pagina'] ?? 'login';

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            $pagina = 'login';
        }

        return $pagina;
    }

    private function dispatchAction(): void
    {
        $controllerClass = self::CONTROLLERS[$this->pagina];
        $controller      = new $controllerClass();

        if (method_exists($controller, 'handle')) {
            $controller->handle();
            exit;
        }
    }

    private function logout(): void
    {
        session_regenerate_id(true);
        $_SESSION = [];
        session_destroy();
        $this->redirect('login');
    }

    private function render(): void
    {
        if (in_array($this->pagina, self::PUBLIC_PAGES, true)) {
            $rutaVista = $this->viewsDir() . $this->pagina . '.php';
            if (is_file($rutaVista)) {
                require $rutaVista;
            } else {
                http_response_code(404);
                echo '<h1>Error 404: Página no encontrada</h1>';
            }
            return;
        }

        $rutaVista = $this->viewsDir() . $this->pagina . '.php';

        if (!is_file($rutaVista)) {
            http_response_code(404);
            echo '<h1>Error 404: Página no encontrada</h1>';
            echo "<p>La página <strong>{$this->pagina}</strong> no existe.</p>";
            echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
            return;
        }

        $pageTitle   = self::PAGE_TITLES[$this->pagina] ?? 'EIS System';
        $headerExtra = self::PAGE_EXTRA_HEADERS[$this->pagina] ?? '';
        $contentView = $rutaVista;
        $pagina      = $this->pagina;

        require __DIR__ . '/../template/layout.php';
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        return !empty($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    private function viewsDir(): string
    {
        return __DIR__ . '/../Views/';
    }

    private function redirect(string $pagina): void
    {
        header('Location: ?pagina=' . $pagina);
        exit;
    }
}
```

### Metodos clave

| Metodo | Explicacion |
|--------|-------------|
| `__construct()` | Inicia sesion (si no existe) y genera el CSRF token una sola vez por sesion (`bin2hex(random_bytes(32))`) |
| `handle()` | Metodo principal: determina el tipo de peticion y ejecuta la accion |
| `CONTROLLERS` | Mapa `pagina => clase` que centraliza los 12 controladores AJAX (13 archivos con `AuthController`) |
| `PUBLIC_PAGES` | Constante `['login', 'login_validate']` (páginas accesibles sin sesión) |
| `PAGE_TITLES` / `PAGE_EXTRA_HEADERS` | Constantes con títulos y cabeceras extra de cada página |
| `resolvePagina()` | Lee `$_GET["pagina"]`, valida con regex `/^[a-zA-Z0-9_-]+$/`, retorna el nombre (default: "login") |
| `dispatchAction()` | Si la pagina esta en `CONTROLLERS` y hay `action`, instancia el controlador y ejecuta `handle()` |
| `logout()` | Cierra sesion (`session_regenerate_id` + `session_destroy`) desde `?pagina=login&logout=1` |
| `render()` | Renderiza vistas publicas directas y protegidas con layout (prepara `$pageTitle`, `$headerExtra`, `$contentView`) |
| `verifyCsrfToken()` | Estatico: compara el token con `hash_equals()` contra `$_SESSION['csrf_token']` |
| `redirect()` | Envia `header('Location: ?pagina=...')` |

### Mejoras sobre la version procedural

| Aspecto | Version anterior (procedural) | Version actual (OOP) |
|---------|------------------------------|----------------------|
| **Tipo** | Script procedural (75 lineas) | Clase con namespace |
| **Autoloader** | No usado | Composer PSR-4 |
| **AJAX** | Solo inventario | 12 controladores via mapa `CONTROLLERS` + `dispatchAction()` |
| **Auth** | login_validate.php (vista) | AuthController con login() + Router::logout() + session_regenerate_id |
| **CSRF** | No implementado | `bin2hex(random_bytes(32))` en constructor + `<input name="csrf_token">` |
| **Seguridad AJAX** | No verificaba auth | Control de acceso en `handle()` con redireccion a login |
| **Logout** | No implementado | `Router::logout()` via `?pagina=login&logout=1` |
| **Titulos** | 9 modulos | 12 modulos (constante `PAGE_TITLES`) |
| **Validacion** | Sin helpers | `Model.php` (helpers) + clase final `Validator` (reglas estaticas) |

---

## 4. `src/app/template/layout.php` — Layout Maestro

### Variables disponibles en el layout

| Variable | Origen | Proposito |
|----------|--------|-----------|
| `$pageTitle` | `$titulos[$pagina]` en router.php | Titulo en `<title>` y barra de navegacion |
| `$headerExtra` | `$extraHeaders[$pagina]` en router.php | HTML extra en el header (badges) |
| `$contentView` | Ruta absoluta a la vista | Incluido dentro del layout |
| `$pagina` | `$_GET["pagina"]` | Nombre de pagina actual (para clase `active` en sidebar y carga condicional de JS) |

### Carga condicional de JavaScript

```php
<!-- Scripts base (siempre) -->
<script src="Public/js/materialize.min.js"></script>
<script src="Public/js/jquery.dataTables.min.js"></script>
<script src="Public/js/dataTables.materialize.js"></script>
<script src="Public/js/app.core.js"></script>
<script src="Public/js/app.init.js"></script>
<script src="Public/js/app.selects.js"></script>
<script src="Public/js/app.tables.js"></script>
<script src="Public/js/app.ui.js"></script>

<!-- Scripts especificos por pagina (modulos) -->
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
<?php if ($pagina === 'roles'): ?>
<script src="Public/js/app.roles.js"></script>
<?php endif; ?>
<?php if ($pagina === 'proveedores'): ?>
<script src="Public/js/app.proveedores.js"></script>
<?php endif; ?>
<?php if ($pagina === 'proveedores-gestion'): ?>
<script src="Public/js/app.proveedores-gestion.js"></script>
<?php endif; ?>
<?php if ($pagina === 'clientes'): ?>
<script src="Public/js/app.clientes.js"></script>
<?php endif; ?>
<?php if ($pagina === 'activos'): ?>
<script src="Public/js/app.activos.js"></script>
<?php endif; ?>
<?php if ($pagina === 'reportes'): ?>
<script src="Public/js/app.reportes.js"></script>
<?php endif; ?>
<?php if ($pagina === 'usuarios'): ?>
<script src="Public/js/app.usuarios.js"></script>
<?php endif; ?>
```

---

## 5. Mapa de Paginas

| Parametro | Vista/Controlador | Publica? | JS Adicional |
|-----------|-------------------|----------|-------------|
| `login` | `login.php` | Si | Ninguno |
| `login&logout=1` | `Router::logout()` | Si | Ninguno |
| `login_validate` (POST) | `AuthController::login()` | Si | Ninguno |
| `dashboard` | `dashboard.php` | No | `app.reportes.js`? No — ninguno (KPIs via `app.reportes.js` no; dashboard usa `app.core/init/ui`) |
| `dashboard&action=X` | `DashboardController::handle()` | No | (AJAX) |
| `inventario` | `inventario.php` | No | `app.inventario.js` |
| `inventario&action=X` | `InventarioController::handle()` | No | (AJAX) |
| `ventas` | `ventas.php` | No | `app.pos.js` |
| `ventas&action=X` | `VentaController::handle()` | No | (AJAX) |
| `proveedores` | `proveedores.php` | No | `app.proveedores.js` |
| `proveedores&action=X` | `ProveedorController::handle()` | No | (AJAX) |
| `clientes` | `clientes.php` | No | `app.clientes.js` |
| `clientes&action=X` | `ClienteController::handle()` | No | (AJAX) |
| `proveedores-gestion` | proveedores-gestion.php | No | `app.proveedores-gestion.js` |
| `proveedores-gestion&action=X` | `ProveedorGestionController::handle()` | No | (AJAX) |
| `ciberControl` | `ciberControl.php` | No | `app.cyber.js` |
| `ciberControl&action=X` | `CiberController::handle()` | No | (AJAX) |
| `reportes` | `reportes.php` | No | `app.reportes.js` |
| `reportes&action=X` | `ReporteController::handle()` | No | (AJAX) |
| `activos` | `activos.php` | No | `app.activos.js` |
| `activos&action=X` | `ActivoController::handle()` | No | (AJAX) |
| `asesorias` | `asesorias.php` | No | `app.legal.js` |
| `asesorias&action=X` | `AsesoriaController::handle()` | No | (AJAX) |
| `usuarios` | `usuarios.php` | No | `app.usuarios.js` |
| `usuarios&action=X` | `UsuarioController::handle()` | No | (AJAX) |
| `roles` | `roles.php` | No | `app.roles.js` |
| `roles&action=X` | `RolController::handle()` | No | (AJAX) |
| `menu` | `menu.php` | No | Ninguno |

---

## 6. Vistas

### Vistas publicas (standalone, sin layout)
- `login.php` — Tiene su propio DOCTYPE, head, body. Carga jQuery, Materialize JS y app.core.js.

### Vistas protegidas (dentro del layout)
Todas las demas vistas son solo fragmentos HTML sin estructura completa de pagina. El layout maestro provee el HTML comun.

```
Views/
├── login.php                   # Publica, standalone
├── login_validate.php          # Publica, solo PHP (legacy)
├── dashboard.php               # Protegida, dentro del layout
├── inventario.php              # Conectado a BD
├── ventas.php
├── proveedores.php             # Conectado a BD (solicitudes)
├── clientes.php                # Conectado a BD
├── reportes.php
├── activos.php
├── ciberControl.php
├── asesorias.php
├── menu.php
├── usuarios.php                # Conectado a BD
└── roles.php                   # Conectado a BD
```

---

## 7. Seguridad del Sistema

| Aspecto | Implementacion |
|---------|----------------|
| **Path Traversal** | Regex `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` en router.php |
| **Autenticacion** | Verificacion de `$_SESSION['logged_in']` antes de cargar vistas protegidas |
| **404** | `is_file()` verifica existencia del archivo de vista |
| **CSRF** | Token en constructor: `bin2hex(random_bytes(32))`, inyectado en `window.EIS.csrfToken` y `<input name="csrf_token">` |
| **XSS** | Helper `escHtml()` en JS, `htmlspecialchars()` en PHP, Model sanitizes strings |
| **Session Hardening** | `session_regenerate_id(true)` en login/logout |
| **SQL Injection** | Los modelos usan prepared statements con PDO (`ATTR_EMULATE_PREPARES => false`) |
| **Listado Directorios** | `Options All -Indexes` en .htaccess |
| **Double Escaping** | Controllers usan solo `trim()`, Model maneja sanitizacion |

---

## 8. Como Agregar una Nueva Pagina

### Paso 1: Crear la Vista
Crea `src/app/Views/mi-pagina.php` con el contenido HTML.

### Paso 2: Registrar el Titulo
En `src/app/core/router.php`, agrega el titulo en `$titulos`:
```php
'mi-pagina' => 'Titulo de mi pagina',
```

### Paso 3: Agregar al Menu
En `src/app/template/layout.php`, agrega un enlace en el `<ul class="sidenav">`.

### Paso 4: (Opcional) Agregar JS Especifico
En `src/app/template/layout.php`, agrega la carga condicional:
```php
<?php if ($pagina === 'mi-pagina'): ?>
<script src="Public/js/app.mi-pagina.js"></script>
<?php endif; ?>
```

### Si la pagina es publica
No requiere autenticacion. Agrega `'mi-pagina'` al array `$public_pages` en router.php.

---

## 9. Flujo Completo de una Peticion

```
Usuario: GET /src/?pagina=ventas

1. Apache recibe la peticion
   └── /.htaccess: RewriteRule ^(.*)$ src/$1 [L]
   └── src/.htaccess: RewriteRule ^(.*)$ index.php [QSA,L]

2. index.php:
   └── require_once ../vendor/autoload.php (autoloader Composer)
   └── configura manejo global de errores (Logger, errores.md)
   └── $router = new Router(); $router->handle()

3. Router::handle():
   ├── session_start() (si no existe) + token CSRF
   ├── $pagina = "ventas"
   ├── preg_match -> OK
   ├── $_SESSION['logged_in']? -> Si (o redirige a login)
   ├── ¿Controlador con action? -> No (ventas no tiene action)
   ├── $rutaVista = ".../Views/ventas.php" -> existe
   ├── $pageTitle = 'Punto de Venta (POS)'
   ├── $headerExtra = ''
   ├── $contentView = ".../Views/ventas.php"
   └── require __DIR__ . '/../template/layout.php'

4. layout.php:
   ├── <html><head> con Materialize CSS + jQuery + DataTables locales
   ├── Sidebar con enlaces a modulos
   ├── Header con reloj y notificaciones
   ├── <main> -> require $contentView (ventas.php)
   ├── Scripts: app.core.js, app.init.js, app.selects.js, app.tables.js, app.ui.js (+ DataTables)
   ├── $pagina === 'ventas' -> app.pos.js
   └── Service Worker registration

5. Navegador:
   ├── jQuery inicializa componentes Materialize
   ├── Reloj digital, animacion de contadores
   ├── app.pos.js prepara carrito POS
   └── Service Worker registrado para offline
```

---

## 10. Diferencia con una Arquitectura MVC Completa

| Aspecto | Actual (Procedural -> OOP) | MVC Completo |
|---------|---------------------------|--------------|
| **Enrutador** | `Router` clase con namespace, OOP | Framework (Laravel, Symfony) con routing DSL |
| **Punto de entrada** | `autoload.php` + `new Router()` + `->handle()` | Igual, pero con contenedor DI |
| **Controladores** | 13 controladores con namespace, metodo `handle()` | Controladores con acciones por metodo |
| **Layout** | `template/layout.php` | `Views/layouts/main.php` |
| **Vistas** | `Views/dashboard.php` (plano) | `Views/dashboard/index.php` (subdirectorios) |
| **Autoloader** | Composer PSR-4 | Igual |
| **Logica de login** | `AuthController::login()` con `password_verify` + CSRF | LoginController::validate() con guards |
| **Datos de Cyber** | En la vista (`ciberControl.php`) | En el controlador |
| **Titulos** | Array en `Router::render()` | Propiedad de clase Controller |
| **ORM** | PDO directo | Eloquent/Doctrine |
| **Middleware** | `requireAuth()` interno | Sistema de middlewares encadenables |
| **Request** | `$_GET`, `$_POST` directos | Request::capture() encapsulado |
| **Validacion** | Model.php helpers (non-empty, min-length, FK existence) | FormRequest classes |

---

## 11. Pruebas Realizadas

| Prueba | Resultado |
|--------|-----------|
| Cargar login.php sin sesion | PASS |
| Login con credenciales correctas -> dashboard | PASS |
| Login con credenciales incorrectas -> error | PASS |
| Acceder a dashboard sin sesion -> redirige a login | PASS |
| Cargar inventario, ventas, etc. con sesion | PASS |
| `?pagina=inexistente` -> 404 | PASS |
| `?pagina=../../../etc` -> redirige a login | PASS |
| Tema oscuro/claro persiste en localStorage | PASS |
| Carrito POS agrega/quita productos | PASS |
| Cyber toggle de estados | PASS |
| Asesoria valida documentos permitidos/denegados | PASS |
| Busqueda en tablas filtra correctamente | PASS |
| Paginacion cambia de pagina visualmente | PASS |
| Service Worker se registra correctamente | PASS |
| Assets locales funcionan sin CDN | PASS |
| Todos los archivos PHP sin errores de sintaxis | PASS |
| CRUD inventario via AJAX (crear, editar, eliminar) | PASS |
| CRUD roles via AJAX (crear, editar, eliminar, permisos) | PASS |
| CRUD proveedores via AJAX (crear, editar, eliminar) | PASS |
| CRUD usuarios via AJAX (crear, editar, eliminar) | PASS |
| CRUD clientes via AJAX (crear, editar, eliminar) | PASS |
| CRUD proveedores-gestion via AJAX (crear, editar, eliminar) | PASS |
| Login con usuario de BD via AuthController::login() | PASS |
| Logout via `?pagina=login&logout=1` (Router::logout) | PASS |
| CSRF token se genera y valida en todas las peticiones | PASS |
| Autoloader de Composer (PSR-4) funciona | PASS |

**Verificación extremo a extremo (v4.2, suite `test_crud.ps1`):** 70/70 PASS sobre los 13 controladores y todos los módulos (35 endpoints AJAX GET, CRUD de clientes/inventario/proveedores/activos/roles/usuarios/asesorías/ventas/cyber, 5 reportes + exportaciones CSV/Excel/PDF y eliminación de roles con permisos).

---

**Documentacion**: Julio 2026 (actualizada Sept 2026, v4.3 — fix en `app.selects.js`: la barra de búsqueda de los selects de Materialize ya no se cierra al hacer clic en ella y ya no roba el foco al escribir)

