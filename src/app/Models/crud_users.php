<?php
require_once __DIR__.'/../../Config/database.php';

function crearUsuario($pdo, $user_name, $password, $nombre, $apellido, $email) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_name, $hash, $nombre, $apellido, $email]);
}

function obtenerUsuarios($pdo) {
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

function obtenerUsuarioPorId($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
        FROM usuarios u
        LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
        LEFT JOIN roles r ON ru.fk_rol = r.id
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function obtenerUsuarioPorUsername($pdo, $username) {
    $stmt = $pdo->prepare("
        SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
        FROM usuarios u
        LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
        LEFT JOIN roles r ON ru.fk_rol = r.id
        WHERE u.user_name = ? AND u.estatus = 'activo'
    ");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function autenticarUsuario($pdo, $username, $password) {
    $usuario = obtenerUsuarioPorUsername($pdo, $username);
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        return $usuario;
    }
    return false;
}

function actualizarUsuario($pdo, $id, $nombre, $apellido, $email, $fk_rol_usuario = null, $estatus = 'activo') {
    $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, fk_rol_usuario = COALESCE(?, fk_rol_usuario), estatus = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $apellido, $email, $fk_rol_usuario, $estatus, $id]);
}

function actualizarPassword($pdo, $id, $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
    return $stmt->execute([$hash, $id]);
}

function eliminarUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    return $stmt->execute([$id]);
}
