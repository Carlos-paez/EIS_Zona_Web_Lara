<?php
// Incluye el archivo de configuración de la base de datos para obtener la conexión $pdo
require_once __DIR__.'/../../Config/database.php';

/**
 * Obtiene o crea un cliente de asesoría basado en su cédula.
 * Si el cliente ya existe retorna su ID; si no, lo inserta y retorna el nuevo ID.
 *
 * @param PDO    $pdo     Conexión a la base de datos.
 * @param string $cedula  Cédula del cliente.
 * @param string $nombre  Nombre del cliente.
 * @param string $apellido Apellido del cliente (opcional).
 * @return int            ID del cliente.
 */
function obtenerOcrearCliente($pdo, $cedula, $nombre, $apellido = '') {
    // 1. Buscar o crear en la tabla clientes
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cedula = ?");
    $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
    $stmt->execute();
    $cliente = $stmt->fetch();
    if ($cliente) {
        $cliente_id = (int)$cliente['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES (?, ?, ?, '', '')");
        $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
        $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $apellido, PDO::PARAM_STR);
        $stmt->execute();
        $cliente_id = (int)$pdo->lastInsertId();
    }
    // 2. Buscar o crear en cliente_asesoria vinculado al cliente
    $stmt = $pdo->prepare("SELECT id FROM cliente_asesoria WHERE fk_cliente = ?");
    $stmt->bindParam(1, $cliente_id, PDO::PARAM_INT);
    $stmt->execute();
    $ca = $stmt->fetch();
    if ($ca) return (int)$ca['id'];
    $stmt = $pdo->prepare("INSERT INTO cliente_asesoria (fk_cliente, email, rif, tipo) VALUES (?, 'N/A', 'N/A', 'civil')");
    $stmt->bindParam(1, $cliente_id, PDO::PARAM_INT);
    $stmt->execute();
    return (int)$pdo->lastInsertId();
}

/**
 * Obtiene el ID del tipo de asesoría según el nombre (comparación sin distinción de mayúsculas).
 *
 * @param PDO    $pdo       Conexión a la base de datos.
 * @param string $documento Nombre del tipo de asesoría.
 * @return int|null         ID del tipo o null si no se encuentra.
 */
