# Sistema de Enrutamiento MVC — Documentación Técnica

## Arquitectura Actual (MVC)

```
src/
├── index.php                    ← Front Controller (autoloader + Router::dispatch)
├── .htaccess                    ← Reglas de reescritura (URLs query string)
├── app/
│   ├── Core/
│   │   ├── Router.php           ← Clase enrutadora con mapa de rutas (namespace App\Core)
│   │   └── Controller.php       ← Clase base abstracta para todos los controladores
│   ├── Controllers/             ← 10 controladores (uno por módulo)
│   │   ├── LoginController.php
│   │   ├── DashboardController.php
│   │   ├── InventarioController.php
│   │   ├── VentasController.php
│   │   ├── ProveedoresController.php
│   │   ├── ReportesController.php
│   │   ├── ActivosController.php
│   │   ├── CiberControlController.php
│   │   ├── AsesoriasController.php
│   │   └── MenuController.php
│   ├── Models/
│   │   ├── crud_users.php       # Funciones CRUD (procedural, incluidas manualmente)
│   │   └── crud_asesorias.php
│   └── Views/
│       ├── auth/login.php
│       ├── dashboard/index.php
│       ├── inventario/index.php
│       ├── ventas/index.php
│       ├── proveedores/index.php
│       ├── reportes/index.php
│       ├── activos/index.php
│       ├── ciber-control/index.php
│       ├── asesorias/index.php
│       ├── menu/index.php
│       └── layouts/main.php
├── Config/
│   └── database.php
└── Public/
    ├── css/
    └── js/
```

---

## 1. `composer.json` — Autoloading PSR-4

```json
{
    "name": "carlospez/clase",
    "autoload": {
        "psr-4": {
            "App\\": "src/app/"
        }
    },
    "require": {}
}
```

| Clave | Explicación |
|-------|-------------|
| `"App\\": "src/app/"` | Mapea el namespace `App\` al directorio `src/app/`. Ej: `App\Core\Router` → `src/app/Core/Router.php`, `App\Controllers\LoginController` → `src/app/Controllers/LoginController.php` |
| `require: {}` | Sin dependencias externas. Sistema 100% vanilla PHP. |

El autoloader se genera con `composer dump-autoload` y se carga desde `index.php` mediante `require_once __DIR__.'/../vendor/autoload.php'`.

---

## 2. `src/.htaccess` — URLs con Query String

```apache
Options All -Indexes
RewriteEngine On

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

| Línea | Explicación |
|-------|-------------|
| `Options All -Indexes` | Bloquea el listado de directorios por seguridad. |
| `RewriteEngine On` | Activa el módulo de reescritura de Apache. |
| `RewriteRule ^$ index.php [L,QSA]` | La raíz `/` se redirige internamente a `index.php`. |
| `RewriteCond %{REQUEST_FILENAME} !-f` | Solo aplica si el archivo NO existe físicamente. |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Solo aplica si el directorio NO existe. |
| `RewriteRule ^(.*)$ index.php [QSA,L]` | Cualquier otra ruta se envía a `index.php`. `QSA` preserva los query parameters. |

Actualmente las URLs usan el formato `?pagina=nombre`. No hay URLs limpias implementadas.

---

## 3. `src/index.php` — Front Controller

```php
<?php
require_once __DIR__.'/../vendor/autoload.php';

use App\Core\Router;

$router = new Router();
$router->dispatch();
```

| Línea | Explicación |
|-------|-------------|
| `require_once __DIR__.'/../vendor/autoload.php'` | Carga el autoloader de Composer. Gracias al mapeo PSR-4, las clases en `src/app/` se cargan automáticamente al usarlas. |
| `use App\Core\Router` | Importa la clase Router desde el namespace `App\Core`. |
| `new Router()` | Crea una instancia del Router. El constructor define el mapa de rutas. |
| `$router->dispatch()` | Ejecuta el método dispatch que inicia la sesión, lee `?pagina=`, verifica autenticación, encuentra la ruta e instancia el controlador correspondiente. |

---

## 4. `src/app/Core/Router.php` — Clase Enrutadora

### Mapa de rutas

```php
private array $routes = [
    'login'         => ['controller' => 'LoginController',        'method' => 'index',    'public' => true],
    'login_validate' => ['controller' => 'LoginController',       'method' => 'validate', 'public' => true],
    'dashboard'     => ['controller' => 'DashboardController',    'method' => 'index'],
    'inventario'    => ['controller' => 'InventarioController',   'method' => 'index'],
    'ventas'        => ['controller' => 'VentasController',       'method' => 'index'],
    'ciberControl'  => ['controller' => 'CiberControlController', 'method' => 'index'],
    'proveedores'   => ['controller' => 'ProveedoresController',  'method' => 'index'],
    'reportes'      => ['controller' => 'ReportesController',     'method' => 'index'],
    'activos'       => ['controller' => 'ActivosController',      'method' => 'index'],
    'asesorias'     => ['controller' => 'AsesoriasController',    'method' => 'index'],
    'menu'          => ['controller' => 'MenuController',         'method' => 'index'],
];
```

