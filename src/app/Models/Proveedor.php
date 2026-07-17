<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;

/**
 * Clase Proveedor que extiende de Model.
 * Gestiona proveedores, órdenes de abastecimiento y líneas de abastecimiento.
 */
class Proveedor extends Model
{
    /**
     * Obtiene todas las órdenes de abastecimiento con datos del proveedor y estado.
     *
     * @return array Lista de órdenes de abastecimiento.
     */
    public function obtenerOrdenes(): array
    {
        // Consulta con JOIN a proveedores y status_seguimiento, ordenada por fecha descendente
        $stmt = $this->db->query("
            SELECT oa.id, oa.numero_de_orden, oa.fecha,
                   p.id AS proveedor_id, p.nombre AS proveedor_nombre, p.rif,
                   ss.id AS status_id, ss.status AS estado
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            ORDER BY oa.fecha DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una orden de abastecimiento específica por su ID.
     *
     * @param int $id ID de la orden.
     * @return array|false Datos de la orden o false si no existe.
     */
    public function obtenerOrdenPorId(int $id): array|false
    {
        // Consulta parametrizada para una orden específica con proveedor y estado
        $stmt = $this->db->prepare("
            SELECT oa.*, p.nombre AS proveedor_nombre, p.rif, ss.status AS estado
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            WHERE oa.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva orden de abastecimiento.
     *
     * @param string $numero      Número de orden.
     * @param string $fecha       Fecha de la orden.
     * @param int    $fk_proveedor ID del proveedor.
     * @param int    $fk_status    ID del estado inicial.
     * @return bool  True si la inserción fue exitosa.
     */
    public function crearOrden(string $numero, string $fecha, int $fk_proveedor, int $fk_status): bool
    {
        // Inserta una nueva orden de abastecimiento con los datos proporcionados
        $sql = "INSERT INTO orden_abastecimiento (numero_de_orden, fecha, fk_proveedor, fk_status) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$numero, $fecha, $fk_proveedor, $fk_status]);
    }

    /**
     * Actualiza los datos de una orden de abastecimiento existente.
     *
     * @param int    $id           ID de la orden.
     * @param string $numero       Nuevo número de orden.
     * @param string $fecha        Nueva fecha.
     * @param int    $fk_proveedor Nuevo ID del proveedor.
     * @param int    $fk_status    Nuevo ID del estado.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizarOrden(int $id, string $numero, string $fecha, int $fk_proveedor, int $fk_status): bool
    {
        // Actualiza todos los campos de la orden identificada por ID
        $sql = "UPDATE orden_abastecimiento SET numero_de_orden = ?, fecha = ?, fk_proveedor = ?, fk_status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$numero, $fecha, $fk_proveedor, $fk_status, $id]);
    }

    /**
     * Elimina una orden de abastecimiento y todas sus líneas asociadas.
     * La operación se realiza dentro de una transacción para mantener consistencia.
     *
     * @param int $id ID de la orden a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminarOrden(int $id): bool
    {
        // Inicia transacción
        $this->db->beginTransaction();
        try {
            // Primero elimina todas las líneas de abastecimiento asociadas a la orden
            $stmt = $this->db->prepare("DELETE FROM lineas_abastecimiento WHERE fk_orden_abastecimiento = ?");
            $stmt->execute([$id]);
            // Luego elimina la orden en sí
            $stmt = $this->db->prepare("DELETE FROM orden_abastecimiento WHERE id = ?");
            $stmt->execute([$id]);
            // Confirma la transacción
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Si algo falla, revierte todos los cambios
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Obtiene todos los proveedores registrados.
     * Se usa para poblar el select de proveedores en el formulario de órdenes.
     *
     * @return array Lista de proveedores.
     */
    public function obtenerProveedores(): array
    {
        // Consulta todos los proveedores ordenados por nombre
        $stmt = $this->db->query("SELECT id, rif, nombre, email, telefono FROM proveedores ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los estados de seguimiento disponibles.
     *
     * @return array Lista de estados.
     */
    public function obtenerStatuses(): array
    {
        // Consulta todos los registros de la tabla status_seguimiento ordenados por ID
        $stmt = $this->db->query("SELECT id, status FROM status_seguimiento ORDER BY id");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los productos (id, código, nombre, precio_compra) para usar en líneas de abastecimiento.
     *
     * @return array Lista de productos.
     */
    public function obtenerProductos(): array
    {
        // Consulta básica de productos, solo los campos necesarios para una orden
        $stmt = $this->db->query("SELECT id, codigo, nombre, precio_compra FROM productos ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las líneas de abastecimiento de una orden específica.
     *
     * @param int $orden_id ID de la orden de abastecimiento.
     * @return array  Lista de líneas con datos del producto.
     */
    public function obtenerLineas(int $orden_id): array
    {
        // Consulta parametrizada con JOIN a productos para obtener detalles de cada línea
        $stmt = $this->db->prepare("
            SELECT la.id, la.cantidad, la.precio,
                   p.id AS producto_id, p.nombre AS producto_nombre, p.codigo AS producto_codigo
            FROM lineas_abastecimiento la
            LEFT JOIN productos p ON la.fk_producto = p.id
            WHERE la.fk_orden_abastecimiento = ?
            ORDER BY la.id
        ");
        $stmt->execute([$orden_id]);
        return $stmt->fetchAll();
    }

    /**
     * Agrega una línea (producto) a una orden de abastecimiento.
     *
     * @param int   $orden_id   ID de la orden.
     * @param int   $producto_id ID del producto.
     * @param int   $cantidad   Cantidad solicitada.
     * @param float $precio     Precio unitario.
     * @return bool True si la inserción fue exitosa.
     */
    public function agregarLinea(int $orden_id, int $producto_id, int $cantidad, float $precio): bool
    {
        // Inserta una nueva línea de abastecimiento en la orden
        $sql = "INSERT INTO lineas_abastecimiento (cantidad, precio, fk_orden_abastecimiento, fk_producto) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cantidad, $precio, $orden_id, $producto_id]);
    }

    /**
     * Elimina una línea de abastecimiento por su ID.
     *
     * @param int $id ID de la línea a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminarLinea(int $id): bool
    {
        // Elimina la línea de abastecimiento por su identificador único
        $stmt = $this->db->prepare("DELETE FROM lineas_abastecimiento WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Cuenta el número total de solicitudes (órdenes de abastecimiento).
     *
     * @return int Cantidad total de órdenes.
     */
    public function totalSolicitudes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM orden_abastecimiento");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Cuenta las órdenes de abastecimiento agrupadas por estado de seguimiento.
     *
     * @return array Arreglo con cada estado y su total.
     */
    public function contarPorEstado(): array
    {
        // Agrupa las órdenes por status y las cuenta
        $stmt = $this->db->query("
            SELECT ss.status AS estado, COUNT(*) AS total
            FROM orden_abastecimiento oa
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            GROUP BY ss.status
        ");
        return $stmt->fetchAll();
    }
}
