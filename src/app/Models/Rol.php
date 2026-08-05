<?php

namespace App\Models;

use App\Core\Model;

class Rol extends Model
{
    private int $id = 0;
    private string $nombreRol = '';

    private const MIN_NOMBRE_ROL = 2;
    private const MAX_NOMBRE_ROL = 50;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getNombreRol(): string
    {
        return $this->nombreRol;
    }

    public function setNombreRol(string $nombreRol): void
    {
        $nombreRol = $this->sanitizeString($nombreRol);
        $this->validateNotEmpty($nombreRol, 'nombre de rol');
        $this->validateMinLength($nombreRol, 'nombre de rol', self::MIN_NOMBRE_ROL);
        $this->validateLength($nombreRol, 'nombre de rol', self::MAX_NOMBRE_ROL);
        $this->nombreRol = $nombreRol;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'nombre_rol'  => $this->nombreRol,
        ];
    }

    public static function fromArray(array $data): self
    {
        $rol = new self();
        $rol->setId((int)($data['id'] ?? 0));
        $rol->setNombreRol($data['nombre_rol'] ?? $data['nombre'] ?? '');
        return $rol;
    }

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
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT id, nombre_rol AS nombre FROM roles WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crearRol(string $nombre_rol): bool
    {
        $this->setNombreRol($nombre_rol);
        $sql = "INSERT INTO roles (nombre_rol) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->nombreRol, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarRol(int $id, string $nombre_rol): bool
    {
        $this->setId($id);
        $this->setNombreRol($nombre_rol);
        $sql = "UPDATE roles SET nombre_rol = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->nombreRol, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarRol(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $sql = "SELECT COUNT(*) AS total FROM rol_usuarios WHERE fk_rol = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        if ((int)$fila['total'] > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function obtenerPermisos(): array
    {
        $stmt = $this->db->query("SELECT id, permisos AS nombre FROM permisos ORDER BY permisos");
        return $stmt->fetchAll();
    }

    public function obtenerPermisosPorRol(int $rol_id): array
    {
        $rol_id = $this->sanitizeInt($rol_id);
        $sql = "SELECT fk_permiso AS permiso_id FROM permisos_rol WHERE fk_rol = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $rol_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_column($rows, 'permiso_id');
    }

    public function guardarPermisosRol(int $rol_id, array $permiso_ids): bool
    {
        $rol_id = $this->sanitizeInt($rol_id);
        $permiso_ids = array_map('intval', $permiso_ids);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM permisos_rol WHERE fk_rol = ?");
            $stmt->bindParam(1, $rol_id, PDO::PARAM_INT);
            $stmt->execute();
            if (!empty($permiso_ids)) {
                $sql = "INSERT INTO permisos_rol (fk_rol, fk_permiso) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $rol_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $pid, PDO::PARAM_INT);
                foreach ($permiso_ids as $pid) {
                    $stmt->execute();
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
        $usuario_id = $this->sanitizeInt($usuario_id);
        $rol_id = $this->sanitizeInt($rol_id);
        $stmt = $this->db->prepare("UPDATE usuarios SET fk_rol_usuario = ? WHERE id = ?");
        $stmt->bindParam(1, $rol_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $usuario_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function existeNombreRol(string $nombre, int $excludeId = 0): bool
    {
        $nombre = $this->sanitizeString($nombre);
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM roles WHERE nombre_rol = ? AND id != ?");
            $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
            $stmt->bindParam(2, $excludeId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM roles WHERE nombre_rol = ?");
            $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
            $stmt->execute();
        }
        return (int)$stmt->fetch()['total'] > 0;
    }

    public function totalRoles(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM roles");
        return (int)$stmt->fetch()['total'];
    }

    public function totalPermisos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM permisos");
        return (int)$stmt->fetch()['total'];
    }
}