### Método `dispatch()`

```php
public function dispatch(): void
{
    session_start();
    $pagina = $_GET["pagina"] ?? 'login';

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
        $pagina = 'login';
    }

    $route = $this->routes[$pagina] ?? null;

    if (!$route) {
        http_response_code(404);
        echo "<h1>Error 404: Página no encontrada</h1>";
        return;
    }

    if (empty($route['public']) && !isset($_SESSION['logged_in'])) {
        header("Location: ?pagina=login");
        exit;
    }

    $controllerClass = "App\\Controllers\\{$route['controller']}";
    $method = $route['method'];

    if (!class_exists($controllerClass)) {
        throw new \Exception("Controller {$controllerClass} no encontrado");
    }

    $controller = new $controllerClass();
    $controller->$method();
}
```

| Paso | Explicación |
|------|-------------|
| `session_start()` | Inicia la sesión PHP. Debe llamarse antes de cualquier salida. |
| `$pagina = $_GET["pagina"] ?? 'login'` | Lee el parámetro de la URL. Por defecto: login. |
| `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` | Validación de seguridad: solo caracteres seguros. Previene path traversal. |
| `$route = $this->routes[$pagina] ?? null` | Busca la ruta en el mapa. null si no existe. |
| 404 si `$route` es null | Muestra error si la página no está registrada. |
| `if (empty($route['public']) && !isset($_SESSION['logged_in']))` | Redirige al login si la ruta requiere autenticación y el usuario no ha iniciado sesión. |
| `$controllerClass = "App\\Controllers\\{$route['controller']}"` | Construye el nombre completo de la clase (con namespace). |
| `class_exists($controllerClass)` | Verifica que la clase exista (carga automática vía PSR-4). |
| `$controller = new $controllerClass(); $controller->$method()` | Instancia el controlador y ejecuta el método. |

---

## 5. `src/app/Core/Controller.php` — Clase Base

```php
abstract class Controller
{
    protected array $pageTitles = [
        'dashboard'    => 'Panel de Control',
        'inventario'   => 'Gestión de Inventario',
        'ventas'       => 'Punto de Venta (POS)',
        'ciberControl' => 'Control de Cybercafé',
        'proveedores'  => 'Solicitudes a Proveedores',
        'reportes'     => 'Reportes y Estadísticas',
        'activos'      => 'Gestión de Activos',
        'asesorias'    => 'Asesoría Legal',
    ];

    protected array $extraHeaders = [
        'ciberControl' => '<span class="chip green white-text">5 Disponibles</span><span class="chip orange white-text">4 Ocupadas</span>',
    ];

    protected string $currentPage;

    public function __construct()
    {
        $this->currentPage = $_GET["pagina"] ?? 'dashboard';
    }

    protected function render(string $viewPath, array $data = []): void
    {
        $pageTitle = $this->pageTitles[$this->currentPage] ?? 'EIS System';
        $headerExtra = $this->extraHeaders[$this->currentPage] ?? '';
        $pagina = $this->currentPage;
        extract($data);
        $contentView = __DIR__ . '/../Views/' . $viewPath . '.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    protected function renderPublic(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $viewPath . '.php';
    }
}
```

### Métodos

| Método | Descripción |
|--------|-------------|
| `render($viewPath, $data)` | Renderiza una vista dentro del layout principal. Establece `$pageTitle`, `$headerExtra`, `$pagina`, extrae `$data` como variables e incluye `layouts/main.php` que a su vez incluye la vista. |
| `renderPublic($viewPath, $data)` | Renderiza una vista pública sin layout (login, páginas standalone). |

### Variables disponibles en el layout

| Variable | Origen | Propósito |
|----------|--------|-----------|
| `$pageTitle` | `$this->pageTitles[$this->currentPage]` | Título en `<title>` y barra de navegación |
| `$headerExtra` | `$this->extraHeaders[$this->currentPage]` | HTML extra en el header (badges) |
| `$pagina` | `$this->currentPage` | Nombre de página actual (para clase `active` en sidebar) |
| `$contentView` | Ruta absoluta a la vista | Incluido dentro del layout |

---

## 6. Controladores

Cada módulo tiene su propio controlador que extiende `Controller`:

| Controlador | Método | Vista | Descripción |
|-------------|--------|-------|-------------|
| `LoginController` | `index()` | `auth/login.php` | Muestra formulario de login |
| `LoginController` | `validate()` | — | Procesa credenciales (POST) |
| `DashboardController` | `index()` | `dashboard/index.php` | Panel de control |
| `InventarioController` | `index()` | `inventario/index.php` | Gestión de inventario |
| `VentasController` | `index()` | `ventas/index.php` | Punto de venta POS |
| `ProveedoresController` | `index()` | `proveedores/index.php` | Solicitudes a proveedores |
| `ReportesController` | `index()` | `reportes/index.php` | Reportes y estadísticas |
| `ActivosController` | `index()` | `activos/index.php` | Activos fijos |
| `CiberControlController` | `index()` | `ciber-control/index.php` | Cybercafé (datos PHP) |
| `AsesoriasController` | `index()` | `asesorias/index.php` | Asesoría legal |
| `MenuController` | `index()` | `menu/index.php` | Menú principal |

### Ejemplo: `CiberControlController`

Este controlador **prepara datos en el servidor** y los pasa a la vista, a diferencia de otros que renderizan datos estáticos:

```php
class CiberControlController extends Controller
{
    public function index(): void
    {
        $zonas = [ /* array de zonas y estaciones */ ];
        $countDisponibles = count(array_filter(..., fn($e) => $e['status'] === 'disponible'));
        // ... más cálculos ...
        $this->render('ciber-control/index', compact(
            'zonas', 'countDisponibles', 'countOcupadas',
            'countMantenimiento', 'totalEstaciones', 'statusLabels'
        ));
    }
}
```

---

## 7. Vistas

Organizadas en subdirectorios por módulo:

```
Views/
├── auth/login.php              # Pública, standalone (sin layout)
├── dashboard/index.php         # Protegida, dentro del layout
├── inventario/index.php
├── ventas/index.php
├── proveedores/index.php
├── reportes/index.php
├── activos/index.php
├── ciber-control/index.php
├── asesorias/index.php
├── menu/index.php
└── layouts/main.php            # Layout principal
```

### `layouts/main.php`
Proporciona:
- `<!DOCTYPE html>` y estructura HTML completa
- Sidebar con Materialize Sidenav (9 módulos + theme toggle + cerrar sesión)
- Header con nav, reloj, notificaciones, header extra
- Contenedor `<main>` que incluye `$contentView`
- Botón "volver arriba"
- Materialize JS + `app.js`

### Vistas standalone (sin layout)
- `auth/login.php` — tiene su propio DOCTYPE, head, body. Usa `renderPublic()`.

---

## 8. Seguridad del Sistema

| Aspecto | Implementación |
|---------|----------------|
| **Inyección de rutas** | Regex `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` en Router |
| **Autenticación** | Verificación de `$_SESSION['logged_in']` en Router antes de llamar al controlador |
| **404** | Ruta no encontrada en mapa → `http_response_code(404)` |
| **CSRF** | Pendiente de implementar |
| **SQL Injection** | Los modelos usan prepared statements con PDO |
| **Path Traversal** | El router valida el parámetro `pagina` con regex estricto |

---

## 9. Cómo Agregar una Nueva Página

### Paso 1: Crear el Controlador

Crea `src/app/Controllers/MiPaginaController.php`:

```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class MiPaginaController extends Controller
{
    public function index(): void
    {
        $this->render('mi-pagina/index');
    }
}
```

### Paso 2: Crear la Vista

Crea `src/app/Views/mi-pagina/index.php` con el contenido HTML:

```html
<div class="card">
    <h2>Mi Nueva Página</h2>
    <p>Contenido aquí...</p>
</div>
```

### Paso 3: Registrar la Ruta

En `src/app/Core/Router.php`, agrega la ruta en el constructor:

```php
'mi-pagina' => ['controller' => 'MiPaginaController', 'method' => 'index'],
```

### Paso 4: Agregar al título

En `src/app/Core/Controller.php`, agrega el título en `$pageTitles`:

```php
'mi-pagina' => 'Título de mi página',
```

### Paso 5: Agregar al menú

En `src/app/Views/layouts/main.php`, agrega un enlace en el `<ul class="sidenav">`.

### Si la página es pública

Agrega `'public' => true` en la ruta y usa `$this->renderPublic()` en el controlador.

---

## 10. Mapa de Páginas