function obtenerTipoAsesoria($pdo, $documento) {
    // Compara usando LOWER para ignorar mayúsculas/minúsculas
    $stmt = $pdo->prepare("SELECT id FROM tipo_asesoria WHERE LOWER(tipo) = LOWER(?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->execute();
    $tipo = $stmt->fetch();
    // Retorna el ID si existe, null en caso contrario
    return $tipo ? (int)$tipo['id'] : null;
}

/**
 * Crea una nueva asesoría en la base de datos.
 * Divide el nombre completo, busca o crea el cliente, determina el tipo y registra la asesoría.
 *
 * @param PDO    $pdo         Conexión a la base de datos.
 * @param string $ciudadano   Nombre completo del ciudadano (nombre y apellido).
 * @param string $cedula      Cédula del ciudadano.
 * @param string $documento   Tipo de documento/asesoría.
 * @param string $descripcion Descripción de la asesoría.
 * @return bool               True si la inserción fue exitosa.
 */
function crearAsesoria($pdo, $ciudadano, $cedula, $documento, $descripcion) {
    // Separa el nombre completo en nombre (índice 0) y apellido (índice 1)
    $nombre_partes = explode(' ', $ciudadano, 2);
    $nombre = $nombre_partes[0];
    $apellido = $nombre_partes[1] ?? '';
    // Obtiene (o crea) el cliente y el tipo de asesoría
    $fk_cliente = obtenerOcrearCliente($pdo, $cedula, $nombre, $apellido);
    $fk_tipo_asesoria = obtenerTipoAsesoria($pdo, $documento);
    // Inserta la asesoría con fecha actual (CURDATE)
    $sql = "INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente_asesoria, fk_tipo_asesoria) VALUES (?, ?, CURDATE(), ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(3, $fk_cliente, PDO::PARAM_INT);
    $stmt->bindParam(4, $fk_tipo_asesoria, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Obtiene todas las asesorías registradas, ordenadas por fecha descendente.
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @return array   Lista de asesorías.
 */
function obtenerAsesorias($pdo) {
    // Consulta con JOIN a cliente_asesoria, clientes y tipo_asesoria
    $stmt = $pdo->query("
        SELECT a.id, a.documento, a.descripcion, a.fecha,
               cli.cedula, CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
        LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        ORDER BY a.fecha DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene asesorías filtradas por estado (Permitido / Denegado).
 *
 * @param PDO    $pdo    Conexión a la base de datos.
 * @param string $estado 'Permitido' o 'Denegado'.
 * @return array  Lista de asesorías filtradas.
 */
function obtenerAsesoriasPorEstado($pdo, $estado) {
    // Convierte el texto a 1 o 0 según el estado
    $permitido = ($estado === 'Permitido' ? 1 : 0);
    // Consulta parametrizada filtrando por ta.permitido
    $stmt = $pdo->prepare("
        SELECT a.id, a.documento, a.descripcion, a.fecha,
               cli.cedula, CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
         FROM asesoria a
        LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
        LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        WHERE ta.permitido = ?
        ORDER BY a.fecha DESC
    ");
    $stmt->bindParam(1, $permitido, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Obtiene una asesoría específica por su ID.
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $id  ID de la asesoría.
 * @return array|false Datos de la asesoría o false si no existe.
 */
function obtenerAsesoriaPorId($pdo, $id) {
    // Consulta parametrizada por ID con JOIN a cliente y tipo
    $stmt = $pdo->prepare("
        SELECT a.*, cli.cedula, CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
        LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        WHERE a.id = ?
    ");
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Busca asesorías por cédula del ciudadano (búsqueda parcial con LIKE).
 *
 * @param PDO    $pdo    Conexión a la base de datos.
 * @param string $cedula Cédula o parte de ella.
 * @return array  Lista de asesorías que coinciden.
 */
function buscarAsesoriasPorCedula($pdo, $cedula) {
    // INNER JOIN con cliente_asesoria para filtrar por cédula con LIKE
    $stmt = $pdo->prepare("
        SELECT a.id, a.documento, a.descripcion, a.fecha,
               cli.cedula, CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
               ta.tipo AS tipo_documento, ta.permitido
        FROM asesoria a
        INNER JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
        INNER JOIN clientes cli ON ca.fk_cliente = cli.id
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        WHERE cli.cedula LIKE ?
        ORDER BY a.fecha DESC
    ");
    // Agrega comodines % para búsqueda parcial
    $patron = "%$cedula%";
    $stmt->bindParam(1, $patron, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Actualiza el documento y descripción de una asesoría.
 * El tipo de asesoría se actualiza automáticamente según el nuevo documento.
 *
 * @param PDO    $pdo         Conexión a la base de datos.
 * @param int    $id          ID de la asesoría.
 * @param string $documento   Nuevo tipo de documento.
 * @param string $descripcion Nueva descripción.
 * @return bool  True si la actualización fue exitosa.
 */
function actualizarAsesoria($pdo, $id, $documento, $descripcion) {
    // Obtiene el ID del nuevo tipo de asesoría
    $fk_tipo_asesoria = obtenerTipoAsesoria($pdo, $documento);
    // Actualiza; COALESCE conserva el tipo anterior si no se encuentra el nuevo
    $sql = "UPDATE asesoria SET documento = ?, descripcion = ?, fk_tipo_asesoria = COALESCE(?, fk_tipo_asesoria) WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(3, $fk_tipo_asesoria, PDO::PARAM_INT);
    $stmt->bindParam(4, $id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Elimina una asesoría por su ID.
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $id  ID de la asesoría a eliminar.
 * @return bool    True si la eliminación fue exitosa.
 */
function eliminarAsesoria($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM asesoria WHERE id = ?");
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Cuenta las asesorías agrupadas por estado (Permitido / Denegado).
 *
 * @param PDO $pdo Conexión a la base de datos.
 * @return array   Arreglo con cada estado y su total.
 */
function contarAsesoriasPorEstado($pdo) {
    // Usa CASE para convertir permitido (1/0) a texto legible y los cuenta
    $stmt = $pdo->query("
        SELECT CASE WHEN ta.permitido = 1 THEN 'Permitido' ELSE 'Denegado' END AS estado, COUNT(*) AS total
        FROM asesoria a
        LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
        GROUP BY ta.permitido
    ");
    return $stmt->fetchAll();
}
