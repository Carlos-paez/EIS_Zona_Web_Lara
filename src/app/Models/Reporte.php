<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo Reporte.
 *
 * Genera KPIs mensuales y consultas por tipo de reporte, rango de fechas y
 * filtros estratégicos. Cada consulta puede agruparse, ordenarse y limitarse,
 * y devuelve además un resumen ejecutivo de los datos filtrados.
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
     * Configuración de agrupaciones, filtros y ordenamientos por reporte.
     *
     * La vista y el JavaScript usan esta estructura para mostrar únicamente
     * los controles que aplican al tipo de reporte seleccionado.
     */
    public const CONFIG = [
        'ventas' => [
            'fechas'  => true,
            'filtros' => ['cliente', 'usuario', 'categoria', 'producto', 'monto_min'],
            'grupos'  => [
                'detalle' => [
                    'label' => 'Detalle por orden',
                    'orden' => [
                        'fecha_desc' => 'Más recientes primero',
                        'fecha_asc'  => 'Más antiguas primero',
                        'total_desc' => 'Mayor total',
                        'total_asc'  => 'Menor total',
                    ],
                ],
                'dia' => [
                    'label' => 'Por día',
                    'orden' => [
                        'fecha_desc' => 'Día más reciente',
                        'fecha_asc'  => 'Día más antiguo',
                        'total_desc' => 'Mayor total',
                        'total_asc'  => 'Menor total',
                    ],
                ],
                'mes' => [
                    'label' => 'Por mes',
                    'orden' => [
                        'periodo_desc' => 'Mes más reciente',
                        'periodo_asc'  => 'Mes más antiguo',
                        'total_desc'   => 'Mayor total',
                        'total_asc'    => 'Menor total',
                    ],
                ],
                'cliente' => [
                    'label' => 'Por cliente',
                    'orden' => [
                        'total_desc'  => 'Mayor total',
                        'total_asc'   => 'Menor total',
                        'nombre_asc'  => 'Cliente (A-Z)',
                        'compras_desc' => 'Más compras',
                    ],
                ],
                'producto' => [
                    'label' => 'Por producto',
                    'orden' => [
                        'unidades_desc' => 'Más unidades',
                        'unidades_asc'  => 'Menos unidades',
                        'total_desc'    => 'Mayor total',
                        'nombre_asc'    => 'Producto (A-Z)',
                    ],
                ],
                'vendedor' => [
                    'label' => 'Por vendedor',
                    'orden' => [
                        'total_desc' => 'Mayor total',
                        'total_asc'  => 'Menor total',
                        'nombre_asc' => 'Vendedor (A-Z)',
                    ],
                ],
            ],
        ],
        'inventario' => [
            'fechas'  => false,
            'filtros' => ['categoria', 'producto', 'estado_stock'],
            'grupos'  => [
                'detalle' => [
                    'label' => 'Detalle por producto',
                    'orden' => [
                        'nombre_asc' => 'Producto (A-Z)',
                        'stock_asc'  => 'Menor stock',
                        'stock_desc' => 'Mayor stock',
                        'valor_desc' => 'Mayor valor de stock',
                        'valor_asc'  => 'Menor valor de stock',
                    ],
                ],
                'categoria' => [
                    'label' => 'Por categoría',
                    'orden' => [
                        'categoria_asc' => 'Categoría (A-Z)',
                        'valor_desc'    => 'Mayor valor de venta',
                        'criticos_desc' => 'Más productos críticos',
                        'productos_desc' => 'Más productos',
                    ],
                ],
                'estado' => [
                    'label' => 'Por estado de stock',
                    'orden' => [
                        'estado_asc'    => 'Estado (A-Z)',
                        'productos_desc' => 'Más productos',
                        'valor_desc'    => 'Mayor valor de venta',
                    ],
                ],
            ],
        ],
        'movimientos' => [
            'fechas'  => true,
            'filtros' => ['cliente', 'usuario', 'categoria', 'producto'],
            'grupos'  => [
                'producto' => [
                    'label' => 'Por producto',
                    'orden' => [
                        'unidades_desc' => 'Más unidades',
                        'unidades_asc'  => 'Menos unidades',
                        'total_desc'    => 'Mayor total',
                        'total_asc'     => 'Menor total',
                        'nombre_asc'    => 'Producto (A-Z)',
                    ],
                ],
                'categoria' => [
                    'label' => 'Por categoría',
                    'orden' => [
                        'total_desc'   => 'Mayor total',
                        'unidades_desc' => 'Más unidades',
                        'categoria_asc' => 'Categoría (A-Z)',
                    ],
                ],
                'dia' => [
                    'label' => 'Por día',
                    'orden' => [
                        'fecha_desc' => 'Día más reciente',
                        'fecha_asc'  => 'Día más antiguo',
                        'total_desc' => 'Mayor total',
                    ],
                ],
                'mes' => [
                    'label' => 'Por mes',
                    'orden' => [
                        'periodo_desc' => 'Mes más reciente',
                        'periodo_asc'  => 'Mes más antiguo',
                        'total_desc'   => 'Mayor total',
                    ],
                ],
            ],
        ],
        'proveedores' => [
            'fechas'  => true,
            'filtros' => ['proveedor', 'estado', 'categoria', 'producto', 'monto_min'],
            'grupos'  => [
                'detalle' => [
                    'label' => 'Detalle por orden',
                    'orden' => [
                        'fecha_desc' => 'Más recientes primero',
                        'fecha_asc'  => 'Más antiguas primero',
                        'monto_desc' => 'Mayor monto',
                        'monto_asc'  => 'Menor monto',
                    ],
                ],
                'proveedor' => [
                    'label' => 'Por proveedor',
                    'orden' => [
                        'monto_desc'  => 'Mayor monto',
                        'monto_asc'   => 'Menor monto',
                        'nombre_asc'  => 'Proveedor (A-Z)',
                        'ordenes_desc' => 'Más órdenes',
                    ],
                ],
                'estado' => [
                    'label' => 'Por estado',
                    'orden' => [
                        'estado_asc'  => 'Estado (A-Z)',
                        'monto_desc'  => 'Mayor monto',
                        'ordenes_desc' => 'Más órdenes',
                    ],
                ],
                'mes' => [
                    'label' => 'Por mes',
                    'orden' => [
                        'periodo_desc' => 'Mes más reciente',
                        'periodo_asc'  => 'Mes más antiguo',
                        'monto_desc'   => 'Mayor monto',
                    ],
                ],
            ],
        ],
        'cyber' => [
            'fechas'  => false,
            'filtros' => ['cliente', 'activo', 'estado_ciber'],
            'grupos'  => [
                'detalle' => [
                    'label' => 'Detalle por sesión',
                    'orden' => [
                        'id_desc'      => 'Sesión más reciente',
                        'cliente_asc'  => 'Cliente (A-Z)',
                        'activo_asc'   => 'Estación (A-Z)',
                        'estado_asc'   => 'Estado (A-Z)',
                    ],
                ],
                'cliente' => [
                    'label' => 'Por cliente',
                    'orden' => [
                        'sesiones_desc' => 'Más sesiones',
                        'nombre_asc'    => 'Cliente (A-Z)',
                        'tiempo_desc'   => 'Mayor tiempo de uso',
                    ],
                ],
                'activo' => [
                    'label' => 'Por estación',
                    'orden' => [
                        'sesiones_desc' => 'Más sesiones',
                        'activo_asc'    => 'Estación (A-Z)',
                        'tiempo_desc'   => 'Mayor tiempo de uso',
                    ],
                ],
                'estado' => [
                    'label' => 'Por estado',
                    'orden' => [
                        'estado_asc'    => 'Estado (A-Z)',
                        'sesiones_desc' => 'Más sesiones',
                        'tiempo_desc'   => 'Mayor tiempo de uso',
                    ],
                ],
            ],
        ],
    ];

    /**
     * Total de ventas del mes actual.
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
     */
    public function productosActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Total de sesiones de cybercafé registradas.
     */
    public function totalSesionesCyber(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM sesion_ciber");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Sesiones de cybercafé activas en este momento.
     */
    public function sesionesCyberActivas(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM sesion_ciber WHERE finalizada = 0");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Total de solicitudes de abastecimiento.
     */
    public function totalSolicitudes(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM orden_abastecimiento");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Solicitudes aprobadas o en tránsito (procesadas).
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
     * Indica si un tipo de reporte existe.
     */
    public static function existeTipo(string $tipo): bool
    {
        return isset(self::CONFIG[$tipo]);
    }

    /**
     * Indica si un reporte usa rango de fechas.
     */
    public static function usaFechas(string $tipo): bool
    {
        return (bool)(self::CONFIG[$tipo]['fechas'] ?? false);
    }

    /**
     * Opciones reales de la base de datos para los filtros contextuales.
     */
    public function opcionesFiltros(): array
    {
        return [
            'clientes' => $this->db->query("
                SELECT id, cedula, nombre, apellido
                FROM clientes
                ORDER BY nombre, apellido
            ")->fetchAll(\PDO::FETCH_ASSOC),
            'categorias' => $this->db->query("
                SELECT id, nombre_categoria
                FROM categoria
                ORDER BY nombre_categoria
            ")->fetchAll(\PDO::FETCH_ASSOC),
            'productos' => $this->db->query("
                SELECT id, codigo, nombre
                FROM productos
                ORDER BY nombre
            ")->fetchAll(\PDO::FETCH_ASSOC),
            'proveedores' => $this->db->query("
                SELECT id, nombre
                FROM proveedores
                ORDER BY nombre
            ")->fetchAll(\PDO::FETCH_ASSOC),
            'usuarios' => $this->db->query("
                SELECT id, nombre, apellido, user_name
                FROM usuarios
                ORDER BY nombre, apellido
            ")->fetchAll(\PDO::FETCH_ASSOC),
            'estados' => $this->db->query("
                SELECT id, status
                FROM status_seguimiento
                ORDER BY id
            ")->fetchAll(\PDO::FETCH_ASSOC),
            'activos' => $this->db->query("
                SELECT id, marca, descripcion
                FROM activos
                WHERE is_ciber = 1 AND activa = 1
                ORDER BY marca, descripcion
            ")->fetchAll(\PDO::FETCH_ASSOC),
        ];
    }

    /**
     * Ejecuta la consulta del reporte según su tipo, rango de fechas y filtros.
     *
     * @return array{columnas: array, filas: array, resumen: array, contexto: array, agrupacion: string}
     */
    public function consultar(string $tipo, string $desde, string $hasta, array $filtros = []): array
    {
        if (!self::existeTipo($tipo)) {
            throw new \InvalidArgumentException('Tipo de reporte no válido');
        }

        $filtros = $this->normalizarFiltros($tipo, $filtros);

        $resultado = match ($tipo) {
            'ventas'      => $this->queryVentas($desde, $hasta, $filtros),
            'inventario'  => $this->queryInventario($filtros),
            'movimientos' => $this->queryMovimientos($desde, $hasta, $filtros),
            'proveedores' => $this->queryProveedores($desde, $hasta, $filtros),
            'cyber'       => $this->queryCyber($filtros),
        };

        $resultado['resumen']   = $this->resumenReporte($tipo, $desde, $hasta, $filtros);
        $resultado['contexto']  = $this->contextoFiltros($tipo, $filtros);
        $resultado['agrupacion'] = self::CONFIG[$tipo]['grupos'][$filtros['agrupar']]['label'];
        $resultado['tipo']      = $tipo;
        $resultado['filtros']   = $filtros;
        $resultado['limite']    = $filtros['limite'];
        $resultado['total']     = count($resultado['filas']);

        return $resultado;
    }

    /**
     * Normaliza y valida los filtros recibidos desde la interfaz.
     */
    private function normalizarFiltros(string $tipo, array $filtros): array
    {
        $config  = self::CONFIG[$tipo];
        $grupos  = array_keys($config['grupos']);

        $agrupar = $this->textoFiltro($filtros['agrupar'] ?? '');
        if (!in_array($agrupar, $grupos, true)) {
            $agrupar = (string)$grupos[0];
        }

        $ordenes = $config['grupos'][$agrupar]['orden'];
        $orden   = $this->textoFiltro($filtros['orden'] ?? '');
        if (!array_key_exists($orden, $ordenes)) {
            $orden = (string)array_key_first($ordenes);
        }

        $limite = $this->enteroFiltro($filtros['limite'] ?? 0);
        if (!in_array($limite, [0, 10, 25, 50, 100, 200], true)) {
            $limite = 0;
        }

        $estadoStock = $this->textoFiltro($filtros['estado_stock'] ?? 'todos');
        if (!in_array($estadoStock, ['todos', 'critico', 'bajo', 'agotado', 'ok'], true)) {
            $estadoStock = 'todos';
        }

        $estadoCiber = strtolower($this->textoFiltro($filtros['estado_ciber'] ?? ''));
        if (!in_array($estadoCiber, ['', 'activa', 'finalizada'], true)) {
            $estadoCiber = '';
        }

        $normalizados = [
            'agrupar'      => $agrupar,
            'orden'        => $orden,
            'limite'       => $limite,
            'cliente'      => $this->enteroFiltro($filtros['cliente'] ?? 0),
            'usuario'      => $this->enteroFiltro($filtros['usuario'] ?? 0),
            'categoria'    => $this->enteroFiltro($filtros['categoria'] ?? 0),
            'producto'     => $this->enteroFiltro($filtros['producto'] ?? 0),
            'proveedor'    => $this->enteroFiltro($filtros['proveedor'] ?? 0),
            'estado'       => $this->enteroFiltro($filtros['estado'] ?? 0),
            'activo'       => $this->enteroFiltro($filtros['activo'] ?? 0),
            'monto_min'    => $this->decimalFiltro($filtros['monto_min'] ?? 0),
            'estado_stock' => $estadoStock,
            'estado_ciber' => $estadoCiber,
        ];

        // Los filtros ocultos de otro tipo de reporte no deben contaminar
        // el contexto ni el resumen (por ejemplo, un cliente conservado al
        // cambiar de ventas a inventario).
        foreach (['cliente', 'usuario', 'categoria', 'producto', 'proveedor', 'estado', 'activo'] as $clave) {
            if (!in_array($clave, $config['filtros'], true)) {
                $normalizados[$clave] = 0;
            }
        }

        if (!in_array('monto_min', $config['filtros'], true)) {
            $normalizados['monto_min'] = 0.0;
        }
        if (!in_array('estado_stock', $config['filtros'], true)) {
            $normalizados['estado_stock'] = 'todos';
        }
        if (!in_array('estado_ciber', $config['filtros'], true)) {
            $normalizados['estado_ciber'] = '';
        }

        return $normalizados;
    }

    private function textoFiltro(mixed $valor): string
    {
        return is_scalar($valor) ? trim((string)$valor) : '';
    }

    private function enteroFiltro(mixed $valor): int
    {
        return is_scalar($valor) ? max(0, (int)$valor) : 0;
    }

    private function decimalFiltro(mixed $valor): float
    {
        if (!is_scalar($valor) || !is_numeric($valor)) {
            return 0.0;
        }
        return max(0.0, (float)$valor);
    }

    private function baseVentas(): string
    {
        return "
            FROM orden_de_venta ov
            LEFT JOIN clientes cl ON ov.fk_cliente = cl.id
            LEFT JOIN usuarios u ON ov.fk_usuario = u.id
            JOIN lineas_venta lv ON lv.fk_orden = ov.id
            LEFT JOIN productos p ON lv.fk_producto = p.id
            LEFT JOIN categoria cat ON p.fk_categoria = cat.id
        ";
    }

    private function baseInventario(): string
    {
        return "
            FROM productos p
            LEFT JOIN categoria cat ON p.fk_categoria = cat.id
        ";
    }

    private function baseMovimientos(): string
    {
        return "
            FROM lineas_venta lv
            JOIN orden_de_venta ov ON lv.fk_orden = ov.id
            JOIN productos p ON lv.fk_producto = p.id
            LEFT JOIN categoria cat ON p.fk_categoria = cat.id
        ";
    }

    private function baseProveedores(): string
    {
        return "
            FROM orden_abastecimiento oa
            LEFT JOIN proveedores pr ON oa.fk_proveedor = pr.id
            LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
            LEFT JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
            LEFT JOIN productos p ON la.fk_producto = p.id
            LEFT JOIN categoria cat ON p.fk_categoria = cat.id
        ";
    }

    private function baseCyber(): string
    {
        return "
            FROM sesion_ciber sc
            LEFT JOIN clientes cl ON sc.fk_cliente = cl.id
            LEFT JOIN activos a ON sc.fk_activo = a.id
        ";
    }

    private function whereVentas(string $desde, string $hasta, array $f, array &$params): string
    {
        $where   = ['ov.fecha BETWEEN ? AND ?'];
        $params  = [$desde, $hasta];

        if ($f['cliente'] > 0) {
            $where[]  = 'ov.fk_cliente = ?';
            $params[] = $f['cliente'];
        }
        if ($f['usuario'] > 0) {
            $where[]  = 'ov.fk_usuario = ?';
            $params[] = $f['usuario'];
        }
        if ($f['categoria'] > 0) {
            $where[]  = 'p.fk_categoria = ?';
            $params[] = $f['categoria'];
        }
        if ($f['producto'] > 0) {
            $where[]  = 'lv.fk_producto = ?';
            $params[] = $f['producto'];
        }
        if ($f['monto_min'] > 0) {
            // El monto mínimo se evalúa por orden antes de agrupar, de modo
            // que el detalle y los totales agrupados usen el mismo conjunto.
            $where[]  = 'ov.id IN (
                SELECT lv_monto.fk_orden
                FROM lineas_venta lv_monto
                GROUP BY lv_monto.fk_orden
                HAVING SUM(lv_monto.cantidad * lv_monto.precio) >= ?
            )';
            $params[] = $f['monto_min'];
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    private function whereInventario(array $f, array &$params): string
    {
        $where  = ['1 = 1'];
        $params = [];

        if ($f['categoria'] > 0) {
            $where[]  = 'p.fk_categoria = ?';
            $params[] = $f['categoria'];
        }
        if ($f['producto'] > 0) {
            $where[]  = 'p.id = ?';
            $params[] = $f['producto'];
        }

        switch ($f['estado_stock']) {
            case 'critico':
                $where[] = 'p.stock <= p.stock_minimo';
                break;
            case 'bajo':
                $where[] = 'p.stock > 0 AND p.stock <= p.stock_minimo';
                break;
            case 'agotado':
                $where[] = 'p.stock <= 0';
                break;
            case 'ok':
                $where[] = 'p.stock > p.stock_minimo';
                break;
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    private function whereMovimientos(string $desde, string $hasta, array $f, array &$params): string
    {
        $where  = ['ov.fecha BETWEEN ? AND ?'];
        $params = [$desde, $hasta];

        if ($f['cliente'] > 0) {
            $where[]  = 'ov.fk_cliente = ?';
            $params[] = $f['cliente'];
        }
        if ($f['usuario'] > 0) {
            $where[]  = 'ov.fk_usuario = ?';
            $params[] = $f['usuario'];
        }
        if ($f['categoria'] > 0) {
            $where[]  = 'p.fk_categoria = ?';
            $params[] = $f['categoria'];
        }
        if ($f['producto'] > 0) {
            $where[]  = 'lv.fk_producto = ?';
            $params[] = $f['producto'];
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    private function whereProveedores(string $desde, string $hasta, array $f, array &$params): string
    {
        $where  = ['oa.fecha BETWEEN ? AND ?'];
        $params = [$desde, $hasta];

        if ($f['proveedor'] > 0) {
            $where[]  = 'oa.fk_proveedor = ?';
            $params[] = $f['proveedor'];
        }
        if ($f['estado'] > 0) {
            $where[]  = 'oa.fk_status = ?';
            $params[] = $f['estado'];
        }
        if ($f['categoria'] > 0) {
            $where[]  = 'p.fk_categoria = ?';
            $params[] = $f['categoria'];
        }
        if ($f['producto'] > 0) {
            $where[]  = 'la.fk_producto = ?';
            $params[] = $f['producto'];
        }
        if ($f['monto_min'] > 0) {
            // Igual que en ventas: el umbral se aplica por orden.
            $where[]  = 'oa.id IN (
                SELECT la_monto.fk_orden_abastecimiento
                FROM lineas_abastecimiento la_monto
                GROUP BY la_monto.fk_orden_abastecimiento
                HAVING SUM(la_monto.cantidad * la_monto.precio) >= ?
            )';
            $params[] = $f['monto_min'];
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    private function whereCyber(array $f, array &$params): string
    {
        $where  = ['1 = 1'];
        $params = [];

        if ($f['cliente'] > 0) {
            $where[]  = 'sc.fk_cliente = ?';
            $params[] = $f['cliente'];
        }
        if ($f['activo'] > 0) {
            $where[]  = 'sc.fk_activo = ?';
            $params[] = $f['activo'];
        }
        if ($f['estado_ciber'] === 'activa') {
            $where[] = 'sc.finalizada = 0';
        } elseif ($f['estado_ciber'] === 'finalizada') {
            $where[] = 'sc.finalizada = 1';
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    private function limitSql(int $limite): string
    {
        return $limite > 0 ? ' LIMIT ' . $limite : '';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function fetchFilas(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchUno(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
        return is_array($fila) ? $fila : [];
    }

    // ------------------------------------------------------------------
    // Consultas por tipo de reporte
    // ------------------------------------------------------------------

    private function queryVentas(string $desde, string $hasta, array $f): array
    {
        $params = [];
        $where  = $this->whereVentas($desde, $hasta, $f, $params);

        $order = $this->ordenPorTipo('ventas', $f['agrupar'], $f['orden']);
        $limit = $this->limitSql($f['limite']);
        $base  = $this->baseVentas();

        switch ($f['agrupar']) {
            case 'dia':
                $sql = "SELECT DATE_FORMAT(ov.fecha, '%d/%m/%Y') AS dia,
                               COUNT(DISTINCT ov.id) AS ordenes,
                               COUNT(lv.id) AS articulos,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY ov.fecha, DATE_FORMAT(ov.fecha, '%d/%m/%Y')"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Día', 'Órdenes', 'Artículos', 'Unidades', 'Total'];
                break;

            case 'mes':
                $sql = "SELECT DATE_FORMAT(ov.fecha, '%m/%Y') AS periodo,
                               COUNT(DISTINCT ov.id) AS ordenes,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY YEAR(ov.fecha), MONTH(ov.fecha), DATE_FORMAT(ov.fecha, '%m/%Y')"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Periodo', 'Órdenes', 'Unidades', 'Total'];
                break;

            case 'cliente':
                $sql = "SELECT CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente,
                               COUNT(DISTINCT ov.id) AS compras,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY cl.id, cl.nombre, cl.apellido"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Cliente', 'Compras', 'Unidades', 'Total'];
                break;

            case 'producto':
                $sql = "SELECT p.codigo,
                               p.nombre AS producto,
                               COALESCE(cat.nombre_categoria, 'Sin categoría') AS categoria,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY p.id, p.codigo, p.nombre, cat.nombre_categoria"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Código', 'Producto', 'Categoría', 'Unidades', 'Total'];
                break;

            case 'vendedor':
                $sql = "SELECT CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, '')) AS vendedor,
                               COUNT(DISTINCT ov.id) AS ordenes,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY u.id, u.nombre, u.apellido"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Vendedor', 'Órdenes', 'Unidades', 'Total'];
                break;

            case 'detalle':
            default:
                $sql = "SELECT DATE_FORMAT(ov.fecha, '%d/%m/%Y') AS fecha,
                               CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente,
                               ov.numero_de_orden,
                               COUNT(lv.id) AS articulos,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY ov.id, ov.fecha, ov.numero_de_orden, cl.nombre, cl.apellido"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Fecha', 'Cliente', 'N° Orden', 'Artículos', 'Unidades', 'Total'];
                break;
        }

        return [
            'columnas' => $columnas,
            'filas'    => $this->fetchFilas($sql, $params),
        ];
    }

    private function queryInventario(array $f): array
    {
        $params = [];
        $where  = $this->whereInventario($f, $params);
        $order  = $this->ordenPorTipo('inventario', $f['agrupar'], $f['orden']);
        $limit  = $this->limitSql($f['limite']);
        $base   = $this->baseInventario();

        switch ($f['agrupar']) {
            case 'categoria':
                $sql = "SELECT COALESCE(cat.nombre_categoria, 'Sin categoría') AS categoria,
                               COUNT(p.id) AS productos,
                               COALESCE(SUM(p.stock), 0) AS unidades,
                               COALESCE(SUM(p.stock * p.precio_compra), 0) AS valor_costo,
                               COALESCE(SUM(p.stock * p.precio_venta), 0) AS valor_venta,
                               SUM(CASE WHEN p.stock <= p.stock_minimo THEN 1 ELSE 0 END) AS criticos"
                    . $base . $where
                    . ' GROUP BY cat.id, cat.nombre_categoria'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Categoría', 'Productos', 'Unidades', 'Valor Costo', 'Valor Venta', 'Críticos'];
                break;

            case 'estado':
                $sql = "SELECT CASE
                                   WHEN p.stock <= 0 THEN 'Agotado'
                                   WHEN p.stock <= p.stock_minimo THEN 'Bajo'
                                   ELSE 'Disponible'
                               END AS estado,
                               COUNT(*) AS productos,
                               COALESCE(SUM(p.stock), 0) AS unidades,
                               COALESCE(SUM(p.stock * p.precio_venta), 0) AS valor_venta"
                    . $base . $where
                    . " GROUP BY CASE
                                   WHEN p.stock <= 0 THEN 'Agotado'
                                   WHEN p.stock <= p.stock_minimo THEN 'Bajo'
                                   ELSE 'Disponible'
                               END"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Estado', 'Productos', 'Unidades', 'Valor Venta'];
                break;

            case 'detalle':
            default:
                $sql = "SELECT p.codigo,
                               p.nombre AS producto,
                               COALESCE(cat.nombre_categoria, 'Sin categoría') AS categoria,
                               p.stock,
                               p.stock_minimo,
                               p.precio_compra AS costo,
                               p.precio_venta AS venta,
                               (p.stock * p.precio_venta) AS valor_stock,
                               CASE
                                   WHEN p.stock <= 0 THEN 'Agotado'
                                   WHEN p.stock <= p.stock_minimo THEN 'Bajo'
                                   ELSE 'Disponible'
                               END AS estado"
                    . $base . $where
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Código', 'Producto', 'Categoría', 'Stock', 'Stock Mín.', 'Costo', 'Venta', 'Valor Stock', 'Estado'];
                break;
        }

        return [
            'columnas' => $columnas,
            'filas'    => $this->fetchFilas($sql, $params),
        ];
    }

    private function queryMovimientos(string $desde, string $hasta, array $f): array
    {
        $params = [];
        $where  = $this->whereMovimientos($desde, $hasta, $f, $params);
        $order  = $this->ordenPorTipo('movimientos', $f['agrupar'], $f['orden']);
        $limit  = $this->limitSql($f['limite']);
        $base   = $this->baseMovimientos();

        switch ($f['agrupar']) {
            case 'categoria':
                $sql = "SELECT COALESCE(cat.nombre_categoria, 'Sin categoría') AS categoria,
                               COUNT(DISTINCT p.id) AS productos,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . ' GROUP BY cat.id, cat.nombre_categoria'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Categoría', 'Productos', 'Unidades Vendidas', 'Total'];
                break;

            case 'dia':
                $sql = "SELECT DATE_FORMAT(ov.fecha, '%d/%m/%Y') AS dia,
                               COUNT(DISTINCT p.id) AS productos,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY ov.fecha, DATE_FORMAT(ov.fecha, '%d/%m/%Y')"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Día', 'Productos', 'Unidades Vendidas', 'Total'];
                break;

            case 'mes':
                $sql = "SELECT DATE_FORMAT(ov.fecha, '%m/%Y') AS periodo,
                               COUNT(DISTINCT p.id) AS productos,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . " GROUP BY YEAR(ov.fecha), MONTH(ov.fecha), DATE_FORMAT(ov.fecha, '%m/%Y')"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Periodo', 'Productos', 'Unidades Vendidas', 'Total'];
                break;

            case 'producto':
            default:
                $sql = "SELECT p.codigo,
                               p.nombre AS producto,
                               COALESCE(cat.nombre_categoria, 'Sin categoría') AS categoria,
                               COALESCE(SUM(lv.cantidad), 0) AS unidades,
                               COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total"
                    . $base . $where
                    . ' GROUP BY p.id, p.codigo, p.nombre, cat.nombre_categoria'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Código', 'Producto', 'Categoría', 'Unidades Vendidas', 'Total'];
                break;
        }

        return [
            'columnas' => $columnas,
            'filas'    => $this->fetchFilas($sql, $params),
        ];
    }

    private function queryProveedores(string $desde, string $hasta, array $f): array
    {
        $params = [];
        $where  = $this->whereProveedores($desde, $hasta, $f, $params);

        $order = $this->ordenPorTipo('proveedores', $f['agrupar'], $f['orden']);
        $limit = $this->limitSql($f['limite']);
        $base  = $this->baseProveedores();

        switch ($f['agrupar']) {
            case 'proveedor':
                $sql = "SELECT COALESCE(pr.nombre, 'N/A') AS proveedor,
                               COUNT(DISTINCT oa.id) AS ordenes,
                               COALESCE(SUM(la.cantidad), 0) AS unidades,
                               COALESCE(SUM(la.cantidad * la.precio), 0) AS monto,
                               COALESCE(SUM(la.cantidad * la.precio) / NULLIF(COUNT(DISTINCT oa.id), 0), 0) AS monto_promedio"
                    . $base . $where
                    . ' GROUP BY pr.id, pr.nombre'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Proveedor', 'Órdenes', 'Unidades', 'Monto', 'Monto Promedio'];
                break;

            case 'estado':
                $sql = "SELECT COALESCE(ss.status, 'N/A') AS estado,
                               COUNT(DISTINCT oa.id) AS ordenes,
                               COALESCE(SUM(la.cantidad), 0) AS unidades,
                               COALESCE(SUM(la.cantidad * la.precio), 0) AS monto"
                    . $base . $where
                    . ' GROUP BY ss.id, ss.status'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Estado', 'Órdenes', 'Unidades', 'Monto'];
                break;

            case 'mes':
                $sql = "SELECT DATE_FORMAT(oa.fecha, '%m/%Y') AS periodo,
                               COUNT(DISTINCT oa.id) AS ordenes,
                               COALESCE(SUM(la.cantidad), 0) AS unidades,
                               COALESCE(SUM(la.cantidad * la.precio), 0) AS monto"
                    . $base . $where
                    . " GROUP BY YEAR(oa.fecha), MONTH(oa.fecha), DATE_FORMAT(oa.fecha, '%m/%Y')"
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Periodo', 'Órdenes', 'Unidades', 'Monto'];
                break;

            case 'detalle':
            default:
                $sql = "SELECT oa.numero_de_orden,
                               DATE_FORMAT(oa.fecha, '%d/%m/%Y') AS fecha,
                               COALESCE(pr.nombre, 'N/A') AS proveedor,
                               COALESCE(ss.status, 'N/A') AS estado,
                               COUNT(la.id) AS lineas,
                               COALESCE(SUM(la.cantidad), 0) AS unidades,
                               COALESCE(SUM(la.cantidad * la.precio), 0) AS monto"
                    . $base . $where
                    . ' GROUP BY oa.id, oa.numero_de_orden, oa.fecha, pr.nombre, ss.status'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['N° Orden', 'Fecha', 'Proveedor', 'Estado', 'Líneas', 'Unidades', 'Monto'];
                break;
        }

        return [
            'columnas' => $columnas,
            'filas'    => $this->fetchFilas($sql, $params),
        ];
    }

    private function queryCyber(array $f): array
    {
        $params = [];
        $where  = $this->whereCyber($f, $params);
        $order  = $this->ordenPorTipo('cyber', $f['agrupar'], $f['orden']);
        $limit  = $this->limitSql($f['limite']);
        $base   = $this->baseCyber();

        switch ($f['agrupar']) {
            case 'cliente':
                $sql = "SELECT CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente,
                               COUNT(*) AS sesiones,
                               SUM(CASE WHEN sc.finalizada = 0 THEN 1 ELSE 0 END) AS activas,
                               SUM(CASE WHEN sc.finalizada = 1 THEN 1 ELSE 0 END) AS finalizadas,
                               SEC_TO_TIME(COALESCE(SUM(TIME_TO_SEC(sc.tiempo_uso)), 0)) AS tiempo_total"
                    . $base . $where
                    . ' GROUP BY cl.id, cl.nombre, cl.apellido'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Cliente', 'Sesiones', 'Activas', 'Finalizadas', 'Tiempo Total'];
                break;

            case 'activo':
                $sql = "SELECT CONCAT(COALESCE(a.marca, 'N/A'), ' ', COALESCE(a.descripcion, '')) AS activo,
                               COUNT(*) AS sesiones,
                               SUM(CASE WHEN sc.finalizada = 0 THEN 1 ELSE 0 END) AS activas,
                               SUM(CASE WHEN sc.finalizada = 1 THEN 1 ELSE 0 END) AS finalizadas,
                               SEC_TO_TIME(COALESCE(SUM(TIME_TO_SEC(sc.tiempo_uso)), 0)) AS tiempo_total"
                    . $base . $where
                    . ' GROUP BY a.id, a.marca, a.descripcion'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Estación', 'Sesiones', 'Activas', 'Finalizadas', 'Tiempo Total'];
                break;

            case 'estado':
                $sql = "SELECT IF(sc.finalizada = 0, 'Activa', 'Finalizada') AS estado,
                               COUNT(*) AS sesiones,
                               SEC_TO_TIME(COALESCE(SUM(TIME_TO_SEC(sc.tiempo_uso)), 0)) AS tiempo_total"
                    . $base . $where
                    . ' GROUP BY sc.finalizada'
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['Estado', 'Sesiones', 'Tiempo Total'];
                break;

            case 'detalle':
            default:
                $sql = "SELECT sc.id,
                               CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente,
                               CONCAT(COALESCE(a.marca, 'N/A'), ' ', COALESCE(a.descripcion, '')) AS activo,
                               sc.tiempo_uso,
                               IF(sc.finalizada = 0, 'Activa', 'Finalizada') AS estado"
                    . $base . $where
                    . ' ORDER BY ' . $order . $limit;
                $columnas = ['ID', 'Cliente', 'Estación', 'Tiempo Uso', 'Estado'];
                break;
        }

        return [
            'columnas' => $columnas,
            'filas'    => $this->fetchFilas($sql, $params),
        ];
    }

    // ------------------------------------------------------------------
    // Resumen estratégico
    // ------------------------------------------------------------------

    private function resumenReporte(string $tipo, string $desde, string $hasta, array $f): array
    {
        return match ($tipo) {
            'ventas'      => $this->resumenVentas($desde, $hasta, $f),
            'inventario'  => $this->resumenInventario($f),
            'movimientos' => $this->resumenMovimientos($desde, $hasta, $f),
            'proveedores' => $this->resumenProveedores($desde, $hasta, $f),
            'cyber'       => $this->resumenCyber($f),
        };
    }

    private function resumenVentas(string $desde, string $hasta, array $f): array
    {
        $params = [];
        $where  = $this->whereVentas($desde, $hasta, $f, $params);

        $row = $this->fetchUno("
            SELECT COUNT(DISTINCT ov.id) AS ordenes,
                   COUNT(lv.id) AS articulos,
                   COALESCE(SUM(lv.cantidad), 0) AS unidades,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total
            " . $this->baseVentas() . $where, $params);

        $ordenes = (int)($row['ordenes'] ?? 0);
        $total   = (float)($row['total'] ?? 0);

        return [
            ['label' => 'Ventas totales', 'valor' => $total, 'formato' => 'money', 'icono' => 'payments', 'tono' => 'primary'],
            ['label' => 'Órdenes', 'valor' => $ordenes, 'formato' => 'int', 'icono' => 'receipt_long', 'tono' => 'info'],
            ['label' => 'Unidades', 'valor' => (int)($row['unidades'] ?? 0), 'formato' => 'int', 'icono' => 'inventory_2', 'tono' => 'success'],
            ['label' => 'Ticket promedio', 'valor' => $ordenes > 0 ? $total / $ordenes : 0, 'formato' => 'money', 'icono' => 'local_atm', 'tono' => 'warning'],
        ];
    }

    private function resumenInventario(array $f): array
    {
        $params = [];
        $where  = $this->whereInventario($f, $params);

        $row = $this->fetchUno("
            SELECT COUNT(*) AS productos,
                   COALESCE(SUM(p.stock), 0) AS unidades,
                   COALESCE(SUM(p.stock * p.precio_compra), 0) AS valor_costo,
                   COALESCE(SUM(p.stock * p.precio_venta), 0) AS valor_venta,
                   SUM(CASE WHEN p.stock <= p.stock_minimo THEN 1 ELSE 0 END) AS criticos,
                   SUM(CASE WHEN p.stock <= 0 THEN 1 ELSE 0 END) AS agotados
            " . $this->baseInventario() . $where, $params);

        return [
            ['label' => 'Productos', 'valor' => (int)($row['productos'] ?? 0), 'formato' => 'int', 'icono' => 'inventory_2', 'tono' => 'primary'],
            ['label' => 'Unidades', 'valor' => (int)($row['unidades'] ?? 0), 'formato' => 'int', 'icono' => 'widgets', 'tono' => 'info'],
            ['label' => 'Valor costo', 'valor' => (float)($row['valor_costo'] ?? 0), 'formato' => 'money', 'icono' => 'account_balance_wallet', 'tono' => 'warning'],
            ['label' => 'Valor venta', 'valor' => (float)($row['valor_venta'] ?? 0), 'formato' => 'money', 'icono' => 'sell', 'tono' => 'success'],
            ['label' => 'Stock crítico', 'valor' => (int)($row['criticos'] ?? 0), 'formato' => 'int', 'icono' => 'warning_amber', 'tono' => 'danger'],
            ['label' => 'Agotados', 'valor' => (int)($row['agotados'] ?? 0), 'formato' => 'int', 'icono' => 'remove_shopping_cart', 'tono' => 'danger'],
        ];
    }

    private function resumenMovimientos(string $desde, string $hasta, array $f): array
    {
        $params = [];
        $where  = $this->whereMovimientos($desde, $hasta, $f, $params);

        $row = $this->fetchUno("
            SELECT COUNT(DISTINCT p.id) AS productos,
                   COUNT(DISTINCT ov.id) AS ordenes,
                   COALESCE(SUM(lv.cantidad), 0) AS unidades,
                   COALESCE(SUM(lv.cantidad * lv.precio), 0) AS total
            " . $this->baseMovimientos() . $where, $params);

        return [
            ['label' => 'Unidades vendidas', 'valor' => (int)($row['unidades'] ?? 0), 'formato' => 'int', 'icono' => 'sync_alt', 'tono' => 'primary'],
            ['label' => 'Ventas', 'valor' => (float)($row['total'] ?? 0), 'formato' => 'money', 'icono' => 'payments', 'tono' => 'success'],
            ['label' => 'Productos', 'valor' => (int)($row['productos'] ?? 0), 'formato' => 'int', 'icono' => 'inventory_2', 'tono' => 'info'],
            ['label' => 'Órdenes', 'valor' => (int)($row['ordenes'] ?? 0), 'formato' => 'int', 'icono' => 'receipt_long', 'tono' => 'warning'],
        ];
    }

    private function resumenProveedores(string $desde, string $hasta, array $f): array
    {
        $params = [];
        $where  = $this->whereProveedores($desde, $hasta, $f, $params);

        $row = $this->fetchUno("
            SELECT COUNT(DISTINCT oa.id) AS ordenes,
                   COALESCE(SUM(la.cantidad), 0) AS unidades,
                   COALESCE(SUM(la.cantidad * la.precio), 0) AS monto,
                   COUNT(DISTINCT CASE
                       WHEN ss.status IN ('Pendiente', 'Aprobado', 'En Tránsito') THEN oa.id
                   END) AS pendientes
            " . $this->baseProveedores() . $where, $params);

        return [
            ['label' => 'Órdenes', 'valor' => (int)($row['ordenes'] ?? 0), 'formato' => 'int', 'icono' => 'assignment', 'tono' => 'primary'],
            ['label' => 'Unidades', 'valor' => (int)($row['unidades'] ?? 0), 'formato' => 'int', 'icono' => 'inventory', 'tono' => 'info'],
            ['label' => 'Monto total', 'valor' => (float)($row['monto'] ?? 0), 'formato' => 'money', 'icono' => 'request_quote', 'tono' => 'success'],
            ['label' => 'Pendientes', 'valor' => (int)($row['pendientes'] ?? 0), 'formato' => 'int', 'icono' => 'pending_actions', 'tono' => 'warning'],
        ];
    }

    private function resumenCyber(array $f): array
    {
        $params = [];
        $where  = $this->whereCyber($f, $params);

        $row = $this->fetchUno("
            SELECT COUNT(*) AS sesiones,
                   SUM(CASE WHEN sc.finalizada = 0 THEN 1 ELSE 0 END) AS activas,
                   SUM(CASE WHEN sc.finalizada = 1 THEN 1 ELSE 0 END) AS finalizadas,
                   SEC_TO_TIME(COALESCE(SUM(TIME_TO_SEC(sc.tiempo_uso)), 0)) AS tiempo_total
            " . $this->baseCyber() . $where, $params);

        return [
            ['label' => 'Sesiones', 'valor' => (int)($row['sesiones'] ?? 0), 'formato' => 'int', 'icono' => 'desktop_windows', 'tono' => 'primary'],
            ['label' => 'Activas', 'valor' => (int)($row['activas'] ?? 0), 'formato' => 'int', 'icono' => 'play_circle', 'tono' => 'success'],
            ['label' => 'Finalizadas', 'valor' => (int)($row['finalizadas'] ?? 0), 'formato' => 'int', 'icono' => 'check_circle', 'tono' => 'info'],
            ['label' => 'Tiempo total', 'valor' => (string)($row['tiempo_total'] ?? '00:00:00'), 'formato' => 'text', 'icono' => 'schedule', 'tono' => 'warning'],
        ];
    }

    // ------------------------------------------------------------------
    // Ordenamiento y contexto de filtros
    // ------------------------------------------------------------------

    private function ordenPorTipo(string $tipo, string $agrupar, string $orden): string
    {
        $mapas = [
            'ventas' => [
                'detalle' => [
                    'fecha_desc' => 'ov.fecha DESC, ov.id DESC',
                    'fecha_asc'  => 'ov.fecha ASC, ov.id ASC',
                    'total_desc' => 'total DESC, ov.fecha DESC',
                    'total_asc'  => 'total ASC, ov.fecha DESC',
                ],
                'dia' => [
                    'fecha_desc' => 'ov.fecha DESC',
                    'fecha_asc'  => 'ov.fecha ASC',
                    'total_desc' => 'total DESC, ov.fecha DESC',
                    'total_asc'  => 'total ASC, ov.fecha DESC',
                ],
                'mes' => [
                    'periodo_desc' => 'YEAR(ov.fecha) DESC, MONTH(ov.fecha) DESC',
                    'periodo_asc'  => 'YEAR(ov.fecha) ASC, MONTH(ov.fecha) ASC',
                    'total_desc'   => 'total DESC',
                    'total_asc'    => 'total ASC',
                ],
                'cliente' => [
                    'total_desc'  => 'total DESC',
                    'total_asc'   => 'total ASC',
                    'nombre_asc'  => 'cliente ASC',
                    'compras_desc' => 'compras DESC',
                ],
                'producto' => [
                    'unidades_desc' => 'unidades DESC',
                    'unidades_asc'  => 'unidades ASC',
                    'total_desc'    => 'total DESC',
                    'nombre_asc'    => 'producto ASC',
                ],
                'vendedor' => [
                    'total_desc' => 'total DESC',
                    'total_asc'  => 'total ASC',
                    'nombre_asc' => 'vendedor ASC',
                ],
            ],
            'inventario' => [
                'detalle' => [
                    'nombre_asc' => 'p.nombre ASC',
                    'stock_asc'  => 'p.stock ASC, p.nombre ASC',
                    'stock_desc' => 'p.stock DESC, p.nombre ASC',
                    'valor_desc' => 'valor_stock DESC',
                    'valor_asc'  => 'valor_stock ASC',
                ],
                'categoria' => [
                    'categoria_asc'  => 'categoria ASC',
                    'valor_desc'     => 'valor_venta DESC',
                    'criticos_desc'  => 'criticos DESC',
                    'productos_desc' => 'productos DESC',
                ],
                'estado' => [
                    'estado_asc'     => 'estado ASC',
                    'productos_desc' => 'productos DESC',
                    'valor_desc'     => 'valor_venta DESC',
                ],
            ],
            'movimientos' => [
                'producto' => [
                    'unidades_desc' => 'unidades DESC',
                    'unidades_asc'  => 'unidades ASC',
                    'total_desc'    => 'total DESC',
                    'total_asc'     => 'total ASC',
                    'nombre_asc'    => 'producto ASC',
                ],
                'categoria' => [
                    'total_desc'    => 'total DESC',
                    'unidades_desc' => 'unidades DESC',
                    'categoria_asc' => 'categoria ASC',
                ],
                'dia' => [
                    'fecha_desc' => 'ov.fecha DESC',
                    'fecha_asc'  => 'ov.fecha ASC',
                    'total_desc' => 'total DESC',
                ],
                'mes' => [
                    'periodo_desc' => 'YEAR(ov.fecha) DESC, MONTH(ov.fecha) DESC',
                    'periodo_asc'  => 'YEAR(ov.fecha) ASC, MONTH(ov.fecha) ASC',
                    'total_desc'   => 'total DESC',
                ],
            ],
            'proveedores' => [
                'detalle' => [
                    'fecha_desc' => 'oa.fecha DESC, oa.id DESC',
                    'fecha_asc'  => 'oa.fecha ASC, oa.id ASC',
                    'monto_desc' => 'monto DESC',
                    'monto_asc'  => 'monto ASC',
                ],
                'proveedor' => [
                    'monto_desc'   => 'monto DESC',
                    'monto_asc'    => 'monto ASC',
                    'nombre_asc'   => 'proveedor ASC',
                    'ordenes_desc' => 'ordenes DESC',
                ],
                'estado' => [
                    'estado_asc'   => 'estado ASC',
                    'monto_desc'   => 'monto DESC',
                    'ordenes_desc' => 'ordenes DESC',
                ],
                'mes' => [
                    'periodo_desc' => 'YEAR(oa.fecha) DESC, MONTH(oa.fecha) DESC',
                    'periodo_asc'  => 'YEAR(oa.fecha) ASC, MONTH(oa.fecha) ASC',
                    'monto_desc'   => 'monto DESC',
                ],
            ],
            'cyber' => [
                'detalle' => [
                    'id_desc'     => 'sc.id DESC',
                    'cliente_asc' => 'cliente ASC',
                    'activo_asc'  => 'activo ASC',
                    'estado_asc'  => 'estado ASC',
                ],
                'cliente' => [
                    'sesiones_desc' => 'sesiones DESC',
                    'nombre_asc'    => 'cliente ASC',
                    'tiempo_desc'   => 'SUM(TIME_TO_SEC(sc.tiempo_uso)) DESC',
                ],
                'activo' => [
                    'sesiones_desc' => 'sesiones DESC',
                    'activo_asc'    => 'activo ASC',
                    'tiempo_desc'   => 'SUM(TIME_TO_SEC(sc.tiempo_uso)) DESC',
                ],
                'estado' => [
                    'estado_asc'    => 'estado ASC',
                    'sesiones_desc' => 'sesiones DESC',
                    'tiempo_desc'   => 'SUM(TIME_TO_SEC(sc.tiempo_uso)) DESC',
                ],
            ],
        ];

        $mapa = $mapas[$tipo][$agrupar] ?? [];

        if (isset($mapa[$orden])) {
            return $mapa[$orden];
        }

        return (string)(reset($mapa) ?: '1');
    }

    /**
     * Descripción legible de la agrupación y los filtros activos.
     *
     * @return string[]
     */
    private function contextoFiltros(string $tipo, array $f): array
    {
        $items = ['Agrupación: ' . self::CONFIG[$tipo]['grupos'][$f['agrupar']]['label']];

        $etiquetas = [
            'cliente'   => 'Cliente',
            'usuario'   => 'Vendedor',
            'categoria' => 'Categoría',
            'producto'  => 'Producto',
            'proveedor' => 'Proveedor',
            'estado'    => 'Estado',
            'activo'    => 'Estación',
        ];

        foreach ($etiquetas as $clave => $etiqueta) {
            if (($f[$clave] ?? 0) > 0) {
                $items[] = $etiqueta . ': ' . $this->etiquetaFiltro($clave, (int)$f[$clave]);
            }
        }

        $estadosStock = [
            'critico' => 'Stock crítico (menor o igual al mínimo)',
            'bajo'    => 'Solo bajo stock',
            'agotado' => 'Agotados',
            'ok'      => 'Disponible',
        ];
        if (isset($estadosStock[$f['estado_stock']])) {
            $items[] = 'Stock: ' . $estadosStock[$f['estado_stock']];
        }

        $estadosCiber = [
            'activa'     => 'Activas',
            'finalizada' => 'Finalizadas',
        ];
        if (isset($estadosCiber[$f['estado_ciber']])) {
            $items[] = 'Estado de sesión: ' . $estadosCiber[$f['estado_ciber']];
        }

        if ($f['monto_min'] > 0) {
            $items[] = 'Monto mínimo por orden: $' . number_format($f['monto_min'], 2, ',', '.');
        }

        if ($f['limite'] > 0) {
            $items[] = 'Límite: ' . $f['limite'] . ' registros';
        }

        return $items;
    }

    private function etiquetaFiltro(string $clave, int $id): string
    {
        $sql = match ($clave) {
            'cliente'   => "SELECT CONCAT(nombre, ' ', apellido) FROM clientes WHERE id = ?",
            'usuario'   => "SELECT CONCAT(nombre, ' ', apellido) FROM usuarios WHERE id = ?",
            'categoria' => "SELECT nombre_categoria FROM categoria WHERE id = ?",
            'producto'  => "SELECT CONCAT(COALESCE(codigo, ''), ' - ', nombre) FROM productos WHERE id = ?",
            'proveedor' => "SELECT nombre FROM proveedores WHERE id = ?",
            'estado'    => "SELECT status FROM status_seguimiento WHERE id = ?",
            'activo'    => "SELECT CONCAT(marca, ' - ', descripcion) FROM activos WHERE id = ?",
            default     => "",
        };

        if ($sql === '') {
            return '#' . $id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $valor = $stmt->fetchColumn();

        if ($valor === false || $valor === null || trim((string)$valor) === '') {
            return '#' . $id;
        }

        return trim((string)$valor);
    }

    /**
     * Nombre legible de un tipo de reporte.
     */
    public function nombreTipo(string $tipo): string
    {
        return self::TIPOS[$tipo] ?? 'Reporte';
    }
}
