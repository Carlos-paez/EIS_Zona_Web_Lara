<?php

use App\Models\Reporte;

// KPIs mensuales reales desde la base de datos
$rep       = new Reporte();
$kpis      = $rep->kpis();
$opciones  = $rep->opcionesFiltros();
$repConfig = Reporte::CONFIG;

$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$etiquetaCliente = static function (array $c): string {
    $nombre = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
    $cedula = trim((string)($c['cedula'] ?? ''));
    return $nombre . ($cedula !== '' ? ' - ' . $cedula : '');
};

$etiquetaProducto = static function (array $p): string {
    $codigo = trim((string)($p['codigo'] ?? ''));
    $nombre = trim((string)($p['nombre'] ?? ''));
    return $codigo !== '' ? $codigo . ' - ' . $nombre : $nombre;
};

$etiquetaUsuario = static function (array $u): string {
    $nombre = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));
    $user   = trim((string)($u['user_name'] ?? ''));
    return $nombre . ($user !== '' ? ' (' . $user . ')' : '');
};

$etiquetaActivo = static function (array $a): string {
    $marca = trim((string)($a['marca'] ?? ''));
    $desc  = trim((string)($a['descripcion'] ?? ''));
    return trim($marca . ($desc !== '' ? ' - ' . $desc : ''));
};

$fmt = fn($v) => '$' . number_format((float)$v, 2);
$mesActual = strtoupper(date('F'));
$mesesEsp = [
    'JANUARY' => 'Enero', 'FEBRUARY' => 'Febrero', 'MARCH' => 'Marzo',
    'APRIL' => 'Abril', 'MAY' => 'Mayo', 'JUNE' => 'Junio',
    'JULY' => 'Julio', 'AUGUST' => 'Agosto', 'SEPTEMBER' => 'Septiembre',
    'OCTOBER' => 'Octubre', 'NOVEMBER' => 'Noviembre', 'DECEMBER' => 'Diciembre',
];
$nombreMes = $mesesEsp[$mesActual] ?? strtolower($mesActual);
?>

<!-- ============================================================
     VISTA: REPORTES Y ESTADÍSTICAS
     Generador de reportes con filtros contextuales, agrupación,
     ordenamiento, límite de resultados y resumen ejecutivo.
     ============================================================ -->

