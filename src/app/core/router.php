<?php
// =============================================================================
// CLASE Router (Enrutador Principal)
// =============================================================================
// Propósito: Determinar qué página mostrar según el parámetro GET 'pagina',
//            manejar autenticación, redirigir a controladores AJAX,
//            y renderizar las vistas con el layout principal.
// =============================================================================

// Declara el namespace 'App\Core' para organizar esta clase dentro de la aplicación
namespace App\Core;

/**
 * Clase Router - Enrutador principal de la aplicación.
 * 
 * Interpreta el parámetro 'pagina' de la URL, verifica autenticación,
 * deriva peticiones AJAX a sus controladores específicos, maneja acciones
 * de autenticación (login/logout) y renderiza las vistas solicitadas
 * dentro del layout principal.
 */
class Router
{
    /**
     * Nombre de la página solicitada a través de $_GET['pagina'].
     * 
     * Almacena la página resuelta y validada. Ejemplos: "dashboard",
     * "login", "inventario", "ventas", etc.
     *
     * @var string
     */
    private string $pagina;

    /**
     * Constructor de la clase Router.
     * 
     * Inicia o reanuda la sesión del usuario y determina cuál es la
     * página solicitada en la URL actual.
     */
    public function __construct()
    {
        session_start();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->setPagina($this->resolvePage());
    }

    public function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    public function getPagina(): string
    {
        return $this->pagina;
    }

