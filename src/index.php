<?php
// ============================================================
// FRONT CONTROLLER — PUNTO DE ENTRADA ÚNICO DE LA APLICACIÓN
// ============================================================
// Todas las solicitudes HTTP pasan por aquí gracias a las reglas
// de reescritura de Apache (.htaccess). Este archivo:
//   1. Carga el autoloader de Composer para cargar clases automáticamente
//   2. Importa la clase Router desde el namespace App\Core
//   3. Crea una instancia del Router y ejecuta el método dispatch()
//      que se encarga de analizar la URL, determinar el controlador
//      y método a ejecutar, y gestionar la autenticación.

// Cargar el autoloader de Composer.
// __DIR__ . '/../vendor/autoload.php' resuelve la ruta absoluta
// al archivo vendor/autoload.php generado por Composer.
// Este autoloader implementa PSR-4 y carga automáticamente las
// clases cuando se usan por primera vez, según el mapeo definido
// en composer.json (App\ → src/app/).
require_once __DIR__.'/../vendor/autoload.php';

// Importar la clase Router desde el namespace App\Core.
// La sentencia "use" permite referirse a Router sin escribir
// el namespace completo cada vez.
use App\Core\Router;

// Crear una nueva instancia del Router.
// El constructor de Router define el mapa de rutas
// (qué controlador y método ejecutar para cada ?pagina=xxx).
$router = new Router();

// Ejecutar el método dispatch() del Router.
// dispatch() se encarga de todo el flujo:
//   - Iniciar/reanudar la sesión
//   - Leer y sanitizar el parámetro ?pagina=
//   - Verificar autenticación (redirigir al login si no está logueado)
//   - Encontrar la ruta en el mapa de rutas
//   - Instanciar el controlador correspondiente
//   - Ejecutar el método del controlador
//   - Manejar errores 404 si la ruta no existe
$router->dispatch();
