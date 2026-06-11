<?php
namespace App\Core;

class Router
{
    private string $pagina;

    public function __construct()
    {
        session_start();
        $this->pagina = $this->resolvePage();
    }

    public function handle(): void
    {
        if ($this->isAjaxInventario()) {
            $this->runInventarioController();
            return;
        }

        if ($this->isAuthAction()) {
            $this->runAuthAction();
            return;
        }

        $this->renderView();
    }

    private function resolvePage(): string
    {
        $pagina = 'login';

        if (!empty($_GET['pagina'])) {
            $pagina = $_GET['pagina'];
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            $pagina = 'login';
        }

        return $pagina;
    }

    private function isAjaxInventario(): bool
    {
        return $this->pagina === 'inventario' && isset($_GET['action']);
    }

    private function isAuthAction(): bool
    {
        return $this->pagina === 'login_validate' || $this->pagina === 'logout';
    }

    private function runInventarioController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\InventarioController();
        $controller->handle();
        exit;
    }

    private function runAuthAction(): void
    {
        if ($this->pagina === 'logout') {
            $controller = new \App\Controllers\AuthController();
            $controller->logout();
            return;
        }

        $controller = new \App\Controllers\AuthController();
        $controller->login();
        exit;
    }

    private function renderView(): void
    {
        $publicPages = ['login'];

        if (!isset($_SESSION['logged_in']) && !in_array($this->pagina, $publicPages)) {
            header('Location: ?pagina=login');
            exit;
        }

        $rutaVista = __DIR__ . '/../Views/' . $this->pagina . '.php';

        if (!is_file($rutaVista)) {
            http_response_code(404);
            echo '<h1>Error 404: Página no encontrada</h1>';
            echo '<p>La página <strong>' . htmlspecialchars($this->pagina) . '</strong> no existe.</p>';
            echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
            return;
        }

        if (in_array($this->pagina, $publicPages)) {
            require $rutaVista;
            return;
        }

        $this->renderWithLayout($rutaVista);
    }

    private function renderWithLayout(string $contentView): void
    {
        $pagina = $this->pagina;

        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestión de Inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafé',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadísticas',
            'activos'      => 'Gestión de Activos',
            'asesorias'    => 'Asesoría Legal',
            'usuarios'     => 'Gestión de Usuarios',
        ];

        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">5 Disponibles</span><span class="chip orange white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">4 Ocupadas</span>',
        ];

        $pageTitle   = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';

        require __DIR__ . '/../template/layout.php';
    }
}