    public function setPagina(string $pagina): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            $pagina = 'login';
        }
        $this->pagina = $pagina;
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Método principal que procesa la solicitud entrante.
     * 
     * Evalúa el tipo de petición (AJAX de inventario, roles, proveedores,
     * acción de autenticación, o vista normal) y ejecuta la acción
     * correspondiente. Si no coincide con ningún caso especial, renderiza
     * la vista solicitada.
     *
     * @return void
     */
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

        // Si es una petición AJAX de clientes, deriva al controlador
        if ($this->isAjaxCliente()) {
            $this->runClienteController();
            return;
        }

        // Si es una petición AJAX de asesorías, deriva al controlador
        if ($this->isAjaxAsesorias()) {
            $this->runAsesoriaController();
            return;
        }

        // Si es una petición AJAX del punto de venta (POS), deriva al controlador
        if ($this->isAjaxVentas()) {
            $this->runVentaController();
            return;
        }

        // Si es una petición AJAX del control de cybercafé, deriva al controlador
        if ($this->isAjaxCiber()) {
            $this->runCiberController();
            return;
        }

        // Si es una petición AJAX de gestión de proveedores, deriva al controlador
        if ($this->isAjaxProveedorGestion()) {
            $this->runProveedorGestionController();
            return;
        }

        // Si es una petición AJAX de proveedores/solicitudes (tiene ?action=...), deriva al controlador
        if ($this->isAjaxProveedores()) {
            $this->runProveedorController();
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

    /**
     * Determina el nombre de la página desde $_GET['pagina'] con validación de seguridad.
     * 
     * Si no se especifica 'pagina' en la URL, se asume 'login' como valor
     * por defecto. Se aplica una expresión regular para asegurar que el
     * nombre solo contenga caracteres alfanuméricos, guiones y guiones bajos,
     * evitando así ataques de path traversal.
     *
     * @return string Nombre de la página validado
     */
    private function resolvePage(): string
    {
        // Página por defecto si no se especifica en la URL
        $pagina = 'login';

        // Si existe el parámetro 'pagina' en la URL y no está vacío, lo usa
        if (!empty($_GET['pagina'])) {
            $pagina = $_GET['pagina'];
        }

        // Valida que el nombre solo contenga caracteres seguros (letras, números, guiones)
        // Esto evita inyección de rutas como "../../etc/passwd"
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            // Si no pasa la validación, redirige a login como medida de seguridad
            $pagina = 'login';
        }

        // Devuelve el nombre de la página ya validado
        return $pagina;
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de inventario.
     * 
     * Se considera AJAX de inventario cuando la página solicitada es
     * 'inventario' y existe el parámetro 'action' en la URL.
     *
     * @return bool True si es una petición AJAX de inventario, false en caso contrario
     */
    private function isAjaxInventario(): bool
    {
        // Debe ser página 'inventario' Y tener el parámetro 'action' en la URL
        return $this->pagina === 'inventario' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de roles.
     * 
     * Se considera AJAX de roles cuando la página solicitada es 'roles'
     * y existe el parámetro 'action' en la URL.
     *
     * @return bool True si es una petición AJAX de roles, false en caso contrario
     */
    private function isAjaxRoles(): bool
    {
        // Debe ser página 'roles' Y tener el parámetro 'action' en la URL
        return $this->pagina === 'roles' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de clientes.
     *
     * @return bool True si es una petición AJAX de clientes, false en caso contrario
     */
    private function isAjaxCliente(): bool
    {
        return $this->pagina === 'clientes' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de asesorías.
     *
     * @return bool True si es una petición AJAX de asesorías, false en caso contrario
     */
    private function isAjaxAsesorias(): bool
    {
        return $this->pagina === 'asesorias' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de punto de venta.
     *
     * @return bool True si es una petición AJAX de ventas, false en caso contrario
     */
    private function isAjaxVentas(): bool
    {
        return $this->pagina === 'ventas' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de control de cybercafé.
     *
     * @return bool True si es una petición AJAX de cybercafé, false en caso contrario
     */
    private function isAjaxCiber(): bool
    {
        return $this->pagina === 'ciberControl' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de gestión de proveedores.
     *
     * @return bool True si es una petición AJAX de gestión de proveedores, false en caso contrario
     */
    private function isAjaxProveedorGestion(): bool
    {
        return $this->pagina === 'proveedores-gestion' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una petición AJAX del módulo de proveedores.
     * 
     * Se considera AJAX de proveedores cuando la página solicitada es
     * 'proveedores' y existe el parámetro 'action' en la URL.
     *
     * @return bool True si es una petición AJAX de proveedores, false en caso contrario
     */
    private function isAjaxProveedores(): bool
    {
        // Debe ser página 'proveedores' Y tener el parámetro 'action' en la URL
        return $this->pagina === 'proveedores' && isset($_GET['action']);
    }

    /**
     * Verifica si la solicitud actual es una acción de autenticación.
     * 
     * Las acciones de autenticación son 'login_validate' (procesar el
     * formulario de inicio de sesión) y 'logout' (cerrar la sesión).
     *
     * @return bool True si es una acción de autenticación, false en caso contrario
     */
    private function isAuthAction(): bool
    {
        // login_validate = procesar formulario de login
        // logout = cerrar sesión
        // Retorna true si la página coincide con alguna de estas dos opciones
        return $this->pagina === 'login_validate' || $this->pagina === 'logout';
    }

    /**
     * Verifica que el usuario esté autenticado. Si no lo está, termina la ejecución.
     * 
     * Comprueba si existe la variable 'logged_in' en la sesión. Si no existe,
     * envía una respuesta JSON con error y finaliza la ejecución del script.
     * Este método se usa en las peticiones AJAX que requieren autenticación.
     *
     * @return void
     */
    private function requireAuth(): void
    {
        // Si el usuario no ha iniciado sesión (no existe 'logged_in' en $_SESSION)
        if (!isset($_SESSION['logged_in'])) {
            // Establece el tipo de contenido como JSON para la respuesta
            header('Content-Type: application/json');
            // Devuelve un JSON con indicador de error y mensaje de no autenticado
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            // Termina la ejecución del script inmediatamente
            exit;
        }
    }

    /**
     * Ejecuta el controlador de inventario para peticiones AJAX.
     * 
     * Verifica primero que el usuario esté autenticado, luego instancia
     * el controlador de inventario y ejecuta su método handle().
     * Finaliza la ejecución después de procesar la respuesta.
     *
     * @return void
     */
    private function runInventarioController(): void
    {
        // Verifica que el usuario tenga una sesión activa
        $this->requireAuth();
        // Crea una instancia del controlador de inventario
        $controller = new \App\Controllers\InventarioController();
        // Ejecuta el método handle del controlador (procesa la acción AJAX)
        $controller->handle();
        // Termina la ejecución para evitar que se renderice cualquier vista
        exit;
    }

    /**
     * Ejecuta el controlador de roles para peticiones AJAX.
     * 
     * Verifica autenticación, instancia el controlador de roles
     * y ejecuta su manejador. Finaliza después de procesar.
     *
     * @return void
     */
    private function runRolController(): void
    {
        // Verifica que el usuario tenga una sesión activa
        $this->requireAuth();
        // Crea una instancia del controlador de roles
        $controller = new \App\Controllers\RolController();
        // Ejecuta el método handle del controlador (procesa la acción AJAX)
        $controller->handle();
        // Termina la ejecución para evitar que se renderice cualquier vista
        exit;
    }

    /**
     * Ejecuta el controlador de clientes para peticiones AJAX.
     *
     * @return void
     */
    private function runClienteController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\ClienteController();
        $controller->handle();
        exit;
    }

    /**
     * Ejecuta el controlador de asesorías para peticiones AJAX.
     *
     * @return void
     */
    private function runAsesoriaController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\AsesoriaController();
        $controller->handle();
        exit;
    }

    /**
     * Ejecuta el controlador de ventas para peticiones AJAX.
     *
     * @return void
     */
    private function runVentaController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\VentaController();
        $controller->handle();
        exit;
    }

    /**
     * Ejecuta el controlador de cybercafé para peticiones AJAX.
     *
     * @return void
     */
    private function runCiberController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\CiberController();
        $controller->handle();
        exit;
    }

    /**
     * Ejecuta el controlador de gestión de proveedores para peticiones AJAX.
     *
     * @return void
     */
    private function runProveedorGestionController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\ProveedorGestionController();
        $controller->handle();
        exit;
    }

    /**
     * Ejecuta el controlador de proveedores para peticiones AJAX.
     * 
     * Verifica autenticación, instancia el controlador de proveedores
     * y ejecuta su manejador. Finaliza después de procesar.
     *
     * @return void
     */
    private function runProveedorController(): void
    {
        // Verifica que el usuario tenga una sesión activa
        $this->requireAuth();
        // Crea una instancia del controlador de proveedores
        $controller = new \App\Controllers\ProveedorController();
        // Ejecuta el método handle del controlador (procesa la acción AJAX)
        $controller->handle();
        // Termina la ejecución para evitar que se renderice cualquier vista
        exit;
    }

    /**
     * Ejecuta la acción de autenticación solicitada (login o logout).
     * 
     * Si la página es 'logout', instancia el AuthController y llama
     * a su método logout() para destruir la sesión y redirigir.
     * Si la página es 'login_validate', instancia el AuthController
     * y llama a su método login() para validar credenciales.
     *
     * @return void
     */
    private function runAuthAction(): void
    {
        // Si la página solicitada es 'logout', procede a cerrar la sesión
        if ($this->pagina === 'logout') {
            // Crea una instancia del controlador de autenticación
            $controller = new \App\Controllers\AuthController();
            // Llama al método logout que destruye la sesión y redirige al login
            $controller->logout();
            // Sale del método sin continuar
            return;
        }

        // Si la página es 'login_validate', procesa el formulario de inicio de sesión
        // Crea una instancia del controlador de autenticación
        $controller = new \App\Controllers\AuthController();
        // Llama al método login que valida las credenciales del usuario
        $controller->login();
        // Termina la ejecución después de procesar el login
        exit;
    }

    /**
     * Renderiza la vista solicitada, aplicando el layout si corresponde.
     * 
     * Si la página es pública (como login), se renderiza sin layout.
     * Si requiere autenticación y el usuario no ha iniciado sesión,
     * redirige al login. Si la vista no existe, muestra error 404.
     * Para páginas protegidas, renderiza dentro del layout principal
     * que incluye sidebar, navbar y footer.
     *
     * @return void
     */
    private function renderView(): void
    {
        // Lista de páginas públicas que no requieren autenticación
        $publicPages = ['login'];

        // Si el usuario NO ha iniciado sesión y la página no está en las públicas, redirige al login
        if (!isset($_SESSION['logged_in']) && !in_array($this->pagina, $publicPages)) {
            // Redirige mediante HTTP Location a la página de login
            header('Location: ?pagina=login');
            // Termina la ejecución para que no se siga procesando
            exit;
        }

        // Construye la ruta al archivo de la vista en el directorio Views
        $rutaVista = __DIR__ . '/../Views/' . $this->pagina . '.php';

        // Verifica si el archivo de la vista existe en el sistema de archivos
        if (!is_file($rutaVista)) {
            // Establece el código de respuesta HTTP 404 (No encontrado)
            http_response_code(404);
            // Muestra el título del error
            echo '<h1>Error 404: Página no encontrada</h1>';
            // Muestra el nombre de la página que no se encontró (escapado por seguridad)
            echo '<p>La página <strong>' . htmlspecialchars($this->pagina) . '</strong> no existe.</p>';
            // Enlace para volver al dashboard
            echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
            // Sale del método sin continuar
            return;
        }

        // Si la página es pública (como login), la renderiza directamente sin el layout
        if (in_array($this->pagina, $publicPages)) {
            // Incluye el archivo de la vista (que se renderiza solo, sin sidebar ni navbar)
            require $rutaVista;
            return;
        }

        // Para páginas protegidas, renderiza dentro del layout principal (con sidebar, navbar y footer)
        $this->renderWithLayout($rutaVista);
    }

    /**
     * Renderiza la vista solicitada dentro del layout principal de la aplicación.
     * 
     * Prepara variables como el título de la página y cabeceras adicionales
     * según la página solicitada, y luego incluye el archivo layout.php
     * que a su vez incluirá la vista específica ($contentView).
     *
     * @param string $contentView Ruta absoluta al archivo de la vista a incluir
     * @return void
     */
    private function renderWithLayout(string $contentView): void
    {
        // Alias de la página actual para usar directamente en el layout
        $pagina = $this->pagina;

        // Mapa de nombres de página a títulos descriptivos en español
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestión de inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafé',
            'proveedores'  => 'Solicitudes a Proveedores',
            'proveedores-gestion' => 'Gestión de Proveedores',
            'clientes'            => 'Gestión de Clientes',
            'reportes'     => 'Reportes y Estadísticas',
            'activos'      => 'Gestión de Activos',
            'asesorias'    => 'Asesoría Legal',
            'usuarios'     => 'Gestión de Usuarios',
            'roles'        => 'Gestión de Roles y Permisos',
        ];

        // Cabeceras adicionales específicas por página (ej: chips de estado para ciberControl)
        $extraHeaders = [
            // Para ciberControl muestra chips con conteos actualizados dinámicamente por JS
            'ciberControl' => '<span class="chip green white-text" id="hdrDisponibles" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">0 Disponibles</span><span class="chip orange white-text" id="hdrOcupadas" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">0 Ocupadas</span>',
        ];

        // Obtiene el título de la página desde el mapa, o usa 'EIS System' como valor por defecto
        $pageTitle   = $titulos[$pagina] ?? 'EIS System';
        // Obtiene la cabecera adicional si existe para esta página, o cadena vacía si no
        $headerExtra = $extraHeaders[$pagina] ?? '';

        // Incluye el layout principal que a su vez incluirá la vista específica ($contentView)
        require __DIR__ . '/../template/layout.php';
    }
}