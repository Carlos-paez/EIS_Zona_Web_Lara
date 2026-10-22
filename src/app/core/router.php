<?php

namespace App\Core;

use App\Controllers\ActivoController;
use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\InventarioController;
use App\Controllers\VentaController;
use App\Controllers\RolController;
use App\Controllers\ProveedorController;
use App\Controllers\ProveedorGestionController;
use App\Controllers\AsesoriaController;
use App\Controllers\CiberController;
use App\Controllers\DashboardController;
use App\Controllers\ReporteController;
use App\Controllers\UsuarioController;

/**
 * Enrutador principal (Front Controller).
 *
 * - Inicia la sesión y genera el token CSRF si no existe.
 * - Resuelve la página solicitada (?pagina=...), validando su nombre.
 * - Aplica el control de acceso (páginas públicas vs privadas).
 * - Despacha las peticiones AJAX (?pagina=X&action=Y) al controlador
 *   correspondiente mediante su método handle().
 * - Renderiza las vistas protegidas a través del layout principal.
 *
 * @method static bool verifyCsrfToken(?string $token)
 */
class Router
{
    /** Páginas accesibles sin iniciar sesión. */
    private const PUBLIC_PAGES = ['login', 'login_validate'];

    /** Títulos de cada página para el layout. */
    private const PAGE_TITLES = [
        'dashboard'          => 'Dashboard',
        'inventario'         => 'Gestión de inventario',
        'ventas'             => 'Punto de Venta (POS)',
        'ciberControl'       => 'Control de Ciber',
        'proveedores'        => 'Solicitudes a Proveedores',
        'proveedores-gestion' => 'Gestión de Proveedores',
        'clientes'           => 'Gestión de Clientes',
        'reportes'           => 'Reportes y Estadísticas',
        'activos'            => 'Gestión de Activos',
        'asesorias'          => 'Asesoría Legal',
        'usuarios'           => 'Configuración de Usuarios',
        'roles'              => 'Roles y Permisos',
        'manual_usuario'     => 'Manual de Usuario',
    ];

    /** Encabezados extra opcionales por página (chips, badges, etc.). */
    private const PAGE_EXTRA_HEADERS = [
        'ciberControl' => '<span id="hdrDisponibles" class="chip green white-text">Disponibles</span><span id="hdrOcupadas" class="chip orange white-text">Ocupadas</span>',
    ];

    /** Mapa página => controlador para el despacho AJAX (?pagina=X&action=Y). */
    private const CONTROLLERS = [
        'clientes'           => ClienteController::class,
        'inventario'         => InventarioController::class,
        'ventas'             => VentaController::class,
        'roles'              => RolController::class,
        'proveedores'        => ProveedorController::class,
        'proveedores-gestion' => ProveedorGestionController::class,
        'asesorias'          => AsesoriaController::class,
        'ciberControl'       => CiberController::class,
        'activos'            => ActivoController::class,
        'dashboard'          => DashboardController::class,
        'reportes'           => ReporteController::class,
        'usuarios'           => UsuarioController::class,
    ];

    /** @var string Página actual resuelta. */
    private string $pagina;

    public function __construct()
    {
        // Sesión única para toda la aplicación.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Token CSRF: se genera una sola vez por sesión y se reutiliza.
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Procesa la solicitud entrante.
     */
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

        // El rol y los permisos se releen de la base de datos en cada
        // petición: un cambio de rol, de estado o de la matriz de permisos
        // surte efecto de inmediato, sin esperar a que caduque la sesión.
        if (isset($_SESSION['logged_in']) && !Permisos::refrescar()) {
            // El usuario fue desactivado o eliminado: la sesión se invalida.
            Permisos::cerrarSesion();
            $this->redirectLogin('deshabilitado');
        }

        // Control de acceso por página: cada módulo exige su permiso base.
        // Las páginas sin vista se resuelven después con un 404, no con un 403.
        if (!$this->esPaginaPublica() && $this->tieneVista() && !Permisos::accesoPagina($this->pagina)) {
            $this->denegar('No tienes permisos para acceder a este módulo.');
        }

        // Despacho de peticiones AJAX de los módulos (?pagina=X&action=Y).
        if (array_key_exists($this->pagina, self::CONTROLLERS) && isset($_GET['action'])) {
            $this->dispatchAction();
        }

        // Flujo de inicio de sesión (POST ?pagina=login_validate).
        if ($this->pagina === 'login_validate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->login();
            return;
        }

        $this->render();
    }

    /**
     * Resuelve y sanitiza el parámetro ?pagina=.
     */
    private function resolvePagina(): string
    {
        $pagina = $_GET['pagina'] ?? 'login';

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            $pagina = 'login';
        }

