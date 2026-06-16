<?php
// =============================================================================
// MODELO Usuario
// =============================================================================
// Propósito: Gestiona todas las operaciones de la tabla 'usuarios' en la BD:
//            crear, leer, autenticar, actualizar y eliminar usuarios.
// Extiende la clase abstracta Model que provee la conexión PDO ($this->db).
// =============================================================================
namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    // Crea un nuevo usuario con contraseña hasheada con BCRYPT
    public function crear(string $username, string $password, string $nombre, string $email, ?string $telefono = null, int $rol_id = 2): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT); // Genera hash seguro de la contraseña
        $sql = "INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);                    // Prepara consulta parametrizada
        return $stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]); // Ejecuta
    }

    // Obtiene todos los usuarios activos con su nombre de rol (JOIN con tabla roles)
    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT u.id, u.username, u.nombre, u.email, u.telefono, u.activo, u.ultimo_acceso, u.created_at, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.rol_id = r.id ORDER BY u.nombre");
        return $stmt->fetchAll(); // Devuelve array de todos los usuarios
    }

    // Obtiene un usuario específico por su ID
    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(); // Devuelve el usuario o false si no existe
    }

    // Obtiene un usuario por su nombre de usuario (solo si está activo)
    public function obtenerPorUsername(string $username): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = TRUE");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // Autentica un usuario: verifica contraseña y actualiza último acceso
    public function autenticar(string $username, string $password): array|false
    {
        $usuario = $this->obtenerPorUsername($username); // Busca el usuario por username
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // Si la contraseña coincide, actualiza la fecha de último acceso
            $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            return $usuario; // Devuelve los datos del usuario autenticado
        }
        return false; // Credenciales inválidas
    }

    // Actualiza los datos de un usuario (nombre, email, teléfono, rol, estado)
    public function actualizar(int $id, string $nombre, string $email, ?string $telefono = null, ?int $rol_id = null, bool $activo = true): bool
    {
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol_id = COALESCE(?, rol_id), activo = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $email, $telefono, $rol_id, $activo, $id]);
    }

    // Actualiza solo la contraseña de un usuario (genera nuevo hash)
    public function actualizarPassword(int $id, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT); // Hashea la nueva contraseña
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    // Elimina un usuario de la base de datos por su ID
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
