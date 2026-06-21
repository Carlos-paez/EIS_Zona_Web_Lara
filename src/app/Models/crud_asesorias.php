<?php
require_once __DIR__.'/../../Config/database.php';

function obtenerOcrearCliente($pdo, $cedula, $nombre, $apellido = '') {
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cedula = ?");
    $stmt->execute([$cedula]);
    $cliente = $stmt->fetch();
    if ($cliente) return (int)$cliente['id'];
    $stmt = $pdo->prepare("INSERT INTO clientes (cedula, nombre, apellido) VALUES (?, ?, ?)");
    $stmt->execute([$cedula, $nombre, $apellido]);
    return (int)$pdo->lastInsertId();
}

function obtenerTipoAsesoria($pdo, $documento) {
    $stmt = $pdo->prepare("SELECT id FROM tipo_asesoria WHERE LOWER(tipo) = LOWER(?)");
    $stmt->execute([$documento]);
    $tipo = $stmt->fetch();
    return $tipo ? (int)$tipo['id'] : null;
}

function crearAsesoria($pdo, $ciudadano, $cedula, $documento, $descripcion) {
    $nombre_partes = explode(' ', $ciudadano, 2);
    $nombre = $nombre_partes[0];
    $apellido = $nombre_partes[1] ?? '';
    $fk_cliente = obtenerOcrearCliente($pdo, $cedula, $nombre, $apellido);
    $fk_tipo_asesoria = obtenerTipoAsesoria($pdo, $documento);
    $sql = "INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente, fk_tipo_asesoria) VALUES (?, ?, CURDATE(), ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$documento, $descripcion, $fk_cliente, $fk_tipo_asesoria]);
}

function obtenerAsesorias($pdo) {
    $stmt = $pdo->query("
        SELECT a.id, a.documento, a.descripcion, a.fecha,
               c.cedula, CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        LEFT JOIN clientes c ON a.fk_cliente = c.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        ORDER BY a.fecha DESC
    ");
    return $stmt->fetchAll();
}

function obtenerAsesoriasPorEstado($pdo, $estado) {
    $permitido = ($estado === 'Permitido' ? 1 : 0);
    $stmt = $pdo->prepare("
        SELECT a.id, a.documento, a.descripcion, a.fecha,
               c.cedula, CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        LEFT JOIN clientes c ON a.fk_cliente = c.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        WHERE ta.permitido = ?
        ORDER BY a.fecha DESC
    ");
    $stmt->execute([$permitido]);
    return $stmt->fetchAll();
}

function obtenerAsesoriaPorId($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT a.*, c.cedula, CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        LEFT JOIN clientes c ON a.fk_cliente = c.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function buscarAsesoriasPorCedula($pdo, $cedula) {
    $stmt = $pdo->prepare("
        SELECT a.id, a.documento, a.descripcion, a.fecha,
               c.cedula, CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        INNER JOIN clientes c ON a.fk_cliente = c.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        WHERE c.cedula LIKE ?
        ORDER BY a.fecha DESC
    ");
    $stmt->execute(["%$cedula%"]);
    return $stmt->fetchAll();
}

function actualizarAsesoria($pdo, $id, $documento, $descripcion) {
    $fk_tipo_asesoria = obtenerTipoAsesoria($pdo, $documento);
    $sql = "UPDATE asesoria SET documento = ?, descripcion = ?, fk_tipo_asesoria = COALESCE(?, fk_tipo_asesoria) WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$documento, $descripcion, $fk_tipo_asesoria, $id]);
}

function eliminarAsesoria($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM asesoria WHERE id = ?");
    return $stmt->execute([$id]);
}

function contarAsesoriasPorEstado($pdo) {
    $stmt = $pdo->query("
        SELECT CASE WHEN ta.permitido = 1 THEN 'Permitido' ELSE 'Denegado' END AS estado, COUNT(*) AS total
        FROM asesoria a
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        GROUP BY ta.permitido
    ");
    return $stmt->fetchAll();
}
