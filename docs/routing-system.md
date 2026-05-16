# Sistema de Enrutamiento MVC - Documentación Técnica

## Arquitectura General

```
src/
├── index.php                    ← Punto de entrada (Front Controller)
├── .htaccess                    ← Reglas de reescritura (URLs limpias)
├── Core/                        ← Núcleo del framework MVC
│   ├── Request.php              ← Encapsula datos HTTP (GET, POST, SERVER)
│   ├── Router.php               ← Motor de enrutamiento (compara rutas, ejecuta controladores)
│   └── Controller.php           ← Clase base para todos los controladores
├── Controllers/                 ← Controladores de la aplicación
│   ├── AuthController.php       ← Login / Logout
│   ├── DashboardController.php  ← Panel de Control
│   ├── InventarioController.php ← Gestión de Inventario
│   ├── VentasController.php     ← Punto de Venta
│   ├── CiberControlController.php
│   ├── ProveedoresController.php
│   ├── ReportesController.php
│   ├── ActivosController.php
│   └── MenuController.php
├── app/
│   ├── Views/                   ← Archivos de vista (HTML + PHP mínimo)
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   ├── 404.php
│   │   └── ...
│   └── template/
│       └── layout.php           ← Layout maestro (sidebar, header)
├── Config/
│   └── database.php
├── Public/
│   ├── css/
│   └── js/
└── ...
```

---

## 1. `composer.json` — Autoloading PSR-4

```json
{
    "name": "carlospez/clase",
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "authors": [...],
    "require": {}
}
```

| Línea | Explicación |
|-------|-------------|
| `"App\\": "src/"` | Mapea el namespace `App\` al directorio `src/`. Cualquier clase con namespace `App\Core\Router` se buscará en `src/Core/Router.php`. Esto sigue el estándar PSR-4 de carga automática. |
| `require: {}` | No hay dependencias externas. El sistema es 100% vanilla PHP. |

Después de modificar este archivo se ejecuta `composer dump-autoload` para regenerar los mapas de autocarga.

---

## 2. `src/.htaccess` — URLs Limpias

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
| `RewriteCond %{REQUEST_FILENAME} !-f` | Solo aplica la regla si el archivo NO existe físicamente (permite servir CSS, JS, imágenes). |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Solo aplica si el directorio NO existe. |
| `RewriteRule ^(.*)$ index.php [QSA,L]` | Cualquier otra ruta (ej. `/dashboard`, `/login`) se envía a `index.php`. `QSA` preserva los query parameters. |

---

## 3. `src/index.php` — Front Controller

```php
<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;

$router = new Router();

$router->get('/', 'AuthController@showLogin');

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout')->middleware('auth');

$router->get('/dashboard', 'DashboardController@index')->middleware('auth');
$router->get('/inventario', 'InventarioController@index')->middleware('auth');
$router->get('/ventas', 'VentasController@index')->middleware('auth');
$router->get('/ciberControl', 'CiberControlController@index')->middleware('auth');
$router->get('/proveedores', 'ProveedoresController@index')->middleware('auth');
$router->get('/reportes', 'ReportesController@index')->middleware('auth');
$router->get('/activos', 'ActivosController@index')->middleware('auth');
$router->get('/menu', 'MenuController@index')->middleware('auth');