<!-- ===== TARJETAS DE MÉTRICAS MENSUALES (KPIs) ===== -->
<div class="row" style="margin-bottom:1.5rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <div class="metric-label">Ventas del Mes</div>
            <div class="metric-value"><?php echo $fmt($kpis['ventas_mes']); ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> <?php echo $h($nombreMes); ?></div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card success">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">inventory_2</i></div>
            <div class="metric-label">Productos Activos</div>
            <div class="metric-value" style="color:var(--success);"><?php echo (int)$kpis['productos_activos']; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">En inventario</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">desktop_windows</i></div>
            <div class="metric-label">Sesiones Cyber</div>
            <div class="metric-value" style="color:var(--warning);"><?php echo (int)$kpis['sesiones_cyber']; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><?php echo (int)$kpis['sesiones_activas']; ?> activas</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card info">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">assignment</i></div>
            <div class="metric-label">Solicitudes</div>
            <div class="metric-value" style="color:var(--info);"><?php echo (int)$kpis['solicitudes']; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Procesadas</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- ===== COLUMNA IZQUIERDA: GENERADOR DE REPORTES (FORMULARIO) ===== -->
    <div class="col s12 l5">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">assessment</i>Generador de Reportes</span>

                <form id="formReporte" autocomplete="off">
                    <div class="input-field">
                        <select name="tipo" id="reporteTipo">
                            <?php foreach ($repConfig as $clave => $cfg): ?>
                                <option value="<?php echo $h($clave); ?>" <?php echo $clave === 'ventas' ? 'selected' : ''; ?>>
                                    <?php echo $h(Reporte::TIPOS[$clave] ?? $clave); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Tipo de Reporte</label>
                    </div>

                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6">
                            <div class="input-field">
                                <select name="agrupar" id="reporteAgrupar"></select>
                                <label>Agrupación</label>
                            </div>
                        </div>
                        <div class="col s12 m6">
                            <div class="input-field">
                                <select name="orden" id="reporteOrden"></select>
                                <label>Ordenar por</label>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas: solo para reportes que usan periodo -->
                    <div class="reporte-filtro-grupo" data-filtro="fechas">
                        <div class="reporte-form-section-title"><i class="material-icons">date_range</i>Periodo</div>
                        <div class="reporte-presets" id="reportePresets">
                            <button type="button" class="btn-small btn-flat date-preset" data-preset="hoy">Hoy</button>
                            <button type="button" class="btn-small btn-flat date-preset" data-preset="7dias">7 días</button>
                            <button type="button" class="btn-small btn-flat date-preset" data-preset="30dias">30 días</button>
                            <button type="button" class="btn-small btn-flat date-preset active" data-preset="mes">Este mes</button>
                            <button type="button" class="btn-small btn-flat date-preset" data-preset="mes_anterior">Mes anterior</button>
                            <button type="button" class="btn-small btn-flat date-preset" data-preset="ano">Este año</button>
                        </div>
                        <div class="row" style="margin-bottom:0;">
                            <div class="col s12 m6">
                                <div class="input-field">
                                    <input type="date" id="fechaInicio" name="desde" value="<?php echo date('Y-m-01'); ?>">
                                    <label for="fechaInicio" class="active">Fecha Inicio</label>
                                </div>
                            </div>
                            <div class="col s12 m6">
                                <div class="input-field">
                                    <input type="date" id="fechaFin" name="hasta" value="<?php echo date('Y-m-d'); ?>">
                                    <label for="fechaFin" class="active">Fecha Fin</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros contextuales -->
                    <div class="reporte-filtro-grupo" data-filtro="cliente">
                        <div class="input-field">
                            <select name="cliente" id="filtroCliente">
                                <option value="">Todos los clientes</option>
                                <?php foreach ($opciones['clientes'] as $cliente): ?>
                                    <option value="<?php echo (int)$cliente['id']; ?>"><?php echo $h($etiquetaCliente($cliente)); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label>Cliente</label>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="usuario">
                            <div class="input-field">
                                <select name="usuario" id="filtroUsuario">
                                    <option value="">Todos los vendedores</option>
                                    <?php foreach ($opciones['usuarios'] as $usuario): ?>
                                        <option value="<?php echo (int)$usuario['id']; ?>"><?php echo $h($etiquetaUsuario($usuario)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Vendedor</label>
                            </div>
                        </div>
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="categoria">
                            <div class="input-field">
                                <select name="categoria" id="filtroCategoria">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($opciones['categorias'] as $categoria): ?>
                                        <option value="<?php echo (int)$categoria['id']; ?>"><?php echo $h($categoria['nombre_categoria']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Categoría</label>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="producto">
                            <div class="input-field">
                                <select name="producto" id="filtroProducto">
                                    <option value="">Todos los productos</option>
                                    <?php foreach ($opciones['productos'] as $producto): ?>
                                        <option value="<?php echo (int)$producto['id']; ?>"><?php echo $h($etiquetaProducto($producto)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Producto</label>
                            </div>
                        </div>
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="proveedor">
                            <div class="input-field">
                                <select name="proveedor" id="filtroProveedor">
                                    <option value="">Todos los proveedores</option>
                                    <?php foreach ($opciones['proveedores'] as $proveedor): ?>
                                        <option value="<?php echo (int)$proveedor['id']; ?>"><?php echo $h($proveedor['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Proveedor</label>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="estado">
                            <div class="input-field">
                                <select name="estado" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <?php foreach ($opciones['estados'] as $estado): ?>
                                        <option value="<?php echo (int)$estado['id']; ?>"><?php echo $h($estado['status']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Estado de la solicitud</label>
                            </div>
                        </div>
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="estado_stock">
                            <div class="input-field">
                                <select name="estado_stock" id="filtroEstadoStock">
                                    <option value="todos">Todo el inventario</option>
                                    <option value="critico">Stock crítico (≤ mínimo)</option>
                                    <option value="bajo">Solo bajo stock</option>
                                    <option value="agotado">Agotados</option>
                                    <option value="ok">Disponible</option>
                                </select>
                                <label>Estado del stock</label>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="activo">
                            <div class="input-field">
                                <select name="activo" id="filtroActivo">
                                    <option value="">Todas las estaciones</option>
                                    <?php foreach ($opciones['activos'] as $activo): ?>
                                        <option value="<?php echo (int)$activo['id']; ?>"><?php echo $h($etiquetaActivo($activo)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Estación</label>
                            </div>
                        </div>
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="estado_ciber">
                            <div class="input-field">
                                <select name="estado_ciber" id="filtroEstadoCiber">
                                    <option value="">Todas las sesiones</option>
                                    <option value="activa">Activas</option>
                                    <option value="finalizada">Finalizadas</option>
                                </select>
                                <label>Estado de la sesión</label>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6 reporte-filtro-grupo" data-filtro="monto_min">
                            <div class="input-field">
                                <i class="material-icons prefix">attach_money</i>
                                <input type="number" name="monto_min" id="filtroMontoMin" min="0" step="0.01" value="0">
                                <label for="filtroMontoMin" class="active">Monto mínimo por orden</label>
                            </div>
                        </div>
                        <div class="col s12 m6">
                            <div class="input-field">
                                <select name="limite" id="reporteLimite">
                                    <option value="0">Todos los resultados</option>
                                    <option value="10">Top 10</option>
                                    <option value="25">Top 25</option>
                                    <option value="50">Top 50</option>
                                    <option value="100">Top 100</option>
                                    <option value="200">Top 200</option>
                                </select>
                                <label>Límite de resultados</label>
                            </div>
                        </div>
                    </div>

                    <div class="reporte-form-section">
                        <div class="reporte-form-section-title"><i class="material-icons">download</i>Formato de salida</div>
                        <div class="reporte-formatos">
                            <label><input name="formato" type="radio" value="csv"><span>CSV</span></label>
                            <label><input name="formato" type="radio" value="excel"><span>Excel</span></label>
                            <label><input name="formato" type="radio" value="pdf" checked><span>PDF</span></label>
                        </div>

                        <div class="reporte-acciones">
                            <button type="submit" class="btn waves-effect waves-light indigo">
                                <i class="material-icons left">search</i>Consultar
                            </button>
                            <button type="button" id="btnExportar" class="btn waves-effect waves-light green darken-1">
                                <i class="material-icons left">download</i>Descargar
                            </button>
                        </div>
                        <button type="button" id="btnLimpiarReporte" class="btn-flat" style="width:100%;margin-top:0.35rem;text-transform:none;">
                            <i class="material-icons left" style="font-size:1.1rem;">restart_alt</i>Restablecer filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== COLUMNA DERECHA: RESULTADOS DEL REPORTE ===== -->
    <div class="col s12 l7">
        <div class="card">
            <div class="card-content" style="padding:0;">
                <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                    <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                        <i class="material-icons" style="color:var(--primary);">bar_chart</i> Resultados del Reporte
                    </span>
                    <span class="result-count" id="reporteCount" style="color:var(--text-muted);font-size:0.85rem;">—</span>
                </div>

                <div id="reporteResumen" class="reporte-resumen"></div>
                <div id="reporteResumenNota" class="reporte-resumen-nota" style="display:none;">
                    Resumen calculado sobre el conjunto filtrado, sin aplicar el límite de resultados.
                </div>
                <div id="reporteContexto" class="reporte-contexto"></div>

                <div class="reporte-tabla-wrap">
                    <table class="striped" id="tablaReporte" style="min-width:520px;">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                    <div id="reporteVacio" style="display:none;text-align:center;color:var(--text-muted);padding:2rem 0;">
                        Selecciona un tipo de reporte y consulta para ver los datos.
                    </div>
                    <div id="reporteError" style="display:none;text-align:center;color:var(--danger);padding:1.5rem 0;font-weight:600;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.EIS_REPORTES_CONFIG = <?php echo json_encode($repConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
