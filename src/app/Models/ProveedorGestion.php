<?php
// =============================================================================
// MODELO ProveedorGestion (Gestión de Proveedores)
// =============================================================================
// Propósito: Gestión CRUD de proveedores (alta, baja, consulta, actualización).
//            Módulo separado de las solicitudes/órdenes de compra.
// =============================================================================

namespace App\Models;

use App\Core\Model;

class ProveedorGestion extends Model
{
    /**
     * Obtiene todos los proveedores registrados.
     *
     * @return array Lista de proveedores.
     */
    public function obtenerProveedores(): array
    {
        $stmt = $this->db->query("SELECT id, rif, nombre, email, telefono FROM proveedores ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un proveedor específico por su ID.
     *
     * @param int $id ID del proveedor.
     * @return array|false Datos del proveedor o false si no existe.
     */
    public function obtenerProveedorPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo proveedor en la base de datos.
     *
     * @param string $rif      RIF del proveedor.
     * @param string $nombre   Nombre del proveedor.
     * @param string $email    Correo electrónico del proveedor.
     * @param string $telefono Teléfono del proveedor.
     * @return bool  True si la inserción fue exitosa.
     */
    public function crearProveedor(string $rif, string $nombre, string $email, string $telefono): bool
    {
        $sql = "INSERT INTO proveedores (rif, nombre, email, telefono) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rif, $nombre, $email, $telefono]);
    }

    /**
     * Actualiza los datos de un proveedor existente.
     *
     * @param int    $id       ID del proveedor.
     * @param string $rif      Nuevo RIF.
     * @param string $nombre   Nuevo nombre.
     * @param string $email    Nuevo correo.
     * @param string $telefono Nuevo teléfono.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizarProveedor(int $id, string $rif, string $nombre, string $email, string $telefono): bool
    {
        $sql = "UPDATE proveedores SET rif = ?, nombre = ?, email = ?, telefono = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rif, $nombre, $email, $telefono, $id]);
    }

    /**
     * Elimina un proveedor por su ID.
     *
     * @param int $id ID del proveedor a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminarProveedor(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Cuenta el número total de proveedores registrados.
     *
     * @return int Cantidad total de proveedores.
     */
    public function totalProveedores(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM proveedores");
        return (int)$stmt->fetch()['total'];
    }
}
