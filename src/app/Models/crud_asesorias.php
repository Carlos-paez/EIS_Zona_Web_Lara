<?php

require_once __DIR__.'/../../Config/database.php';

function crearAsesoria($pdo, $ciudadano, $cedula, $documento, $descripcion, $estado = 'Pendiente', $usuario_id = null) {
    $sql = "INSERT INTO asesorias (ciudadano, cedula, documento, descripcion, estado, usuario_id, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $usuario_id]);
}

function obtenerAsesorias($pdo) {
    $stmt = $pdo->query("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha_registro DESC");
    return $stmt->fetchAll();
}

function obtenerAsesoriasPorEstado($pdo, $estado) {
    $stmt = $pdo->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.estado = ? ORDER BY a.fecha_registro DESC");
    $stmt->execute([$estado]);
    return $stmt->fetchAll();
}

function obtenerAsesoriaPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function buscarAsesoriasPorCedula($pdo, $cedula) {
    $stmt = $pdo->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.cedula LIKE ? ORDER BY a.fecha_registro DESC");
    $stmt->execute(["%$cedula%"]);
    return $stmt->fetchAll();
}

function actualizarAsesoria($pdo, $id, $ciudadano, $cedula, $documento, $descripcion, $estado) {
    $fecha_cierre = ($estado === 'Finalizada' || $estado === 'Archivada') ? date('Y-m-d H:i:s') : null;
    $sql = "UPDATE asesorias SET ciudadano = ?, cedula = ?, documento = ?, descripcion = ?, estado = ?, fecha_cierre = COALESCE(?, fecha_cierre) WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $fecha_cierre, $id]);
}

function eliminarAsesoria($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM asesorias WHERE id = ?");
    return $stmt->execute([$id]);
}

function contarAsesoriasPorEstado($pdo) {
    $stmt = $pdo->query("SELECT estado, COUNT(*) AS total FROM asesorias GROUP BY estado");
    return $stmt->fetchAll();
}
