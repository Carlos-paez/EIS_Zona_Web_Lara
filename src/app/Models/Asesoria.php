<?php
namespace App\Models;

use App\Core\Model;

class Asesoria extends Model
{
    public function crear(string $ciudadano, string $cedula, string $documento, string $descripcion, string $estado = 'Pendiente', ?int $usuario_id = null): bool
    {
        $sql = "INSERT INTO asesorias (ciudadano, cedula, documento, descripcion, estado, usuario_id, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $usuario_id]);
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha_registro DESC");
        return $stmt->fetchAll();
    }

    public function obtenerPorEstado(string $estado): array
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.estado = ? ORDER BY a.fecha_registro DESC");
        $stmt->execute([$estado]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarPorCedula(string $cedula): array
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario_registro FROM asesorias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.cedula LIKE ? ORDER BY a.fecha_registro DESC");
        $stmt->execute(["%$cedula%"]);
        return $stmt->fetchAll();
    }

    public function actualizar(int $id, string $ciudadano, string $cedula, string $documento, string $descripcion, string $estado): bool
    {
        $fecha_cierre = ($estado === 'Finalizada' || $estado === 'Archivada') ? date('Y-m-d H:i:s') : null;
        $sql = "UPDATE asesorias SET ciudadano = ?, cedula = ?, documento = ?, descripcion = ?, estado = ?, fecha_cierre = COALESCE(?, fecha_cierre) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ciudadano, $cedula, $documento, $descripcion, $estado, $fecha_cierre, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM asesorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contarPorEstado(): array
    {
        $stmt = $this->db->query("SELECT estado, COUNT(*) AS total FROM asesorias GROUP BY estado");
        return $stmt->fetchAll();
    }
}
