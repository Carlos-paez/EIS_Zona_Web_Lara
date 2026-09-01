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
     VISTA: REPORTES Y ESTADÍSTICAS
     Muestra KPIs mensuales reales, un generador de reportes con
     selección de tipo, rango de fechas y formato de salida, y un
     visor de resultados obtenidos de la base de datos.
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
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">assessment</i>Generador de Reportes</span>
                <form id="formReporte">
                    <div class="input-field">
                        <select name="tipo" id="reporteTipo">
                            <option value="ventas" selected>Ventas por fecha</option>
                            <option value="inventario">Estado de inventario</option>
                            <option value="movimientos">Movimientos de stock</option>
                            <option value="proveedores">Solicitudes a proveedores</option>
                            <option value="cyber">Horas Cybercafé</option>
                        </select>
                        <label>Tipo de Reporte</label>
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
                    <div style="margin-bottom:1.5rem;">
                        <label style="font-size:0.9rem;font-weight:500;display:block;margin-bottom:0.5rem;">Formato de salida</label>
                        <p><label><input name="formato" type="radio" value="csv"><span>CSV</span></label></p>
                        <p><label><input name="formato" type="radio" value="excel"><span>Excel</span></label></p>
                        <p><label><input name="formato" type="radio" value="pdf" checked><span>PDF</span></label></p>
                    </div>
                    <div class="row" style="margin-bottom:0;gap:0.5rem;">
                        <button type="submit" class="btn waves-effect waves-light indigo" style="width:100%;height:3rem;border-radius:8px;">
                            <i class="material-icons left">search</i>Consultar
                        </button>
                        <button type="button" id="btnExportar" class="btn waves-effect waves-light green darken-1" style="width:100%;height:3rem;border-radius:8px;margin-top:0.5rem;">
                            <i class="material-icons left">download</i>Descargar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== COLUMNA DERECHA: RESULTADOS DEL REPORTE ===== -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content" style="padding:0;">
                <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                    <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                        <i class="material-icons" style="color:var(--primary);">bar_chart</i> Resultados del Reporte
                    </span>
                    <span class="result-count" id="reporteCount" style="color:var(--text-muted);font-size:0.85rem;">—</span>
                </div>
                <div style="overflow-x:auto;margin-top:0.75rem;padding:0 1.5rem 1.5rem;">
                    <table class="striped" id="tablaReporte" style="min-width:480px;">
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
