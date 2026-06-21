<?php
namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    public function crear(string $user_name, string $password, string $nombre, string $apellido, string $email): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$user_name, $hash, $nombre, $apellido, $email]);
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("
            SELECT u.id, u.user_name AS username, u.nombre, u.apellido, u.email, u.estatus AS activo,
                   ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            ORDER BY u.nombre
        ");
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function obtenerPorUsername(string $username): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            WHERE u.user_name = ? AND u.estatus = 'activo'
        ");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function autenticar(string $username, string $password): array|false
    {
        $usuario = $this->obtenerPorUsername($username);
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            return $usuario;
        }
        return false;
    }

    public function actualizar(int $id, string $nombre, string $apellido, string $email, ?int $fk_rol_usuario = null, string $estatus = 'activo'): bool
    {
        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, fk_rol_usuario = COALESCE(?, fk_rol_usuario), estatus = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $apellido, $email, $fk_rol_usuario, $estatus, $id]);
    }

    public function actualizarPassword(int $id, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
