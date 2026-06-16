<?php
// =============================================================================
// MODELO Asesoria (Asesoría Legal)
// =============================================================================
// Propósito: Gestiona las operaciones de la tabla 'asesorias' para el módulo
//            de asesoría legal. Permite registrar ciudadanos, sus documentos,
//            realizar búsquedas, actualizar estados y obtener estadísticas.
// =============================================================================
namespace App\Models;

use App\Core\Model;

class Asesoria extends Model
{
    // Crea un nuevo registro de asesoría legal
    public function crear(string $ciudadano, string $cedula, string $documento, string $descripcion, string $estado = 'Pendiente', ?int $usuario_id = null): bool
    {
        $sql = "INSERT INTO asesorias (ciudadano, cedula, documento, descripcion, estado, usuario_id, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $usuario_id]);
    }

    // Obtiene todas las asesorías registradas, ordenadas por fecha (más reciente primero)
    public function obtenerTodas(): array
    {
        $stmt = $this->db->query("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha_registro DESC");
        return $stmt->fetchAll();
    }

    // Filtra asesorías por su estado (Pendiente, Finalizada, Archivada, etc.)
    public function obtenerPorEstado(string $estado): array
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.estado = ? ORDER BY a.fecha_registro DESC");
        $stmt->execute([$estado]);
        return $stmt->fetchAll();
    }

    // Obtiene una asesoría específica por su ID
    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Busca asesorías por número de cédula (búsqueda parcial con LIKE)
    public function buscarPorCedula(string $cedula): array
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.cedula LIKE ? ORDER BY a.fecha_registro DESC");
        $stmt->execute(["%$cedula%"]);
        return $stmt->fetchAll();
    }

    // Actualiza los datos de una asesoría y registra fecha de cierre si corresponde
    public function actualizar(int $id, string $ciudadano, string $cedula, string $documento, string $descripcion, string $estado): bool
    {
        // Si el estado es 'Finalizada' o 'Archivada', asigna la fecha y hora actual como cierre
        $fecha_cierre = ($estado === 'Finalizada' || $estado === 'Archivada') ? date('Y-m-d H:i:s') : null;
        $sql = "UPDATE asesorias SET ciudadano = ?, cedula = ?, documento = ?, descripcion = ?, estado = ?, fecha_cierre = COALESCE(?, fecha_cierre) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $fecha_cierre, $id]);
    }

    // Elimina una asesoría de la base de datos
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM asesorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Cuenta cuántas asesorías hay en cada estado (agrupado por estado)
    public function contarPorEstado(): array
    {
        $stmt = $this->db->query("SELECT estado, COUNT(*) AS total FROM asesorias GROUP BY estado");
        return $stmt->fetchAll();
    }
}
