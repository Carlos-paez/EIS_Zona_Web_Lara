<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Venta extends Model
{
    private const MAX_CEDULA    = 20;
    private const MAX_NOMBRE    = 100;
    private const MAX_APELLIDO  = 100;
    private const MAX_DIRECCION = 500;
    private const MAX_TELEFONO  = 20;
    private const MAX_CANTIDAD  = 99999;
    private const MAX_ITEMS     = 200;

    /**
     * Obtiene los productos disponibles para la venta (con stock > 0).
     *
     * @return array Lista de productos con precio de venta.
     */
    public function listarProductos(): array
    {
        $stmt = $this->db->query("
            SELECT id, codigo, nombre, descripcion, stock, precio_venta
            FROM productos
            WHERE stock > 0
            ORDER BY nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Registra una venta de forma transaccional: cliente (get-or-create),
     * orden de venta y líneas de venta, descontando el stock.
     *
     * @param array  $items     Lista de items: [['id' => int, 'cantidad' => int], ...].
     * @param string $ciudadano Nombre completo del cliente.
     * @param string $cedula    Cédula del cliente.
     * @param string $direccion Dirección del cliente (opcional).
     * @param string $telefono  Teléfono del cliente (opcional).
     * @param int    $usuarioId ID del usuario que registra la venta.
     * @return int|false        ID de la orden creada, o false si falla.
     */
    public function registrarVenta(array $items, string $ciudadano, string $cedula, string $direccion = '', string $telefono = '', int $usuarioId = 0): int|false
    {
        $ciudadano = $this->sanitizeString($ciudadano);
        $cedula    = $this->sanitizeString($cedula);
        $direccion = $this->sanitizeString($direccion);
        $telefono  = $this->sanitizeString($telefono);

        $this->validateNotEmpty($ciudadano, 'nombre del cliente');
        $this->validateNotEmpty($cedula, 'cédula');
        $this->validateLength($cedula, 'cédula', self::MAX_CEDULA);
        $this->validateMinLength($cedula, 'cédula', 5);
        $this->validatePattern($cedula, '/^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/', 'La cédula no tiene un formato válido');
        $this->validateLength($ciudadano, 'nombre del cliente', self::MAX_NOMBRE);
        $this->validateLength($direccion, 'dirección', self::MAX_DIRECCION);
        $this->validateLength($telefono, 'teléfono', self::MAX_TELEFONO);
        $this->validateTelefono($telefono, 'teléfono');

        if (empty($items)) {
            throw new \InvalidArgumentException('El carrito está vacío');
        }
        if (count($items) > self::MAX_ITEMS) {
            throw new \InvalidArgumentException('El carrito excede el máximo de ' . self::MAX_ITEMS . ' ítems permitidos');
        }

        // Separa el nombre completo en nombre y apellido (máximo 2 partes)
        $nombre_partes = explode(' ', $ciudadano, 2);
        $nombre        = $nombre_partes[0];
        $apellido      = $nombre_partes[1] ?? '';

        // Valida la longitud del nombre (min 2, max 100)
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > self::MAX_NOMBRE) {
            throw new \InvalidArgumentException('El nombre del cliente debe tener entre 2 y ' . self::MAX_NOMBRE . ' caracteres');
        }
        if ($apellido !== '' && mb_strlen($apellido) > self::MAX_APELLIDO) {
            throw new \InvalidArgumentException('El apellido no puede exceder ' . self::MAX_APELLIDO . ' caracteres');
        }

        try {
            $this->db->beginTransaction();

            // 1. Obtener o crear el cliente (reutiliza el modelo centralizado)
            $cliente = new Cliente();
            $fk_cliente = $cliente->obtenerOCrearPorCedula($cedula, $nombre, $apellido, $direccion, $telefono);

            // 2. Crear la orden de venta
            $numero = 'V-' . date('YmdHis');
            $sql = "INSERT INTO orden_de_venta (numero_de_orden, fecha, fk_usuario, fk_cliente) VALUES (?, CURDATE(), ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $numero, PDO::PARAM_STR);
            $stmt->bindParam(2, $usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(3, $fk_cliente, PDO::PARAM_INT);
            $stmt->execute();
            $orden_id = (int)$this->db->lastInsertId();

            // 3. Registrar las líneas de venta y descontar stock
            $sqlLinea = "INSERT INTO lineas_venta (cantidad, precio, fk_orden, fk_producto) VALUES (?, ?, ?, ?)";
            $stmtLinea = $this->db->prepare($sqlLinea);
            $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $stmtStock = $this->db->prepare($sqlStock);
            $sqlPrecio = "SELECT precio_venta, stock FROM productos WHERE id = ?";
            $stmtPrecio = $this->db->prepare($sqlPrecio);

            $vistos = [];

            foreach ($items as $item) {
                $productoId = (int)($item['id'] ?? 0);
                $cantidad   = (int)($item['cantidad'] ?? 0);

                if ($productoId <= 0 || $cantidad < 1 || $cantidad > self::MAX_CANTIDAD) {
                    throw new \InvalidArgumentException('Ítem de venta no válido');
                }

                // Evita productos duplicados en la misma venta
                if (isset($vistos[$productoId])) {
                    throw new \InvalidArgumentException('Un producto se repite en el carrito');
                }
                $vistos[$productoId] = true;

                // Precio y stock tomados de la base de datos (no del cliente)
                $stmtPrecio->bindParam(1, $productoId, PDO::PARAM_INT);
                $stmtPrecio->execute();
                $producto = $stmtPrecio->fetch();

                if (!$producto) {
                    throw new \InvalidArgumentException('Producto no encontrado');
                }
                if ((int)$producto['stock'] < $cantidad) {
                    throw new \InvalidArgumentException('Stock insuficiente para un producto');
                }

                $precio = (float)$producto['precio_venta'];

                $stmtLinea->bindParam(1, $cantidad, PDO::PARAM_INT);
                $stmtLinea->bindParam(2, $precio, PDO::PARAM_STR);
                $stmtLinea->bindParam(3, $orden_id, PDO::PARAM_INT);
                $stmtLinea->bindParam(4, $productoId, PDO::PARAM_INT);
                $stmtLinea->execute();

                $stmtStock->bindParam(1, $cantidad, PDO::PARAM_INT);
                $stmtStock->bindParam(2, $productoId, PDO::PARAM_INT);
                $stmtStock->bindParam(3, $cantidad, PDO::PARAM_INT);
                $stmtStock->execute();
            }

            $this->db->commit();
            return $orden_id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}