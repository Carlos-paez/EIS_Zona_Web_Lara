<?php
namespace App\Models;

use App\Core\Model;

class Inventario extends Model
{
    public function crearProducto(string $codigo, string $nombre, int $fk_categoria, int $stock, int $stock_minimo, float $precio_compra, float $precio_venta, string $descripcion = ''): bool
    {
        $sql = "INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fk_categoria, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$codigo, $nombre, $descripcion, $stock, $stock_minimo, $precio_compra, $precio_venta, $fk_categoria]);
    }

    public function obtenerProductos(): array
    {
        $stmt = $this->db->query("
            SELECT p.id, p.codigo, p.nombre, p.descripcion, p.stock, p.stock_minimo,
                   p.precio_compra, p.precio_venta, p.fecha_creacion, p.fecha_actualizacion,
                   p.fk_categoria AS categoria_id,
                   c.nombre_categoria AS categoria
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            ORDER BY p.nombre
        ");
        return $stmt->fetchAll();
    }

    public function obtenerProductoPorId(int $id): array|false
    {
        $sql = "
            SELECT p.id, p.codigo, p.nombre, p.descripcion, p.stock, p.stock_minimo,
                   p.precio_compra AS costo_compra, p.precio_venta,
                   p.fecha_creacion, p.fecha_actualizacion,
                   p.fk_categoria AS categoria_id,
                   c.nombre_categoria AS categoria
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            WHERE p.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarProductos(string $termino): array
    {
        $sql = "
            SELECT p.id, p.codigo, p.nombre, p.descripcion, p.stock, p.stock_minimo,
                   p.precio_compra, p.precio_venta, p.fecha_creacion, p.fecha_actualizacion,
                   p.fk_categoria AS categoria_id,
                   c.nombre_categoria AS categoria
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            WHERE p.nombre LIKE ? OR p.codigo LIKE ?
            ORDER BY p.nombre
        ";
        $stmt = $this->db->prepare($sql);
        $buscar = "%$termino%";
        $stmt->execute([$buscar, $buscar]);
        return $stmt->fetchAll();
    }

    public function actualizarProducto(int $id, string $codigo, string $nombre, int $fk_categoria, int $stock, int $stock_minimo, float $precio_compra, float $precio_venta, string $descripcion = ''): bool
    {
        $sql = "UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, stock = ?, stock_minimo = ?, precio_compra = ?, precio_venta = ?, fk_categoria = ?, fecha_actualizacion = CURDATE() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$codigo, $nombre, $descripcion, $stock, $stock_minimo, $precio_compra, $precio_venta, $fk_categoria, $id]);
    }

    public function eliminarProducto(int $id): bool
    {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function totalProductos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function stockCritico(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 0");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function stockBajo(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock > 0 AND stock <= stock_minimo");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function valorTotalInventario(): float
    {
        $stmt = $this->db->query("SELECT SUM(stock * precio_venta) AS total FROM productos");
        $fila = $stmt->fetch();
        return $fila['total'] ? (float)$fila['total'] : 0.0;
    }

    public function obtenerCategorias(): array
    {
        $stmt = $this->db->query("SELECT id, nombre_categoria AS nombre FROM categoria ORDER BY nombre_categoria");
        return $stmt->fetchAll();
    }

    public function registrarEntrada(int $producto_id, int $cantidad, int $usuario_id, string $motivo): bool
    {
        $sql = "SELECT stock FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch();

        if (!$producto) return false;

        $stock_anterior = (int)$producto['stock'];
        $stock_nuevo = $stock_anterior + $cantidad;

        try {
            $this->db->beginTransaction();

            $this->db->prepare("UPDATE productos SET stock = ?, fecha_actualizacion = CURDATE() WHERE id = ?")
                     ->execute([$stock_nuevo, $producto_id]);

            $this->db->prepare("INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'entrada', ?, ?, ?, ?, ?)")
                     ->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function registrarSalida(int $producto_id, int $cantidad, int $usuario_id, string $motivo): bool
    {
        $sql = "SELECT stock FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch();

        if (!$producto) return false;

        $stock_anterior = (int)$producto['stock'];
        $stock_nuevo = $stock_anterior - $cantidad;

        if ($stock_nuevo < 0) return false;

        try {
            $this->db->beginTransaction();

            $this->db->prepare("UPDATE productos SET stock = ?, fecha_actualizacion = CURDATE() WHERE id = ?")
                     ->execute([$stock_nuevo, $producto_id]);

            $this->db->prepare("INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'salida', ?, ?, ?, ?, ?)")
                     ->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function obtenerMovimientos(int $producto_id): array
    {
        $sql = "
            SELECT m.id, m.tipo, m.cantidad, m.stock_anterior, m.stock_nuevo,
                   m.motivo, m.fecha,
                   COALESCE(CONCAT(u.nombre, ' ', u.apellido), 'Sistema') AS usuario
            FROM movimientos_inventario m
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.producto_id = ?
            ORDER BY m.fecha DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll();
    }
}
