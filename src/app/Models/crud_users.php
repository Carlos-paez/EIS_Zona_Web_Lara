<?php

require_once __DIR__.'/../../Config/database.php';

function crearUsuario($pdo, $username, $password, $nombre, $email, $telefono = null, $rol_id = 2) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]);
}

function obtenerUsuarios($pdo) {
    $stmt = $pdo->query("SELECT u.id, u.username, u.nombre, u.email, u.telefono, u.activo, u.ultimo_acceso, u.created_at, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.rol_id = r.id ORDER BY u.nombre");
    return $stmt->fetchAll();
}

function obtenerUsuarioPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function obtenerUsuarioPorUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = TRUE");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function autenticarUsuario($pdo, $username, $password) {
    $usuario = obtenerUsuarioPorUsername($pdo, $username);
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        return $usuario;
    }
    return false;
}

function actualizarUsuario($pdo, $id, $nombre, $email, $telefono = null, $rol_id = null, $activo = true) {
    $sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol_id = COALESCE(?, rol_id), activo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $email, $telefono, $rol_id, $activo, $id]);
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
