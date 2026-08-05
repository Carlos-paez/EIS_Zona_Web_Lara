<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    private int $id = 0;
    private string $userName = '';
    private string $passwordHash = '';
    private string $nombre = '';
    private string $apellido = '';
    private string $email = '';
    private string $estatus = '1';
    private ?int $fkRolUsuario = null;

    private const MIN_USERNAME = 3;
    private const MAX_USERNAME = 50;
    private const MAX_NOMBRE   = 100;
    private const MAX_APELLIDO = 100;
    private const MAX_EMAIL    = 100;
    private const MIN_PASSWORD = 8;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $userName = $this->sanitizeString($userName);
        $this->validateNotEmpty($userName, 'nombre de usuario');
        $this->validateMinLength($userName, 'nombre de usuario', self::MIN_USERNAME);
        $this->validateLength($userName, 'nombre de usuario', self::MAX_USERNAME);
        $this->userName = $userName;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $hash): void
    {
        $this->passwordHash = $hash;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $nombre = $this->sanitizeString($nombre);
        $this->validateLength($nombre, 'nombre', self::MAX_NOMBRE);
        $this->nombre = $nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): void
    {
        $apellido = $this->sanitizeString($apellido);
        $this->validateLength($apellido, 'apellido', self::MAX_APELLIDO);
        $this->apellido = $apellido;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = $this->sanitizeString($email);
        if ($email !== '' && !$this->validateEmail($email)) {
            throw new \InvalidArgumentException('El formato del email no es válido');
        }
        $this->validateLength($email, 'email', self::MAX_EMAIL);
        $this->email = $email;
    }

    public function getEstatus(): string
    {
        return $this->estatus;
    }

    public function setEstatus(string $estatus): void
    {
        $this->estatus = in_array($estatus, ['0', '1']) ? $estatus : '1';
    }

    public function getFkRolUsuario(): ?int
    {
        return $this->fkRolUsuario;
    }

    public function setFkRolUsuario(?int $fkRolUsuario): void
    {
        $this->fkRolUsuario = $fkRolUsuario !== null ? $this->sanitizeInt($fkRolUsuario) : null;
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'user_name'        => $this->userName,
            'password_hash'    => $this->passwordHash,
            'nombre'           => $this->nombre,
            'apellido'         => $this->apellido,
            'email'            => $this->email,
            'estatus'          => $this->estatus,
            'fk_rol_usuario'   => $this->fkRolUsuario,
        ];
    }

    public static function fromArray(array $data): self
    {
        $u = new self();
        $u->setId((int)($data['id'] ?? 0));
        $u->setUserName($data['user_name'] ?? '');
        $u->setPasswordHash($data['password_hash'] ?? '');
        $u->setNombre($data['nombre'] ?? '');
        $u->setApellido($data['apellido'] ?? '');
        $u->setEmail($data['email'] ?? '');
        $u->setEstatus($data['estatus'] ?? '1');
        $u->setFkRolUsuario(isset($data['fk_rol_usuario']) ? (int)$data['fk_rol_usuario'] : null);
        return $u;
    }

    private function hashPassword(string $password): string
    {
        if (mb_strlen($password) < self::MIN_PASSWORD) {
            throw new \InvalidArgumentException("La contraseña debe tener al menos " . self::MIN_PASSWORD . " caracteres");
        }
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function crear(string $user_name, string $password, string $nombre, string $apellido, string $email): bool
    {
        $this->setUserName($user_name);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setEmail($email);
        $hash = $this->hashPassword($password);

        $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email, estatus) VALUES (?, ?, ?, ?, ?, '1')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->userName, PDO::PARAM_STR);
        $stmt->bindParam(2, $hash, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->apellido, PDO::PARAM_STR);
        $stmt->bindParam(5, $this->email, PDO::PARAM_STR);
        return $stmt->execute();
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
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("
            SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            WHERE u.id = ?
        ");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function obtenerPorUsername(string $username): array|false
    {
        $username = $this->sanitizeString($username);
        $stmt = $this->db->prepare("
            SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            WHERE u.user_name = ? AND u.estatus = '1'
        ");
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        $stmt->execute();
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

    public function actualizar(int $id, string $nombre, string $apellido, string $email, ?int $fk_rol_usuario = null, string $estatus = '1'): bool
    {
        $this->setId($id);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setEmail($email);
        $this->setEstatus($estatus);
        $this->setFkRolUsuario($fk_rol_usuario);

        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, fk_rol_usuario = COALESCE(?, fk_rol_usuario), estatus = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->apellido, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->email, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->fkRolUsuario, PDO::PARAM_INT);
        $stmt->bindParam(5, $this->estatus, PDO::PARAM_STR);
        $stmt->bindParam(6, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizarPassword(int $id, string $password): bool
    {
        $id = $this->sanitizeInt($id);
        $hash = $this->hashPassword($password);
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $stmt->bindParam(1, $hash, PDO::PARAM_STR);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
