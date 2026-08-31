<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Cliente extends Model
{
    private int $id = 0;
    private string $cedula = '';
    private string $nombre = '';
    private string $apellido = '';
    private string $direccion = '';
    private string $telefono = '';

    private const MIN_CEDULA    = 5;
    private const MAX_CEDULA    = 20;
    private const MIN_NOMBRE    = 2;
    private const MAX_NOMBRE    = 100;
    private const MIN_APELLIDO  = 2;
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
        $this->validateNotEmpty($cedula, 'cédula');
        $this->validateMinLength($cedula, 'cédula', self::MIN_CEDULA);
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
        $this->validateNotEmpty($nombre, 'nombre');
        $this->validateMinLength($nombre, 'nombre', self::MIN_NOMBRE);
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
        $this->validateNotEmpty($apellido, 'apellido');
        $this->validateMinLength($apellido, 'apellido', self::MIN_APELLIDO);
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
        $this->validateNotEmpty($direccion, 'dirección');
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
        $this->validateNotEmpty($telefono, 'teléfono');
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
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function obtenerClientePorCedula(string $cedula): array|false
    {
        $cedula = $this->sanitizeString($cedula);
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE cedula = ?");
        $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtiene o crea un cliente por su cédula de forma segura y validada.
     * Pensado para que los módulos relacionados (asesorías, ventas, cybercafé)
     * reutilicen la data de clientes sin duplicar lógica ni saltarse la validación.
     *
     * - Si el cliente ya existe, actualiza únicamente los campos no vacíos
     *   recibidos (nombre, apellido, dirección, teléfono).
     * - Si no existe, lo crea y retorna su ID.
     *
     * @param string $cedula    Cédula del cliente (obligatoria, 5-20 caracteres).
     * @param string $nombre    Nombre del cliente (obligatorio, 2-100 caracteres).
     * @param string $apellido  Apellido del cliente (opcional).
     * @param string $direccion Dirección del cliente (opcional).
     * @param string $telefono  Teléfono del cliente (opcional).
     * @return int              ID del cliente existente o recién creado.
     */
    public function obtenerOCrearPorCedula(string $cedula, string $nombre, string $apellido = '', string $direccion = '', string $telefono = ''): int
    {
        // Encapsulación: se cargan los valores mediante setters con validación.
        // Se reinician los campos opcionales para no heredar estado de llamadas anteriores.
        $this->setCedula($cedula);
        $this->setNombre($nombre);
        $this->apellido = '';
        $this->direccion = '';
        $this->telefono = '';
        if ($apellido !== '') {
            $this->setApellido($apellido);
        }
        if ($direccion !== '') {
            $this->setDireccion($direccion);
        }
        if ($telefono !== '') {
            $this->setTelefono($telefono);
        }

        $stmt = $this->db->prepare("SELECT id, nombre, apellido, direccion, telefono FROM clientes WHERE cedula = ?");
        $stmt->bindParam(1, $this->cedula, PDO::PARAM_STR);
        $stmt->execute();
        $cliente = $stmt->fetch();

        if ($cliente) {
            $id = (int)$cliente['id'];

            // Solo modifica los campos que el módulo relacionado envía no vacíos
            $nuevoNombre    = $this->nombre;
            $nuevoApellido  = $this->apellido !== '' ? $this->apellido : (string)$cliente['apellido'];
            $nuevaDireccion = $this->direccion !== '' ? $this->direccion : (string)$cliente['direccion'];
            $nuevoTelefono  = $this->telefono !== '' ? $this->telefono : (string)$cliente['telefono'];

            if (
                $nuevoNombre !== $cliente['nombre']
                || $nuevoApellido !== $cliente['apellido']
                || $nuevaDireccion !== $cliente['direccion']
                || $nuevoTelefono !== $cliente['telefono']
            ) {
                $stmt = $this->db->prepare("UPDATE clientes SET nombre = ?, apellido = ?, direccion = ?, telefono = ? WHERE id = ?");
                $stmt->bindParam(1, $nuevoNombre, PDO::PARAM_STR);
                $stmt->bindParam(2, $nuevoApellido, PDO::PARAM_STR);
                $stmt->bindParam(3, $nuevaDireccion, PDO::PARAM_STR);
                $stmt->bindParam(4, $nuevoTelefono, PDO::PARAM_STR);
                $stmt->bindParam(5, $id, PDO::PARAM_INT);
                $stmt->execute();
            }

            return $id;
        }

        $sql = "INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->cedula, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->apellido, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->direccion, PDO::PARAM_STR);
        $stmt->bindParam(5, $this->telefono, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
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
        $stmt->bindParam(1, $this->cedula, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->apellido, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->direccion, PDO::PARAM_STR);
        $stmt->bindParam(5, $this->telefono, PDO::PARAM_STR);
        return $stmt->execute();
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
        $stmt->bindParam(1, $this->cedula, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->apellido, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->direccion, PDO::PARAM_STR);
        $stmt->bindParam(5, $this->telefono, PDO::PARAM_STR);
        $stmt->bindParam(6, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarCliente(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function totalClientes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM clientes");
        return (int)$stmt->fetch()['total'];
    }

    public function existeCedula(string $cedula, int $excludeId = 0): bool
    {
        $cedula = $this->sanitizeString($cedula);
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM clientes WHERE cedula = ? AND id != ?");
            $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
            $stmt->bindParam(2, $excludeId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM clientes WHERE cedula = ?");
            $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
            $stmt->execute();
        }
        return (int)$stmt->fetch()['total'] > 0;
    }
}
