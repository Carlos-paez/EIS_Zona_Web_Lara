<?php
// src/app/core/router.php

session_start();

$pagina = "login";

if(!empty($_GET["pagina"])){
    $pagina = $_GET["pagina"];
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
    $pagina = "login";
}

$public_pages = ['login', 'login_validate'];

if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
    header("Location: ?pagina=login");
    exit;
}

// ============================================================
// MANEJO DE ACCIONES PARA CiberControl (AJAX)
// ============================================================
if ($pagina === 'ciberControl' && isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    
    // Validar que la acción solo contenga caracteres seguros
    if (!preg_match('/^[a-zA-Z_]+$/', $accion)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        exit;
    }
    
    // Cargar el controlador y ejecutar la acción
    require_once __DIR__ . '/../Controllers/CiberController.php';
    $controller = new CiberController();
    
    switch ($accion) {
        case 'iniciar':
            $controller->iniciarSesion();
            break;
        case 'finalizar':
            $controller->finalizarSesion();
            break;
        case 'estadisticas':
            $controller->obtenerEstadisticas();
            break;
        case 'historial':
            $controller->obtenerHistorial();
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Acción no encontrada']);
    }
    exit;
}

// Ejecutar el controlador para ciberControl cuando se muestra la página principal
if ($pagina === 'ciberControl' && !isset($_GET['accion'])) {
    require_once __DIR__ . '/../Controllers/CiberController.php';
    $controller = new CiberController();
    $controller->index();
    exit;
}

// ============================================================

$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';

if(is_file($rutaVista)){
    if (in_array($pagina, $public_pages)) {
        require $rutaVista;
    } else {
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestión de Inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafé',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadísticas',
            'activos'      => 'Gestión de Activos',
            'asesorias'    => 'Asesoría Legal',
        ];
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text">Disponibles</span><span class="chip orange white-text">Ocupadas</span>',
        ];
        $pageTitle = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';
        $contentView = $rutaVista;
        require __DIR__ . '/../template/layout.php';
    }
} else {
    http_response_code(404);
    echo "<h1>Error 404: Página no encontrada</h1>";
    echo "<p>La página <strong>{$pagina}</strong> no existe.</p>";
    echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
}
?>