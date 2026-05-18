<?php
// ============================================================
// MODELO: CRUD DE ASESORÍAS LEGALES
// ============================================================
// Este archivo contiene funciones para operaciones CRUD sobre
// la tabla 'asesorias' de la base de datos. Gestiona el registro
// de asesorías jurídicas gratuitas ofrecidas a ciudadanos.
//
// Todas las funciones reciben la conexión PDO como primer
// parámetro y usan consultas preparadas con placeholders (?)
// para prevenir inyecciones SQL.

// Incluir la configuración de la base de datos.
// Esto establece la conexión PDO en la variable $pdo.
require_once __DIR__.'/../../Config/database.php';

// ============================================
// FUNCIÓN: CREAR ASESORÍA
// ============================================
// Registra una nueva asesoría legal en el sistema.
//
// Parámetros:
//   $pdo        — Objeto de conexión PDO
//   $ciudadano  — Nombre completo del ciudadano que solicita asesoría
//   $cedula     — Número de cédula de identidad del ciudadano
//   $documento  — Tipo de documento/asesoría (ej: "Consulta Laboral")
//   $descripcion — Descripción o motivo de la consulta
//   $estado     — Estado inicial de la asesoría ('Pendiente' por defecto)
//   $usuario_id — ID del usuario que registra la asesoría (opcional)
//
// Retorna:
//   true si la inserción fue exitosa, false en caso contrario
function crearAsesoria($pdo, $ciudadano, $cedula, $documento, $descripcion, $estado = 'Pendiente', $usuario_id = null) {
    // Consulta SQL de inserción con 7 placeholders.
    // fecha_registro usa NOW() para la fecha/hora actual del servidor MySQL.
    $sql = "INSERT INTO asesorias (ciudadano, cedula, documento, descripcion, estado, usuario_id, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $usuario_id]);
}

// ============================================
// FUNCIÓN: OBTENER TODAS LAS ASESORÍAS
// ============================================
// Retorna todas las asesorías registradas, ordenadas por fecha
// de registro descendente (más recientes primero).
// Incluye el nombre del usuario que registró cada asesoría
// mediante un LEFT JOIN con la tabla 'usuarios'.
//
// Retorna:
//   Array de arrays asociativos con los datos de cada asesoría
function obtenerAsesorias($pdo) {
    // LEFT JOIN: si el usuario fue eliminado, la asesoría igual se muestra
    // (usuario_registro será null en ese caso).
    // ORDER BY a.fecha_registro DESC: más recientes primero.
    $stmt = $pdo->query("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha_registro DESC");
    return $stmt->fetchAll();
}

// ============================================
// FUNCIÓN: OBTENER ASESORÍAS POR ESTADO
// ============================================
// Filtra las asesorías por su estado actual (Pendiente,
// Finalizada, Archivada, etc.).
//
// Parámetros:
//   $pdo    — Conexión PDO
//   $estado — Estado por el que filtrar
//
// Retorna:
//   Array de asesorías que coinciden con el estado
function obtenerAsesoriasPorEstado($pdo, $estado) {
    // Consulta preparada con WHERE para filtrar por estado
    $stmt = $pdo->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.estado = ? ORDER BY a.fecha_registro DESC");
    $stmt->execute([$estado]);
    return $stmt->fetchAll();
}

// ============================================
// FUNCIÓN: OBTENER ASESORÍA POR ID
// ============================================
// Busca una asesoría específica por su ID.
//
// Parámetros:
//   $pdo — Conexión PDO
//   $id  — ID de la asesoría
//
// Retorna:
//   Array asociativo con los datos de la asesoría, o false
function obtenerAsesoriaPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============================================
// FUNCIÓN: BUSCAR ASESORÍAS POR CÉDULA
// ============================================
// Busca asesorías donde la cédula del ciudadano contenga
// el texto buscado (búsqueda parcial con LIKE).
//
// Parámetros:
//   $pdo    — Conexión PDO
//   $cedula — Texto a buscar en la cédula
//
// Retorna:
//   Array de asesorías que coinciden con la búsqueda
function buscarAsesoriasPorCedula($pdo, $cedula) {
    // LIKE %$cedula% busca coincidencias parciales.
    // El % alrededor del valor significa "cualquier texto antes/después".
    // NOTA: Al interpolar $cedula directamente en la cadena SQL,
    // el valor ya está escapado por la consulta preparada.
    $stmt = $pdo->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.cedula LIKE ? ORDER BY a.fecha_registro DESC");
    $stmt->execute(["%$cedula%"]);
    return $stmt->fetchAll();
}

// ============================================
// FUNCIÓN: ACTUALIZAR ASESORÍA
// ============================================
// Actualiza todos los campos editables de una asesoría.
// Si el nuevo estado es 'Finalizada' o 'Archivada', establece
// la fecha de cierre automáticamente.
//
// Parámetros:
//   $pdo         — Conexión PDO
//   $id          — ID de la asesoría a actualizar
//   $ciudadano   — Nombre actualizado del ciudadano
//   $cedula      — Cédula actualizada
//   $documento   — Tipo de documento actualizado
//   $descripcion — Descripción actualizada
//   $estado      — Nuevo estado (Pendiente, Finalizada, etc.)
//
// Retorna:
//   true si la actualización fue exitosa
function actualizarAsesoria($pdo, $id, $ciudadano, $cedula, $documento, $descripcion, $estado) {
    // Si el estado indica que se cierra el caso, establecer fecha_cierre.
    // date('Y-m-d H:i:s') formatea la fecha actual de PHP.
    // Si no es estado final, se deja null (COALESCE mantendrá el valor actual).
    $fecha_cierre = ($estado === 'Finalizada' || $estado === 'Archivada') ? date('Y-m-d H:i:s') : null;

    // UPDATE con COALESCE para fecha_cierre:
    // Si $fecha_cierre es null, mantiene el valor actual de la columna.
    // Si $fecha_cierre tiene una fecha, la actualiza.
    $sql = "UPDATE asesorias SET ciudadano = ?, cedula = ?, documento = ?, descripcion = ?, estado = ?, fecha_cierre = COALESCE(?, fecha_cierre) WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $fecha_cierre, $id]);
}

// ============================================
// FUNCIÓN: ELIMINAR ASESORÍA
// ============================================
// Elimina físicamente una asesoría de la base de datos.
//
// Parámetros:
//   $pdo — Conexión PDO
//   $id  — ID de la asesoría a eliminar
//
// Retorna:
//   true si la eliminación fue exitosa
function eliminarAsesoria($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM asesorias WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============================================
// FUNCIÓN: CONTAR ASESORÍAS POR ESTADO
// ============================================
// Agrupa y cuenta las asesorías según su estado actual.
// Útil para mostrar gráficos o resúmenes estadísticos.
//
// Retorna:
//   Array de arrays asociativos: ['estado' => 'Pendiente', 'total' => 5]
function contarAsesoriasPorEstado($pdo) {
    // GROUP BY estado agrupa por cada valor único de estado.
    // COUNT(*) cuenta cuántas filas hay en cada grupo.
    $stmt = $pdo->query("SELECT estado, COUNT(*) AS total FROM asesorias GROUP BY estado");
    return $stmt->fetchAll();
}
