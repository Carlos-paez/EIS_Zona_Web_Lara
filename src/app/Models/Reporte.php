<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo Reporte.
 *
 * Genera KPIs mensuales y consultas por tipo de reporte y rango de fechas,
 * así como la exportación de los resultados en distintos formatos
 * (CSV, Excel y PDF) sin dependencias externas.
 */
class Reporte extends Model
{
    /**
     * Tipos de reporte soportados.
     */
    public const TIPOS = [
        'ventas'      => 'Ventas por fecha',
        'inventario'  => 'Estado de inventario',
        'movimientos' => 'Movimientos de stock',
        'proveedores' => 'Solicitudes a proveedores',
        'cyber'       => 'Horas Cybercafé',
    ];

    /**
     * Total de ventas del mes actual.
     *
     * @return float
     */
    public function ventasMes(): float
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total
            FROM orden_de_venta ov
            JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE MONTH(ov.fecha) = MONTH(CURDATE()) AND YEAR(ov.fecha) = YEAR(CURDATE())
        ");
        return (float)$stmt->fetch()['total'];
    }

    /**
     * Productos activos en inventario.
     *
     * @return int
     */
    public function productosActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Total de sesiones de cybercafé registradas.
     *
     * @return int
     */
    public function totalSesionesCyber(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM sesion_ciber");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Sesiones de cybercafé activas en este momento.
     *
     * @return int
     */
    public function sesionesCyberActivas(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM sesion_ciber WHERE finalizada = 0");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Total de solicitudes de abastecimiento.
     *
     * @return int
     */
    public function totalSolicitudes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM orden_abastecimiento");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Solicitudes aprobadas o en tránsito (procesadas).
     *
     * @return int
     */
    public function solicitudesProcesadas(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total
            FROM orden_abastecimiento oa
            JOIN status_seguimiento ss ON oa.fk_status = ss.id
            WHERE ss.status IN ('Aprobado', 'En Tránsito', 'Recibido Parcial', 'Recibido Completo')
        ");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * KPIs agregados para el encabezado mensual.
     *
     * @return array
     */
    public function kpis(): array
    {
        return [
            'ventas_mes'        => $this->ventasMes(),
            'productos_activos' => $this->productosActivos(),
            'sesiones_cyber'    => $this->totalSesionesCyber(),
            'sesiones_activas'  => $this->sesionesCyberActivas(),
            'solicitudes'       => $this->solicitudesProcesadas(),
        ];
    }

    /**
     * Ejecuta la consulta del reporte según su tipo y rango de fechas.
     *
     * @param string $tipo      Uno de self::TIPOS.
     * @param string $desde     Fecha inicio (YYYY-MM-DD).
     * @param string $hasta     Fecha fin (YYYY-MM-DD).
     *
     * @return array{columnas: array, filas: array}
     */
    public function consultar(string $tipo, string $desde, string $hasta): array
    {
        if (!array_key_exists($tipo, self::TIPOS)) {
            throw new \InvalidArgumentException('Tipo de reporte no válido');
        }

        return match ($tipo) {
            'ventas'      => $this->queryVentas($desde, $hasta),
            'inventario'  => $this->queryInventario(),
            'movimientos' => $this->queryMovimientos($desde, $hasta),
            'proveedores' => $this->queryProveedores($desde, $hasta),
            'cyber'       => $this->queryCyber(),
        };
    }

    private function queryVentas(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare("
            SELECT ov.fecha,
                   CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.apellido, '')) AS cliente,
                   ov.numero_de_orden,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total,
                   COUNT(lv.id) AS articulos
            FROM orden_de_venta ov
            LEFT JOIN clientes c ON ov.fk_cliente = c.id
            LEFT JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE ov.fecha BETWEEN ? AND ?
            GROUP BY ov.id, ov.fecha, ov.numero_de_orden, c.nombre, c.apellido
            ORDER BY ov.fecha, ov.id
        ");
        $stmt->bindParam(1, $desde, \PDO::PARAM_STR);
        $stmt->bindParam(2, $hasta, \PDO::PARAM_STR);
        $stmt->execute();
        return [
            'columnas' => ['Fecha', 'Cliente', 'N° Orden', 'Artículos', 'Total'],
            'filas'    => $stmt->fetchAll(),
        ];
    }

    private function queryInventario(): array
    {
        $stmt = $this->db->query("
            SELECT p.codigo, p.nombre, p.descripcion, p.stock, p.stock_minimo,
                   p.precio_compra, p.precio_venta,
                   COALESCE(c.nombre_categoria, 'Sin categoría') AS categoria
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            ORDER BY p.nombre
        ");
        return [
            'columnas' => ['Código', 'Producto', 'Categoría', 'Stock', 'Stock Mín.', 'Costo', 'Venta'],
            'filas'    => $stmt->fetchAll(),
        ];
    }

    private function queryMovimientos(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare("
            SELECT p.codigo, p.nombre,
                   SUM(lv.cantidad) AS unidades,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total
            FROM lineas_venta lv
            JOIN orden_de_venta ov ON lv.fk_orden = ov.id
            JOIN productos p ON lv.fk_producto = p.id
            WHERE ov.fecha BETWEEN ? AND ?
            GROUP BY p.id, p.codigo, p.nombre
            ORDER BY unidades DESC
        ");
        $stmt->bindParam(1, $desde, \PDO::PARAM_STR);
        $stmt->bindParam(2, $hasta, \PDO::PARAM_STR);
        $stmt->execute();
        return [
            'columnas' => ['Código', 'Producto', 'Unidades Vendidas', 'Total'],
            'filas'    => $stmt->fetchAll(),
        ];
    }

    private function queryProveedores(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare("
            SELECT oa.numero_de_orden, oa.fecha,
                   COALESCE(p.nombre, 'N/A') AS proveedor,
                   COALESCE(ss.status, 'N/A') AS estado,
                   COUNT(la.id) AS lineas
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            LEFT JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
            WHERE oa.fecha BETWEEN ? AND ?
            GROUP BY oa.id, oa.numero_de_orden, oa.fecha, p.nombre, ss.status
            ORDER BY oa.fecha DESC
        ");
        $stmt->bindParam(1, $desde, \PDO::PARAM_STR);
        $stmt->bindParam(2, $hasta, \PDO::PARAM_STR);
        $stmt->execute();
        return [
            'columnas' => ['N° Orden', 'Fecha', 'Proveedor', 'Estado', 'Líneas'],
            'filas'    => $stmt->fetchAll(),
        ];
    }

    private function queryCyber(): array
    {
        $stmt = $this->db->query("
            SELECT sc.id,
                   CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.apellido, '')) AS cliente,
                   COALESCE(a.marca, 'N/A') AS activo,
                   sc.tiempo_uso,
                   IF(sc.finalizada = 0, 'Activa', 'Finalizada') AS estado
            FROM sesion_ciber sc
            LEFT JOIN clientes c ON sc.fk_cliente = c.id
            LEFT JOIN activos a ON sc.fk_activo = a.id
            ORDER BY sc.id DESC
        ");
        return [
            'columnas' => ['ID', 'Cliente', 'Estación', 'Tiempo Uso', 'Estado'],
            'filas'    => $stmt->fetchAll(),
        ];
    }

    /**
     * Nombre legible de un tipo de reporte.
     */
    public function nombreTipo(string $tipo): string
    {
        return self::TIPOS[$tipo] ?? 'Reporte';
    }
}
