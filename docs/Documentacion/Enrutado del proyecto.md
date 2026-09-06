El proceso de enrutamiento de esta aplicación sigue el patrón **Front Controller** con una clase **Router OOP** en `App\Core\Router`. Todas las peticiones son centralizadas, procesadas por la clase Router, y derivadas a controladores según el tipo de solicitud.

### 1. Nivel de Servidor: Redirección (.htaccess)

Antes de que el código PHP se ejecute, el servidor web (Apache) prepara el camino:

- **Dentro de `src/` (`src/.htaccess`):**
    - `Options All -Indexes` bloquea el listado de directorios.
    - `RewriteEngine On` activa el motor de reescritura.
    - `RewriteRule ^$ index.php [L,QSA]`: la raíz va a `index.php`.
    - `RewriteCond %{REQUEST_FILENAME} !-f` y `!-d`: solo aplica si no es archivo/directorio real.
    - `RewriteRule ^([\w-]+)$ index.php?pagina=$1 [L,QSA]`: convierte `/dashboard` en `?pagina=dashboard` (URLs limpias parciales).

### 2. Punto de Entrada: index.php (21 líneas)

La ejecución de PHP:

- `require_once __DIR__ . '/../vendor/autoload.php'` — carga el autoloader PSR-4 de Composer.
- `use App\Core\Router` — importa la clase Router.
- `$router = new Router()` — constructor: `session_start()` (si no existe) + token CSRF.
- `$router->handle()` — método principal que procesa la solicitud.

El Router **no usa Request encapsulado ni registro de rutas con métodos HTTP**. En su lugar, usa un enfoque más simple: analiza `$_GET['pagina']` y deriva según casos.

### 3. El Motor del Router (router.php)

La clase `Router` tiene los siguientes métodos clave:

| Método | Función |
|--------|---------|
| `__construct()` | Inicia sesión si no existe y genera el token CSRF una sola vez por sesión |
| `handle()` | Determina el tipo de petición y redirige al controlador/vista correspondiente |
| `CONTROLLERS` | Mapa `pagina => clase` que centraliza los 12 controladores AJAX |
| `resolvePagina()` | Lee `$_GET["pagina"]`, valida con regex (solo alfanumérico+guiones), retorna string |
| `dispatchAction()` | Si la página está en `CONTROLLERS` y hay `action`, instancia el controlador y ejecuta `handle()` |
| `logout()` | Cierra sesión (regenera id + destruye) desde `?pagina=login&logout=1` |
| `verifyCsrfToken()` | Estático: compara el token con `hash_equals()` |
| `render()` | Renderiza páginas públicas directas y protegidas dentro del layout |
| `PUBLIC_PAGES` | Constante `['login', 'login_validate']`; control de acceso en `handle()` |
| `redirect()` | Envía `header('Location: ?pagina=...')` |

### 4. Procesamiento en handle()

1. ¿Es `login` con `logout=1` y hay sesión? → `Router::logout()`
2. Si no hay sesión y la página no es pública → `redirect('login')`
3. ¿La página está en el mapa `CONTROLLERS` y hay `action`? → `dispatchAction()`: instancia el controlador según `CONTROLLERS[$pagina]` y ejecuta `handle()` → JSON response
4. ¿Es `login_validate` por POST? → `AuthController::login()`
5. ¿Es vista normal? → `render()`:
   - Si es pública (`login`/`login_validate`): carga directa
   - Si es privada: renderiza dentro de `layout.php` (`$pageTitle`, `$headerExtra`, `$contentView`)

### 5. Controladores (Namespace App\Controllers)

| Controlador | Acciones |
|-------------|----------|
| `AuthController` | `login()` — verifica credenciales vs BD con `password_verify`; `logout()` — destruye sesión |
| `InventarioController` | `handle()` — acciones CRUD para inventario via AJAX |
| `RolController` | `handle()` — acciones CRUD para roles/permisos via AJAX |
| `ProveedorController` | `handle()` — acciones CRUD para proveedores/ordenes via AJAX |
| `ProveedorGestionController` | `handle()` — acciones CRUD para proveedores (gestión) via AJAX |
| `ClienteController` | `handle()` — acciones CRUD para clientes via AJAX |
| `VentaController` | `handle()` — acciones CRUD para ventas (POS) via AJAX |
| `CiberController` | `handle()` — control de estaciones cyber via AJAX |
| `AsesoriaController` | `handle()` — acciones CRUD para asesorías via AJAX |
| `ActivoController` | `handle()` — acciones CRUD para activos fijos via AJAX |
| `DashboardController` | `handle()` — métricas del dashboard via AJAX |
| `ReporteController` | `handle()` — generación de reportes via AJAX |
| `UsuarioController` | `handle()` — CRUD usuarios, estados y password via AJAX (nuevo) |

### 6. Diferencia con el diseño anterior

El diseño anterior (descrito en versiones previas de la documentación) usaba:
- Router procedural con `require_once` directo
- `login_validate.php` como vista independiente
- Solo rutas AJAX para inventario
- Sin autoloader de Composer para el enrutamiento

El diseño actual usa:
- Clase Router OOP con namespace y métodos privados
- Mapa `CONTROLLERS` que centraliza la resolución de controladores + `dispatchAction()`
- AuthController para login y Router para logout
- 12 controladores AJAX resueltos dinámicamente desde el mapa `CONTROLLERS` (13 archivos en total con `AuthController`)
- Autoloader PSR-4 de Composer
- URLs limpias parciales via .htaccess