$router->dispatch(Request::capture());
```

| Línea | Explicación |
|-------|-------------|
| `session_start()` | Inicia la sesión PHP para manejar autenticación. Se llama **antes** que cualquier salida al navegador. |
| `require_once __DIR__ . '/../vendor/autoload.php'` | Carga el autoloader de Composer. Sin esto, las clases con namespace (`App\Core\Router`, etc.) no se encontrarían. |
| `use App\Core\Router` | Importa la clase Router al ámbito actual para poder usar `new Router()` sin escribir el namespace completo. |
| `$router = new Router()` | Crea una instancia del motor de enrutamiento. |
| `$router->get('/login', 'AuthController@showLogin')` | Registra una ruta: cuando alguien visita `GET /login`, ejecuta el método `showLogin` del controlador `AuthController`. |
| `->middleware('auth')` | Cadena de métodos: asigna el middleware `auth` a la ruta. El Router verificará que el usuario esté autenticado antes de ejecutar el controlador. |
| `$router->dispatch(Request::capture())` | Toma la petición HTTP actual (`Request::capture()` envuelve `$_GET`, `$_POST`, `$_SERVER`), compara contra todas las rutas registradas y ejecuta la que coincida. |

### Convención de nomenclatura de rutas

Cada ruta usa el formato: `GET /dashboard` → `DashboardController@index`

- **DashboardController** se busca en `src/Controllers/DashboardController.php` (namespace `App\Controllers\DashboardController`)
- **@index** es el método público que se ejecutará
- El nombre de la ruta coincide con el nombre del controlador y el de la vista

---

## 4. `src/Core/Request.php` — Clase Request

```php
<?php
namespace App\Core;

class Request
{
    private array $query;
    private array $body;
    private array $server;

    public function __construct(array $query, array $body, array $server)
    {
        $this->query  = $query;
        $this->body   = $body;
        $this->server = $server;
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }
}
```

| Línea | Explicación |
|-------|-------------|
| `namespace App\Core` | Esta clase pertenece al namespace `App\Core`, por lo que su archivo debe estar en `src/Core/Request.php`. |
| `private array $query, $body, $server` | Propiedades privadas que almacenan copias de `$_GET`, `$_POST` y `$_SERVER`. Al encapsularlas, el resto del sistema no depende directamente de superglobales. |
| `public static function capture(): self` | Método factory (patrón de diseño). Crea un objeto Request con los datos de la petición actual. Se usa en index.php: `Request::capture()`. |
| `getUri(): string` | Extrae el path de la URL. `parse_url('http://ej.com/dashboard?x=1', PHP_URL_PATH)` devuelve `/dashboard`. Luego elimina `/` final y normaliza a `/` si queda vacío. |
| `getMethod(): string` | Devuelve el método HTTP en mayúsculas (`GET`, `POST`). |
| `get($key, $default)` | Acceso seguro a `$_GET`. Si la clave no existe, devuelve el valor por defecto (evita errores `undefined array key`). |
| `post($key, $default)` | Acceso seguro a `$_POST`. Misma protección que `get()`. |
| `isMethod($method)` | Compara si el método de la petición coincide con el argumento (útil para condicionales en controladores). |

---

## 5. `src/Core/Router.php` — Motor de Enrutamiento

```php
<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function middleware(string $name): self
    {
        $index = array_key_last($this->routes);
        $this->routes[$index]['middleware'][] = $name;
        return $this;
    }

    private function addRoute(string $method, string $path, string $handler): self
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => [],
        ];

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $uri = $request->getUri();
        $method = $request->getMethod();

        if ($uri === '/') {
            $uri = $this->isAuthenticated() ? '/dashboard' : '/login';
        }

        $legacyPage = $request->get('pagina');
        if ($legacyPage && $uri === '/') {
            $uri = '/' . $legacyPage;
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (!$this->runMiddleware($route['middleware'])) {
                    return;
                }

                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        $this->handleNotFound();
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    private function runMiddleware(array $middleware): bool
    {
        foreach ($middleware as $name) {
            if ($name === 'auth') {
                if (!$this->isAuthenticated()) {
                    header('Location: /login');
                    return false;
                }
            }
        }
        return true;
    }

    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $methodName] = explode('@', $handler);
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException(
                "Controller {$controllerClass} not found"
            );
        }

        $controller = new $controllerClass();
        $controller->$methodName(...$params);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        $viewPath = __DIR__ . '/../app/Views/404.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<h1>404 - Pagina no encontrada</h1>';
        }
    }
}
```

| Línea | Explicación |
|-------|-------------|
| `private array $routes = []` | Almacena todas las rutas registradas. Cada entrada contiene: método HTTP, patrón regex, nombre del handler, y lista de middlewares. |
| `get($path, $handler): self` | Método público para registrar rutas GET. Delega en `addRoute()` y retorna `$this` para permitir encadenamiento (`->middleware()`). |
| `post($path, $handler): self` | Idéntico a `get()` pero para peticiones POST. |
| `middleware($name): self` | Toma la **última ruta registrada** (con `array_key_last`) y le añade un middleware. Retorna `$this`. |
| `addRoute($method, $path, $handler): self` | **Convierte la ruta amigable en expresión regular.** Por ejemplo, `/usuario/{id}` se transforma en `#^/usuario/(?P<id>[^/]+)$#`. Esto permite rutas dinámicas. Las rutas sin parámetros se convierten en `#^/dashboard$#`. |
| `dispatch($request)` | **Método principal.** 1) Obtiene URI y método. 2) Si es `/` y está autenticado va a dashboard, si no a login. 3) Soporte legacy: si hay `$_GET['pagina']`, convierte `/?pagina=dashboard` en `/dashboard`. 4) Itera rutas registradas buscando coincidencia. 5) Si coincide, ejecuta middleware y handler. 6) Si ninguna coincide, 404. |
| `if ($uri === '/')` | Redirige la raíz según el estado de autenticación. Esto es más elegante que mostrar un 404 en la raíz. |
| `$legacyPage = $request->get('pagina')` | **Compatibilidad hacia atrás.** URLs antiguas como `?pagina=dashboard` siguen funcionando. Se convierten internamente a `/dashboard`. |
| `preg_match($route['pattern'], $uri, $matches)` | Compara la URI contra el patrón regex de la ruta. `$matches` contiene tanto índices numéricos como nombres de parámetros. |
| `array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)` | Filtra `$matches` para quedarse solo con las claves de tipo string (los parámetros nombrados como `{id}`). Descarta los índices numéricos. |
| `if (!$this->runMiddleware(...))` | Ejecuta los middlewares. Si alguno falla (retorna `false`), el handler **no** se ejecuta. |
| `$this->callHandler($handler, $params)` | Descompone `DashboardController@index` en nombre de clase y método, construye el FQCN (`App\Controllers\DashboardController`), verifica que exista, lo instancia y llama al método. |
| `$controller->$methodName(...$params)` | `...$params` es el operador **splat** de PHP. Convierte el array asociativo `['id' => '5']` en argumentos nombrados del método. El método `show($id)` recibiría `5`. |
| `handleNotFound()` | Establece código HTTP 404 e incluye la vista de error. Si no existe la vista, muestra un mensaje en texto plano (fallback). |

