<?php

use App\Models\Reporte;

// KPIs mensuales reales desde la base de datos
$rep = new Reporte();
$kpis = $rep->kpis();

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
     VISTA: REPORTES Y ESTADÍSTICAS ESTRATÉGICAS
     Permite generar reportes analíticos con filtros dinámicos por
     módulo, rangos temporales rápidos, resumen de métricas clave,
     tabla interactiva y exportación multiplataforma.
     ============================================================ -->

<!-- ===== TARJETAS DE MÉTRICAS MENSUALES (KPIs) ===== -->
<div class="row" style="margin-bottom:1.5rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <div class="metric-label">Ventas del Mes</div>
            <div class="metric-value"><?php echo $fmt($kpis['ventas_mes']); ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> <?php echo $nombreMes; ?></div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card success">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">inventory_2</i></div>
            <div class="metric-label">Productos Activos</div>
            <div class="metric-value" style="color:var(--success);"><?php echo (int)$kpis['productos_activos']; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">En catálogo activo</div>
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
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Abastecimiento procesadas</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- ===== COLUMNA IZQUIERDA: GENERADOR Y FILTROS ESTRATÉGICOS ===== -->
    <div class="col s12 l4">
        <div class="card" style="border-radius:12px;box-shadow:var(--shadow);">
            <div class="card-content">
                <span class="card-title" style="font-size:1.2rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;">
                    <i class="material-icons" style="color:var(--primary);">tune</i> Filtros Estratégicos
                </span>

                <form id="formReporte" data-validate>
                    <!-- Selector de Reporte Estratégico -->
                    <div class="input-field" style="margin-top:1.25rem;">
                        <select name="tipo" id="reporteTipo" required>
                            <optgroup label="Área Comercial y Ventas">
                                <option value="ventas" selected>Ventas por fecha</option>
                                <option value="ventas_por_producto">Ventas por producto (Top rotación)</option>
                                <option value="ventas_por_cliente">Ventas por cliente (Top clientes)</option>
                                <option value="caja_diaria">Arqueo y resumen diario de caja</option>
                            </optgroup>
                            <optgroup label="Inventario y Operaciones">
                                <option value="inventario">Estado y valorización de inventario</option>
                                <option value="stock_bajo">Alertas de stock bajo / reposición</option>
                                <option value="movimientos">Movimientos de rotación de stock</option>
                                <option value="rentabilidad">Rentabilidad y márgenes de ganancia</option>
                            </optgroup>
                            <optgroup label="Proveedores y Compras">
                                <option value="proveedores">Historial de solicitudes a proveedores</option>
                                <option value="compras">Detalle de compras por producto</option>
                            </optgroup>
                            <optgroup label="Servicios y Asesorías">
                                <option value="cyber">Sesiones y uso de cybercafé</option>
                                <option value="asesorias">Registro de asesorías legales</option>
                            </optgroup>
                        </select>
                        <label>Tipo de Reporte</label>
                    </div>

                    <!-- Presets Rápidos de Fechas -->
                    <div class="fechas-presets-container fechas-row" style="margin-bottom:0.75rem;">
                        <label style="font-size:0.8rem;color:var(--text-muted);font-weight:600;display:block;margin-bottom:0.4rem;">
                            RANGOS PREDEFINIDOS
                        </label>
                        <div class="btn-group-presets" style="display:flex;flex-wrap:wrap;gap:0.35rem;">
                            <button type="button" class="btn-flat btn-small chip-preset" data-range="hoy">Hoy</button>
                            <button type="button" class="btn-flat btn-small chip-preset" data-range="ayer">Ayer</button>
                            <button type="button" class="btn-flat btn-small chip-preset" data-range="semana">7 días</button>
                            <button type="button" class="btn-flat btn-small chip-preset active" data-range="mes">Este Mes</button>
                            <button type="button" class="btn-flat btn-small chip-preset" data-range="mes_anterior">Mes Ant.</button>
                            <button type="button" class="btn-flat btn-small chip-preset" data-range="ano">Este Año</button>
                        </div>
                    </div>

                    <!-- Rango de Fechas Personalizado -->
                    <div class="row fechas-row" style="margin-bottom:0;">
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

                    <!-- FILTROS DINÁMICOS DEPENDIENTES -->
                    <!-- Filtro Categoría -->
                    <div class="input-field filtro-extra" id="filtroCategoriaContainer" style="display:none;margin-top:0.75rem;">
                        <select name="categoria_id" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                        </select>
                        <label>Filtrar por Categoría</label>
                    </div>

                    <!-- Filtro Cliente -->
                    <div class="input-field filtro-extra" id="filtroClienteContainer" style="display:none;margin-top:0.75rem;">
                        <select name="cliente_id" id="filtroCliente">
                            <option value="">Todos los clientes</option>
                        </select>
                        <label>Filtrar por Cliente</label>
                    </div>

                    <!-- Filtro Proveedor -->
                    <div class="input-field filtro-extra" id="filtroProveedorContainer" style="display:none;margin-top:0.75rem;">
                        <select name="proveedor_id" id="filtroProveedor">
                            <option value="">Todos los proveedores</option>
                        </select>
                        <label>Filtrar por Proveedor</label>
                    </div>

                    <!-- Filtro Estatus Solicitud -->
                    <div class="input-field filtro-extra" id="filtroStatusContainer" style="display:none;margin-top:0.75rem;">
                        <select name="status_id" id="filtroStatus">
                            <option value="">Todos los estados</option>
                        </select>
                        <label>Estado de Solicitud</label>
                    </div>

                    <!-- Filtro Estado Cyber -->
                    <div class="input-field filtro-extra" id="filtroCyberContainer" style="display:none;margin-top:0.75rem;">
                        <select name="estado_cyber" id="filtroCyber">
                            <option value="">Todas las sesiones</option>
                            <option value="0">Solo Activas</option>
                            <option value="1">Solo Finalizadas</option>
                        </select>
                        <label>Estado de Sesión Cyber</label>
                    </div>

                    <!-- Formato de Descarga -->
                    <div style="margin:1.25rem 0 1.5rem;padding:0.75rem;background:var(--surface-hover);border-radius:8px;border:1px solid var(--border-light);">
                        <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:0.5rem;color:var(--text);">
                            Formato de exportación
                        </label>
                        <div style="display:flex;gap:1.25rem;">
                            <label><input name="formato" type="radio" value="pdf" checked><span>PDF</span></label>
                            <label><input name="formato" type="radio" value="excel"><span>Excel</span></label>
                            <label><input name="formato" type="radio" value="csv"><span>CSV</span></label>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div style="display:flex;flex-direction:column;gap:0.6rem;">
                        <button type="submit" class="btn waves-effect waves-light indigo darken-1" style="width:100%;height:2.8rem;border-radius:8px;font-weight:600;">
                            <i class="material-icons left">query_stats</i>Consultar Reporte
                        </button>
                        <button type="button" id="btnExportar" class="btn waves-effect waves-light green darken-1" style="width:100%;height:2.8rem;border-radius:8px;font-weight:600;">
                            <i class="material-icons left">file_download</i>Exportar Archivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== COLUMNA DERECHA: RESULTADOS, TARJETAS DE RESUMEN Y TABLA ===== -->
    <div class="col s12 l8">
        <!-- Panel de KPIs Dinámicos del Reporte Consultado -->
        <div id="reporteResumenCards" class="row" style="display:none;margin-bottom:1rem;">
            <!-- Inyectado dinámicamente por app.reportes.js -->
        </div>

        <!-- Tarjeta de Resultados con Tabla DataTables -->
        <div class="card" style="border-radius:12px;box-shadow:var(--shadow);">
            <div class="card-content" style="padding:0;">
                <div style="padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;border-bottom:1px solid var(--border-light);">
                    <div style="display:flex;align-items:center;gap:0.6rem;">
                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(57,73,171,0.1);display:flex;align-items:center;justify-content:center;color:var(--primary);">
                            <i class="material-icons">bar_chart</i>
                        </div>
                        <div>
                            <span id="reporteTituloVisible" style="font-size:1.15rem;font-weight:700;display:block;">Resultados del Reporte</span>
                            <span id="reporteSubtituloVisible" style="font-size:0.8rem;color:var(--text-muted);">Ventas por fecha</span>
                        </div>
                    </div>
                    <span class="badge new blue darken-1" id="reporteCount" data-badge-caption="registros" style="float:none;font-size:0.85rem;padding:0.25rem 0.65rem;border-radius:6px;font-weight:600;">0</span>
                </div>

                <div style="padding:1rem 1.5rem 1.5rem;overflow-x:auto;">
                    <!-- Tabla interactiva -->
                    <table class="striped highlight responsive-table" id="tablaReporte" style="width:100%;min-width:540px;">
                        <thead></thead>
                        <tbody></tbody>
                    </table>

                    <div id="reporteLoading" style="display:none;text-align:center;padding:3rem 1rem;">
                        <div class="preloader-wrapper small active">
                            <div class="spinner-layer spinner-blue-only">
                                <div class="circle-clipper left"><div class="circle"></div></div>
                                <div class="gap-patch"><div class="circle"></div></div>
                                <div class="circle-clipper right"><div class="circle"></div></div>
                            </div>
                        </div>
                        <p style="margin-top:0.75rem;color:var(--text-muted);font-weight:500;">Generando reporte estratégico...</p>
                    </div>

                    <div id="reporteVacio" style="display:none;text-align:center;color:var(--text-muted);padding:3rem 1rem;">
                        <i class="material-icons" style="font-size:3rem;opacity:0.4;display:block;margin-bottom:0.5rem;">assessment</i>
                        <span style="font-weight:500;font-size:1rem;">Selecciona un tipo de reporte y pulsa "Consultar" para ver los datos analíticos.</span>
                    </div>

                    <div id="reporteError" style="display:none;text-align:center;color:var(--danger);padding:2rem 1rem;font-weight:600;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
