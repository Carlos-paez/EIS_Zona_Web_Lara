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
- `$router = new Router()` — constructor: `session_start()` + `$this->resolvePage()`.
- `$router->handle()` — método principal que procesa la solicitud.

El Router **no usa Request encapsulado ni registro de rutas con métodos HTTP**. En su lugar, usa un enfoque más simple: analiza `$_GET['pagina']` y deriva según casos.

### 3. El Motor del Router (router.php)

La clase `Router` tiene los siguientes métodos clave:

| Método | Función |
|--------|---------|
| `__construct()` | Inicia sesión y resuelve `$pagina` mediante `resolvePage()` |
| `handle()` | Determina el tipo de petición y redirige al controlador/vista correspondiente |
| `CONTROLLERS` | Mapa `pagina => clase` que centraliza los 12 controladores AJAX |
| `resolvePage()` | Lee `$_GET["pagina"]`, valida con regex (solo alfanumérico+guiones), retorna string |
| `dispatchAction()` | Si la página está en `CONTROLLERS` y hay `action`, instancia el controlador y ejecuta `handle()` |
| `isAuthAction()` | True si es login_validate o logout |
| `requireAuth()` | Verifica `$_SESSION['logged_in']`, retorna JSON error si no |
| `runAuthAction()` | AuthController::login() o logout() |
| `render()` | Verifica autenticación, carga vista pública o llama a `renderWithLayout()` |
| `renderWithLayout()` | Define `$titulos` (12 módulos) y `$extraHeaders`, incluye `layout.php` |

### 4. Procesamiento en handle()

1. ¿Es auth (login_validate/logout)? → `AuthController::login()` o `logout()`
2. ¿La página está en el mapa `CONTROLLERS` y hay `action`? → `dispatchAction()`: instancia el controlador según `CONTROLLERS[$pagina]` y ejecuta `handle()` → JSON response
3. ¿Es vista normal? → `render()`:
   - Si es pública (`login`): carga directa
   - Si es privada: verifica `$_SESSION['logged_in']` → redirige a login si no autenticado
   - Renderiza dentro de `layout.php` si está autenticado

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

### 6. Diferencia con el diseño anterior

El diseño anterior (descrito en versiones previas de la documentación) usaba:
- Router procedural con `require_once` directo
- `login_validate.php` como vista independiente
- Solo rutas AJAX para inventario
- Sin autoloader de Composer para el enrutamiento

El diseño actual usa:
- Clase Router OOP con namespace y métodos privados
- Mapa `CONTROLLERS` que centraliza la resolución de controladores + `dispatchAction()`
- AuthController para login/logout
- 12 controladores AJAX resueltos dinámicamente desde el mapa `CONTROLLERS`
- Autoloader PSR-4 de Composer
- URLs limpias parciales via .htaccess