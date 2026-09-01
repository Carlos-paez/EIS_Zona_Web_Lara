# Documentación Completa: EIS System — Aplicación PHP MVC

  

> **⚠️ Nota (Agosto 2026):** Este documento describe una **versión anterior/intermedia del enrutador** basada en `Request.php`, `Controller.php` y registro de rutas (`$router->get('/dashboard', 'Controller@index')`). La arquitectura **actual** usa un Front Controller con la clase `Router` (namespace `App\Core`) y un mapa `CONTROLLERS` (`pagina => clase`) + `dispatchAction()` + `render()`, sin `Request` ni `Controller` en `App\Core`, ni `VentasController`/`ReportesController`/`MenuController`. Los controladores actuales son 12 (`Auth`, `Cliente`, `Inventario`, `Venta`, `Rol`, `Proveedor`, `ProveedorGestion`, `Asesoria`, `Ciber`, `Dashboard`, `Reporte`, `Activo`) y hay 13 modelos POO. La BD final tiene 21 tablas. Este documento conserva valor de estudio de conceptos MVC, pero los nombres de archivos/rutas referidos ya no corresponden al código fuente actual.

  

> **Propósito:** Este documento explica el funcionamiento de cada archivo y cada línea de la aplicación EIS System, un sistema de gestión empresarial integral (inventario, ventas POS, cybercafé, proveedores, activos, reportes). Está diseñado para ser ingresado a NotebookLM y generar material de estudio.

  

---

  

## Índice

  

