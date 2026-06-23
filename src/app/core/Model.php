<?php
// =============================================================================
// CLASE ABSTRACTA Model
// =============================================================================
// Propósito: Clase base para todos los modelos de la aplicación.
//            Proporciona la conexión a la base de datos PDO a todas las
//            subclases mediante el patrón Singleton (Database::getConnection()).
// =============================================================================

// Declara el namespace 'App\Core' para organizar esta clase dentro de la aplicación
namespace App\Core;

// Importa la clase PDO de PHP para usarla como tipo en la propiedad $db
use PDO;

/**
 * Clase abstracta Model - Clase base para todos los modelos de datos.
 * 
 * Todos los modelos del negocio (Usuario, Producto, Venta, etc.) heredan
 * de esta clase para tener acceso automático a la conexión PDO a través
 * del patrón Singleton implementado en Database.
 */
abstract class Model
{
    /**
     * Instancia de la conexión PDO a la base de datos.
     * 
     * Es protegida para que las subclases puedan acceder directamente
     * a la conexión y ejecutar sus consultas SQL.
     *
     * @var PDO Objeto de conexión PDO
     */
    protected PDO $db;

    /**
     * Constructor de la clase Model.
     * 
     * Obtiene la instancia única de la conexión a la base de datos
     * mediante el método estático Database::getConnection(). De esta
     * forma, todos los modelos comparten la misma conexión PDO.
     */
    public function __construct()
    {
        // Llama al método estático getConnection() de la clase Database
        // Este método implementa el patrón Singleton: solo hay una conexión en toda la app
        $this->db = Database::getConnection();
    }
}