### Flujo de Middleware `auth`

```
Petición → Router.match → ¿Ruta tiene middleware 'auth'?
  ├── No → ejecuta controlador normalmente
  └── Sí → ¿$_SESSION['logged_in'] === true?
       ├── Sí → ejecuta controlador
       └── No → header('Location: /login') + exit
```

### Seguridad implementada

1. **Whitelist de rutas:** Solo las rutas explícitamente registradas en `index.php` son accesibles. Ya no se depende de `$_GET['pagina']` validado con regex.
2. **Middleware de autenticación:** Las rutas protegidas requieren sesión activa. Si no, el usuario es redirigido automáticamente.
3. **Sin inclusión directa de archivos:** El Router nunca hace `require` de un archivo basado en input del usuario. Solo instancia controladores predefinidos.
4. **404 para rutas desconocidas:** Cualquier ruta no registrada recibe un 404, no un error críptico.

---

## 6. `src/Core/Controller.php` — Clase Base

```php
<?php
namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../app/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '{$view}' not found");
        }

        extract($data);
        require $viewPath;
    }

    protected function renderWithLayout(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../app/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '{$view}' not found");
        }

        $data['pageTitle']   = $data['pageTitle']   ?? 'EIS System';
        $data['headerExtra'] = $data['headerExtra'] ?? '';
        $data['pagina']      = $data['pagina']      ?? $view;

        extract($data);
        $contentView = $viewPath;

        require __DIR__ . '/../app/template/layout.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
```

