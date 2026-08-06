<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;
use PDO;

/**
 * Clase Proveedor que extiende de Model.
 * Gestiona proveedores, órdenes de abastecimiento y líneas de abastecimiento.
 *
 * Encapsulación: todos los atributos son privados y se accede a ellos
 * únicamente mediante getters y setters.
 */
class Proveedor extends Model
{
    // Atributos de la orden de abastecimiento
    private int $id = 0;
    private string $numero = '';
    private string $fecha = '';
    private int $fkProveedor = 0;
    private int $fkStatus = 0;

    // Atributos de la línea de abastecimiento
    private int $lineaId = 0;
    private int $ordenId = 0;
    private int $productoId = 0;
    private int $cantidad = 0;
    private float $precio = 0.0;

    private const MAX_NUMERO = 50;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): void
    {
        $numero = $this->sanitizeString($numero);
        $this->validateNotEmpty($numero, 'número de orden');
        $this->validateLength($numero, 'número de orden', self::MAX_NUMERO);
        $this->numero = $numero;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function setFecha(string $fecha): void
    {
        $this->fecha = $this->validarFecha($fecha);
    }

    public function getFkProveedor(): int
    {
        return $this->fkProveedor;
    }

    public function setFkProveedor(int $fkProveedor): void
    {
        $this->fkProveedor = $this->sanitizeInt($fkProveedor);
    }

    public function getFkStatus(): int
    {
        return $this->fkStatus;
    }

    public function setFkStatus(int $fkStatus): void
    {
        $this->fkStatus = $this->sanitizeInt($fkStatus);
    }

    public function getLineaId(): int
    {
        return $this->lineaId;
    }

    public function setLineaId(int $lineaId): void
    {
        $this->lineaId = $this->sanitizeInt($lineaId);
    }

    public function getOrdenId(): int
    {
        return $this->ordenId;
    }

    public function setOrdenId(int $ordenId): void
    {
        $this->ordenId = $this->sanitizeInt($ordenId);
    }

    public function getProductoId(): int
    {
        return $this->productoId;
    }

    public function setProductoId(int $productoId): void
    {
        $this->productoId = $this->sanitizeInt($productoId);
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): void
    {
        $this->validateGreaterOrEqual((float)$cantidad, 'cantidad', 1);
        $this->cantidad = $this->sanitizeInt($cantidad);
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): void
    {
        $this->validatePositive($precio, 'precio');
        $this->precio = $this->sanitizeFloat($precio);
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'numero'       => $this->numero,
            'fecha'        => $this->fecha,
            'fk_proveedor' => $this->fkProveedor,
            'fk_status'    => $this->fkStatus,
            'linea_id'     => $this->lineaId,
            'orden_id'     => $this->ordenId,
            'producto_id'  => $this->productoId,
            'cantidad'     => $this->cantidad,
            'precio'       => $this->precio,
        ];
    }

    public static function fromArray(array $data): self
    {
        $p = new self();
        $p->setId((int)($data['id'] ?? 0));
        $p->setNumero($data['numero'] ?? '');
        $p->setFecha($data['fecha'] ?? '');
        $p->setFkProveedor((int)($data['fk_proveedor'] ?? 0));
        $p->setFkStatus((int)($data['fk_status'] ?? 0));
        $p->setLineaId((int)($data['linea_id'] ?? 0));
        $p->setOrdenId((int)($data['orden_id'] ?? 0));
        $p->setProductoId((int)($data['producto_id'] ?? 0));
        $p->setCantidad((int)($data['cantidad'] ?? 0));
        $p->setPrecio((float)($data['precio'] ?? 0));
        return $p;
    }

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
        // Encapsulación: se carga el ID mediante el setter
        $this->setId($id);

        // Consulta parametrizada para una orden específica con proveedor y estado
        $stmt = $this->db->prepare("
            SELECT oa.*, p.nombre AS proveedor_nombre, p.rif, ss.status AS estado
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            WHERE oa.id = ?
        ");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crea una nueva orden de abastecimiento.
     *
     * @param string $numero       Número de orden.
     * @param string $fecha        Fecha de la orden.
     * @param int    $fk_proveedor ID del proveedor.
     * @param int    $fk_status    ID del estado inicial.
     * @return int|false  ID insertado si fue exitoso, false si falló.
     */
    public function crearOrden(string $numero, string $fecha, int $fk_proveedor, int $fk_status): int|false
    {
        // Encapsulación: se cargan los valores mediante setters con validación
        $this->setNumero($numero);
        $this->setFecha($fecha);
        $this->setFkProveedor($fk_proveedor);
        $this->setFkStatus($fk_status);

        if ($this->fecha === '') {
            throw new \InvalidArgumentException('La fecha es obligatoria');
        }
        if (!$this->fkProveedor) {
            throw new \InvalidArgumentException('El proveedor es obligatorio');
        }
        if (!$this->fkStatus) {
            throw new \InvalidArgumentException('El estado es obligatorio');
        }
        if (!$this->existeProveedor($this->fkProveedor)) {
            throw new \InvalidArgumentException('El proveedor seleccionado no existe');
        }
        if (!$this->existeStatus($this->fkStatus)) {
            throw new \InvalidArgumentException('El estado seleccionado no existe');
        }

        $sql = "INSERT INTO orden_abastecimiento (numero_de_orden, fecha, fk_proveedor, fk_status) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->numero, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->fecha, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->fkProveedor, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->fkStatus, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() ? (int) $this->db->lastInsertId() : false;
    }

    public function obtenerSiguienteNumeroOrden(): string
    {
        $stmt = $this->db->query(
            "SELECT numero_de_orden FROM orden_abastecimiento
            ORDER BY id DESC LIMIT 1"
        );

        $row = $stmt->fetch();

        if (!$row){
            return 'OC-0001';
        }

        $ultimo = (int) str_replace('OC-', '', $row['numero_de_orden']);

        return 'OC-'. str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
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
        // Encapsulación: se cargan los valores mediante setters con validación
        $this->setId($id);
        $this->setNumero($numero);
        $this->setFecha($fecha);
        $this->setFkProveedor($fk_proveedor);
        $this->setFkStatus($fk_status);

        if ($this->fecha === '') {
            throw new \InvalidArgumentException('La fecha es obligatoria');
        }
        if (!$this->fkProveedor) {
            throw new \InvalidArgumentException('El proveedor es obligatorio');
        }
        if (!$this->fkStatus) {
            throw new \InvalidArgumentException('El estado es obligatorio');
        }
        if (!$this->existeProveedor($this->fkProveedor)) {
            throw new \InvalidArgumentException('El proveedor seleccionado no existe');
        }
        if (!$this->existeStatus($this->fkStatus)) {
            throw new \InvalidArgumentException('El estado seleccionado no existe');
        }

        $sql = "UPDATE orden_abastecimiento SET numero_de_orden = ?, fecha = ?, fk_proveedor = ?, fk_status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->numero, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->fecha, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->fkProveedor, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->fkStatus, PDO::PARAM_INT);
        $stmt->bindParam(5, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
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
        // Encapsulación: se carga el ID mediante el setter
        $this->setId($id);

        // Inicia transacción
        $this->db->beginTransaction();
        try {
            // Primero elimina todas las líneas de abastecimiento asociadas a la orden
            $stmt = $this->db->prepare("DELETE FROM lineas_abastecimiento WHERE fk_orden_abastecimiento = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            // Luego elimina la orden en sí
            $stmt = $this->db->prepare("DELETE FROM orden_abastecimiento WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
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
        // Encapsulación: se carga el ID mediante el setter
        $this->setOrdenId($orden_id);

        // Consulta parametrizada con JOIN a productos para obtener detalles de cada línea
        $stmt = $this->db->prepare("
            SELECT la.id, la.cantidad, la.precio,
                   p.id AS producto_id, p.nombre AS producto_nombre, p.codigo AS producto_codigo
            FROM lineas_abastecimiento la
            LEFT JOIN productos p ON la.fk_producto = p.id
            WHERE la.fk_orden_abastecimiento = ?
            ORDER BY la.id
        ");
        $stmt->bindParam(1, $this->ordenId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Agrega una línea (producto) a una orden de abastecimiento.
     *
     * @param int   $orden_id    ID de la orden.
     * @param int   $producto_id ID del producto.
     * @param int   $cantidad    Cantidad solicitada.
     * @param float $precio      Precio unitario.
     * @return bool True si la inserción fue exitosa.
     */
    public function agregarLinea(int $orden_id, int $producto_id, int $cantidad, float $precio): bool
    {
        // Encapsulación: se cargan los valores mediante setters con validación
        $this->setOrdenId($orden_id);
        $this->setProductoId($producto_id);
        $this->setCantidad($cantidad);
        $this->setPrecio($precio);

        $sql = "INSERT INTO lineas_abastecimiento (cantidad, precio, fk_orden_abastecimiento, fk_producto) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->cantidad, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->precio, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->ordenId, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->productoId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Elimina una línea de abastecimiento por su ID.
     *
     * @param int $id ID de la línea a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminarLinea(int $id): bool
    {
        // Encapsulación: se carga el ID mediante el setter
        $this->setLineaId($id);
        // Elimina la línea de abastecimiento por su identificador único
        $stmt = $this->db->prepare("DELETE FROM lineas_abastecimiento WHERE id = ?");
        $stmt->bindParam(1, $this->lineaId, PDO::PARAM_INT);
        return $stmt->execute();
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
        $stmt = $this->db->query("
            SELECT ss.status AS estado, COUNT(*) AS total
            FROM orden_abastecimiento oa
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            GROUP BY ss.status
        ");
        return $stmt->fetchAll();
    }

    public function existeProveedor(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM proveedores WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetch()['total'] > 0;
    }

    public function existeStatus(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM status_seguimiento WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetch()['total'] > 0;
    }

    private function validarFecha(string $fecha): string
    {
        $fecha = trim($fecha);
        if ($fecha !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido (use YYYY-MM-DD)');
        }
        return $fecha;
    }
}
