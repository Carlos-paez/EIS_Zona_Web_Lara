<?php
// =============================================================================
// CLASE Router (Enrutador Principal)
// =============================================================================
// Propósito: Determinar qué página mostrar según el parámetro GET 'pagina',
//            manejar autenticación, redirigir a controladores AJAX,
//            y renderizar las vistas con el layout principal.
// =============================================================================
namespace App\Core;

class Router
{
    // Almacena el nombre de la página solicitada (ej: "dashboard", "login")
    private string $pagina;

    // Constructor: inicia la sesión y resuelve la página solicitada
    public function __construct()
    {
        session_start();                      // Inicia o reanuda la sesión del usuario
        $this->pagina = $this->resolvePage(); // Determina qué página se pidió
    }

    // Método principal: decide qué acción tomar según la página solicitada
    public function handle(): void
    {
        // Si es una petición AJAX de inventario (tiene ?action=...), deriva al controlador
        if ($this->isAjaxInventario()) {
            $this->runInventarioController();
            return;                           // Sale para no seguir procesando
        }

        // Si es una petición AJAX de roles (tiene ?action=...), deriva al controlador
        if ($this->isAjaxRoles()) {
            $this->runRolController();
            return;
        }

        // Si es una acción de autenticación (login_validate o logout), deriva al AuthController
        if ($this->isAuthAction()) {
            $this->runAuthAction();
            return;
        }

        // Para cualquier otra página, renderiza la vista correspondiente
        $this->renderView();
    }

    // Determina el nombre de la página desde $_GET['pagina'] con validación de seguridad
    private function resolvePage(): string
    {
        $pagina = 'login';                    // Página por defecto si no se especifica

        // Si existe el parámetro 'pagina' en la URL, lo usa
        if (!empty($_GET['pagina'])) {
            $pagina = $_GET['pagina'];
        }

        // Valida que el nombre solo contenga caracteres seguros (letras, números, guiones)
        // Esto evita inyección de rutas como "../../etc/passwd"
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            $pagina = 'login';                // Si no pasa la validación, redirige a login
        }

        return $pagina;
    }

    // Verifica si la solicitud es una petición AJAX del módulo de inventario
    private function isAjaxInventario(): bool
    {
        // Debe ser página 'inventario' Y tener el parámetro 'action'
        return $this->pagina === 'inventario' && isset($_GET['action']);
    }

    // Verifica si la solicitud es una petición AJAX del módulo de roles
    private function isAjaxRoles(): bool
    {
        return $this->pagina === 'roles' && isset($_GET['action']);
    }

    // Verifica si la solicitud es una acción de autenticación
    private function isAuthAction(): bool
    {
        // login_validate = procesar formulario de login
        // logout = cerrar sesión
        return $this->pagina === 'login_validate' || $this->pagina === 'logout';
    }

    // Verifica que el usuario esté autenticado, si no, devuelve error JSON y termina
    private function requireAuth(): void
    {
        if (!isset($_SESSION['logged_in'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }
    }

    // Ejecuta el controlador de inventario para peticiones AJAX
    private function runInventarioController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\InventarioController();
        $controller->handle();
        exit;
    }

    // Ejecuta el controlador de roles para peticiones AJAX
    private function runRolController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\RolController();
        $controller->handle();
        exit;
    }

    // Ejecuta la acción de autenticación (login o logout)
    private function runAuthAction(): void
    {
        // Si la página es 'logout', destruye la sesión
        if ($this->pagina === 'logout') {
            $controller = new \App\Controllers\AuthController();
            $controller->logout();            // Llama al método logout (destruye sesión y redirige)
            return;
        }

        // Si es 'login_validate', procesa el formulario de inicio de sesión
        $controller = new \App\Controllers\AuthController();
        $controller->login();                 // Llama al método login (valida credenciales)
        exit;
    }

    // Renderiza la vista solicitada, aplicando layout si corresponde
    private function renderView(): void
    {
        $publicPages = ['login'];             // Páginas públicas que no requieren autenticación

        // Si el usuario NO ha iniciado sesión y la página no es pública, redirige al login
        if (!isset($_SESSION['logged_in']) && !in_array($this->pagina, $publicPages)) {
            header('Location: ?pagina=login');
            exit;
        }

        // Construye la ruta al archivo de la vista
        $rutaVista = __DIR__ . '/../Views/' . $this->pagina . '.php';

        // Si el archivo de la vista no existe, muestra error 404
        if (!is_file($rutaVista)) {
            http_response_code(404);
            echo '<h1>Error 404: Página no encontrada</h1>';
            echo '<p>La página <strong>' . htmlspecialchars($this->pagina) . '</strong> no existe.</p>';
            echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
            return;
        }

        // Si es una página pública (login), la renderiza sin el layout
        if (in_array($this->pagina, $publicPages)) {
            require $rutaVista;
            return;
        }

        // Para páginas protegidas, renderiza dentro del layout principal (con sidebar y navbar)
        $this->renderWithLayout($rutaVista);
    }

    // Renderiza la vista dentro del layout principal (sidebar + navbar + footer)
    private function renderWithLayout(string $contentView): void
    {
        $pagina = $this->pagina;              // Alias para usar en el layout

        // Mapa de páginas -> títulos descriptivos
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestión de inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafé',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadísticas',
            'activos'      => 'Gestión de Activos',
            'asesorias'    => 'Asesoría Legal',
            'usuarios'     => 'Gestión de Usuarios',
            'roles'        => 'Gestión de Roles y Permisos',
        ];

        // Cabeceras adicionales por página (ej: chips de estado para ciberControl)
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">5 Disponibles</span><span class="chip orange white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">4 Ocupadas</span>',
        ];

        // Si la página no existe en el mapa, usa 'EIS System' como título genérico
        $pageTitle   = $titulos[$pagina] ?? 'EIS System';
        // Cabecera adicional vacía si no está definida para esta página
        $headerExtra = $extraHeaders[$pagina] ?? '';

        // Incluye el layout principal (que a su vez incluirá $contentView)
        require __DIR__ . '/../template/layout.php';
    }
}