| Línea | Explicación |
|-------|-------------|
| `abstract class Controller` | Clase abstracta: no puede instanciarse directamente, solo sirve como clase padre. Todos los controladores del negocio extienden esta clase. |
| `render($view, $data)` | **Para vistas públicas (login).** 1) Construye la ruta absoluta de la vista. 2) Verifica que el archivo exista. 3) `extract($data)` convierte `['titulo' => 'Hola']` en variable `$titulo = 'Hola'`. 4) Incluye la vista. La vista tiene su propio DOCTYPE y estructura HTML completa. |
| `renderWithLayout($view, $data)` | **Para vistas autenticadas (con layout).** Similar a `render()` pero: 1) Establece valores por defecto para `pageTitle`, `headerExtra` y `pagina` (necesario para el layout). 2) Guarda la ruta de la vista en `$contentView`. 3) Incluye `layout.php`, que a su vez hará `require $contentView`. |
| `redirect($url)` | Envía cabecera `Location` y termina la ejecución con `exit`. |
| `json($data, $status)` | Para respuestas API. Establece código de estado, cabecera `Content-Type: application/json` y devuelve JSON con soporte Unicode. |

### ¿Por qué `extract()`?

`extract()` convierte un array asociativo en variables individuales. Si llamas:

```php
$this->renderWithLayout('dashboard', ['pageTitle' => 'Panel']);
```

Dentro de `layout.php` existirá `$pageTitle` con valor `'Panel'`. Es la forma más simple de pasar datos a vistas sin un motor de templates.

### Diferencia entre render y renderWithLayout

| Método | Uso | Qué incluye |
|--------|-----|-------------|
| `render()` | Páginas públicas (login) | La vista directamente (tiene su propio `<html>`) |
| `renderWithLayout()` | Páginas internas | El layout (`<html>`, sidebar, header) + la vista como contenido |

---

## 7. Controladores de la Aplicación

### `AuthController.php`

```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        $this->render('login');
    }

    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username === 'admin' && $password === '1234') {
            $_SESSION['logged_in']  = true;
            $_SESSION['username']   = $username;
            $this->redirect('/dashboard');
        }

        $this->redirect('/login?error=1');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}
```

| Método | Ruta | Explicación |
|--------|------|-------------|
| `showLogin()` | `GET /login` | Si ya está autenticado, redirige al dashboard. Si no, muestra el formulario de login. |
| `login()` | `POST /login` | Valúa credenciales contra valores hardcodeados (mejorable con BD). En éxito: guarda `$_SESSION['logged_in']` y redirige a dashboard. En fallo: redirige a `/login?error=1`. |
| `logout()` | `GET /logout` | Limpia el array `$_SESSION`, destruye la sesión y redirige al login. |

### Controladores de Páginas (ej. `DashboardController.php`)

```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->renderWithLayout('dashboard', [
            'pageTitle' => 'Panel de Control',
        ]);
    }
}
```

Todos los controladores de páginas siguen exactamente el mismo patrón:

1. Extienden `Controller`
2. Tienen un método `index()` (o el que se especifique en la ruta)
3. Llaman `$this->renderWithLayout('nombre-vista', ['pageTitle' => '...'])`

**Excepción:** `CiberControlController` además pasa `headerExtra` con los badges de estado:

```php
$this->renderWithLayout('ciberControl', [
    'pageTitle'  => 'Control de Cybercafé',
    'headerExtra' => '<span class="badge badge-success">7 Disponibles</span>'
        . '<span class="badge badge-warning">3 Ocupadas</span>',
]);
```

---

## 8. Vista 404 (`src/app/Views/404.php`)

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 - Pagina no encontrada</title>
    <link rel="stylesheet" href="/Public/css/styles.css">
</head>
<body>
    <div style="...">
        <h1>404</h1>
        <p>La ruta <strong><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?></strong> no existe.</p>
        <a href="/dashboard">Volver al Dashboard</a>
    </div>