1. [Estructura del proyecto](#1-estructura-del-proyecto)

2. [Configuración del servidor web (.htaccess)](#2-htaccess)

3. [Punto de entrada (index.php)](#3-indexphp)

4. [Núcleo del sistema (Core)](#4-core)

   - [Router.php](#41-routerphp)

   - [Request.php](#42-requestphp)

   - [Controller.php](#43-controllerphp)

5. [Configuración (Config/database.php)](#5-databasephp)

6. [Controladores (Controllers)](#6-controllers)

   - [AuthController.php](#61-authcontrollerphp)

   - [DashboardController.php](#62-dashboardcontrollerphp)

   - [InventarioController.php](#63-inventariocontrollerphp)

   - [VentasController.php](#64-ventascontrollerphp)

   - [ProveedoresController.php](#65-proveedorescontrollerphp)

   - [ReportesController.php](#66-reportescontrollerphp)

   - [CiberControlController.php](#67-cibercontrolcontrollerphp)

   - [ActivosController.php](#68-activoscontrollerphp)

   - [MenuController.php](#69-menucontrollerphp)

7. [Vistas (Views)](#7-views)

   - [Layout principal (template/layout.php)](#71-layoutphp)

   - [login.php](#72-loginphp)

   - [dashboard.php](#73-dashboardphp)

   - [inventario.php](#74-inventariophp)

   - [ventas.php](#75-ventasphp)

   - [proveedores.php](#76-proveedoresphp)

   - [reportes.php](#77-reportesphp)

   - [ciberControl.php](#78-cibercontrolphp)

   - [activos.php](#79-activosphp)

   - [menu.php](#710-menuphp)

   - [404.php](#711-404php)

   - [login_validate.php](#712-login_validatephp-archivo-legacy)

8. [Modelo (Models)](#8-models)

   - [crud_users.php](#81-crud_usersphp)

9. [Assets frontend](#9-assets-frontend)

   - [styles.css](#91-stylescss)

   - [login.css](#92-logincss)

   - [app.js](#93-appjs)

10. [composer.json](#10-composerjson)

11. [Flujo de navegación completo](#11-flujo-de-navegacion-completo)

  

---

  

## 1. Estructura del proyecto

  

```

eis_zona_web_lara/

├── .htaccess                  # Redirige al directorio src/

├── composer.json              # Configuración de dependencias PHP (PSR-4)

├── README.md                  # Documentación general

├── docs/                      # Documentos de diseño de BD

├── vendor/                    # Dependencias instaladas (Composer)

└── src/

    ├── .htaccess              # Reescribe URLs al front controller

    ├── index.php              # PUNTO DE ENTRADA (Front Controller + autoloader)

    ├── manifest.json          # Manifiesto PWA

    ├── sw.js                  # Service Worker (caché offline)

    ├── offline.php            # Página de fallo offline

    ├── Config/

    │   └── database.php       # Conexión a MySQL con PDO (legacy)

    ├── app/

    │   ├── core/

    │   │   ├── Database.php   # Conexión PDO Singleton (moderna)

    │   │   ├── Model.php      # Clase base abstracta para modelos

    │   │   └── router.php     # Enrutador OOP (clase Router, namespace App\Core)

    │   ├── Controllers/

    │   │   ├── AuthController.php        # Login/Logout con sesiones

    │   │   ├── inventarioController.php  # CRUD inventario AJAX

    │   │   ├── RolController.php         # CRUD roles/permisos AJAX

    │   │   └── ProveedorController.php   # CRUD proveedores AJAX

    │   ├── Models/

    │   │   ├── Inventario.php  # Modelo POO inventario (namespace)

    │   │   ├── Usuario.php     # Modelo POO usuarios

    │   │   ├── Proveedor.php   # Modelo POO proveedores

    │   │   ├── Rol.php         # Modelo POO roles/permisos

    │   │   ├── Asesoria.php    # Modelo POO asesorías

    │   │   ├── crud_users.php  # CRUD usuarios legacy (funciones sueltas)

    │   │   └── crud_asesorias.php # CRUD asesorías legacy

    │   ├── template/

    │   │   └── layout.php      # Layout HTML principal (sidebar + header, 12 módulos)

    │   └── Views/

    │       ├── login.php       # Formulario de inicio de sesión

    │       ├── login_validate.php # Legacy: validación login

    │       ├── dashboard.php   # Panel de control con métricas

    │       ├── inventario.php  # Tabla de productos (conectado a BD)

    │       ├── ventas.php      # POS con catálogo y carrito modal

    │       ├── proveedores.php # Solicitudes a proveedores (conectado a BD)

    │       ├── reportes.php    # Generador de reportes

    │       ├── ciberControl.php# Estaciones de cybercafé

    │       ├── activos.php     # Activos fijos (equipos, licencias)

    │       ├── asesorias.php   # Asesoría legal

    │       ├── menu.php        # Página de menú independiente

    │       ├── usuarios.php    # Gestión de usuarios (conectado a BD)

    │       └── roles.php       # Gestión de roles y permisos (conectado a BD)

    ├── Database/

    │   ├── estructura.sql      # Esquema BD v3.0 (21 tablas)

    │   ├── seed_data.sql       # Datos de prueba

    │   └── seed_data_masivo.sql # Datos masivos de prueba

    └── Public/

        ├── css/

        │   ├── styles.css      # Estilos principales (temas claro/oscuro)

        │   ├── login.css       # Estilos específicos del login

        │   ├── materialize.min.css # Materialize CSS (local)

        │   └── material-icons.css  # Material Icons (local)

        ├── js/

        │   ├── jquery-3.7.1.min.js  # jQuery (local)

        │   ├── materialize.min.js   # Materialize JS (local)

        │   ├── app.core.js      # Utilidades compartidas (EIS, debounce)

        │   ├── app.init.js      # Inicialización Materialize, reloj, tema

        │   ├── app.tables.js    # Búsqueda y filtro de tablas

        │   ├── app.ui.js        # Notificaciones, botones, tooltips

        │   ├── app.pos.js       # Sistema de carrito POS

        │   ├── app.cyber.js     # Gestión de estaciones Cyber

        │   ├── app.legal.js     # Validación de asesoría legal

        │   ├── app.inventario.js # CRUD inventario vía AJAX

        │   ├── app.roles.js     # CRUD roles/permisos vía AJAX

        │   └── app.proveedores.js # CRUD proveedores vía AJAX

        └── fonts/

            └── MaterialIcons-Regular.ttf  # Material Icons (local)

```

  

---

  

## 2. .htaccess

  

### Raíz del proyecto (`/.htaccess`)

  

```

# Línea 1-6: Comentarios que explican que este archivo redirige todo al directorio src/

# cuando el servidor web apunta a la raíz del proyecto en vez de a src/.

  

Línea 7:  RewriteEngine On

           - Activa el motor de reescritura de URLs de Apache (mod_rewrite).

  

Línea 9:  RewriteCond %{REQUEST_FILENAME} !-f

           - Condición: solo aplica si el archivo NO existe físicamente.

           - Permite servir archivos reales como CSS, JS, imágenes.

  

Línea 10: RewriteCond %{REQUEST_FILENAME} !-d

           - Condición: solo aplica si el directorio NO existe físicamente.

  

Línea 11: RewriteRule ^(.*)$ src/$1 [L]

           - Regla: cualquier ruta (^ captura toda la URL) se redirige internamente a src/.

           - [L] = Last, última regla que se aplica.

  

Línea 14: RewriteRule ^$ src/ [L,R=301]

           - Si la URL es exactamente la raíz, redirige permanentemente (301) a src/.

```

  

### Dentro de `src/` (`src/.htaccess`)

  

```

Línea 7-9: Options All -Indexes

           - Bloquea el listado de directorios (seguridad).

           - Si alguien visita /sin-index, no podrá ver los archivos.

  

Línea 12: RewriteEngine On

           - Activa el motor de reescritura.

  

Línea 16: RewriteRule ^$ index.php [L,QSA]

           - Si la URL es la raíz (src/), redirige a index.php.

           - L = última regla, QSA = Query String Append (preserva ?param=valor).

  

Línea 20: RewriteCond %{REQUEST_FILENAME} !-f

           - Solo aplica si NO es un archivo real (CSS, JS, imágenes pasan directo).

  

Línea 22: RewriteCond %{REQUEST_FILENAME} !-d

           - Solo aplica si NO es un directorio real.

  

Línea 25: RewriteRule ^(.*)$ index.php [QSA,L]

           - Cualquier otra ruta (dashboard, login, etc.) se envía a index.php.

           - Esto es el "Front Controller Pattern": todo pasa por index.php.

```

  

**Concepto clave:** Todas las peticiones HTTP llegan a `index.php` sin importar la URL. Esto permite tener URLs limpias estilo `/dashboard` en vez de `?pagina=dashboard`.

  

---

  

## 3. index.php (Front Controller)

  

```

Línea 1:  <?php

           - Apertura del bloque PHP.

  

Línea 2:  session_start();

           - Inicia o reanuda la sesión del usuario.

           - $_SESSION ahora está disponible globalmente.

           - Es necesario para recordar si el usuario inició sesión.

  

Línea 4:  require_once __DIR__ . '/../vendor/autoload.php';

           - Carga el autoloader de Composer.

           - Busca en vendor/composer/autoload_*.php

           - Permite usar namespaces sin require explícitos (PSR-4).

  

Línea 5-10: Comentarios sobre detección de BASE_URL.

  

Línea 12: $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

           - Obtiene la ruta del script actual desde el servidor.

           - ?? es null coalescing: si SCRIPT_NAME no existe, usa '/index.php'.

  

Línea 13: $basePath = rtrim(dirname($scriptName), '/\\');

           - dirname() obtiene el directorio padre del script.

           - rtrim() elimina barras finales.

           - Ej: si SCRIPT_NAME es "/eis_zona/src/index.php", $basePath = "/eis_zona/src".

  

Línea 15: define('BASE_URL', ($basePath === '/' || $basePath === '\\' || $basePath === '.') ? '' : $basePath);

           - Define una constante BASE_URL con la ruta base del proyecto.

           - Si el proyecto está en la raíz del servidor, BASE_URL es ''.

           - Si está en un subdirectorio, BASE_URL contiene el prefijo.

           - Esto permite que la app funcione tanto en la raíz como en subcarpetas.

  

Línea 17: use App\Core\Router;

           - Importa la clase Router desde el namespace App\Core.

  

Línea 18: use App\Core\Request;

           - Importa la clase Request desde el namespace App\Core.

  

Línea 20: $router = new Router();

           - Crea una instancia del Router (el corazón del enrutamiento).

  

Línea 22-40: Definición de rutas (ver sección de Router para detalles de cada método).

  

Línea 23: $router->get('/', 'AuthController@showLogin');

           - Ruta raíz: muestra el login (o redirige a dashboard si ya autenticado).

  

Línea 26: $router->get('/login', 'AuthController@showLogin');

           - GET /login: muestra el formulario de inicio de sesión.

  

Línea 27: $router->post('/login', 'AuthController@login');

           - POST /login: procesa el envío del formulario de login.

  

Línea 30: $router->get('/logout', 'AuthController@logout')->middleware('auth');

           - GET /logout: cierra sesión, protegido por middleware auth.

  

Línea 33-40: Rutas protegidas con middleware auth:

  /dashboard   -> DashboardController@index

  /inventario  -> InventarioController@index

  /ventas      -> VentasController@index

  /ciberControl-> CiberControlController@index

  /proveedores -> ProveedoresController@index

  /reportes    -> ReportesController@index

  /activos     -> ActivosController@index

  /menu        -> MenuController@index

  

Línea 42: $router->dispatch(Request::capture());

           - Request::capture() crea un objeto Request con los datos HTTP actuales.

           - $router->dispatch() procesa la ruta y ejecuta el controlador correspondiente.

           - Es la línea que inicia todo el procesamiento de la petición.

```

  

---

  

## 4. Core

  

### 4.1 Router.php

  

Define la clase `App\Core\Router` — el corazón del sistema de enrutamiento MVC.

  

```

Línea 3:  namespace App\Core;

           - Esta clase pertenece al namespace App\Core.

           - Composer la autocarga desde src/Core/Router.php.

  

Línea 6:  class Router

           - Define la clase principal del enrutador.

  

Línea 10: private array $routes = [];

           - Arreglo privado que almacena todas las rutas registradas.

           - Cada elemento contiene: method, pattern, handler, middleware.

  

--- Método get() ---

Línea 16: public function get(string $path, string $handler): self

           - Registra una ruta que responde a GET.

           - $path: la URL amigable (ej: "/dashboard").

           - $handler: string "Controlador@metodo" (ej: "DashboardController@index").

           - Retorna $this para permitir encadenamiento.

  

Línea 19: return $this->addRoute('GET', $path, $handler);

           - Delega en addRoute con el método HTTP 'GET'.

  

--- Método post() ---

Línea 24: public function post(string $path, string $handler): self

           - Igual que get(), pero para peticiones POST.

  

--- Método middleware() ---

Línea 33: public function middleware(string $name): self

           - Asigna un middleware a la ÚLTIMA ruta registrada.

           - $name: nombre del middleware (actualmente solo 'auth').

  

Línea 36: $index = array_key_last($this->routes);

           - Obtiene el índice del último elemento del arreglo $routes.

  

Línea 38: $this->routes[$index]['middleware'][] = $name;

           - Agrega el nombre del middleware a la lista de la ruta.

  

Línea 40: return $this;

           - Retorna $this para más encadenamiento.

  

--- Método privado addRoute() ---

Línea 47: private function addRoute(string $method, string $path, string $handler): self

           - Método privado que agrega una ruta al arreglo interno.

  

Línea 52: $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);

           - Convierte parámetros dinámicos {id} a grupos de captura regex.

           - Ej: "/usuario/{id}" -> "/usuario/(?P<id>[^/]+)"

           - (?P<id>...) es un grupo nombrado: permite extraer el valor por nombre.

  

Línea 55: $pattern = '#^' . $pattern . '$#';

           - Envuelve el patrón con delimitadores # y anclas ^ $.

           - #^/dashboard$#  o  #^/usuario/(?P<id>[^/]+)$#

  

Línea 58-63: $this->routes[] = [

               'method'     => $method,

               'pattern'    => $pattern,

               'handler'    => $handler,

               'middleware' => [],

             ];

           - Guarda la ruta en el arreglo interno.

  

--- Método principal dispatch() ---

Línea 71: public function dispatch(Request $request): void

           - Método principal: recibe una Request, la compara con las rutas y ejecuta la que coincida.

           - Si ninguna coincide, muestra página 404.

  

Línea 74: $uri = $request->getUri();

           - Obtiene la URI limpia de la petición (ej: "/dashboard").

  

Línea 76: $method = $request->getMethod();

           - Obtiene el método HTTP (GET, POST).

  

Línea 80-84: if ($uri === '/') {

                 $uri = $this->isAuthenticated() ? '/dashboard' : '/login';

             }

           - Si la URI es "/", redirige según autenticación.

           - Si el usuario ya inició sesión, va al dashboard.

           - Si no, va al login.

  

Línea 89-92: $legacyPage = $request->get('pagina');

              if ($legacyPage && $uri === '/') {

                  $uri = '/' . $legacyPage;

              }

           - Soporte legacy: si existe $_GET['pagina'] y la URI es "/", convierte a ruta limpia.

           - Ej: "?pagina=dashboard" se comporta como "/dashboard".

  

Línea 95-119: foreach ($this->routes as $route) {

                   if ($route['method'] !== $method) continue;

                   if (preg_match($route['pattern'], $uri, $matches)) {

                       $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                       if (!$this->runMiddleware($route['middleware'])) return;

                       $this->callHandler($route['handler'], $params);

                       return;

                   }

               }

           - Itera sobre todas las rutas registradas.

           - Si el método HTTP no coincide, salta a la siguiente.

           - Prueba la expresión regular contra la URI.

           - Filtra $matches para quedarse solo con claves string (parámetros nombrados).

           - Ejecuta middleware antes del controlador.

           - Si el middleware falla, retorna sin ejecutar el controlador.

           - Si pasa, ejecuta el controlador.

  

Línea 122: $this->handleNotFound();

           - Si ningún ruta coincidió, muestra página 404.

  

--- Método privado isAuthenticated() ---

Línea 126-130: return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

           - Verifica si el usuario tiene sesión activa revisando $_SESSION.

  

--- Método privado runMiddleware() ---

Línea 135-152: Itera sobre cada middleware y ejecuta su lógica.

           - Middleware 'auth': si no está autenticado, redirige al login y retorna false.

           - Retorna true si todos los middleware pasan.

  

--- Método privado callHandler() ---

Línea 157-178: Instancia el controlador y llama al método.

  

Línea 160: [$controllerName, $methodName] = explode('@', $handler);

           - Divide "DashboardController@index" en ["DashboardController", "index"].

           - Sintaxis de desestructuración de arreglos (PHP 7.1+).

  

Línea 163: $controllerClass = 'App\\Controllers\\' . $controllerName;

           - Construye el nombre completo con namespace.

  

Línea 166-171: Verifica que la clase exista con class_exists().

           - Si no existe, lanza RuntimeException.

  

Línea 174: $controller = new $controllerClass();

           - Crea instancia del controlador (sin constructor, hereda de Controller).

  

Línea 177: $controller->$methodName(...$params);

           - Llama al método con los parámetros usando splat operator (...).

           - Convierte ['id' => '5'] en $controller->metodo('5').

  

--- Método privado handleNotFound() ---

Línea 181-194:

           - Establece código HTTP 404.

           - Busca la vista 404.php en app/Views/.

           - Si existe, la incluye. Si no, muestra mensaje de fallback.

```

  

### 4.2 Request.php

  

Encapsula los datos de la petición HTTP (GET, POST, SERVER) para no depender directamente de superglobales.

  

```

Línea 4:  namespace App\Core;

  

Línea 7:  class Request

  

Línea 10: private array $query;   // Almacena $_GET

Línea 12: private array $body;    // Almacena $_POST

Línea 14: private array $server;  // Almacena $_SERVER

  

--- Constructor ---

Línea 18-23: public function __construct(array $query, array $body, array $server)

           - Recibe copias de las superglobales y las guarda como propiedades privadas.

           - El sistema ya no depende directamente de $_GET, $_POST, $_SERVER.

  

--- Método estático capture() ---

Línea 27-31: public static function capture(): self

           - Método factory que crea un Request con las superglobales actuales.

           - Uso típico: Request::capture().

  

--- Método getUri() ---

Línea 37-55: public function getUri(): string

           - Obtiene la ruta URI limpia.

           - Descarta el query string con parse_url($uri, PHP_URL_PATH).

           - Elimina el prefijo del directorio del script para soportar subdirectorios.

           - Elimina la barra final con rtrim($uri, '/').

           - Si quedó vacío, retorna '/'.

  

--- Método getMethod() ---

Línea 58-62: Retorna el método HTTP en mayúsculas (GET, POST, etc.).

  

--- Método get() ---

Línea 66-69: Obtiene un valor del query string ($_GET) de forma segura.

           - Si la clave no existe, devuelve $default (null coalescing operator ??).

  

--- Método post() ---

Línea 74-77: Obtiene un valor del cuerpo POST ($_POST) de forma segura.

  

--- Método isMethod() ---

Línea 82-86: Compara si el método de la petición coincide con $method.

```

  

### 4.3 Controller.php

  

Clase abstracta base para todos los controladores. NO se puede instanciar directamente.

  

```

Línea 3:  namespace App\Core;

  

Línea 7:  abstract class Controller

  

--- Método render() ---

Línea 12-30: protected function render(string $view, array $data = []): void

           - Renderiza una vista SIN layout (para páginas públicas como login).

           - $view: nombre del archivo en app/Views/ (sin extensión .php).

           - $data: arreglo asociativo de variables para pasar a la vista.

           - extract($data) convierte ['titulo' => 'Hola'] en $titulo = 'Hola'.

           - require $viewPath incluye el archivo de vista.

  

--- Método renderWithLayout() ---

Línea 36-59: protected function renderWithLayout(string $view, array $data = []): void

           - Renderiza una vista DENTRO del layout principal (sidebar + header).

           - Para páginas internas que requieren autenticación.

           - Establece valores por defecto: pageTitle, headerExtra, pagina.

           - Guarda la ruta de la vista en $contentView.

           - Incluye layout.php, que internamente hará require de $contentView.

  

--- Método redirect() ---

Línea 64-74: protected function redirect(string $url): void

           - Redirige el navegador a otra URL.

           - Automáticamente antepone BASE_URL para soportar subdirectorios.

           - Envía header('Location: ' . $url) y termina con exit.

  

--- Método json() ---

Línea 79-90: protected function json(array $data, int $status = 200): void

           - Envía respuesta JSON (para APIs o AJAX).

           - Establece código HTTP y Content-Type.

           - json_encode con JSON_UNESCAPED_UNICODE para acentos.

           - Termina con exit.

```

  

---

  

## 5. database.php

  

```

Línea 1-5: Comentarios de configuración de base de datos.

  

Línea 8-12: $host = "localhost"; $db = "zona_web_lara"; $user = "root"; $pass = ""; $charset = 'utf8mb4';

           - Datos de conexión a MySQL.

           - utf8mb4 soporta emojis y caracteres especiales.

  

Línea 16: $dns = "mysql:host=$host;dbname=$db;charset=$charset";

           - Cadena DSN (Data Source Name) para PDO.

           - Contiene toda la info para localizar la base de datos.

  

Línea 19-26: $options = [

                 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Errores como excepciones

                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Resultados asociativos

                 PDO::ATTR_EMULATE_PREPARES => false,               // Prepares nativos (más seguro)

             ];

  

Línea 29-38: try { $pdo = new PDO($dns, $user, $pass, $options); }

              catch (\PDOException $e) { throw new \PDOException(...); }

           - Intenta la conexión. Si falla, relanza la excepción.

           - NOTA: El echo "Conexion exitosa" en línea 33 rompería respuestas JSON.

```

  

---

  

## 6. Controladores

  

### 6.1 AuthController.php

  

Maneja login, logout y verificación de sesión.

  

```

Línea 3:  namespace App\Controllers;

Línea 6:  use App\Core\Controller;

Línea 9:  class AuthController extends Controller

  

--- Método showLogin() ---

Línea 12-22: Muestra el formulario de login (ruta: GET /login).

           - Si el usuario ya está autenticado, redirige al dashboard.

           - Si no, renderiza login.php (sin layout, es página completa).

  

--- Método login() ---

Línea 26-46: Procesa el inicio de sesión (ruta: POST /login).

           - Obtiene username y password de $_POST.

           - Valida contra credenciales hardcodeadas (admin/1234).

           - MEJORA FUTURA notada en comentario: usar base de datos.

           - Si son correctas: marca $_SESSION['logged_in'] = true, guarda username.

           - Redirige a /dashboard.

           - Si son incorrectas: redirige a /login?error=1.

  

--- Método logout() ---

Línea 49-57: Cierra la sesión (ruta: GET /logout).

           - $_SESSION = [] limpia todas las variables de sesión.

           - session_destroy() elimina la sesión del servidor.

           - Redirige al login.

  

--- Método privado isAuthenticated() ---

Línea 61-65: Verifica si el usuario tiene sesión activa.

```

  

### 6.2 DashboardController.php

  

```

Línea 3:  namespace App\Controllers;

Línea 6:  use App\Core\Controller;

Línea 10: class DashboardController extends Controller

  

--- Método index() ---

Línea 13-21: Renderiza 'dashboard' dentro del layout con pageTitle = 'Panel de Control'.

```

  

### 6.3 InventarioController.php

  

```

--- Método index() ---

           Renderiza 'inventario' dentro del layout con pageTitle = 'Gestion de Inventario'.

```

  

### 6.4 VentasController.php

  

```

--- Método index() ---

           Renderiza 'ventas' dentro del layout con pageTitle = 'Punto de Venta (POS)'.

```

  

### 6.5 ProveedoresController.php

  

```

--- Método index() ---

           Renderiza 'proveedores' dentro del layout con pageTitle = 'Solicitudes a Proveedores'.

```

  

### 6.6 ReportesController.php

  

```

--- Método index() ---

           Renderiza 'reportes' dentro del layout con pageTitle = 'Reportes y Estadisticas'.

```

  

### 6.7 CiberControlController.php

  

```

--- Método index() ---

           Renderiza 'ciberControl' dentro del layout.

           Pasa pageTitle = 'Control de Cybercafe'.

           Pasa headerExtra con badges HTML: "7 Disponibles" y "3 Ocupadas".

           Estos badges se muestran en el header del layout (junto al reloj y campana).

```

  

### 6.8 ActivosController.php

  

```

--- Método index() ---

           Renderiza 'activos' dentro del layout con pageTitle = 'Gestion de Activos'.

```

  

### 6.9 MenuController.php

  

```

--- Método index() ---

           Renderiza 'menu' SIN layout (usa render(), no renderWithLayout()).

           menu.php es una página independiente con su propio HTML, CSS y JS.

```

  

---

  

## 7. Vistas

  

### 7.1 layout.php

  

Layout principal que envuelve todas las páginas internas (autenticadas). Usa Materialize CSS.

  

```

Línea 1:  <!DOCTYPE html> - Declaración del tipo de documento HTML5.

  

Línea 4-7:  Meta tags:

            charset=UTF-8 (soporta acentos y caracteres especiales)

            viewport (responsive design)

            <title> dinámico: "$pageTitle - EIS System"

  

Línea 8:  <base href="..."> - URL base para todas las URLs relativas de la página.

           Usa la constante BASE_URL para soportar subdirectorios.

  

Línea 9:  Material Icons (Google Fonts) - iconos vectoriales.

Línea 10: Materialize CSS 1.0.0 (CDN) - framework CSS similar a Bootstrap.

Línea 11: styles.css - estilos propios de la aplicación.

Línea 12: jQuery 3.7.1 (CDN) - biblioteca JavaScript para manipulación del DOM.

  

Línea 15-33: Sidebar (menú lateral izquierdo):

  - <ul id="slide-out" class="sidenav sidenav-fixed">: menú lateral fijo de Materialize.

  - user-view: muestra el nombre del sistema y descripción.

  - Enlaces del menú: cada <li> tiene un <a> con href a la ruta del módulo.

  - Clase 'active' se agrega dinámicamente según $pagina (compara con cada ruta).

  - Módulos: Dashboard, Inventario, Ventas, Solicitudes, Cyber, Reportes, Activos.

  - Divider: línea separadora.

  - Cerrar Sesión (href="logout").

  - Theme Toggle: botón para modo oscuro/claro.

  

Línea 35-56: Header (barra superior):

  - <nav class="nav-extended indigo darken-3">: barra de navegación color índigo.

  - sidenav-trigger: botón para mostrar/ocultar sidebar en pantallas pequeñas.

  - brand-logo: muestra $pageTitle (título dinámico de la página actual).

  - Reloj: <span id="clock"> (actualizado por JavaScript).

  - headerExtra: HTML extra insertado (ej: badges de cyber en ciberControl).

  - Campana de notificaciones con badge rojo con contador "3".

  - Badge de usuario: "Admin" con icono de persona.

  

Línea 58-62: <main> - Contenido principal:

  - <div class="container"> con padding y max-width.

  - <?php require $contentView; ?>: AQUÍ se incluye la vista específica.

  - $contentView es definido por Controller::renderWithLayout() antes de incluir layout.php.

  

Línea 64-66: Botón "Volver arriba" (#backToTop), oculto inicialmente.

  

Línea 68-69: Scripts:

  - materialize.min.js: componentes JS de Materialize (modales, tooltips, etc.).

  - app.js: lógica frontend propia de la aplicación.

```

  

### 7.2 login.php

  

Página completa (sin layout) del formulario de inicio de sesión.

  

```

Línea 1-12: DOCTYPE, meta tags, title, base, CDNs (Material Icons, Materialize CSS, login.css).

  

Línea 15-17: Botón flotante de cambio de tema (modo oscuro/claro).

           position:fixed, top:1rem, right:1rem.

  

Línea 19-82: Tarjeta de login:

  - .card.login-card.z-depth-4: tarjeta con sombra pronunciada.

  - Logo: ⚡ (rayo) en un contenedor con gradiente.

  - Título: "EIS System", subtítulo: "Ingresa tus credenciales para continuar".

  

Línea 27-32: Mensaje de error:

  - Si $_GET['error'] existe, muestra un panel rojo con mensaje "Credenciales incorrectas".

  

Línea 34-54: Formulario de login:

  - action="login" method="post": envía a POST /login.

  - Campo de usuario (name="username") con icono de persona.

  - Campo de contraseña (name="password") con icono de candado.

  - Botón "¿Olvidaste tu contraseña?" (sin funcionalidad, onclick="return false").

  - Botón submit: "Iniciar Sesión" con estilo ancho completo.

  

Línea 56-76: Sección "O continúa con" (redes sociales):

  - Botones de Google y GitHub (solo SVG, sin funcionalidad real).

  

Línea 78-80: Texto "¿No tienes una cuenta? Regístrate" (sin funcionalidad).

  

Línea 84-104: Scripts:

  - jQuery y Materialize JS.

  - Script inline para el toggle de tema: usa localStorage para persistencia.

  - Cambia el icono del botón según el tema actual.

```

  

### 7.3 dashboard.php

  

Vista del panel de control, se inyecta dentro del layout.

  

```

Línea 1-4: Banner de bienvenida con gradiente índigo:

           "¡Bienvenido de nuevo! Gestiona tu negocio de manera eficiente con EIS System"

  

Línea 6-39: Cuatro tarjetas métricas (metric-card) en un row:

  1. Ventas Hoy: $1,245.50 (icono payments, 23 transacciones)

  2. Stock Crítico: 4 (icono warning, rojo, productos bajo mínimo)

  3. Sesiones Cyber: 7 (icono desktop_windows, naranja, prom 45 min)

  4. Solicitudes Pend.: 3 (icono assignment, azul, cuentas por pagar)

  

Línea 41-74: Tabla de Horas Pico:

  - Muestra transacciones por hora (10:00-11:00: 42 ↑12%, 14:00-15:00: 38 ↑8%, etc.)

  

Línea 75-108: Tabla de Productos Sin Stock:

  - Resma A4 (0, Sin stock), Tóner Negro (0, Sin stock), Cable USB-C (0, Sin stock)

  

Línea 110-135: Sección de Actividad Reciente:

  - Venta #V-00142 procesada (hace 5 min, $245.00)

  - Stock actualizado: Mouse Inalámbrico (hace 15 min, +50 unidades)

  - Nueva sesión Cyber iniciada (hace 30 min, Estación #5)

```

  

### 7.4 inventario.php

  

Vista de gestión de inventario.

  

```

Línea 1-27: Barra de herramientas:

  - Buscador de productos (#searchProducto)

  - Filtro por estado (select): Stock OK, Crítico, Sin stock

  - Botón "Nuevo Producto" (clase .btn-nuevo, data-tipo="producto")

  

Línea 29-110: Tabla de productos:

  - Columnas: ID, Producto, Precio, Stock, Mínimo, Estado, Acciones.

  - 3 productos de ejemplo:

    1. Mouse Inalámbrico (#1042, $12.50, stock:5, mín:10, "Crítico" rojo)

    2. Monitor 24" IPS (#1043, $189.00, stock:24, mín:5, "OK" verde)

    3. Teclado Mecánico RGB (#1044, $45.00, stock:8, mín:10, "Bajo" naranja)

  - Acciones por fila: botón de movimientos (inventory) y editar (edit).

  - Paginación: 3 páginas (solo UI, sin lógica real).

```

  

### 7.5 ventas.php

  

Vista del Punto de Venta (POS) con carrito modal.

  

```

Línea 2-24: Header del POS:

  - Izquierda: título "Punto de Venta" con descripción.

  - Derecha: mini-total del carrito ($0.00 inicial) y botón "Carrito" con badge contador.

  

Línea 27-77: Catálogo de productos:

  - Buscador (#posSearch) con debounce para filtrar productos.

  - Grid de productos: cada uno es un .card-panel.pos-product con:

    - data-name (nombre) y data-price (precio)

    - Icono representativo (keyboard, mouse, headphones, etc.)

    - Nombre, precio, y botón "+" flotante (aparece al hover).

  - 5 productos de ejemplo: Teclado Mecánico ($45), Mouse USB ($12.50),

    Auriculares ($35), Monitor 24" ($189), Cable USB-C ($8).

  

Línea 80-110: Modal del carrito (#posCartModal):

  - Header: "Carrito de Compras" con contador de productos.

  - Cuerpo: lista dinámica de productos agregados.

    - Si está vacío: mensaje "El carrito está vacío".

  - Footer: total, botón "Vaciar" (rojo) y "Procesar Venta" (verde).

```

  

### 7.6 proveedores.php

  

Vista de solicitudes a proveedores.

  

```

Línea 1-27: Barra de herramientas:

  - Buscador de solicitudes (#searchProveedor)

  - Filtro por estado: Pendiente, Recibida, Cancelada

  - Botón "Nueva Solicitud"

  

Línea 29-99: Tabla de solicitudes:

  - Columnas: ID, Proveedor, Fecha, Estado, Acciones.

  - 3 solicitudes de ejemplo:

    1. #SOL-089 - TechSupplies S.A. - 2024-04-10 - Pendiente (naranja)

    2. #SOL-088 - GlobalParts Inc. - 2024-04-08 - Recibida (verde)

    3. #SOL-087 - OfficeMax Corp. - 2024-04-05 - Cancelada (gris)

  - Acción: botón "Ver detalles" (visibility).

  - Paginación similar a inventario.

```

  

### 7.7 reportes.php

  

Vista de reportes y estadísticas.

  

```

Línea 1-34: Cuatro tarjetas métricas:

  1. Ventas del Mes: $34,580 (↑12% vs mes anterior)

  2. Productos Activos: 245

  3. Horas Cyber: 1,240 este mes

  4. Solicitudes: 28 procesadas

  

Línea 36-93: Generador de Reportes (formulario):

  - Select: tipo de reporte (Ventas, Inventario, Movimientos, Proveedores, Cyber)

  - Fechas: inicio y fin (input type="date")

  - Formato: radio buttons (PDF, Excel, CSV)

  - Botón "Generar Reporte"

  

Línea 95-134: Lista de Reportes Recientes:

  - 4 reportes generados (datos de ejemplo estáticos)

  - Cada uno con botón de descarga (sin funcionalidad real)

```

  

### 7.8 ciberControl.php

  

Vista de control de estaciones de cybercafé.

  

```

Línea 2-27: Resumen de estaciones (4 tarjetas):

  - Disponibles: 5 (verde)

  - Ocupadas: 3 (naranja)

  - Mantenimiento: 1 (rojo)

  - Total Estaciones: 9

  

Línea 30-46: Filtros compactos:

  - Botones de filtro: Todas (activo por defecto), Disponibles, Ocupadas, Mantenimiento.

  - Botones de acción: Nueva (estación), Historial.

  

Línea 49-154: Grid de estaciones:

  - 10 estaciones (#1 al #10) en columnas responsivas.

  - Cada estación es un .station-card con clase según estado:

    - disponible: icono check_circle verde, texto "Disponible"

    - ocupada: icono timelapse naranja, texto "Ocupada", muestra tiempo restante y precio

    - mantenimiento: icono build rojo, texto "Mantenimiento", muestra descripción

  - data-status: almacena el estado actual.

  

Línea 156-158: Mensaje informativo: "Haz clic en una estación para cambiar su estado".

```

  

### 7.9 activos.php

  

Vista de gestión de activos fijos.

  

```

Línea 1-27: Barra de herramientas:

  - Buscador de activos (#searchActivo)

  - Filtro por categoría: Todos, Equipos, Herramientas, Licencias

  - Botón "Nuevo Activo"

  

Línea 29-79: Sección Equipos (3):

  - Impresora Láser HP (Serie: HP-2024-001) - Activo (verde)

  - Proyector Epson (Serie: EPS-2023-045) - Mantenimiento (naranja)

  - Router Cisco (Serie: CSC-2024-012) - Activo (verde)

  

Línea 81-121: Sección Licencias (2):

  - Windows 11 Pro (Expira: 2024-12-31) - Vencida (rojo)

  - Office 365 (Expira: 2025-06-15) - Activa (verde)

  

Línea 124-165: Sección Herramientas (2 de 4 mostradas):

  - Kit Destornilladores - Disponible

  - Multímetro Digital - Disponible

  

Línea 166-187: Resumen:

  - Activos Totales: 9 (verde)

  - En Mantenimiento: 1 (azul)

  - Requieren Atención: 1 (rojo)

```

  

### 7.10 menu.php

  

Página independiente (sin layout) que funciona como menú de navegación alternativo. Es un diseño tipo "enlaces rápidos".

  

```

Línea 1-150: Página HTML completa con:

  - Fondo con gradiente oscuro.

  - Tarjeta central con enlaces a todos los módulos.

  - Cada enlace (menu-item) tiene icono Material, nombre y flecha animada.

  - Muestra el nombre de usuario admin en el footer.

  - Toggle de tema oscuro/claro con persistencia en localStorage.

  - NOTA: usa render('menu') en vez de renderWithLayout(), por eso tiene DOCTYPE propio.

```

  

### 7.11 404.php

  

Página de error 404 (ruta no encontrada).

  

```

Línea 1-24: Página HTML completa con:

  - Título "404 - Pagina no encontrada"

  - Número grande "404" en color primario.

  - Mensaje: "La ruta [URL actual] no existe."

  - Enlace "Volver al Dashboard".

  - NOTA: usa htmlspecialchars() para escapar la URI mostrada.

```

  

### 7.12 login_validate.php (archivo legacy)

  

**YA NO SE USA.** Era la lógica de autenticación del sistema anterior (antes de tener Router).

  

```

Línea 1-9: Comentarios explicando que este archivo es legacy y ya no se ejecuta.

           Ahora la autenticación se maneja desde AuthController@login.

  

Línea 14-42: Código antiguo que:

  - Verificaba que la petición fuera POST.

  - Validaba credenciales hardcodeadas (admin/1234).

  - Redirigía usando el formato legacy ?pagina=.

```

  

---

  

## 8. Models

  

### 8.1 crud_users.php

  

Modelo CRUD para la tabla 'usuarios'. Usa funciones sueltas (no clase con namespace).

  

```

Línea 8:  require_once __DIR__ . '/../../Config/database.php';

           - Carga la conexión PDO a MySQL.

  

--- crearUsuario() ---

Línea 15-22: INSERT INTO usuarios (nombre, email) VALUES (?, ?)

           - Prepara la consulta con marcadores ? (previene SQL injection).

           - Ejecuta con los valores reales.

  

--- obtenerUsuarios() ---

Línea 27-32: SELECT * FROM usuarios

           - Obtiene todos los usuarios.

           - fetchAll() devuelve arreglo de arreglos asociativos.

  

--- obtenerUsuarioPorId() ---

Línea 38-45: SELECT * FROM usuarios WHERE id = ?

           - Obtiene un solo usuario por ID.

           - fetch() devuelve un solo registro o false.

  

--- actualizarUsuario() ---

Línea 53-59: UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?

           - Actualiza nombre y email de un usuario.

  

--- eliminarUsuario() ---

Línea 65-70: DELETE FROM usuarios WHERE id = ?

           - Elimina un usuario por ID.

```

  

---

  

## 9. Assets Frontend

  

### 9.1 styles.css

  

Hoja de estilos principal (461 líneas). Define el tema visual completo.

  

```

Línea 1-22: Variables CSS (:root) para tema claro:

  --primary: #3949ab (índigo)

  --success: #43a047 (verde)

  --warning: #fb8c00 (naranja)

  --danger: #e53935 (rojo)

  --info: #1e88e5 (azul)

  --bg: #f5f7fa (fondo gris claro)

  --surface: #ffffff (superficie blanca)

  --text: #263238 (texto oscuro)

  --shadow: sombras progresivas

  --radius: 8px (bordes redondeados)

  

Línea 24-43: [data-theme="dark"]: sobrescribe variables para tema oscuro.

  --bg: #121212, --surface: #1e1e1e, --text: #e0e0e0, etc.

  

Línea 45-50: Estilos base del body (tipografía, fondo, color).

  

Línea 52-59: Header y main con padding-left: 300px (espacio para sidebar).

           En pantallas ≤992px, padding-left: 0 (sidebar se oculta).

  

Línea 61-72: Estilos del sidebar activo (active link con borde derecho).

  

Línea 74-76: Tarjetas con bordes redondeados, sombra, y transición hover.

  

Línea 77-87: Welcome banner con gradiente índigo.

  

Línea 89-141: Metric cards con indicador de color superior (::before).

           Efecto hover: translateY(-4px).

  

Línea 143-190: Station cards (cybercafé):

  - Borde de 4px superior con color según estado.

  - Hover: translateY(-4px) scale(1.02).

  - Estados: disponible (verde), ocupada (naranja), mantenimiento (rojo).

  - Adaptaciones para tema oscuro.

  

Línea 192-216: Carrito (cart-item) con hover highlight y botón de eliminar.

  

Línea 218-241: Activity items con icono circular y contenido flexible.

  

Línea 243-266: Adaptaciones del sidebar y formularios para tema oscuro.

  

Línea 268-305: POS product cards con efecto hover, estado selected, animación pulse.

  

Línea 307-334: Botón "volver arriba" y campana de notificaciones con animación ring.

  

Línea 336-351: Toasts (notificaciones) y paginación.

  

Línea 353-375: Más adaptaciones de tema oscuro (card-panels, activity icons, clock).

  

Línea 377-419: Estilos de station-inner, station-desc, station-price, filter buttons.

  

Línea 421-461: Estilos del modal del carrito POS y botón "+" flotante en productos.

```

  

### 9.2 login.css

  

Estilos específicos para la página de login (67 líneas).

  

```

Línea 1-17: Variables CSS para tema claro y oscuro.

  

Línea 19-27: Body del login:

  - flexbox centrado (horizontal y vertical).

  - Fondo con gradiente: #283593 → #5c6bc0.

  - min-height: 100vh (ocupa toda la pantalla).

  

Línea 29-31: Tema oscuro: gradiente más oscuro.

  

Línea 33-40: Tarjeta de login:

  - max-width: 420px.

  - border-radius: 12px.

  - Animación slideUp (aparece desde abajo).

  

Línea 47-58: Logo con gradiente y sombra pronunciada.

  

Línea 60-67: Adaptaciones para tema oscuro (inputs, labels, divisores).

```

  

### 9.3 app.js

  

Lógica frontend completa con jQuery (362 líneas).

  

```

Línea 1: var EIS = {};

           - Objeto global para almacenar utilidades de la aplicación.

  

Línea 3-17: $(function () { ... });

           - jQuery ready: se ejecuta cuando el DOM está listo.

           - Inicializa componentes Materialize:

             .sidenav(), .formSelect(), .tooltip(), .modal(), .dropdown(), .tabs(),

             .collapsible(), .materialbox(), .parallax(), .pushpin(), .scrollSpy()

  

Línea 19-27: Reloj en tiempo real:

  - actualizarReloj() obtiene hora actual y la formatea.

  - setInterval() actualiza cada 1 segundo.

  - Muestra en el elemento #clock (en el header).

  

Línea 30-35: Sistema de notificaciones Toast:

  - EIS.toast(msg, color, icon) crea un toast de Materialize.

  - color por defecto: 'indigo', icono: 'check_circle'.

  

Línea 37-57: Tema oscuro/claro:

  - updateThemeUI(theme): actualiza icono y texto del botón.

  - Al hacer clic en #themeToggle, alterna el tema.

  - Persiste en localStorage.

  - Muestra toast de confirmación.

  

Línea 59-82: Animación de contadores:

  - animarContadores(): anima los valores numéricos en .metric-value.

  - Usa $.animate() para contar desde 0 hasta el valor final.

  - Soporta formato de moneda ($1,245.50) y números enteros.

  

Línea 84-126: Búsqueda en tablas con debounce:

  - debounce(fn, delay): evita ejecutar la función demasiado seguido.

  - filtrarTabla(inputSelector, tableSelector, colIndex): filtra filas por texto.

  - Actualiza el contador "Mostrando X de Y resultados".

  - Inputs: #searchProducto, #searchProveedor, #searchActivo, #posSearch.

  - Debounce de 200-300ms para mejor rendimiento.

  

Línea 128-144: Filtro por estado (select):

  - Al cambiar #filterEstado o #filterEstadoProv, filtra filas por texto del badge.

  - Actualiza contador de resultados.

  

Línea 146-234: Sistema POS (carrito de compras):

  - var posCart = []: arreglo de productos en el carrito.

  - var posTotal = 0: suma total.

  - Al hacer clic en .pos-product:

    - Lee data-name y data-price.

    - Agrega al carrito, actualiza total y UI.

    - Muestra animación selected y toast.

  - actualizarPosUI(): actualiza mini-total y modal.

  - actualizarMiniTotal(): actualiza el badge y texto del header.

  - actualizarCarritoModal(): renderiza lista de productos en el modal.

    - Si vacío: muestra mensaje "El carrito está vacío".

    - Si tiene items: muestra cada uno con número, nombre, precio y botón eliminar.

  - Eliminar producto: .cart-item-remove, actualiza total y UI.

  - Abrir carrito: #openCartBtn, abre el modal de Materialize.

  - Procesar venta: #procesarVenta, muestra confirmación y toast.

  - Vaciar carrito: #vaciarCarrito, confirma y limpia.

  

Línea 236-291: Cybercafé:

  - actualizarCyberContadores(): cuenta estaciones por estado.

  - Click en .station-card:

    - disponible → ocupada: pide confirmación, cambia clase e icono, anima.

    - ocupada → disponible: pide confirmación, cambia clase e icono.

    - mantenimiento: muestra toast informativo.

  - Filtro de estaciones: .filter-btn, oculta/muestra con slideDown().

    - Botón activo se marca visualmente.

  

Línea 293-302: Reportes:

  - Al enviar #formReporte: previene envío real, muestra toast de generación.

  - Simula proceso con setTimeout (1.2s).

  

Línea 304-314: Botones de acción:

  - [data-confirm]: muestra confirmación antes de ejecutar.

  - .btn-nuevo: muestra toast "Formulario para nuevo X abierto (demo)".

  

Línea 316-325: Paginación: al hacer clic, marca la página como activa.

  

Línea 327-330: Descarga: toast "Descargando archivo...".

  

Línea 332-336: Tooltips mejorados: preserva títulos en hover.

  

Línea 338-347: Notificaciones (campana):

  - Al hacer clic en #notifBell, muestra 3 toasts de notificaciones de ejemplo.

  - Oculta #notifBadge.

  

Línea 349-360: Botón "volver arriba":

  - Aparece al scrollear más de 400px.

  - Al hacer clic, animación smooth scroll al inicio.

```

  

---

  

## 10. composer.json

  

```

Línea 2:  "name": "carlospez/clase"

           - Nombre del proyecto (autor/nombre).

  

Línea 3-7: "autoload": { "psr-4": { "App\\": "src/" } }

           - Configura el autoloading PSR-4.

           - El namespace App\ se mapea al directorio src/.

           - Ej: App\Core\Router → src/Core/Router.php.

           - App\Controllers\AuthController → src/Controllers/AuthController.php.

  

Línea 8-12: Autores: Carlos Páez (carlospaezguerra@gmail.com).

  

Línea 14: "require": {}

           - Sin dependencias externas (solo PHP nativo + Composer autoloader).

```

  

---

  

## 11. Flujo de navegación completo

  

### 11.1 Diagrama de flujo de una petición

  

```

Usuario escribe: http://localhost/eis_zona_web_lara/dashboard

  

1. Apache recibe la petición

   └── .htaccess (raíz)        → RewriteRule ^(.*)$ src/$1 [L]

       └── src/.htaccess        → RewriteCond !-f, !-d → RewriteRule ^(.*)$ index.php [QSA,L]

           └── src/index.php   ← TODAS las peticiones llegan aquí

  

2. index.php:

   ├── session_start()          → Inicia/reanuda sesión

   ├── require autoload.php     → Carga clases automáticamente

   ├── define('BASE_URL', ...)  → Calcula URL base

   ├── $router = new Router()   → Crea el enrutador

   ├── $router->get('/dashboard', 'DashboardController@index')->middleware('auth')

   │                            → Registra la ruta /dashboard

   └── $router->dispatch(Request::capture())

       │

       └── Router::dispatch():

           ├── $request->getUri()    → "/dashboard" (limpia la URI)

           ├── $request->getMethod() → "GET"

           ├── foreach($routes):

           │   ├── Coincide: GET /dashboard

           │   ├── preg_match() → true

           │   ├── runMiddleware(['auth']):

           │   │   └── ¿$_SESSION['logged_in'] === true?

           │   │       ├── NO → header('Location: /login') + return

           │   │       └── SÍ → continue

           │   └── callHandler('DashboardController@index', []):

           │       ├── Explode: [DashboardController, index]

           │       ├── class_exists('App\Controllers\DashboardController') → true

           │       ├── new DashboardController()

           │       └── $controller->index()

           │

           └── DashboardController::index():

               └── $this->renderWithLayout('dashboard', ['pageTitle' => 'Panel de Control'])

                   ├── extract($data) → $pageTitle = 'Panel de Control'

                   ├── $contentView = __DIR__ . '/../app/Views/dashboard.php'

                   └── require layout.php

                       ├── <html><head><title>Panel de Control - EIS System</title>

                       ├── Sidebar (sidenav)

                       ├── Header con reloj

                       ├── <main>

                       │   └── require $contentView → dashboard.php

                       │       ├── Welcome banner

                       │       ├── 4 metric cards

                       │       ├── Tabla horas pico

                       │       ├── Tabla productos sin stock

                       │       └── Actividad reciente

                       ├── Botón volver arriba

                       ├── Materialize JS

                       └── app.js (inicializa componentes, reloj, animaciones)

  

3. Respuesta HTML enviada al navegador

   └── El navegador renderiza la página completa

```

  

### 11.2 Resumen del patrón MVC implementado

  

| Capa | Rol | Archivos |

|------|-----|----------|

| **Modelo** | Datos y lógica de negocio | `app/Models/crud_users.php` |

| **Vista** | Interfaz de usuario | `app/Views/*.php`, `app/template/layout.php` |

| **Controlador** | Coordina Modelo y Vista | `Controllers/*.php` |

| **Front Controller** | Punto de entrada único | `index.php` |

| **Router** | Enrutamiento de peticiones | `Core/Router.php` |

| **Request** | Encapsula petición HTTP | `Core/Request.php` |

  

### 11.3 Mapa de rutas

  

| Método | URL | Controlador@Método | Middleware | Descripción |

|--------|-----|-------------------|------------|-------------|

| GET | `/` | `AuthController@showLogin` | No | Redirige según autenticación |

| GET | `/login` | `AuthController@showLogin` | No | Muestra formulario login |

| POST | `/login` | `AuthController@login` | No | Procesa inicio de sesión |

| GET | `/logout` | `AuthController@logout` | auth | Cierra sesión |

| GET | `/dashboard` | `DashboardController@index` | auth | Panel de control |

| GET | `/inventario` | `InventarioController@index` | auth | Gestión de inventario |

| GET | `/ventas` | `VentasController@index` | auth | Punto de venta POS |

| GET | `/ciberControl` | `CiberControlController@index` | auth | Control de cybercafé |

| GET | `/proveedores` | `ProveedoresController@index` | auth | Solicitudes a proveedores |

| GET | `/reportes` | `ReportesController@index` | auth | Reportes y estadísticas |

| GET | `/activos` | `ActivosController@index` | auth | Gestión de activos fijos |

| GET | `/menu` | `MenuController@index` | auth | Menú alternativo |

  

### 11.4 Tecnologías utilizadas

  

| Tecnología | Versión | Propósito |

|------------|---------|-----------|

| PHP | 8.x | Lenguaje backend |

| MySQL | - | Base de datos (no implementada en vistas actuales) |

| Apache | - | Servidor web con mod_rewrite |

| Composer | - | Autoloading PSR-4 |

| Materialize CSS | 1.0.0 | Framework CSS (similar a Bootstrap) |

| jQuery | 3.7.1 | Manipulación del DOM y eventos |

| Material Icons | - | Iconografía |

| PDO | - | Conexión segura a base de datos |

  

---

  

## Notas importantes

  

1. **Autenticación**: Las credenciales actuales son `admin` / `1234` (hardcodeadas en `AuthController.php`). En producción deberían almacenarse en base de datos con contraseñas hasheadas (password_hash/password_verify).

  

2. **Datos estáticos**: Todas las vistas internas (dashboard, inventario, ventas, etc.) muestran datos de ejemplo estáticos. No hay conexión a base de datos en las vistas actuales. El archivo `database.php` y `crud_users.php` están preparados pero no se usan en la UI.

  

3. **Frontend jQuery**: Toda la interactividad (carrito POS, cybercafé, filtros, animaciones) se maneja con jQuery plano en el lado del cliente. No hay framework frontend (React, Vue, etc.).

  

4. **Modo oscuro**: Persiste la preferencia del usuario en `localStorage` con la clave `theme`.

  

5. **Archivo legacy**: `login_validate.php` es del sistema anterior y ya no se ejecuta. Se conserva como referencia.

  

6. **BASE_URL**: Constante que permite que la aplicación funcione tanto en la raíz del servidor como en subdirectorios, calculada automáticamente desde `$_SERVER['SCRIPT_NAME']`.