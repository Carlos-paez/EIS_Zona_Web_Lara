<?php
// Incluye el archivo de configuración de la base de datos para obtener la conexión $pdo
require_once __DIR__.'/../../Config/database.php';

/**
 * Crea un nuevo usuario en la base de datos.
 * La contraseña se almacena hasheada con bcrypt.
 *
 * @param PDO    $pdo       Conexión a la base de datos.
 * @param string $user_name Nombre de usuario único.
 * @param string $password  Contraseña en texto plano.
 * @param string $nombre    Nombre real del usuario.
 * @param string $apellido  Apellido del usuario.
 * @param string $email     Correo electrónico del usuario.
 * @return bool             True si la inserción fue exitosa.
 */
function crearUsuario($pdo, $user_name, $password, $nombre, $apellido, $email) {
    // Genera el hash bcrypt de la contraseña para almacenamiento seguro
    $hash = password_hash($password, PASSWORD_BCRYPT);
    // Sentencia SQL para insertar un nuevo usuario
    $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email, estatus) VALUES (?, ?, ?, ?, ?, '1')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $user_name, PDO::PARAM_STR);
    $stmt->bindParam(2, $hash, PDO::PARAM_STR);
    $stmt->bindParam(3, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(4, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(5, $email, PDO::PARAM_STR);
    return $stmt->execute();
}

/**
 * Obtiene todos los usuarios con el nombre de su rol asociado.
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @return array   Lista de usuarios.
 */
function obtenerUsuarios($pdo) {
    // Consulta con LEFT JOIN a rol_usuarios y roles para incluir el nombre del rol
    $stmt = $pdo->query("
        SELECT u.id, u.user_name AS username, u.nombre, u.apellido, u.email, u.estatus AS activo,
               ru.rol, r.nombre_rol AS rol_nombre
        FROM usuarios u
        LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
        LEFT JOIN roles r ON ru.fk_rol = r.id
        ORDER BY u.nombre
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene un usuario específico por su ID, incluyendo datos del rol.
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $id  ID del usuario.
 * @return array|false Datos del usuario o false si no existe.
 */
function obtenerUsuarioPorId($pdo, $id) {
    // Consulta parametrizada que une usuarios con sus tablas de rol
    $stmt = $pdo->prepare("
        SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
        FROM usuarios u
        LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
        LEFT JOIN roles r ON ru.fk_rol = r.id
        WHERE u.id = ?
    ");
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Obtiene un usuario por su nombre de usuario, solo si está activo.
 *
 * @param PDO    $pdo      Conexión a la base de datos.
 * @param string $username Nombre de usuario.
 * @return array|false     Datos del usuario o false si no existe o está inactivo.
 */
function obtenerUsuarioPorUsername($pdo, $username) {
    // Consulta parametrizada filtrando por user_name y estatus 'activo'
    $stmt = $pdo->prepare("
        SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
        FROM usuarios u
        LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
        LEFT JOIN roles r ON ru.fk_rol = r.id
        WHERE u.user_name = ? AND u.estatus = '1'
    ");
    $stmt->bindParam(1, $username, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Autentica un usuario verificando su contraseña contra el hash almacenado.
 *
 * @param PDO    $pdo      Conexión a la base de datos.
 * @param string $username Nombre de usuario.
 * @param string $password Contraseña en texto plano.
 * @return array|false     Datos del usuario si la autenticación es exitosa, false si falla.
 */
function autenticarUsuario($pdo, $username, $password) {
    // Obtiene el usuario por su nombre de usuario
    $usuario = obtenerUsuarioPorUsername($pdo, $username);
    // Si existe y la contraseña coincide con el hash, retorna los datos
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        return $usuario;
    }
    // Si no coincide o no existe, retorna false
    return false;
}

/**
 * Actualiza los datos de un usuario existente.
 * El campo fk_rol_usuario solo se actualiza si se proporciona un valor no nulo (COALESCE).
 *
 * @param PDO    $pdo            Conexión a la base de datos.
 * @param int    $id             ID del usuario.
 * @param string $nombre         Nuevo nombre.
 * @param string $apellido       Nuevo apellido.
 * @param string $email          Nuevo correo electrónico.
 * @param int|null $fk_rol_usuario Nuevo ID de rol (opcional, null conserva el actual).
 * @param string $estatus        Nuevo estatus ('activo' por defecto).
 * @return bool  True si la actualización fue exitosa.
 */
function actualizarUsuario($pdo, $id, $nombre, $apellido, $email, $fk_rol_usuario = null, $estatus = '1') {
    // SQL con COALESCE: si fk_rol_usuario es NULL, mantiene el valor actual
    $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, fk_rol_usuario = COALESCE(?, fk_rol_usuario), estatus = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(2, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(3, $email, PDO::PARAM_STR);
    $stmt->bindParam(4, $fk_rol_usuario, PDO::PARAM_INT);
    $stmt->bindParam(5, $estatus, PDO::PARAM_STR);
    $stmt->bindParam(6, $id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Actualiza únicamente la contraseña de un usuario.
 *
 * @param PDO    $pdo      Conexión a la base de datos.
 * @param int    $id       ID del usuario.
 * @param string $password Nueva contraseña en texto plano.
 * @return bool  True si la actualización fue exitosa.
 */
function actualizarPassword($pdo, $id, $password) {
    // Genera el hash bcrypt de la nueva contraseña
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
    $stmt->bindParam(1, $hash, PDO::PARAM_STR);
    $stmt->bindParam(2, $id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Elimina un usuario de la base de datos por su ID.
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $id  ID del usuario a eliminar.
 * @return bool    True si la eliminación fue exitosa.
 */
function eliminarUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    return $stmt->execute();
}
