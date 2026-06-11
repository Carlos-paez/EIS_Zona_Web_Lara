<?php
namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    public function crear(string $username, string $password, string $nombre, string $email, ?string $telefono = null, int $rol_id = 2): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]);
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT u.id, u.username, u.nombre, u.email, u.telefono, u.activo, u.ultimo_acceso, u.created_at, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.rol_id = r.id ORDER BY u.nombre");
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function obtenerPorUsername(string $username): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = TRUE");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function autenticar(string $username, string $password): array|false
    {
        $usuario = $this->obtenerPorUsername($username);
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            return $usuario;
        }
        return false;
    }

    public function actualizar(int $id, string $nombre, string $email, ?string $telefono = null, ?int $rol_id = null, bool $activo = true): bool
    {
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol_id = COALESCE(?, rol_id), activo = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $email, $telefono, $rol_id, $activo, $id]);
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