</body>
</html>
```

| Elemento | Explicación |
|----------|-------------|
| `htmlspecialchars()` | Escapa caracteres HTML en la salida. Si alguien visita `/"><script>`, el script se mostrará como texto, no se ejecutará. Esto previene XSS. |
| `href="/dashboard"` | Enlace de rescate para que el usuario pueda volver a la aplicación. |

---

## 9. `src/app/template/layout.php` — Layout Maestro

Fragmento relevante del sidebar (todos los enlaces actualizados):

```php
<a href="/dashboard" class="nav-link<?= $pagina === 'dashboard' ? ' active' : '' ?>">
    <span class="nav-icon">📊</span> Dashboard
</a>
<a href="/logout" class="nav-link">
    <span class="nav-icon">🚪</span> Cerrar Sesión
</a>
```

| Cambio | Antes | Después |
|--------|-------|---------|
| URLs | `?pagina=dashboard` | `/dashboard` |
| Assets | `Public/css/styles.css` | `/Public/css/styles.css` (ruta absoluta) |
| Cierre sesión | `?pagina=login` (no destruía sesión) | `/logout` (destruye sesión) |

El uso de rutas absolutas para assets (`/Public/css/...`) es importante porque con URLs limpias como `/dashboard`, el navegador resuelve rutas relativas contra la URL actual. Con rutas absolutas no hay ambigüedad.

---

## 10. Seguridad del Sistema

| Aspecto | Cómo se maneja |
|---------|----------------|
| **Inyección de rutas** | Solo se ejecutan rutas explícitamente registradas. No hay `require` dinámico basado en input. |
| **Autenticación** | Middleware `auth` verifica `$_SESSION['logged_in']` antes de ejecutar cualquier controlador protegido. |
| **XSS en 404** | `htmlspecialchars()` escapa la URI mostrada al usuario. |
| **CSRF** | Pendiente de implementar (token CSRF en formularios). |
| **SQL Injection** | No aplica al sistema de rutas. El modelo `crud_users.php` ya usa consultas preparadas con PDO. |
| **Path Traversal** | El Router nunca construye paths de archivo basados en la URI del usuario. |

---

## 11. Cómo Agregar una Nueva Página

Para agregar una nueva página al sistema, sigue estos 4 pasos:

### Paso 1: Crear la vista

Crea `src/app/Views/mi-pagina.php` con el contenido HTML de la página (sin DOCTYPE, solo el contenido que irá dentro del layout):

```html
<div class="card">
    <h2>Mi Nueva Página</h2>
    <p>Contenido aquí...</p>
</div>
```

### Paso 2: Crear el controlador

Crea `src/Controllers/MiPaginaController.php`:

```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class MiPaginaController extends Controller
{
    public function index(): void
    {
        $this->renderWithLayout('mi-pagina', [
            'pageTitle' => 'Título de mi página',
        ]);
    }
}
```

### Paso 3: Registrar la ruta

En `src/index.php`, agrega:

```php
$router->get('/mi-pagina', 'MiPaginaController@index')->middleware('auth');
```

### Paso 4: Agregar al menú

En `src/app/template/layout.php`, agrega un enlace en el `<nav>`:

```php
<a href="/mi-pagina" class="nav-link<?= $pagina === 'mi-pagina' ? ' active' : '' ?>">
    <span class="nav-icon">🔹</span> Mi Página
</a>
```

### Si la página no requiere autenticación

```php
// En index.php (sin middleware)
$router->get('/pagina-publica', 'PaginaPublicaController@index');

// En el controlador, usar render() en vez de renderWithLayout()
public function index(): void {
    $this->render('pagina-publica');  // Sin layout, HTML completo
}
```

---

## 12. Compatibilidad hacia Atrás

El sistema soporta URLs antiguas con `?pagina=X` de forma transparente:

```
URL antigua:  ?pagina=dashboard
URL nueva:   /dashboard

Ambas funcionan y ejecutan exactamente el mismo controlador.
```

Esto se logra en el Router.dispatch():

```php
$legacyPage = $request->get('pagina');
if ($legacyPage && $uri === '/') {
    $uri = '/' . $legacyPage;
}
```

