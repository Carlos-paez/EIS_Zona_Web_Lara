<?php

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    private int $id = 0;
    private string $cedula = '';
    private string $nombre = '';
    private string $apellido = '';
    private string $direccion = '';
    private string $telefono = '';

    private const MAX_CEDULA    = 20;
    private const MAX_NOMBRE    = 100;
    private const MAX_APELLIDO  = 100;
    private const MAX_DIRECCION = 500;
    private const MAX_TELEFONO  = 20;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getCedula(): string
    {
        return $this->cedula;
    }

    public function setCedula(string $cedula): void
    {
        $cedula = $this->sanitizeString($cedula);
        $this->validateLength($cedula, 'cédula', self::MAX_CEDULA);
        $this->cedula = $cedula;
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

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): void
    {
        $direccion = $this->sanitizeString($direccion);
        $this->validateLength($direccion, 'dirección', self::MAX_DIRECCION);
        $this->direccion = $direccion;
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
            'id'        => $this->id,
            'cedula'    => $this->cedula,
            'nombre'    => $this->nombre,
            'apellido'  => $this->apellido,
            'direccion' => $this->direccion,
            'telefono'  => $this->telefono,
        ];
    }

    public static function fromArray(array $data): self
    {
        $cliente = new self();
        $cliente->setId((int)($data['id'] ?? 0));
        $cliente->setCedula($data['cedula'] ?? '');
        $cliente->setNombre($data['nombre'] ?? '');
        $cliente->setApellido($data['apellido'] ?? '');
        $cliente->setDireccion($data['direccion'] ?? '');
        $cliente->setTelefono($data['telefono'] ?? '');
        return $cliente;
    }

    public function obtenerClientes(): array
    {
        $stmt = $this->db->query("SELECT id, cedula, nombre, apellido, direccion, telefono FROM clientes ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function obtenerClientePorId(int $id): array|false
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crearCliente(string $cedula, string $nombre, string $apellido, string $direccion, string $telefono): bool
    {
        $this->setCedula($cedula);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setDireccion($direccion);
        $this->setTelefono($telefono);

        $sql = "INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->cedula,
            $this->nombre,
            $this->apellido,
            $this->direccion,
            $this->telefono,
        ]);
    }

    public function actualizarCliente(int $id, string $cedula, string $nombre, string $apellido, string $direccion, string $telefono): bool
    {
        $this->setId($id);
        $this->setCedula($cedula);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setDireccion($direccion);
        $this->setTelefono($telefono);

        $sql = "UPDATE clientes SET cedula = ?, nombre = ?, apellido = ?, direccion = ?, telefono = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->cedula,
            $this->nombre,
            $this->apellido,
            $this->direccion,
            $this->telefono,
            $this->id,
        ]);
    }

    public function eliminarCliente(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totalClientes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM clientes");
        return (int)$stmt->fetch()['total'];
    }
}
