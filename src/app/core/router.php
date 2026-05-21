<?php
session_start(); // Inicia o reanuda la sesión del usuario (debe ser lo primero)

// --- 1. DETERMINAR LA PÁGINA SOLICITADA ---
$pagina = "login"; // Valor por defecto: página de inicio de sesión

if(!empty($_GET["pagina"])){
    $pagina = $_GET["pagina"]; // Toma el nombre de la página desde la URL: ?pagina=nombre
}

// --- 2. SANITIZAR EL PARÁMETRO ---
// Validar que solo contenga caracteres alfanuméricos y guiones (medida de seguridad)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
    $pagina = "login"; // Si contiene caracteres no válidos, redirige al login
}

// --- 3. CONTROL DE ACCESO (AUTENTICACIÓN) ---
$public_pages = ['login', 'login_validate']; // Páginas públicas que no requieren autenticación

if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
    // Si el usuario NO ha iniciado sesión y la página NO es pública:
    header("Location: ?pagina=login"); // Redirige al login
    exit; // Detiene la ejecución
}

// --- 4. RESOLVER LA RUTA DE LA VISTA ---
$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php'; // Construye la ruta al archivo de vista

// --- 5. CARGAR LA VISTA ---
if(is_file($rutaVista)){ // Verifica que el archivo de vista exista en el sistema de archivos
    if (in_array($pagina, $public_pages)) {
        // Páginas públicas (login): se renderizan SOLAS, sin el layout maestro
        require $rutaVista;
    } else {
        // Páginas protegidas (requieren autenticación): se renderizan DENTRO del layout maestro

        // 5a. Definir el título de cada página para mostrarlo en la barra de navegación y el tag <title>
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

        // 5b. Contenido HTML adicional para el encabezado de páginas específicas
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">5 Disponibles</span><span class="chip orange white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">4 Ocupadas</span>',
        ];

        $pageTitle = $titulos[$pagina] ?? 'EIS System'; // Título de la página (o valor por defecto)
        $headerExtra = $extraHeaders[$pagina] ?? '';    // HTML extra para el header (o vacío)
        $contentView = $rutaVista;                        // Ruta de la vista a incluir dentro del layout

        // Incluye el layout maestro que a su vez incluirá $contentView
        require __DIR__ . '/../template/layout.php';
    }
} else {
    // --- 6. MANEJO DE ERROR 404 ---
    http_response_code(404);                          // Establece el código de respuesta HTTP 404
    echo "<h1>Error 404: Página no encontrada</h1>"; // Mensaje de error
    echo "<p>La página <strong>{$pagina}</strong> no existe.</p>"; // Indica qué página se buscó
    echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";   // Enlace para volver
}
?>