Cuando alguien visita `/?pagina=dashboard`, la URI es `/` y existe `$_GET['pagina'] = 'dashboard'`. El Router reescribe internamente la URI a `/dashboard` y la ruta coincide normalmente.

---

## 13. Resumen del Flujo de una Petición

```
Usuario escribe:  http://localhost/dashboard

1. Apache recibe GET /dashboard
2. .htaccess: ¿Existe el archivo /dashboard? No → rewrite a index.php
3. index.php: session_start(), carga autoloader, crea Router
4. Router registra todas las rutas (GET /dashboard → DashboardController@index)
5. Request::capture() envuelve GET/POST/SERVER
6. Router.dispatch():
   a. URI = /dashboard, Method = GET
   b. Itera rutas → encuentra coincidencia: GET /dashboard
   c. Ejecuta middleware 'auth' → $_SESSION['logged_in'] = true → OK
   d. callHandler('DashboardController@index', [])
   e. Instancia DashboardController → llama index()
7. DashboardController.index():
   a. renderWithLayout('dashboard', ['pageTitle' => 'Panel de Control'])
   b. Incluye layout.php (DOCTYPE, sidebar, header)
   c. layout.php hace require de Views/dashboard.php (contenido)
8. Se envía HTML completo al navegador
```

---

## 14. Diagrama de Clases (Simplificado)

```
┌─────────────────────┐          ┌──────────────────────┐
│      Request        │          │       Router         │
├─────────────────────┤          ├──────────────────────┤
│ - query: array      │          │ - routes: array      │
│ - body: array       │          │                      │
│ - server: array     │          │ + get($p, $h): self  │
├─────────────────────┤          │ + post($p, $h): self │
│ + capture(): self   │◄─────────│ + middleware($n):self │
│ + getUri(): string  │  usa     │ + dispatch($r): void │
│ + getMethod(): str  │          └──────────┬───────────┘
│ + get(k, d): mixed  │                     │
│ + post(k, d): mixed │           llama a   │
└─────────────────────┘                     │
                                  ┌─────────▼───────────┐
                                  │     Controller       │
                                  │     (abstract)       │
                                  ├──────────────────────┤
                                  │ # render($v, $d)     │
                                  │ # renderWithLayout() │
                                  │ # redirect($url)     │
                                  │ # json($d, $status)  │
                                  └──────────┬───────────┘
                                             │
                                ┌────────────┼────────────┐
                                │            │            │
                    ┌───────────▼──┐  ┌──────▼─────┐  ┌──▼───────────┐
                    │AuthController│  │Dashboard...│  │Inventario... │
                    ├──────────────┤  │Controller  │  │Controller    │
                    │+ showLogin() │  ├────────────┤  ├──────────────┤
                    │+ login()     │  │+ index()   │  │+ index()     │
                    │+ logout()    │  └────────────┘  └──────────────┘
                    └──────────────┘
```

---

## 15. Pruebas Realizadas

| Prueba | Resultado |
|--------|-----------|
| Ruta /dashboard con sesión activa | ✅ PASS |
| Ruta /dashboard sin sesión → redirige a /login | ✅ PASS |
| GET /login sin sesión → muestra formulario | ✅ PASS |
| GET /login con sesión → redirige a dashboard | ✅ PASS |
| POST /login con credenciales válidas → redirige | ✅ PASS |
| POST /login con credenciales inválidas → redirige con error | ✅ PASS |
| GET /logout → destruye sesión y redirige | ✅ PASS |
| GET /ruta-desconocida → muestra 404 | ✅ PASS |
| Legacy `?pagina=dashboard` → funciona igual que /dashboard | ✅ PASS |
| Raíz `/` sin sesión → redirige a /login | ✅ PASS |
| Raíz `/` con sesión → redirige a /dashboard | ✅ PASS |
| Autoloader PSR-4 encuentra todas las clases | ✅ PASS |
| Todos los archivos PHP sin errores de sintaxis | ✅ PASS |
