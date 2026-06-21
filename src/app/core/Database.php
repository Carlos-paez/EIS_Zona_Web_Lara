<?php
// =============================================================================
// CLASE Database (Conexión a Base de Datos - Patrón Singleton)
// =============================================================================
// Propósito: Gestionar una única conexión PDO a MySQL para toda la aplicación.
//            Implementa el patrón Singleton para evitar múltiples conexiones
//            que consuman recursos innecesarios.
// =============================================================================
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    // Propiedad estática privada que almacena la única instancia de PDO
    private static ?PDO $instance = null;

    // Método estático público que devuelve la conexión PDO
    public static function getConnection(): PDO
    {
        // Solo crea la conexión si aún no existe (primera vez que se llama)
        if (self::$instance === null) {
            // Configuración del servidor MySQL
            $host = 'localhost';               // Dirección del servidor
            $db   = 'zona_web_lara';                     // Nombre de la base de datos
            $user = 'root';                    // Usuario de MySQL
            $pass = '';                        // Contraseña (vacía en desarrollo)
            $charset = 'utf8mb4';              // Juego de caracteres UTF-8 completo

            // Cadena de conexión (DSN - Data Source Name)
            $dns = "mysql:host=$host;dbname=$db;charset=$charset";

            // Opciones de configuración de PDO
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Los resultados se devuelven como arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                   // Usa consultas preparadas reales (más seguro contra inyección SQL)
            ];

            try {
                // Intenta crear la conexión PDO
                self::$instance = new PDO($dns, $user, $pass, $options);
            } catch (PDOException $e) {
                // Si falla, relanza la excepción para que sea manejada más arriba
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

        // Devuelve la conexión (ya sea recién creada o la existente)
        return self::$instance;
    }
}
