<?php
// =============================================================================
// FUNCIONES CRUD DE USUARIOS (Versión basada en funciones sueltas)
// =============================================================================
// Propósito: Proporciona funciones para gestionar usuarios en la tabla
//            'usuarios' usando una conexión PDO pasada como parámetro.
// NOTA: Existe una versión orientada a objetos (Usuario.php) que hace lo mismo.
//       Esta versión está siendo reemplazada gradualmente por la POO.
// =============================================================================

require_once __DIR__.'/../../Config/database.php'; // Trae la conexión $pdo

// Crea un nuevo usuario con contraseña hasheada
function crearUsuario($pdo, $username, $password, $nombre, $email, $telefono = null, $rol_id = 2) {
    $hash = password_hash($password, PASSWORD_BCRYPT); // Genera hash seguro
    $sql = "INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]);
}

// Obtiene todos los usuarios con JOIN a la tabla roles para obtener el nombre del rol
function obtenerUsuarios($pdo) {
    $stmt = $pdo->query("SELECT u.id, u.username, u.nombre, u.email, u.telefono, u.activo, u.ultimo_acceso, u.created_at, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.rol_id = r.id ORDER BY u.nombre");
    return $stmt->fetchAll();
}

// Obtiene un usuario por su ID
function obtenerUsuarioPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Obtiene un usuario por su nombre de usuario (solo si está activo)
function obtenerUsuarioPorUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = TRUE");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// Autentica un usuario verificando contraseña con password_verify
function autenticarUsuario($pdo, $username, $password) {
    $usuario = obtenerUsuarioPorUsername($pdo, $username); // Busca el usuario
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Si la contraseña es correcta, actualiza la fecha de último acceso
        $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        return $usuario;
    }
    return false; // Credenciales inválidas
}

// Actualiza los datos de un usuario
function actualizarUsuario($pdo, $id, $nombre, $email, $telefono = null, $rol_id = null, $activo = true) {
    // COALESCE(?, rol_id) mantiene el rol actual si se pasa null
    $sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol_id = COALESCE(?, rol_id), activo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $email, $telefono, $rol_id, $activo, $id]);
}

// Actualiza solo la contraseña de un usuario
function actualizarPassword($pdo, $id, $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT); // Hashea la nueva contraseña
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
    return $stmt->execute([$hash, $id]);
}

// Elimina un usuario por su ID
function eliminarUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    return $stmt->execute([$id]);
}
