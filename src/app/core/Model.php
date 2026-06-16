<?php
// =============================================================================
// CLASE ABSTRACTA Model
// =============================================================================
// Propósito: Clase base para todos los modelos de la aplicación.
//            Proporciona la conexión a la base de datos PDO a todas las
//            subclases mediante el patrón Singleton (Database::getConnection()).
// =============================================================================
namespace App\Core;

use PDO;

abstract class Model
{
    // Propiedad protegida que almacena la conexión PDO (accesible desde las subclases)
    protected PDO $db;

    // Constructor: obtiene la instancia única de la conexión a la base de datos
    public function __construct()
    {
        // Llama al método estático getConnection() de la clase Database
        // Este método implementa el patrón Singleton: solo hay una conexión en toda la app
        $this->db = Database::getConnection();
    }
}
