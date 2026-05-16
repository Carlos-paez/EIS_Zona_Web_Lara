<?php
// Modelo CRUD para la tabla 'usuarios'
// Proporciona funciones básicas de Crear, Leer, Actualizar y Eliminar

require_once __DIR__.'/../../Config/database.php'; // Incluye la conexión a la base de datos ($pdo)

/**
 * Crea un nuevo usuario en la base de datos
 * @param PDO $pdo   Conexión activa a la base de datos
 * @param string $nombre Nombre del usuario
 * @param string $email  Correo electrónico del usuario
 * @return bool    True si se insertó correctamente, False en caso contrario
 */
function crearUsuario($pdo, $nombre, $email) {
    $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)"; // Consulta SQL con marcadores de posición
    $stmt = $pdo->prepare($sql);                                 // Prepara la consulta para evitar inyección SQL
    return $stmt->execute([$nombre, $email]);                    // Ejecuta con los valores reales
}

/**
 * Obtiene todos los usuarios registrados
 * @param PDO $pdo Conexión activa a la base de datos
 * @return array   Array asociativo con todos los registros
 */
function obtenerUsuarios($pdo) {
    $stmt = $pdo->query("SELECT * FROM usuarios"); // Ejecuta consulta directa (sin parámetros externos)
    return $stmt->fetchAll();                       // Devuelve todas las filas como array asociativo
}

/**
 * Obtiene un usuario específico por su ID
 * @param PDO $pdo Conexión activa a la base de datos
 * @param int $id  Identificador único del usuario
 * @return array|false Array asociativo del usuario o False si no existe
 */
function obtenerUsuarioPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?"); // Consulta preparada con marcador
    $stmt->execute([$id]);                                         // Ejecuta pasando el ID como parámetro
    return $stmt->fetch();                                         // Devuelve solo la primera fila encontrada
}

/**
 * Actualiza los datos de un usuario existente
 * @param PDO $pdo   Conexión activa a la base de datos
 * @param int $id    ID del usuario a actualizar
 * @param string $nombre Nuevo nombre
 * @param string $email  Nuevo correo electrónico
 * @return bool     True si se actualizó correctamente
 */
function actualizarUsuario($pdo, $id, $nombre, $email) {
    $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?"; // Consulta de actualización
    $stmt = $pdo->prepare($sql);                                     // Prepara la consulta
    return $stmt->execute([$nombre, $email, $id]);                   // Ejecuta con los nuevos valores
}

/**
 * Elimina un usuario de la base de datos
 * @param PDO $pdo Conexión activa a la base de datos
 * @param int $id  ID del usuario a eliminar
 * @return bool   True si se eliminó correctamente
 */
function eliminarUsuario($pdo, $id) {
    $sql = "DELETE FROM usuarios WHERE id = ?"; // Consulta de eliminación
    $stmt = $pdo->prepare($sql);                // Prepara la consulta
    return $stmt->execute([$id]);               // Ejecuta con el ID del usuario a eliminar
}
?>