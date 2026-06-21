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

    $host = "localhost";          // Dirección del servidor de base de datos
    $db = "zona_web_lara";                 // Nombre de la base de datos
    $user = "root";              // Usuario de MySQL
    $pass = "";                  // Contraseña de MySQL (vacía en desarrollo local)
    $charset = 'utf8mb4';        // Juego de caracteres UTF-8 (soporta emojis y caracteres especiales)

    $dns = "mysql:host=$host;dbname=$db;charset=$charset"; // Cadena de conexión (DSN)

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como array asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                   // Usa consultas preparadas reales (más seguro)
    ];

    try {
        // Intenta crear la conexión PDO con los parámetros definidos
        $pdo = new PDO($dns, $user, $pass, $options);

    }catch (\PDOException $e) {
        // Captura y relanza cualquier error de conexión
        throw new \PDOException($e->getMessage(), (int)$e->getCode());

    }