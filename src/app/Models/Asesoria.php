<?php
namespace App\Models;

use App\Core\Model;

class Asesoria extends Model
{
    private function obtenerOcrearCliente(string $cedula, string $nombre, string $apellido = ''): int
    {
        $stmt = $this->db->prepare("SELECT id FROM clientes WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $cliente = $stmt->fetch();
        if ($cliente) {
            return (int)$cliente['id'];
        }
        $stmt = $this->db->prepare("INSERT INTO clientes (cedula, nombre, apellido) VALUES (?, ?, ?)");
        $stmt->execute([$cedula, $nombre, $apellido]);
        return (int)$this->db->lastInsertId();
    }

    private function obtenerTipoAsesoria(string $documento): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM tipo_asesoria WHERE LOWER(tipo) = LOWER(?)");
        $stmt->execute([$documento]);
        $tipo = $stmt->fetch();
        return $tipo ? (int)$tipo['id'] : null;
    }

    public function crear(string $ciudadano, string $cedula, string $documento, string $descripcion): bool
    {
        $nombre_partes = explode(' ', $ciudadano, 2);
        $nombre = $nombre_partes[0];
        $apellido = $nombre_partes[1] ?? '';

        $fk_cliente = $this->obtenerOcrearCliente($cedula, $nombre, $apellido);
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($documento);

        $sql = "INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente, fk_tipo_asesoria) VALUES (?, ?, CURDATE(), ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$documento, $descripcion, $fk_cliente, $fk_tipo_asesoria]);
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN clientes c ON a.fk_cliente = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            ORDER BY a.fecha DESC
        ");
        return $stmt->fetchAll();
    }

    public function obtenerPorEstado(string $estado): array
    {
        $permitido = ($estado === 'Permitido' ? 1 : 0);
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
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

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT a.*, c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN clientes c ON a.fk_cliente = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarPorCedula(string $cedula): array
    {
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
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

    public function actualizar(int $id, string $documento, string $descripcion): bool
    {
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($documento);
        $sql = "UPDATE asesoria SET documento = ?, descripcion = ?, fk_tipo_asesoria = COALESCE(?, fk_tipo_asesoria) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$documento, $descripcion, $fk_tipo_asesoria, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM asesoria WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contarPorEstado(): array
    {
        $stmt = $this->db->query("
            SELECT CASE WHEN ta.permitido = 1 THEN 'Permitido' ELSE 'Denegado' END AS estado, COUNT(*) AS total
            FROM asesoria a
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            GROUP BY ta.permitido
        ");
        return $stmt->fetchAll();
    }
}