        return $pagina;
    }

    /**
     * Despacha una petición AJAX al controlador de la página actual.
     *
     * Antes de invocar al controlador se comprueba el permiso de la acción:
     * una acción no mapeada se deniega (fail closed).
     */
    private function dispatchAction(): void
    {
        $accion = (string)$_GET['action'];

        if (!Permisos::puedeAccion($this->pagina, $accion)) {
            $this->denegar('No tienes permisos para realizar esta acción.', true);
        }

        $controllerClass = self::CONTROLLERS[$this->pagina];
        $controller      = new $controllerClass();

        if (method_exists($controller, 'handle')) {
            $controller->handle();
            exit;
        }
    }

    /**
     * Responde con un rechazo de acceso.
     *
     * Las peticiones AJAX reciben un JSON con HTTP 403; el resto, la vista
     * de acceso denegado dentro del layout principal.
     *
     * @param bool $ajax Fuerza la respuesta JSON.
     */
    private function denegar(string $motivo, bool $ajax = false): void
    {
        http_response_code(403);

        if ($ajax || $this->esPeticionAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error'   => $motivo,
            ]);
            exit;
        }

        $this->renderLayout(
            $this->viewsDir() . 'acceso_denegado.php',
            'Acceso denegado'
        );
        exit;
    }

    /**
     * Indica si la solicitud actual espera una respuesta JSON.
     */
    private function esPeticionAjax(): bool
    {
        if (isset($_GET['action'])) {
            return true;
        }

        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strcasecmp($requestedWith, 'XMLHttpRequest') === 0) {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return stripos($accept, 'application/json') !== false;
    }

    /**
     * Indica si la página actual es pública (no requiere permisos).
     */
    private function esPaginaPublica(): bool
    {
        return in_array($this->pagina, self::PUBLIC_PAGES, true);
    }

    /**
     * Indica si existe la vista asociada a la página actual.
     */
    private function tieneVista(): bool
    {
        return is_file($this->viewsDir() . $this->pagina . '.php');
    }

    /**
     * Cierra la sesión del usuario actual.
     */
    private function logout(): void
    {
        Permisos::cerrarSesion();
        $this->redirect('login');
    }

    /**
     * Redirige al login con un código de error.
     */
    private function redirectLogin(string $error): void
    {
        Permisos::cerrarSesion();
        header('Location: ?pagina=login&error=' . urlencode($error));
        exit;
    }

    /**
     * Renderiza la vista de la página actual a través del layout.
     */
    private function render(): void
    {
        // Vista de inicio de sesión (páginas públicas).
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

        // Vistas protegidas con layout.
        $rutaVista = $this->viewsDir() . $this->pagina . '.php';

        if (!is_file($rutaVista)) {
            http_response_code(404);
            echo '<h1>Error 404: Página no encontrada</h1>';
            echo "<p>La página <strong>{$this->pagina}</strong> no existe.</p>";
            echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
            return;
        }

        $pageTitle  = self::PAGE_TITLES[$this->pagina] ?? 'EIS System';
        $headerExtra = self::PAGE_EXTRA_HEADERS[$this->pagina] ?? '';

        $this->renderLayout($rutaVista, $pageTitle, $headerExtra);
    }

    /**
     * Renderiza una vista dentro del layout principal.
     *
     * El layout recibe el contexto de autorización para poder filtrar el
     * menú lateral: $permisosUsuario, $puede y $paginasPermitidas.
     *
     * @param string $contentView Ruta absoluta de la vista a inyectar.
     * @param string $pageTitle   Título de la pestaña.
     * @param string $headerExtra Contenido extra de la cabecera.
     */
    private function renderLayout(string $contentView, string $pageTitle, string $headerExtra = ''): void
    {
        $pagina            = $this->pagina;
        $permisosUsuario   = Permisos::permisosDeSesion();
        $paginasPermitidas = Permisos::paginasPermitidas();
        $puede             = static fn(string $permiso): bool => Permisos::puede($permiso);

        require __DIR__ . '/../template/layout.php';
    }

    /**
     * Verifica el token CSRF recibido contra el de la sesión.
     *
     * @param string|null $token Token enviado por el cliente.
     * @return bool True si el token es válido.
     */
    public static function verifyCsrfToken(?string $token): bool
    {
        return !empty($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Directorio de vistas.
     */
    private function viewsDir(): string
    {
        return __DIR__ . '/../Views/';
    }

    /**
     * Redirige a una página del sistema.
     */
    private function redirect(string $pagina): void
    {
        header('Location: ?pagina=' . $pagina);
        exit;
    }
}
