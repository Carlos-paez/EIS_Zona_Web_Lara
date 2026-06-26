<?php
// =============================================================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// =============================================================================
// Propósito: Establecer la conexión PDO a MySQL usando las credenciales
//            definidas aquí. Este archivo es incluido por los CRUDs basados
//            en funciones sueltas (crud_*.php). Crea la variable $pdo.
// NOTA: Existe una versión moderna en App\Core\Database (patrón Singleton).
// =============================================================================

// Configuración de la conexión a la base de datos MySQL usando PDO

// Dirección del servidor de base de datos (localhost = misma máquina)
$host = "localhost";
// Nombre de la base de datos a la que se conectará la aplicación
$db = "zona_web_lara";
// Usuario de MySQL con permisos sobre la base de datos
$user = "root";
// Contraseña del usuario de MySQL (vacía en entorno de desarrollo local)
$pass = "";
// Juego de caracteres UTF-8 que soporta emojis y caracteres especiales
$charset = 'utf8mb4';

// Cadena de conexión (DSN - Data Source Name) que PDO necesita para conectarse
$dns = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones de configuración de la conexión PDO
$options = [
    // Modo de error: lanza excepciones cuando ocurre un error SQL
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Modo de obtención por defecto: devuelve los resultados como array asociativo
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Desactiva la emulación de consultas preparadas (usa consultas reales, más seguro)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Intenta crear la conexión PDO con los parámetros definidos anteriormente
    $pdo = new PDO($dns, $user, $pass, $options);

}catch (\PDOException $e) {
    // Captura cualquier error de conexión a la base de datos
    // Relanza la excepción con el mensaje y código original para que sea manejada arriba
    throw new \PDOException($e->getMessage(), (int)$e->getCode());

}