| Parámetro | Controlador | Método | Vista | ¿Pública? |
|-----------|-------------|--------|-------|-----------|
| `login` | `LoginController` | `index()` | `auth/login.php` | Sí |
| `login_validate` | `LoginController` | `validate()` | — (solo lógica) | Sí |
| `dashboard` | `DashboardController` | `index()` | `dashboard/index.php` | No |
| `inventario` | `InventarioController` | `index()` | `inventario/index.php` | No |
| `ventas` | `VentasController` | `index()` | `ventas/index.php` | No |
| `ciberControl` | `CiberControlController` | `index()` | `ciber-control/index.php` | No |
| `proveedores` | `ProveedoresController` | `index()` | `proveedores/index.php` | No |
| `reportes` | `ReportesController` | `index()` | `reportes/index.php` | No |
| `activos` | `ActivosController` | `index()` | `activos/index.php` | No |
| `asesorias` | `AsesoriasController` | `index()` | `asesorias/index.php` | No |
| `menu` | `MenuController` | `index()` | `menu/index.php` | No |

---

## 11. Diferencia con la Arquitectura Anterior (Procedural)

| Aspecto | Antes (Procedural) | Ahora (MVC) |
|---------|-------------------|-------------|
| **Enrutador** | `router.php` (68 líneas, procedural, sin clase) | `Core/Router.php` (clase con namespace, mapa de rutas) |
| **Punto de entrada** | `require_once router.php` directo | `vendor/autoload.php` + `new Router()` + `->dispatch()` |
| **Controladores** | No existían (lógica en vistas) | 10 clases en `Controllers/` con namespace |
| **Layout** | `app/template/layout.php` | `Views/layouts/main.php` |
| **Vistas** | `Views/dashboard.php` (plano) | `Views/dashboard/index.php` (subdirectorios) |
| **Autoloader** | No usado | Composer PSR-4 (`vendor/autoload.php`) |
| **Lógica de login** | `Views/login_validate.php` (vista) | `LoginController::validate()` (controlador) |
| **Datos de Cyber** | En la vista (`ciberControl.php`) | En el controlador (`CiberControlController`) |
| **Títulos** | En el router (`$titulos[]`) | En la clase base `Controller::$pageTitles` |
| **composer.json** | `"App\\": "src/"` | `"App\\": "src/app/"` |

---

## 12. Flujo Completo de una Petición

```
Usuario: GET /eis_zona_web_lara/src/?pagina=dashboard

1. Apache recibe la petición
   └── src/.htaccess → RewriteRule ^(.*)$ index.php [QSA,L]

2. index.php:
   ├── require vendor/autoload.php (carga clases PSR-4)
   ├── use App\Core\Router
   ├── new Router() (define mapa de rutas)
   └── $router->dispatch()

3. Router::dispatch():
   ├── session_start()
   ├── $pagina = "dashboard"
   ├── preg_match → OK
   ├── $route = ['controller' => 'DashboardController', 'method' => 'index']
   ├── ¿$_SESSION['logged_in']? → Sí (o redirige a login)
   ├── class_exists("App\Controllers\DashboardController") → Sí
   ├── $controller = new DashboardController()
   └── $controller->index()

4. DashboardController::index():
   └── $this->render('dashboard/index')

5. Controller::render('dashboard/index'):
   ├── $pageTitle = 'Panel de Control'
   ├── $contentView = __DIR__ . '/../Views/dashboard/index.php'
   └── require __DIR__ . '/../Views/layouts/main.php'

6. layouts/main.php:
   ├── <html><head> con Materialize CSS + jQuery
   ├── Sidebar con enlaces a módulos
   ├── Header con reloj y notificaciones
   ├── <main> → require $contentView (dashboard/index.php)
   └── Scripts: Materialize JS + app.js

7. app.js (cliente):
   ├── Inicializa componentes Materialize
   ├── Reloj digital, animación de contadores
   └── Búsquedas, filtros, carrito, etc.
```

---

## 13. Pruebas Realizadas

| Prueba | Resultado |
|--------|-----------|
| Cargar login.php sin sesión | ✅ PASS |
| Login con credenciales correctas (admin/1234) → dashboard | ✅ PASS |
| Login con credenciales incorrectas → error | ✅ PASS |
| Acceder a dashboard sin sesión → redirige a login | ✅ PASS |
| Cargar inventario, ventas, etc. con sesión | ✅ PASS |
| `?pagina=inexistente` → 404 | ✅ PASS |
| `?pagina=../../../etc` → redirige a login | ✅ PASS |
| Autoloader PSR-4 carga Router y Controllers | ✅ PASS |
| Tema oscuro/claro persiste en localStorage | ✅ PASS |
| Carrito POS agrega/quita productos | ✅ PASS |
| Cyber toggle de estados | ✅ PASS |
| Asesoría valida documentos permitidos/denegados | ✅ PASS |
| Búsqueda en tablas filtra correctamente | ✅ PASS |
| Paginación cambia de página visualmente | ✅ PASS |
| Todos los archivos PHP sin errores de sintaxis (27/27) | ✅ PASS |
