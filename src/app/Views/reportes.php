<!-- ============================================================
     VISTA: REPORTES Y ESTADÍSTICAS
     Muestra KPIs mensuales, un generador de reportes con
     selección de tipo, rango de fechas y formato de salida,
     y un listado de reportes generados recientemente.
     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- ===== TARJETAS DE MÉTRICAS MENSUALES (KPIs) ===== -->
<div class="row" style="margin-bottom:1.5rem;">
    <!-- Tarjeta: Ventas del mes -->
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <div class="metric-label">Ventas del Mes</div>
            <!-- Valor estático de ejemplo -->
            <div class="metric-value">$34,580</div>
            <!-- Indicador de tendencia positiva con ícono -->
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> 12% vs mes anterior</div>
        </div>
    </div>
    <!-- Tarjeta: Productos activos en inventario -->
    <div class="col s12 m6 l3">
        <div class="metric-card success">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">inventory_2</i></div>
            <div class="metric-label">Productos Activos</div>
            <div class="metric-value" style="color:var(--success);">245</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">En inventario</div>
        </div>
    </div>
    <!-- Tarjeta: Horas de cybercafé -->
    <div class="col s12 m6 l3">
        <div class="metric-card warning">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">desktop_windows</i></div>
            <div class="metric-label">Horas Cyber</div>
            <div class="metric-value" style="color:var(--warning);">1,240</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Este mes</div>
        </div>
    </div>
    <!-- Tarjeta: Solicitudes procesadas -->
    <div class="col s12 m6 l3">
        <div class="metric-card info">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">assignment</i></div>
            <div class="metric-label">Solicitudes</div>
            <div class="metric-value" style="color:var(--info);">28</div>
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
                <!-- Formulario de generación de reportes -->
                <form id="formReporte">
                    <!-- Selector: Tipo de reporte -->
                    <div class="input-field">
                        <select>
                            <!-- Opción por defecto: Ventas por fecha -->
                            <option value="" selected>Ventas por fecha</option>
                            <option value="inventario">Estado de inventario</option>
                            <option value="movimientos">Movimientos de stock</option>
                            <option value="proveedores">Solicitudes a proveedores</option>
                            <option value="cyber">Horas Cybercafé</option>
                        </select>
                        <label>Tipo de Reporte</label>
                    </div>
                    <!-- Rango de fechas (inicio y fin) -->
                    <div class="row" style="margin-bottom:0;">
                        <div class="col s12 m6">
                            <div class="input-field">
                                <!-- Fecha de inicio con valor predefinido -->
                                <input type="date" id="fechaInicio" value="2024-04-01">
                                <label for="fechaInicio" class="active">Fecha Inicio</label>
                            </div>
                        </div>
                        <div class="col s12 m6">
                            <div class="input-field">
                                <!-- Fecha de fin con valor predefinido -->
                                <input type="date" id="fechaFin" value="2024-04-30">
                                <label for="fechaFin" class="active">Fecha Fin</label>
                            </div>
                        </div>
                    </div>
                    <!-- Selección del formato de salida (radio buttons) -->
                    <div style="margin-bottom:1.5rem;">
                        <label style="font-size:0.9rem;font-weight:500;display:block;margin-bottom:0.5rem;">Formato de salida</label>
                        <!-- Opción PDF (marcada por defecto) -->
                        <p><label><input name="format" type="radio" value="pdf" checked><span>PDF</span></label></p>
                        <!-- Opción Excel -->
                        <p><label><input name="format" type="radio" value="excel"><span>Excel</span></label></p>
                        <!-- Opción CSV -->
                        <p><label><input name="format" type="radio" value="csv"><span>CSV</span></label></p>
                    </div>
                    <!-- Botón para generar el reporte -->
                    <button type="submit" class="btn waves-effect waves-light indigo" style="width:100%;height:3rem;border-radius:8px;">
                        <i class="material-icons left">download</i>Generar Reporte
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== COLUMNA DERECHA: LISTADO DE REPORTES GENERADOS RECIENTEMENTE ===== -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">history</i>Reportes Recientes</span>
                <!-- Cada activity-item representa un reporte generado -->
                <!-- Reporte 1: Ventas de Abril 2024 -->
                <div class="activity-item">
                    <!-- Icono del reporte con fondo azul claro -->
                    <div class="activity-icon" style="background:#e3f2fd;color:#1565c0;"><i class="material-icons">bar_chart</i></div>
                    <div class="activity-content">
                        <div class="activity-title">Ventas - Abril 2024</div>
                        <div class="activity-time">Generado hoy a las 10:30 AM</div>
                    </div>
                    <!-- Botón de descarga -->
                    <button class="btn-floating waves-effect waves-light grey btn-download tooltipped" data-position="top" data-tooltip="Descargar"><i class="material-icons">download</i></button>
                </div>
                <!-- Reporte 2: Inventario Actual -->
                <div class="activity-item">
                    <div class="activity-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="material-icons">inventory_2</i></div>
                    <div class="activity-content">
                        <div class="activity-title">Inventario Actual</div>
                        <div class="activity-time">Generado ayer a las 3:15 PM</div>
                    </div>
                    <button class="btn-floating waves-effect waves-light grey btn-download tooltipped" data-position="top" data-tooltip="Descargar"><i class="material-icons">download</i></button>
                </div>
                <!-- Reporte 3: Horas Cyber - Marzo -->
                <div class="activity-item">
                    <div class="activity-icon" style="background:#fff3e0;color:#e65100;"><i class="material-icons">desktop_windows</i></div>
                    <div class="activity-content">
                        <div class="activity-title">Horas Cyber - Marzo</div>
                        <div class="activity-time">Generado hace 2 días</div>
                    </div>
                    <button class="btn-floating waves-effect waves-light grey btn-download tooltipped" data-position="top" data-tooltip="Descargar"><i class="material-icons">download</i></button>
                </div>
                <!-- Reporte 4: Solicitudes Q1 2024 -->
                <div class="activity-item">
                    <div class="activity-icon" style="background:#fff3e0;color:#e65100;"><i class="material-icons">assignment</i></div>
                    <div class="activity-content">
                        <div class="activity-title">Solicitudes Q1 2024</div>
                        <div class="activity-time">Generado hace 5 días</div>
                    </div>
                    <button class="btn-floating waves-effect waves-light grey btn-download tooltipped" data-position="top" data-tooltip="Descargar"><i class="material-icons">download</i></button>
                </div>
            </div>
        </div>
    </div>
</div>
