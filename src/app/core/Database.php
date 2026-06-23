<?php
// =============================================================================
// CLASE Database (Conexión a Base de Datos - Patrón Singleton)
// =============================================================================
// Propósito: Gestionar una única conexión PDO a MySQL para toda la aplicación.
//            Implementa el patrón Singleton para evitar múltiples conexiones
//            que consuman recursos innecesarios.
// =============================================================================

// Declara el namespace 'App\Core' para organizar esta clase dentro de la aplicación
namespace App\Core;

// Importa la clase PDO de PHP para poder usarla sin escribir el namespace completo
use PDO;
// Importa la clase PDOException para manejar errores de base de datos
use PDOException;

/**
 * Clase Database - Implementa el patrón Singleton para la conexión PDO a MySQL.
 * 
 * Proporciona un único punto de acceso a la conexión de base de datos en toda
 * la aplicación, evitando la creación de múltiples conexiones que consuman
 * recursos innecesarios.
 */
class Database
{
    /**
     * Única instancia de la conexión PDO (patrón Singleton).
     * 
     * Almacena la conexión PDO una vez creada. Es estática y privada para
     * garantizar que solo exista una instancia y solo se acceda a través
     * del método getConnection().
     *
     * @var PDO|null Inicialmente null, luego almacena el objeto PDO
     */
    private static ?PDO $instance = null;

    /**
     * Obtiene o crea la conexión PDO a la base de datos (Singleton).
     * 
     * Si la conexión ya existe (no es null), la devuelve directamente.
     * Si no existe, crea una nueva conexión PDO con la configuración
     * definida y la almacena en la propiedad estática $instance.
     *
     * @return PDO Objeto de conexión PDO activo
     * @throws PDOException Si falla la conexión a la base de datos
     */
    public static function getConnection(): PDO
    {
        // Solo crea la conexión si aún no existe (primera vez que se llama)
        if (self::$instance === null) {
            // Configuración del servidor MySQL
            $host = 'localhost';               // Dirección del servidor
            $db   = 'zona_web_lara';           // Nombre de la base de datos
            $user = 'root';                    // Usuario de MySQL
            $pass = '';                        // Contraseña (vacía en desarrollo)
            $charset = 'utf8mb4';              // Juego de caracteres UTF-8 completo

            // Cadena de conexión (DSN - Data Source Name) con los parámetros configurados
            $dns = "mysql:host=$host;dbname=$db;charset=$charset";

            // Opciones de configuración de PDO
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Los resultados se devuelven como arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                   // Usa consultas preparadas reales (más seguro contra inyección SQL)
            ];

            try {
                // Intenta crear la conexión PDO con las credenciales y opciones definidas
                self::$instance = new PDO($dns, $user, $pass, $options);
            } catch (PDOException $e) {
                // Si falla la conexión, relanza la excepción para que sea manejada más arriba
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

        // Devuelve la conexión (ya sea recién creada o la que ya existía)
        return self::$instance;
    }
}
