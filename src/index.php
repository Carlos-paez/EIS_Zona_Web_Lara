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

// Importa la clase Router del namespace App\Core para usarla sin prefijo
use App\Core\Router;

// Crea una instancia del enrutador principal (inicia sesión y resuelve la página solicitada)
$router = new Router();
// Procesa la solicitud entrante: determina qué acción ejecutar y renderiza la respuesta
$router->handle();
