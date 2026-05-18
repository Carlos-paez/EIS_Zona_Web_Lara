<?php
// ============================================================
// CLASE ROUTER — ENRUTADOR PRINCIPAL DE LA APLICACIÓN
// ============================================================
// El Router es el componente central que conecta las solicitudes
// HTTP con los Controladores. Su función es:
//   1. Leer el parámetro ?pagina= de la URL
//   2. Validar que el usuario tenga acceso (autenticación)
//   3. Buscar la ruta en el mapa de rutas
//   4. Instanciar el Controlador correspondiente
//   5. Ejecutar el método del Controlador
//   6. Manejar errores 404 si la ruta no existe
//
// Este archivo pertenece al namespace App\Core y se carga
// automáticamente gracias al autoloader PSR-4 de Composer.

namespace App\Core;

class Router
{
    // ============================================
    // MAPA DE RUTAS DE LA APLICACIÓN
    // ============================================
    // Array asociativo que define todas las rutas disponibles.
    // Cada entrada tiene:
    //   - key: el valor de ?pagina= (ej: 'dashboard')
    //   - value: un array con:
    //       * 'controller': nombre de la clase Controladora
    //       * 'method': nombre del método a ejecutar
    //       * 'public': true si no requiere autenticación
    //
    // Las rutas 'public' (login, login_validate) son accesibles
    // sin iniciar sesión. Todas las demás requieren autenticación.
    private array $routes = [];

    // ============================================
    // CONSTRUCTOR
    // ============================================
    // Inicializa el mapa de rutas con todas las rutas disponibles.
    // Se ejecuta automáticamente al crear una instancia de Router.
    public function __construct()
    {
        // Definir todas las rutas de la aplicación.
        // Formato: 'nombre_ruta' => ['controller' => 'NombreController', 'method' => 'nombreMetodo', 'public' => true/false]
        $this->routes = [
            // Ruta pública: muestra el formulario de inicio de sesión
            // LoginController::index() → renderiza Views/auth/login.php
            'login'         => ['controller' => 'LoginController',        'method' => 'index',    'public' => true],

            // Ruta pública: procesa el envío del formulario de login (POST)
            // LoginController::validate() → verifica credenciales y redirige
            'login_validate' => ['controller' => 'LoginController',       'method' => 'validate', 'public' => true],

            // Rutas protegidas (requieren sesión iniciada)
            // Cada una renderiza la vista correspondiente dentro del layout
            'dashboard'     => ['controller' => 'DashboardController',    'method' => 'index'],    // Panel de control
            'inventario'    => ['controller' => 'InventarioController',   'method' => 'index'],    // Gestión de inventario
            'ventas'        => ['controller' => 'VentasController',       'method' => 'index'],    // Punto de venta (POS)
            'ciberControl'  => ['controller' => 'CiberControlController', 'method' => 'index'],    // Control de cybercafé
            'proveedores'   => ['controller' => 'ProveedoresController',  'method' => 'index'],    // Solicitudes a proveedores
            'reportes'      => ['controller' => 'ReportesController',     'method' => 'index'],    // Reportes y estadísticas
            'activos'       => ['controller' => 'ActivosController',      'method' => 'index'],    // Gestión de activos fijos
            'asesorias'     => ['controller' => 'AsesoriasController',    'method' => 'index'],    // Asesoría legal
            'menu'          => ['controller' => 'MenuController',         'method' => 'index'],    // Menú principal alternativo
        ];
    }

    // ============================================
    // MÉTODO DISPATCH — PROCESAR LA SOLICITUD ACTUAL
    // ============================================
    // Este es el método principal del Router. Se llama desde
    // index.php y orquesta todo el flujo de la aplicación.
    // No recibe parámetros; lee $_GET directamente.
    public function dispatch(): void
    {
        // Iniciar o reanudar la sesión del usuario.
        // session_start() debe llamarse antes de cualquier salida HTML.
        // PHP almacena los datos de sesión en el servidor y envía
        // una cookie con el ID de sesión al navegador.
        session_start();

        // ============================================
        // 1. DETERMINAR LA PÁGINA SOLICITADA
        // ============================================
        // Leer el parámetro "pagina" de la URL (?pagina=nombre).
        // Si no existe, el valor por defecto es "login".
        // El operador ?? (null coalescing) devuelve el primer valor
        // si no es null, o el segundo si lo es.
        $pagina = $_GET["pagina"] ?? 'login';

        // ============================================
        // 2. SANITIZAR EL PARÁMETRO
        // ============================================
        // Validar que el parámetro solo contenga caracteres seguros:
        // letras (a-z, A-Z), números (0-9), guiones bajos (_) y
        // guiones medios (-). Esto previene ataques de path traversal
        // (ej: ?pagina=../../../etc/passwd).
        // preg_match() devuelve 1 si coincide, 0 si no.
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            // Si el parámetro contiene caracteres no válidos,
            // redirigir al login por seguridad.
            $pagina = 'login';
        }

        // ============================================
        // 3. BUSCAR LA RUTA EN EL MAPA
        // ============================================
        // Buscar el nombre de página en el array de rutas.
        // Si no existe, $route será null y mostraremos 404.
        $route = $this->routes[$pagina] ?? null;

        // ============================================
        // 4. MANEJAR ERROR 404 (RUTA NO ENCONTRADA)
        // ============================================
        // Si la ruta no está definida en el mapa, mostrar
        // una página de error 404 con información útil.
        if (!$route) {
            // Establecer el código de respuesta HTTP 404 (Not Found)
            http_response_code(404);
            // Mostrar mensaje de error al usuario
            echo "<h1>Error 404: Página no encontrada</h1>";
            echo "<p>La página <strong>{$pagina}</strong> no existe.</p>";
            // Enlace para volver al dashboard (si está autenticado)
            echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
            // Detener la ejecución del script
            return;
        }

        // ============================================
        // 5. CONTROL DE ACCESO (AUTENTICACIÓN)
        // ============================================
        // Si la ruta NO es pública (empty($route['public']) es true)
        // y el usuario NO tiene la variable de sesión 'logged_in',
        // redirigir al login.
        // isset() verifica que la variable existe y no es null.
        if (empty($route['public']) && !isset($_SESSION['logged_in'])) {
            // Redirigir al navegador a la página de login
            header("Location: ?pagina=login");
            // exit() detiene la ejecución del script para que
            // no se procese nada más después de la redirección.
            exit;
        }

        // ============================================
        // 6. INSTANCIAR EL CONTROLADOR
        // ============================================
        // Construir el nombre completo de la clase del controlador
        // incluyendo el namespace. Ej: "App\Controllers\LoginController"
        $controllerClass = "App\\Controllers\\{$route['controller']}";
        // Obtener el nombre del método a ejecutar
        $method = $route['method'];

        // Verificar que la clase del controlador existe.
        // class_exists() intenta cargar la clase automáticamente
        // usando el autoloader de Composer.
        if (!class_exists($controllerClass)) {
            // Si la clase no existe, lanzar una excepción
            throw new \Exception("Controller {$controllerClass} no encontrado");
        }

        // Crear una nueva instancia del controlador.
        // El constructor de Controller.php (clase base) establece
        // $this->currentPage con el nombre de la página actual.
        $controller = new $controllerClass();

        // ============================================
        // 7. EJECUTAR EL MÉTODO DEL CONTROLADOR
        // ============================================
        // Llamar al método correspondiente en el controlador.
        // El método se encargará de preparar los datos y
        // renderizar la vista correspondiente.
        $controller->$method();
    }
}
