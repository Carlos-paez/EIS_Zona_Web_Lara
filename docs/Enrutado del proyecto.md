El proceso de enrutamiento de esta aplicación sigue el patrón **Front Controller**, lo que significa que todas las peticiones son centralizadas y procesadas por un único punto de entrada. A continuación, se detalla el funcionamiento paso a paso y línea por línea basándose en las fuentes:

### 1. Nivel de Servidor: Redirección (.htaccess)

Antes de que el código PHP se ejecute, el servidor web (Apache) prepara el camino:

- **Raíz del proyecto (`/.htaccess`):**
    - **Línea 7:** `RewriteEngine On` activa el motor de reescritura.
    - **Líneas 9-10:** `RewriteCond %{REQUEST_FILENAME} !-f` y `!-d` verifican que la petición no sea un archivo o carpeta real (como una imagen o CSS).
    - **Línea 11:** `RewriteRule ^(.*)$ src/$1 [L]` redirige internamente cualquier petición a la carpeta `src/`.
- **Dentro de `src/` (`src/.htaccess`):**
    - **Línea 25:** `RewriteRule ^(.*)$ index.php [QSA,L]` envía cualquier ruta (como `/dashboard` o `/login`) al archivo **index.php**, preservando los parámetros de la URL con `QSA`.

### 2. Punto de Entrada: index.php

Aquí comienza la ejecución de PHP para cada petición:

- **Línea 2:** `session_start();` inicia la sesión para verificar si el usuario está autenticado.
- **Línea 4:** `require_once __DIR__ . '/../vendor/autoload.php';` carga el autoloader de Composer para que las clases se carguen automáticamente bajo el estándar PSR-4.
- **Líneas 12-15:** Se calcula la **BASE_URL**. Esto permite que la aplicación funcione correctamente ya sea en la raíz del servidor o en subcarpetas, eliminando el nombre del script de la URI para obtener una ruta limpia.
- **Línea 20:** `$router = new Router();` se crea la instancia del enrutador que gestionará las rutas.

### 3. Registro de Rutas e Instancia de Petición

En `index.php` se definen qué controladores responden a qué URLs:

- **Líneas 23-40:** Se registran las rutas usando métodos como `$router->get('/login', 'AuthController@showLogin');`.
- **Línea 30:** Se pueden encadenar middlewares, como `->middleware('auth')`, que protegen rutas específicas exigiendo inicio de sesión.
- **Línea 42:** Se ejecuta `$router->dispatch(Request::capture());`.
    - `Request::capture()` crea un objeto que encapsula `$_GET`, `$_POST` y `$_SERVER` para no usarlos globalmente.
    - `dispatch()` es el motor que busca la coincidencia.

### 4. El Motor del Router (Router.php)

El método `dispatch` realiza el trabajo pesado de comparación:

- **Líneas 74-76:** El Router obtiene la **URI limpia** (ej: `/inventario`) y el **método HTTP** (GET o POST) desde el objeto Request.
- **Líneas 80-84:** Si la URI es la raíz `/`, el sistema decide si mostrar el login o el dashboard basándose en si el usuario está autenticado (`isAuthenticated()`).
- **Líneas 95-119 (Bucle de Rutas):**
    - Itera sobre todas las rutas guardadas en el arreglo `$routes`.
    - **Línea 96:** Verifica que el método HTTP coincida.
    - **Línea 97:** Usa `preg_match` para comparar la URI actual contra el patrón Regex de la ruta (generado previamente al registrar la ruta convirtiendo parámetros como `{id}` en grupos de captura).
    - Si hay coincidencia, ejecuta `runMiddleware()`.

### 5. Ejecución del Controlador (callHandler)

Si la ruta coincide y el middleware (como 'auth') permite el paso, se llama al controlador:

- **Línea 160:** `explode('@', $handler)` divide el string `"DashboardController@index"` en el nombre de la clase y el método.
- **Línea 163:** Construye el nombre completo con namespace: `App\\Controllers\\DashboardController`.
- **Línea 174:** `new $controllerClass()` crea dinámicamente la instancia del controlador.
- **Línea 177:** `$controller->$methodName(...$params)` ejecuta el método pasando los parámetros extraídos de la URL (usando el operador _splat_ `...`).

### 6. Finalización y Renderizado

Finalmente, el controlador decide qué mostrar:

- El controlador (que hereda de `Controller.php`) usa `render()` o `renderWithLayout()`.
- **Línea 33 (Controller.php):** `extract($data)` convierte las claves de un arreglo en variables disponibles para la vista.
- Si el Router no encuentra ninguna coincidencia al final del bucle, ejecuta **`handleNotFound()`** (Línea 122), que establece el código HTTP 404 y carga la vista de error.