<?php

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    public function obtenerClientes(): array
    {
        $stmt = $this->db->query("SELECT id, cedula, nombre, apellido, direccion, telefono FROM clientes ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function obtenerClientePorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crearCliente(string $cedula, string $nombre, string $apellido, string $direccion, string $telefono): bool
    {
        $sql = "INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cedula, $nombre, $apellido, $direccion, $telefono]);
    }

    public function actualizarCliente(int $id, string $cedula, string $nombre, string $apellido, string $direccion, string $telefono): bool
    {
        $sql = "UPDATE clientes SET cedula = ?, nombre = ?, apellido = ?, direccion = ?, telefono = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cedula, $nombre, $apellido, $direccion, $telefono, $id]);
    }

    public function eliminarCliente(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totalClientes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM clientes");
        return (int)$stmt->fetch()['total'];
    }
}
