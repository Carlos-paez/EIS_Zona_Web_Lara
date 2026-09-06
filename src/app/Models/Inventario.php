<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Inventario extends Model
{
    private int $id = 0;
    private string $codigo = '';
    private string $nombre = '';
    private string $descripcion = '';
    private int $stock = 0;
    private int $stockMinimo = 5;
    private float $precioCompra = 0.0;
    private float $precioVenta = 0.0;
    private int $fkCategoria = 0;

    private const MIN_CODIGO      = 1;
    private const MAX_CODIGO      = 50;
    private const MIN_NOMBRE      = 2;
    private const MAX_NOMBRE      = 100;
    private const MAX_DESCRIPCION = 1000;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): void
    {
        $codigo = $this->sanitizeString($codigo);
        $this->validateNotEmpty($codigo, 'código');
        $this->validateMinLength($codigo, 'código', self::MIN_CODIGO);
        $this->validateLength($codigo, 'código', self::MAX_CODIGO);
        $this->validatePattern($codigo, '/^[A-Za-z0-9][A-Za-z0-9_.\-\/#\s]{0,49}$/', 'El código del producto contiene caracteres no permitidos');
        $this->codigo = $codigo;
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

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): void
    {
        $descripcion = $this->sanitizeString($descripcion);
        $this->validateLength($descripcion, 'descripción', self::MAX_DESCRIPCION);
        $this->descripcion = $descripcion;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $stock = $this->sanitizeInt($stock);
        if ($stock < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo');
        }
        $this->stock = $stock;
    }

    public function getStockMinimo(): int
    {
        return $this->stockMinimo;
    }

    public function setStockMinimo(int $stockMinimo): void
    {
        $stockMinimo = $this->sanitizeInt($stockMinimo);
        if ($stockMinimo < 1) {
            throw new \InvalidArgumentException('El stock mínimo debe ser al menos 1');
        }
        $this->stockMinimo = $stockMinimo;
    }

    public function getPrecioCompra(): float
    {
        return $this->precioCompra;
    }

    public function setPrecioCompra(float $precioCompra): void
    {
        $precioCompra = $this->sanitizeFloat($precioCompra);
        if ($precioCompra < 0.0) {
            throw new \InvalidArgumentException('El costo de compra no puede ser negativo');
        }
        $this->precioCompra = $precioCompra;
    }

    public function getPrecioVenta(): float
    {
        return $this->precioVenta;
    }

    public function setPrecioVenta(float $precioVenta): void
    {
        $this->validatePositive($precioVenta, 'precio de venta');
        $this->precioVenta = $this->sanitizeFloat($precioVenta);
    }

    public function getFkCategoria(): int
    {
        return $this->fkCategoria;
    }

    public function setFkCategoria(int $fkCategoria): void
    {
        $this->fkCategoria = $this->sanitizeInt($fkCategoria);
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'codigo'          => $this->codigo,
            'nombre'          => $this->nombre,
            'descripcion'     => $this->descripcion,
            'stock'           => $this->stock,
            'stock_minimo'    => $this->stockMinimo,
            'precio_compra'   => $this->precioCompra,
            'precio_venta'    => $this->precioVenta,
            'fk_categoria'    => $this->fkCategoria,
        ];
    }

    public static function fromArray(array $data): self
    {
        $p = new self();
        $p->setId((int)($data['id'] ?? 0));
        $p->setCodigo($data['codigo'] ?? '');
        $p->setNombre($data['nombre'] ?? '');
        $p->setDescripcion($data['descripcion'] ?? '');
        $p->setStock((int)($data['stock'] ?? 0));
        $p->setStockMinimo((int)($data['stock_minimo'] ?? 5));
        $p->setPrecioCompra((float)($data['precio_compra'] ?? 0));
        $p->setPrecioVenta((float)($data['precio_venta'] ?? 0));
        $p->setFkCategoria((int)($data['fk_categoria'] ?? 0));
        return $p;
    }

    public function crearProducto(string $codigo, string $nombre, int $fk_categoria, int $stock, int $stock_minimo, float $precio_compra, float $precio_venta, string $descripcion = ''): bool
    {
        $this->setCodigo($codigo);
        $this->setNombre($nombre);
        $this->setFkCategoria($fk_categoria);
        $this->setStock($stock);
        $this->setStockMinimo($stock_minimo);
        $this->setPrecioCompra($precio_compra);
        $this->setPrecioVenta($precio_venta);
        $this->setDescripcion($descripcion);

        if (!$this->existeCategoria($this->fkCategoria)) {
            throw new \InvalidArgumentException('La categoría seleccionada no existe');
        }

        $sql = "INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fk_categoria, fecha_creacion, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->codigo, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->stock, PDO::PARAM_INT);
        $stmt->bindParam(5, $this->stockMinimo, PDO::PARAM_INT);
        $stmt->bindParam(6, $this->precioCompra, PDO::PARAM_STR);
        $stmt->bindParam(7, $this->precioVenta, PDO::PARAM_STR);
        $stmt->bindParam(8, $this->fkCategoria, PDO::PARAM_INT);
        return $stmt->execute();
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
        $id = $this->sanitizeInt($id);
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
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function buscarProductos(string $termino): array
    {
        $termino = $this->sanitizeString($termino);
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
        $stmt->bindParam(1, $buscar, PDO::PARAM_STR);
        $stmt->bindParam(2, $buscar, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function actualizarProducto(int $id, string $codigo, string $nombre, int $fk_categoria, int $stock, int $stock_minimo, float $precio_compra, float $precio_venta, string $descripcion = ''): bool
    {
        $this->setId($id);
        $this->setCodigo($codigo);
        $this->setNombre($nombre);
        $this->setFkCategoria($fk_categoria);
        $this->setStock($stock);
        $this->setStockMinimo($stock_minimo);
        $this->setPrecioCompra($precio_compra);
        $this->setPrecioVenta($precio_venta);
        $this->setDescripcion($descripcion);

        if (!$this->existeCategoria($this->fkCategoria)) {
            throw new \InvalidArgumentException('La categoría seleccionada no existe');
        }

        $sql = "UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, stock = ?, stock_minimo = ?, precio_compra = ?, precio_venta = ?, fk_categoria = ?, fecha_actualizacion = CURDATE() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->codigo, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->stock, PDO::PARAM_INT);
        $stmt->bindParam(5, $this->stockMinimo, PDO::PARAM_INT);
        $stmt->bindParam(6, $this->precioCompra, PDO::PARAM_STR);
        $stmt->bindParam(7, $this->precioVenta, PDO::PARAM_STR);
        $stmt->bindParam(8, $this->fkCategoria, PDO::PARAM_INT);
        $stmt->bindParam(9, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarProducto(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function totalProductos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos");
        return (int)$stmt->fetch()['total'];
    }

    public function stockCritico(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 0");
        return (int)$stmt->fetch()['total'];
    }

    public function stockBajo(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock > 0 AND stock <= stock_minimo");
        return (int)$stmt->fetch()['total'];
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

    public function crearCategoria(string $nombre): bool
    {
        $nombre = $this->sanitizeString($nombre);
        $this->validateNotEmpty($nombre, 'nombre de categoría');
        $this->validateLength($nombre, 'nombre de categoría', self::MAX_NOMBRE);
        $sql = "INSERT INTO categoria (nombre_categoria) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarCategoria(int $id, string $nombre): bool
    {
        $id = $this->sanitizeInt($id);
        $nombre = $this->sanitizeString($nombre);
        $this->validateNotEmpty($nombre, 'nombre de categoría');
        $this->validateLength($nombre, 'nombre de categoría', self::MAX_NOMBRE);
        $sql = "UPDATE categoria SET nombre_categoria = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarCategoria(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $sql = "DELETE FROM categoria WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function existeCodigo(string $codigo, int $excludeId = 0): bool
    {
        $codigo = $this->sanitizeString($codigo);
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM productos WHERE codigo = ? AND id != ?");
            $stmt->bindParam(1, $codigo, PDO::PARAM_STR);
            $stmt->bindParam(2, $excludeId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM productos WHERE codigo = ?");
            $stmt->bindParam(1, $codigo, PDO::PARAM_STR);
            $stmt->execute();
        }
        return (int)$stmt->fetch()['total'] > 0;
    }

    public function existeCategoria(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM categoria WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetch()['total'] > 0;
    }
}
