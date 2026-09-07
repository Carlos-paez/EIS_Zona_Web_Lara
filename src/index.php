<?php
// =============================================================================
// ARCHIVO DE ENTRADA PRINCIPAL (Front Controller)
// =============================================================================
// Propósito: Punto de entrada único para todas las peticiones web.
//            Carga el autoloader de Composer, instancia el Router
//            y ejecuta el manejador para procesar la solicitud.
// Todos los request pasan por aquí (usando reglas de reescritura del servidor).
// =============================================================================

// Carga el autoloader de Composer para tener disponibles todas las clases
// y namespaces registrados en el proyecto (autoloading PSR-4)
require_once __DIR__ . '/../vendor/autoload.php';

// Importa las clases necesarias del namespace App\Core
use App\Core\Logger;
use App\Core\Router;

// =============================================================================
// MANEJO GLOBAL DE ERRORES
// -----------------------------------------------------------------------------
// Los errores técnicos NUNCA se muestran al usuario final: se registran en
// src/logs/errores.md y el usuario recibe un mensaje genérico amigable.
// =============================================================================
error_reporting(E_ALL);
ini_set('display_errors', '0');   // No exponer errores al navegador
ini_set('log_errors', '1');
ini_set('html_errors', '0');

// Buffer de salida: permite reemplazar una respuesta parcialmente generada
// por un mensaje de error genérico si ocurre un fallo fatal.
ob_start();

/**
 * Detecta si la petición es AJAX (consume JSON) o una página normal.
 */
function es_peticion_ajax(): bool
{
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || isset($_GET['action']);
}

/**
 * Descarta cualquier salida HTML/JSON ya generada antes del error.
 */
function limpiar_buffer_salida(): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

/**
 * Muestra al usuario final un error genérico (sin detalles técnicos).
 */
function mostrar_error_generico(int $codigo = 500): void
{
    if (!headers_sent()) {
        http_response_code($codigo);
    }

    if (es_peticion_ajax()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Ocurrió un error inesperado. Por favor, intenta nuevamente.']);
        return;
    }

    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - EIS System</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1rem;}
        .card{background:#fff;border-radius:12px;padding:2.5rem;max-width:480px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.1);}
        .card .icon{font-size:3rem;margin-bottom:.5rem;}
        .card h1{color:#283593;font-weight:700;font-size:1.4rem;margin:0 0 .5rem;}
        .card p{color:#78909c;margin:.25rem 0;}
        .card a{color:#1a237e;}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h1>Error interno</h1>
        <p>Algo salió mal. Por favor, intenta nuevamente o contacta al administrador del sistema.</p>
        <p><a href="?pagina=login">Volver al inicio</a></p>
    </div>
</body>
</html>';
}

// Errores de PHP (warnings, notices, etc.): se registran, no se muestran.
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Respeta la supresión con @ (error_reporting() devuelve 0 en esos casos).
    if (!(error_reporting() & $errno)) {
        return false;
    }

    Logger::error(new ErrorException($errstr, 0, $errno, $errfile, $errline), 'Error de PHP');
    return true;
});

// Excepciones no capturadas: se registran y se responde con error genérico.
set_exception_handler(function (\Throwable $t): void {
    Logger::error($t, 'Excepción no capturada');
    limpiar_buffer_salida();
    mostrar_error_generico(500);
});

// Errores fatales (no pasan por set_error_handler): se registran al terminar.
register_shutdown_function(function (): void {
    $last = error_get_last();
    if ($last === null) {
        return;
    }

    $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
    if (in_array($last['type'], $fatales, true)) {
        Logger::error(
            new ErrorException($last['message'], 0, $last['type'], $last['file'], $last['line']),
            'Error fatal'
        );
        limpiar_buffer_salida();
        mostrar_error_generico(500);
    }
});
// =============================================================================

// Crea una instancia del enrutador principal (inicia sesión y resuelve la página solicitada)
$router = new Router();
// Procesa la solicitud entrante: determina qué acción ejecutar y renderiza la respuesta
$router->handle();