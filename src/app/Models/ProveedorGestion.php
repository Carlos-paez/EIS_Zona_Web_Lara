<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ProveedorGestion extends Model
{
    private int $id = 0;
    private string $rif = '';
    private string $nombre = '';
    private string $email = '';
    private string $telefono = '';

    private const MIN_RIF      = 5;
    private const MAX_RIF      = 20;
    private const MIN_NOMBRE   = 2;
    private const MAX_NOMBRE   = 100;
    private const MAX_EMAIL    = 100;
    private const MAX_TELEFONO = 20;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getRif(): string
    {
        return $this->rif;
    }

    public function setRif(string $rif): void
    {
        $rif = $this->sanitizeString($rif);
        $this->validateNotEmpty($rif, 'RIF');
        $this->validateMinLength($rif, 'RIF', self::MIN_RIF);
        $this->validateLength($rif, 'RIF', self::MAX_RIF);
        $this->rif = $rif;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $nombre = $this->sanitizeString($nombre);
        $this->validateNotEmpty($nombre, 'nombre');
        $this->validateMinLength($nombre, 'nombre', self::MIN_NOMBRE);
        $this->validateLength($nombre, 'nombre', self::MAX_NOMBRE);
        $this->nombre = $nombre;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = $this->sanitizeString($email);
        if ($email !== '') {
            if (!$this->validateEmail($email)) {
                throw new \InvalidArgumentException('El formato del email no es válido');
            }
            $this->validateLength($email, 'email', self::MAX_EMAIL);
        }
        $this->email = $email;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): void
    {
        $telefono = $this->sanitizeString($telefono);
        $this->validateNotEmpty($telefono, 'teléfono');
        $this->validateLength($telefono, 'teléfono', self::MAX_TELEFONO);
        $this->telefono = $telefono;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'rif'      => $this->rif,
            'nombre'   => $this->nombre,
            'email'    => $this->email,
            'telefono' => $this->telefono,
        ];
    }

    public static function fromArray(array $data): self
    {
        $prov = new self();
        $prov->setId((int)($data['id'] ?? 0));
        $prov->setRif($data['rif'] ?? '');
        $prov->setNombre($data['nombre'] ?? '');
        $prov->setEmail($data['email'] ?? '');
        $prov->setTelefono($data['telefono'] ?? '');
        return $prov;
    }

    public function obtenerProveedores(): array
    {
        $stmt = $this->db->query("SELECT id, rif, nombre, email, telefono FROM proveedores ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function obtenerProveedorPorId(int $id): array|false
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crearProveedor(string $rif, string $nombre, string $email, string $telefono): bool
    {
        $this->setRif($rif);
        $this->setNombre($nombre);
        $this->setEmail($email);
        $this->setTelefono($telefono);

        $sql = "INSERT INTO proveedores (rif, nombre, email, telefono) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->rif, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->email, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->telefono, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarProveedor(int $id, string $rif, string $nombre, string $email, string $telefono): bool
    {
        $this->setId($id);
        $this->setRif($rif);
        $this->setNombre($nombre);
        $this->setEmail($email);
        $this->setTelefono($telefono);

        $sql = "UPDATE proveedores SET rif = ?, nombre = ?, email = ?, telefono = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->rif, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->email, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->telefono, PDO::PARAM_STR);
        $stmt->bindParam(5, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarProveedor(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function totalProveedores(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM proveedores");
        return (int)$stmt->fetch()['total'];
    }

    public function existeRif(string $rif, int $excludeId = 0): bool
    {
        $rif = $this->sanitizeString($rif);
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM proveedores WHERE rif = ? AND id != ?");
            $stmt->bindParam(1, $rif, PDO::PARAM_STR);
            $stmt->bindParam(2, $excludeId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM proveedores WHERE rif = ?");
            $stmt->bindParam(1, $rif, PDO::PARAM_STR);
            $stmt->execute();
        }
        return (int)$stmt->fetch()['total'] > 0;
    }
}
