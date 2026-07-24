<?php

namespace App\Models;

use App\Core\Model;

class ProveedorGestion extends Model
{
    private int $id = 0;
    private string $rif = '';
    private string $nombre = '';
    private string $email = '';
    private string $telefono = '';

    private const MAX_RIF      = 20;
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
        if ($email !== '' && !$this->validateEmail($email)) {
            throw new \InvalidArgumentException('El formato del email no es válido');
        }
        $this->validateLength($email, 'email', self::MAX_EMAIL);
        $this->email = $email;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): void
    {
        $telefono = $this->sanitizeString($telefono);
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
        $stmt->execute([$id]);
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
        return $stmt->execute([$this->rif, $this->nombre, $this->email, $this->telefono]);
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
        return $stmt->execute([$this->rif, $this->nombre, $this->email, $this->telefono, $this->id]);
    }

    public function eliminarProveedor(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totalProveedores(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM proveedores");
        return (int)$stmt->fetch()['total'];
    }
}
