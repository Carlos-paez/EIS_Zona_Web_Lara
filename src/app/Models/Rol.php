<?php
namespace App\Models;

use App\Core\Model;

class Rol extends Model
{
    public function listarRoles(): array
    {
        $stmt = $this->db->query("
            SELECT r.id, r.nombre_rol AS nombre,
                   (SELECT COUNT(*) FROM rol_usuarios ru WHERE ru.fk_rol = r.id) AS total_usuarios
            FROM roles r ORDER BY r.nombre_rol
        ");
        return $stmt->fetchAll();
    }

    public function obtenerRolPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT id, nombre_rol AS nombre FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crearRol(string $nombre_rol): bool
    {
        $sql = "INSERT INTO roles (nombre_rol) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre_rol]);
    }

    public function actualizarRol(int $id, string $nombre_rol): bool
    {
        $sql = "UPDATE roles SET nombre_rol = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre_rol, $id]);
    }

    public function eliminarRol(int $id): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM rol_usuarios WHERE fk_rol = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        if ((int)$fila['total'] > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function obtenerPermisos(): array
    {
        $stmt = $this->db->query("SELECT id, permisos AS nombre FROM permisos ORDER BY permisos");
        return $stmt->fetchAll();
    }

    public function obtenerPermisosPorRol(int $rol_id): array
    {
        $sql = "SELECT fk_permiso AS permiso_id FROM permisos_rol WHERE fk_rol = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rol_id]);
        $rows = $stmt->fetchAll();
        return array_column($rows, 'permiso_id');
    }

    public function guardarPermisosRol(int $rol_id, array $permiso_ids): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM permisos_rol WHERE fk_rol = ?");
            $stmt->execute([$rol_id]);
            if (!empty($permiso_ids)) {
                $sql = "INSERT INTO permisos_rol (fk_rol, fk_permiso) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                foreach ($permiso_ids as $pid) {
                    $stmt->execute([$rol_id, (int)$pid]);
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function obtenerRoles(): array
    {
        $stmt = $this->db->query("SELECT id, nombre_rol AS nombre FROM roles ORDER BY nombre_rol");
        return $stmt->fetchAll();
    }

    public function obtenerUsuarios(): array
    {
        $stmt = $this->db->query("
            SELECT u.id, u.user_name AS username, u.nombre, u.apellido, u.email, u.estatus AS activo,
                   r.nombre_rol AS rol
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            ORDER BY u.nombre
        ");
        return $stmt->fetchAll();
    }

    public function asignarRolAUsuario(int $usuario_id, int $rol_id): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET fk_rol_usuario = ? WHERE id = ?");
        return $stmt->execute([$rol_id, $usuario_id]);
    }

    public function totalRoles(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM roles");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function totalPermisos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM permisos");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }
}
