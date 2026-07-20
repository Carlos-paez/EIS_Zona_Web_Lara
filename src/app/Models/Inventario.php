<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;

/**
 * Clase Inventario que extiende de Model.
 * Proporciona métodos CRUD para productos y categorías, así como estadísticas de inventario.
 */
class Inventario extends Model
{
    /**
     * Crea un nuevo producto en el inventario.
     *
     * @param string $codigo        Código único del producto.
     * @param string $nombre        Nombre del producto.
     * @param int    $fk_categoria  ID de la categoría a la que pertenece.
     * @param int    $stock         Cantidad inicial en stock.
     * @param int    $stock_minimo  Cantidad mínima permitida antes de alerta.
     * @param float  $precio_compra Precio de compra del producto.
     * @param float  $precio_venta  Precio de venta al público.
     * @param string $descripcion   Descripción opcional del producto.
     * @return bool  True si la inserción fue exitosa.
     */
    public function crearProducto(string $codigo, string $nombre, int $fk_categoria, int $stock, int $stock_minimo, float $precio_compra, float $precio_venta, string $descripcion = ''): bool
    {
        // Inserta el producto con la fecha actual usando CURDATE()
        $sql = "INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fk_categoria, fecha_creacion, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$codigo, $nombre, $descripcion, $stock, $stock_minimo, $precio_compra, $precio_venta, $fk_categoria]);
    }

    /**
     * Obtiene todos los productos con su categoría asociada.
     *
     * @return array Lista completa de productos.
     */
    public function obtenerProductos(): array
    {
        // Consulta que une productos con categorías y ordena por nombre
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

    /**
     * Obtiene un producto específico por su ID.
     *
     * @param int $id ID del producto.
     * @return array|false Datos del producto o false si no existe.
     */
    public function obtenerProductoPorId(int $id): array|false
    {
        // Consulta parametrizada para un producto por ID con su categoría
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

    /**
     * Busca productos por nombre o código (búsqueda parcial con LIKE).
     *
     * @param string $termino Término de búsqueda.
     * @return array  Productos que coinciden con el término.
     */
    public function buscarProductos(string $termino): array
    {
        // Consulta con filtro LIKE sobre nombre y código
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
        // Agrega comodines % al término para búsqueda parcial
        $buscar = "%$termino%";
        $stmt->execute([$buscar, $buscar]);
        return $stmt->fetchAll();
    }

    /**
     * Actualiza todos los campos de un producto existente.
     *
     * @param int    $id             ID del producto.
     * @param string $codigo         Nuevo código.
     * @param string $nombre         Nuevo nombre.
     * @param int    $fk_categoria   Nueva categoría.
     * @param int    $stock          Nuevo stock.
     * @param int    $stock_minimo   Nuevo stock mínimo.
     * @param float  $precio_compra  Nuevo precio de compra.
     * @param float  $precio_venta   Nuevo precio de venta.
     * @param string $descripcion    Nueva descripción.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizarProducto(int $id, string $codigo, string $nombre, int $fk_categoria, int $stock, int $stock_minimo, float $precio_compra, float $precio_venta, string $descripcion = ''): bool
    {
        // Actualiza todos los campos y establece la fecha de actualización con CURDATE()
        $sql = "UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, stock = ?, stock_minimo = ?, precio_compra = ?, precio_venta = ?, fk_categoria = ?, fecha_actualizacion = CURDATE() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$codigo, $nombre, $descripcion, $stock, $stock_minimo, $precio_compra, $precio_venta, $fk_categoria, $id]);
    }

    /**
     * Elimina un producto por su ID.
     *
     * @param int $id ID del producto a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminarProducto(int $id): bool
    {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Cuenta el número total de productos registrados.
     *
     * @return int Cantidad total de productos.
     */
    public function totalProductos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    /**
     * Cuenta los productos con stock igual o menor a cero (stock crítico).
     *
     * @return int Número de productos sin stock.
     */
    public function stockCritico(): int
    {
        // Cuenta productos cuyo stock es 0 o negativo
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 0");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    /**
     * Cuenta los productos con stock bajo (mayor a 0 pero menor o igual al mínimo).
     *
     * @return int Número de productos por debajo del stock mínimo.
     */
    public function stockBajo(): int
    {
        // Cuenta productos con stock positivo pero igual o inferior al mínimo
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock > 0 AND stock <= stock_minimo");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    /**
     * Calcula el valor total del inventario (stock * precio_venta).
     *
     * @return float Sumatoria total del valor del inventario, 0.0 si no hay productos.
     */
    public function valorTotalInventario(): float
    {
        // Suma el producto de stock por precio de venta de todos los productos
        $stmt = $this->db->query("SELECT SUM(stock * precio_venta) AS total FROM productos");
        $fila = $stmt->fetch();
        // Si hay resultado lo retorna como float, si no retorna 0.0
        return $fila['total'] ? (float)$fila['total'] : 0.0;
    }

    /**
     * Obtiene todas las categorías de productos.
     *
     * @return array Lista de categorías (id y nombre).
     */
    public function obtenerCategorias(): array
    {
        $stmt = $this->db->query("SELECT id, nombre_categoria AS nombre FROM categoria ORDER BY nombre_categoria");
        return $stmt->fetchAll();
    }

    /**
     * Crea una nueva categoría de producto.
     */
    public function crearCategoria(string $nombre): bool
    {
        $sql = "INSERT INTO categoria (nombre_categoria) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre]);
    }

    /**
     * Actualiza el nombre de una categoría existente.
     */
    public function actualizarCategoria(int $id, string $nombre): bool
    {
        $sql = "UPDATE categoria SET nombre_categoria = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $id]);
    }

    /**
     * Elimina una categoría por su ID.
     */
    public function eliminarCategoria(int $id): bool
    {
        $sql = "DELETE FROM categoria WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

}
