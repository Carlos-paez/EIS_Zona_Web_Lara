<?php
namespace App\Models;

use App\Core\Model;

class Inventario extends Model
{
    public function crearProducto(string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool
    {
        $sql = "INSERT INTO productos (codigo, nombre, categoria_id, stock, stock_minimo, costo_compra, precio_venta) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta]);
    }

    public function obtenerProductos(): array
    {
        $stmt = $this->db->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE ORDER BY p.nombre");
        return $stmt->fetchAll();
    }

    public function obtenerProductoPorId(int $id): array|false
    {
        $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarProductos(string $termino): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE AND (p.nombre LIKE ? OR p.codigo LIKE ?) ORDER BY p.nombre";
        $stmt = $this->db->prepare($sql);
        $buscar = "%$termino%";
        $stmt->execute([$buscar, $buscar]);
        return $stmt->fetchAll();
    }

    public function actualizarProducto(int $id, string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool
    {
        $sql = "UPDATE productos SET codigo = ?, nombre = ?, categoria_id = ?, stock = ?, stock_minimo = ?, costo_compra = ?, precio_venta = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $id]);
    }

    public function eliminarProducto(int $id): bool
    {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function totalProductos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function stockCritico(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE AND stock <= 0");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function stockBajo(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    public function valorTotalInventario(): float
    {
        $stmt = $this->db->query("SELECT SUM(stock * precio_venta) AS total FROM productos WHERE activo = TRUE");
        $fila = $stmt->fetch();
        return $fila['total'] ? (float)$fila['total'] : 0.0;
    }

    public function obtenerCategorias(): array
    {
        $stmt = $this->db->query("SELECT * FROM categorias WHERE activa = TRUE ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function obtenerSubcategorias(): array
    {
        $stmt = $this->db->query("SELECT * FROM subcategorias WHERE activa = TRUE ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function obtenerMarcas(): array
    {
        $stmt = $this->db->query("SELECT * FROM marcas ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function obtenerModelos(): array
    {
        $stmt = $this->db->query("SELECT * FROM modelos ORDER BY nombre");
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

        $sql1 = "UPDATE productos SET stock = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql1);
        $stmt->execute([$stock_nuevo, $producto_id]);

        $sql2 = "INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'entrada', ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql2);
        $stmt->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

        return true;
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

        $sql1 = "UPDATE productos SET stock = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql1);
        $stmt->execute([$stock_nuevo, $producto_id]);

        $sql2 = "INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'salida', ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql2);
        $stmt->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

        return true;
    }

    public function obtenerMovimientos(int $producto_id): array
    {
        $sql = "SELECT b.*, u.nombre AS usuario FROM bitacora_movimientos_stock b LEFT JOIN usuarios u ON b.usuario_id = u.id WHERE b.producto_id = ? ORDER BY b.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll();
    }
}
