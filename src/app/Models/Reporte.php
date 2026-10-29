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
        'ventas'              => 'Ventas por fecha',
        'ventas_por_producto' => 'Ventas por producto',
        'ventas_por_cliente'  => 'Ventas por cliente',
        'inventario'          => 'Estado de inventario',
        'stock_bajo'          => 'Productos con stock bajo',
        'movimientos'         => 'Movimientos de stock',
        'proveedores'         => 'Solicitudes a proveedores',
        'compras'             => 'Compras a proveedores',
        'cyber'               => 'Horas Cybercafé',
        'caja_diaria'         => 'Resumen de caja diario',
        'rentabilidad'        => 'Rentabilidad por producto',
        'asesorias'           => 'Reporte de asesorías legales',
    ];

    /**
     * Catálogo de opciones para filtros dinámicos (categorías, proveedores, clientes, etc.)
     */
    public function catalogosFiltros(): array
    {
        $categorias = $this->db->query("SELECT id, nombre_categoria FROM categoria WHERE activo = 1 ORDER BY nombre_categoria ASC")->fetchAll();
        $proveedores = $this->db->query("SELECT id, nombre, rif FROM proveedores WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
        $clientes = $this->db->query("SELECT id, CONCAT(nombre, ' ', apellido) AS cliente, cedula FROM clientes WHERE activo = 1 ORDER BY nombre ASC LIMIT 100")->fetchAll();
        $estadosSolicitud = $this->db->query("SELECT id, status FROM status_seguimiento ORDER BY id ASC")->fetchAll();

        return [
            'categorias'       => $categorias,
            'proveedores'      => $proveedores,
            'clientes'         => $clientes,
            'estados_solicitud' => $estadosSolicitud,
        ];
    }
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
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = 1");
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
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM orden_abastecimiento WHERE activo = 1");
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
              AND oa.activo = 1
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
     * Ejecuta la consulta del reporte según su tipo, rango de fechas y filtros opcionales.
     *
     * @param string $tipo      Uno de self::TIPOS.
     * @param string $desde     Fecha inicio (YYYY-MM-DD).
     * @param string $hasta     Fecha fin (YYYY-MM-DD).
     * @param array  $filtros   Filtros adicionales opcionales (categoria_id, proveedor_id, cliente_id, status_id, etc.)
     *
     * @return array{columnas: array, filas: array, resumen?: array}
     */
    public function consultar(string $tipo, string $desde, string $hasta, array $filtros = []): array
    {
        if (!array_key_exists($tipo, self::TIPOS)) {
            throw new \InvalidArgumentException('Tipo de reporte no válido');
        }

        return match ($tipo) {
            'ventas'              => $this->queryVentas($desde, $hasta, $filtros),
            'ventas_por_producto' => $this->queryVentasPorProducto($desde, $hasta, $filtros),
            'ventas_por_cliente'  => $this->queryVentasPorCliente($desde, $hasta, $filtros),
            'inventario'          => $this->queryInventario($filtros),
            'stock_bajo'          => $this->queryStockBajo($filtros),
            'movimientos'         => $this->queryMovimientos($desde, $hasta, $filtros),
            'proveedores'         => $this->queryProveedores($desde, $hasta, $filtros),
            'compras'             => $this->queryCompras($desde, $hasta, $filtros),
            'cyber'               => $this->queryCyber($desde, $hasta, $filtros),
            'caja_diaria'         => $this->queryCajaDiaria($desde, $hasta),
            'rentabilidad'        => $this->queryRentabilidad($desde, $hasta, $filtros),
            'asesorias'           => $this->queryAsesorias($desde, $hasta, $filtros),
        };
    }

    private function queryVentas(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT ov.fecha,
                   CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.apellido, '')) AS cliente,
                   ov.numero_de_orden,
                   COALESCE(SUM(lv.cantidad), 0) AS articulos,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total
            FROM orden_de_venta ov
            LEFT JOIN clientes c ON ov.fk_cliente = c.id
            LEFT JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE ov.fecha BETWEEN :desde AND :hasta
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['cliente_id'])) {
            $sql .= " AND ov.fk_cliente = :cliente_id";
            $params[':cliente_id'] = (int)$filtros['cliente_id'];
        }

        $sql .= "
            GROUP BY ov.id, ov.fecha, ov.numero_de_orden, c.nombre, c.apellido
            ORDER BY ov.fecha DESC, ov.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $totalRecaudado = 0.0;
        $totalArticulos = 0;
        foreach ($filas as $f) {
            $totalRecaudado += (float)$f['total'];
            $totalArticulos += (int)$f['articulos'];
        }

        return [
            'columnas' => ['Fecha', 'Cliente', 'N° Orden', 'Artículos', 'Total'],
            'filas'    => $filas,
            'resumen'  => [
                'total_filas'     => count($filas),
                'total_monto'     => round($totalRecaudado, 2),
                'total_articulos' => $totalArticulos,
                'ticket_promedio' => count($filas) > 0 ? round($totalRecaudado / count($filas), 2) : 0,
            ]
        ];
    }

    private function queryVentasPorProducto(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT p.codigo,
                   p.nombre AS producto,
                   COALESCE(c.nombre_categoria, 'Sin categoría') AS categoria,
                   SUM(lv.cantidad) AS cantidad_vendida,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS subtotal,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) / NULLIF(SUM(lv.cantidad), 0) AS precio_promedio,
                   COUNT(DISTINCT ov.id) AS ordenes
            FROM lineas_venta lv
            JOIN orden_de_venta ov ON lv.fk_orden = ov.id
            JOIN productos p ON lv.fk_producto = p.id
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            WHERE ov.fecha BETWEEN :desde AND :hasta
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.fk_categoria = :categoria_id";
            $params[':categoria_id'] = (int)$filtros['categoria_id'];
        }

        $sql .= "
            GROUP BY p.id, p.codigo, p.nombre, c.nombre_categoria
            ORDER BY cantidad_vendida DESC
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $unidades = 0;
        $total = 0.0;
        foreach ($filas as $f) {
            $unidades += (int)$f['cantidad_vendida'];
            $total += (float)$f['subtotal'];
        }

        return [
            'columnas' => ['Código', 'Producto', 'Categoría', 'Cant. Vendida', 'Subtotal', 'Precio Prom.', 'Órdenes'],
            'filas'    => $filas,
            'resumen'  => [
                'total_filas'     => count($filas),
                'total_unidades'  => $unidades,
                'total_monto'     => round($total, 2),
            ]
        ];
    }

    private function queryVentasPorCliente(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.apellido, '')) AS cliente,
                   c.cedula,
                   c.telefono,
                   COUNT(DISTINCT ov.id) AS num_ordenes,
                   COALESCE(SUM(lv.cantidad), 0) AS total_articulos,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total_comprado,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) / NULLIF(COUNT(DISTINCT ov.id), 0) AS ticket_promedio
            FROM orden_de_venta ov
            LEFT JOIN clientes c ON ov.fk_cliente = c.id
            LEFT JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE ov.fecha BETWEEN :desde AND :hasta
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['cliente_id'])) {
            $sql .= " AND c.id = :cliente_id";
            $params[':cliente_id'] = (int)$filtros['cliente_id'];
        }

        $sql .= "
            GROUP BY c.id, c.nombre, c.apellido, c.cedula, c.telefono
            ORDER BY total_comprado DESC
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $totalComprado = 0.0;
        $totalOrdenes = 0;
        foreach ($filas as $f) {
            $totalComprado += (float)$f['total_comprado'];
            $totalOrdenes += (int)$f['num_ordenes'];
        }

        return [
            'columnas' => ['Cliente', 'Cédula', 'Teléfono', 'N° Órdenes', 'Artículos', 'Total Comprado', 'Ticket Prom.'],
            'filas'    => $filas,
            'resumen'  => [
                'total_filas'    => count($filas),
                'total_ordenes'  => $totalOrdenes,
                'total_monto'    => round($totalComprado, 2),
            ]
        ];
    }

    private function queryInventario(array $filtros = []): array
    {
        $sql = "
            SELECT p.codigo, p.nombre, p.descripcion, p.stock, p.stock_minimo,
                   p.precio_compra, p.precio_venta,
                   COALESCE(c.nombre_categoria, 'Sin categoría') AS categoria,
                   (p.stock * p.precio_venta) AS valor_venta,
                   (p.stock * p.precio_compra) AS valor_costo
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            WHERE p.activo = 1
        ";
        $params = [];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.fk_categoria = :categoria_id";
            $params[':categoria_id'] = (int)$filtros['categoria_id'];
        }

        $sql .= " ORDER BY p.nombre ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $valCosto = 0.0;
        $valVenta = 0.0;
        $stockTotal = 0;
        foreach ($filas as $f) {
            $valCosto += (float)$f['valor_costo'];
            $valVenta += (float)$f['valor_venta'];
            $stockTotal += (int)$f['stock'];
        }

        return [
            'columnas' => ['Código', 'Producto', 'Categoría', 'Stock', 'Stock Mín.', 'Costo', 'Venta', 'Valor en Costo', 'Valor en Venta'],
            'filas'    => $filas,
            'resumen'  => [
                'total_productos' => count($filas),
                'unidades_stock'  => $stockTotal,
                'valor_costo'     => round($valCosto, 2),
                'valor_venta'     => round($valVenta, 2),
                'margen_potencial'=> round($valVenta - $valCosto, 2),
            ]
        ];
    }

    private function queryStockBajo(array $filtros = []): array
    {
        $sql = "
            SELECT p.codigo, p.nombre,
                   COALESCE(c.nombre_categoria, 'Sin categoría') AS categoria,
                   p.stock, p.stock_minimo,
                   GREATEST(0, p.stock_minimo - p.stock) AS faltante,
                   p.precio_compra, p.precio_venta
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            WHERE p.activo = 1 AND p.stock < p.stock_minimo
        ";
        $params = [];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.fk_categoria = :categoria_id";
            $params[':categoria_id'] = (int)$filtros['categoria_id'];
        }

        $sql .= " ORDER BY (p.stock_minimo - p.stock) DESC, p.nombre ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $faltantes = 0;
        $inversionReposicion = 0.0;
        foreach ($filas as $f) {
            $faltantes += (int)$f['faltante'];
            $inversionReposicion += ((int)$f['faltante'] * (float)$f['precio_compra']);
        }

        return [
            'columnas' => ['Código', 'Producto', 'Categoría', 'Stock Actual', 'Stock Mínimo', 'Faltante', 'Costo', 'Venta'],
            'filas'    => $filas,
            'resumen'  => [
                'productos_criticos' => count($filas),
                'unidades_faltantes' => $faltantes,
                'costo_reposicion'   => round($inversionReposicion, 2),
            ]
        ];
    }

    private function queryMovimientos(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT p.codigo, p.nombre,
                   COALESCE(c.nombre_categoria, 'Sin categoría') AS categoria,
                   SUM(lv.cantidad) AS unidades,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total
            FROM lineas_venta lv
            JOIN orden_de_venta ov ON lv.fk_orden = ov.id
            JOIN productos p ON lv.fk_producto = p.id
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            WHERE ov.fecha BETWEEN :desde AND :hasta
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.fk_categoria = :categoria_id";
            $params[':categoria_id'] = (int)$filtros['categoria_id'];
        }

        $sql .= "
            GROUP BY p.id, p.codigo, p.nombre, c.nombre_categoria
            ORDER BY unidades DESC
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $unidades = 0;
        $total = 0.0;
        foreach ($filas as $f) {
            $unidades += (int)$f['unidades'];
            $total += (float)$f['total'];
        }

        return [
            'columnas' => ['Código', 'Producto', 'Categoría', 'Unidades Vendidas', 'Total'],
            'filas'    => $filas,
            'resumen'  => [
                'total_filas'    => count($filas),
                'total_unidades' => $unidades,
                'total_monto'    => round($total, 2),
            ]
        ];
    }

    private function queryProveedores(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT oa.numero_de_orden, oa.fecha,
                   COALESCE(p.nombre, 'N/A') AS proveedor,
                   COALESCE(p.rif, 'N/A') AS rif,
                   COALESCE(ss.status, 'N/A') AS estado,
                   COUNT(la.id) AS lineas,
                   COALESCE(SUM(la.cantidad * la.precio), 0) AS monto_total
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            LEFT JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
            WHERE oa.fecha BETWEEN :desde AND :hasta AND oa.activo = 1
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['proveedor_id'])) {
            $sql .= " AND oa.fk_proveedor = :proveedor_id";
            $params[':proveedor_id'] = (int)$filtros['proveedor_id'];
        }
        if (!empty($filtros['status_id'])) {
            $sql .= " AND oa.fk_status = :status_id";
            $params[':status_id'] = (int)$filtros['status_id'];
        }

        $sql .= "
            GROUP BY oa.id, oa.numero_de_orden, oa.fecha, p.nombre, p.rif, ss.status
            ORDER BY oa.fecha DESC, oa.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $montoTotal = 0.0;
        foreach ($filas as $f) {
            $montoTotal += (float)$f['monto_total'];
        }

        return [
            'columnas' => ['N° Orden', 'Fecha', 'Proveedor', 'RIF', 'Estado', 'Líneas', 'Monto Total'],
            'filas'    => $filas,
            'resumen'  => [
                'total_ordenes' => count($filas),
                'monto_total'   => round($montoTotal, 2),
            ]
        ];
    }

    private function queryCompras(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT p.nombre AS proveedor,
                   oa.numero_de_orden,
                   oa.fecha,
                   pr.codigo,
                   pr.nombre AS producto,
                   la.cantidad,
                   la.precio AS precio_unitario,
                   (la.cantidad * la.precio) AS subtotal,
                   COALESCE(ss.status, 'N/A') AS estado
            FROM lineas_abastecimiento la
            JOIN orden_abastecimiento oa ON la.fk_orden_abastecimiento = oa.id
            LEFT JOIN proveedores p ON oa.fk_proveedor = p.id
            LEFT JOIN productos pr ON la.fk_producto = pr.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            WHERE oa.fecha BETWEEN :desde AND :hasta AND oa.activo = 1
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['proveedor_id'])) {
            $sql .= " AND oa.fk_proveedor = :proveedor_id";
            $params[':proveedor_id'] = (int)$filtros['proveedor_id'];
        }
        if (!empty($filtros['status_id'])) {
            $sql .= " AND oa.fk_status = :status_id";
            $params[':status_id'] = (int)$filtros['status_id'];
        }

        $sql .= " ORDER BY oa.fecha DESC, oa.id DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $unidades = 0;
        $total = 0.0;
        foreach ($filas as $f) {
            $unidades += (int)$f['cantidad'];
            $total += (float)$f['subtotal'];
        }

        return [
            'columnas' => ['Proveedor', 'N° Orden', 'Fecha', 'Código', 'Producto', 'Cantidad', 'Precio Unit.', 'Subtotal', 'Estado'],
            'filas'    => $filas,
            'resumen'  => [
                'total_compras' => count($filas),
                'total_unidades'=> $unidades,
                'total_monto'   => round($total, 2),
            ]
        ];
    }

    private function queryCajaDiaria(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(ov.fecha) AS fecha,
                   COUNT(DISTINCT ov.id) AS ordenes,
                   COALESCE(SUM(lv.cantidad), 0) AS articulos,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS ventas_brutas,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) / NULLIF(COUNT(DISTINCT ov.id), 0) AS ticket_promedio
            FROM orden_de_venta ov
            LEFT JOIN lineas_venta lv ON lv.fk_orden = ov.id
            WHERE DATE(ov.fecha) BETWEEN ? AND ?
            GROUP BY DATE(ov.fecha)
            ORDER BY fecha ASC
        ");
        $stmt->bindParam(1, $desde, \PDO::PARAM_STR);
        $stmt->bindParam(2, $hasta, \PDO::PARAM_STR);
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $totalVentas = 0.0;
        $totalOrdenes = 0;
        foreach ($filas as $f) {
            $totalVentas += (float)$f['ventas_brutas'];
            $totalOrdenes += (int)$f['ordenes'];
        }

        return [
            'columnas' => ['Fecha', 'Órdenes', 'Artículos', 'Ventas Brutas', 'Ticket Promedio'],
            'filas'    => $filas,
            'resumen'  => [
                'total_dias'      => count($filas),
                'total_ordenes'   => $totalOrdenes,
                'total_recaudado' => round($totalVentas, 2),
                'promedio_diario' => count($filas) > 0 ? round($totalVentas / count($filas), 2) : 0,
            ]
        ];
    }

    private function queryCyber(string $desde = '', string $hasta = '', array $filtros = []): array
    {
        $sql = "
            SELECT sc.id,
                   CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.apellido, '')) AS cliente,
                   c.cedula,
                   COALESCE(a.marca, 'N/A') AS activo,
                   t.tarifa_hora,
                   sc.tiempo_uso,
                   IF(sc.finalizada = 0, 'Activa', 'Finalizada') AS estado
            FROM sesion_ciber sc
            LEFT JOIN clientes c ON sc.fk_cliente = c.id
            LEFT JOIN activos a ON sc.fk_activo = a.id
            LEFT JOIN tarifas t ON sc.fk_tarifa = t.id
            WHERE 1=1
        ";
        $params = [];

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $sql .= " AND sc.finalizada = :estado";
            $params[':estado'] = (int)$filtros['estado'];
        }

        $sql .= " ORDER BY sc.id DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $activas = 0;
        $finalizadas = 0;
        foreach ($filas as $f) {
            if ($f['estado'] === 'Activa') $activas++;
            else $finalizadas++;
        }

        return [
            'columnas' => ['ID', 'Cliente', 'Cédula', 'Estación', 'Tarifa/Hora', 'Tiempo Uso', 'Estado'],
            'filas'    => $filas,
            'resumen'  => [
                'total_sesiones' => count($filas),
                'sesiones_activas' => $activas,
                'sesiones_finalizadas' => $finalizadas,
            ]
        ];
    }

    private function queryRentabilidad(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT p.codigo,
                   p.nombre AS producto,
                   COALESCE(c.nombre_categoria, 'Sin categoría') AS categoria,
                   p.precio_compra AS costo_unitario,
                   p.precio_venta AS precio_venta,
                   (p.precio_venta - p.precio_compra) AS ganancia_unitaria,
                   CASE 
                       WHEN p.precio_compra > 0 THEN ROUND(((p.precio_venta - p.precio_compra) / p.precio_compra) * 100, 1)
                       ELSE 100 
                   END AS margen_porcentaje,
                   COALESCE(SUM(lv.cantidad), 0) AS unidades_vendidas,
                   COALESCE(SUM(lv.cantidad * (lv.precio - p.precio_compra)), 0) AS ganancia_total,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS ingresos_totales
            FROM productos p
            LEFT JOIN categoria c ON p.fk_categoria = c.id
            LEFT JOIN lineas_venta lv ON lv.fk_producto = p.id
            LEFT JOIN orden_de_venta ov ON lv.fk_orden = ov.id AND ov.fecha BETWEEN :desde AND :hasta
            WHERE p.activo = 1
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.fk_categoria = :categoria_id";
            $params[':categoria_id'] = (int)$filtros['categoria_id'];
        }

        $sql .= "
            GROUP BY p.id, p.codigo, p.nombre, c.nombre_categoria, p.precio_compra, p.precio_venta
            ORDER BY ganancia_total DESC, unidades_vendidas DESC
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        $ingresos = 0.0;
        $utilidad = 0.0;
        foreach ($filas as $f) {
            $ingresos += (float)$f['ingresos_totales'];
            $utilidad += (float)$f['ganancia_total'];
        }

        return [
            'columnas' => ['Código', 'Producto', 'Categoría', 'Costo Unit.', 'Precio Venta', 'Ganancia Unit.', 'Margen %', 'Uds. Vendidas', 'Ganancia Total', 'Ingresos Totales'],
            'filas'    => $filas,
            'resumen'  => [
                'total_productos' => count($filas),
                'ingresos_totales'=> round($ingresos, 2),
                'utilidad_neta'   => round($utilidad, 2),
                'margen_global'   => $ingresos > 0 ? round(($utilidad / $ingresos) * 100, 1) : 0,
            ]
        ];
    }

    private function queryAsesorias(string $desde, string $hasta, array $filtros = []): array
    {
        $sql = "
            SELECT a.id,
                   a.fecha,
                   a.documento,
                   COALESCE(ta.tipo, 'N/A') AS tipo_asesoria,
                   CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.apellido, '')) AS cliente,
                   COALESCE(c.cedula, 'N/A') AS cedula,
                   COALESCE(ca.rif, 'N/A') AS rif,
                   COALESCE(ca.tipo, 'civil') AS ambito,
                   a.descripcion
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes c ON ca.fk_cliente = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE a.fecha BETWEEN :desde AND :hasta AND a.activo = 1
        ";
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $sql .= " ORDER BY a.fecha DESC, a.id DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll();

        return [
            'columnas' => ['ID', 'Fecha', 'Documento', 'Tipo Asesoría', 'Cliente', 'Cédula', 'RIF', 'Ámbito', 'Descripción'],
            'filas'    => $filas,
            'resumen'  => [
                'total_asesorias' => count($filas),
            ]
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
