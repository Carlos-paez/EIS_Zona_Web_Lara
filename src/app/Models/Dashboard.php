<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo Dashboard.
 *
 * Calcula las métricas clave del negocio a partir de datos reales de la base
 * de datos: ventas del día, stock crítico, sesiones de cybercafé activas,
 * solicitudes pendientes, ventas por día y actividad reciente.
 */
class Dashboard extends Model
{
    /**
     * Total de ventas (suma de líneas) del día actual.
     *
     * @return array{total: float, transacciones: int}
     */
    public function ventasHoy(): array
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total,
                   COUNT(DISTINCT ov.id) AS transacciones
            FROM orden_de_venta ov
            JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE ov.fecha = CURDATE()
        ");
        $fila = $stmt->fetch();
        return [
            'total'         => (float)$fila['total'],
            'transacciones' => (int)$fila['transacciones'],
        ];
    }

    /**
     * Total de ventas (suma de líneas) de los últimos 7 días.
     *
     * @return array{total: float, transacciones: int}
     */
    public function ventasUltimos7Dias(): array
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total,
                   COUNT(DISTINCT ov.id) AS transacciones
            FROM orden_de_venta ov
            JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE ov.fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
        ");
        $fila = $stmt->fetch();
        return [
            'total'         => (float)$fila['total'],
            'transacciones' => (int)$fila['transacciones'],
        ];
    }

    /**
     * Productos con stock crítico (en o por debajo del mínimo).
     *
     * @return int
     */
    public function stockCritico(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= stock_minimo");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Productos sin stock (stock = 0).
     *
     * @return int
     */
    public function stockAgotado(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 0");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Sesiones de cybercafé actualmente activas (no finalizadas).
     *
     * @return int
     */
    public function sesionesCyberActivas(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM sesion_ciber WHERE finalizada = 0");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Solicitudes de abastecimiento pendientes.
     *
     * @return int
     */
    public function solicitudesPendientes(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total
            FROM orden_abastecimiento oa
            JOIN status_seguimiento ss ON oa.fk_status = ss.id
            WHERE ss.status = 'Pendiente'
        ");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Ventas por día de los últimos 7 días (para la tabla de actividad).
     *
     * @return array
     */
    public function ventasPorDia(): array
    {
        $stmt = $this->db->query("
            SELECT ov.fecha,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total,
                   COUNT(DISTINCT ov.id) AS transacciones
            FROM orden_de_venta ov
            LEFT JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE ov.fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
            GROUP BY ov.fecha
            ORDER BY ov.fecha
        ");
        return $stmt->fetchAll();
    }

    /**
     * Productos con stock en o por debajo del mínimo (primeros 5).
     *
     * @return array
     */
    public function productosStockCritico(): array
    {
        $stmt = $this->db->query("
            SELECT nombre, stock, stock_minimo
            FROM productos
            WHERE stock <= stock_minimo
            ORDER BY stock ASC, nombre ASC
            LIMIT 8
        ");
        return $stmt->fetchAll();
    }

    /**
     * Actividad reciente combinada (ventas, solicitudes y sesiones de cyber).
     *
     * @return array
     */
    public function actividadReciente(): array
    {
        $ventas = $this->db->query("
            SELECT CONCAT('Venta ', ov.numero_de_orden, ' procesada') AS titulo,
                   CONCAT('Fecha: ', ov.fecha, ' - $', FORMAT(COALESCE((
                       SELECT SUM(lv2.cantidad * lv2.precio)
                       FROM lineas_venta lv2 WHERE lv2.fk_orden = ov.id
                   ), 0), 2)) AS detalle,
                   'shopping_cart' AS icono,
                   '#e3f2fd' AS fondo,
                   '#1565c0' AS color,
                   ov.fecha AS fecha
            FROM orden_de_venta ov
            ORDER BY ov.id DESC
            LIMIT 4
        ")->fetchAll();

        $solicitudes = $this->db->query("
            SELECT CONCAT('Solicitud ', oa.numero_de_orden) AS titulo,
                   CONCAT('Proveedor: ', COALESCE(p.nombre, 'N/A')) AS detalle,
                   'request_quote' AS icono,
                   '#e8f5e9' AS fondo,
                   '#2e7d32' AS color,
                   oa.fecha AS fecha
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            ORDER BY oa.id DESC
            LIMIT 4
        ")->fetchAll();

        $cyber = $this->db->query("
            SELECT CONCAT('Sesión Cyber ', IF(sc.finalizada = 0, 'activa', 'finalizada')) AS titulo,
                   CONCAT('Estación: ', COALESCE(a.marca, 'N/A')) AS detalle,
                   'desktop_windows' AS icono,
                   '#fff3e0' AS fondo,
                   '#e65100' AS color,
                   NULL AS fecha
            FROM sesion_ciber sc
            LEFT JOIN activos a ON sc.fk_activo = a.id
            ORDER BY sc.id DESC
            LIMIT 4
        ")->fetchAll();

        return array_merge($ventas, $solicitudes, $cyber);
    }

    /**
     * KPI agregados para consumo AJAX.
     *
     * @return array
     */
    public function kpis(): array
    {
        $hoy = $this->ventasHoy();
        return [
            'ventas_hoy'          => $hoy['total'],
            'transacciones_hoy'   => $hoy['transacciones'],
            'stock_critico'       => $this->stockCritico(),
            'sesiones_cyber'      => $this->sesionesCyberActivas(),
            'solicitudes_pend'    => $this->solicitudesPendientes(),
        ];
    }
}
