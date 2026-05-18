<!-- ============================================================
     VISTA: PANEL DE CONTROL (DASHBOARD)
     ============================================================
     Muestra métricas clave del negocio: ventas del día, stock
     crítico, sesiones de cybercafé, solicitudes pendientes,
     horas pico, productos sin stock y actividad reciente.

     Renderizada dentro del layout principal por
     DashboardController::index().

     NOTA: Todos los datos son estáticos (simulados) - UI prototype.
     En producción, estos datos vendrían de consultas a la BD
     mediante los Models correspondientes.
     ============================================================ -->

<!-- ========== BANNER DE BIENVENIDA ========== -->
<div class="welcome-banner">
    <!-- Título principal de bienvenida -->
    <h2>¡Bienvenido de nuevo!</h2>
    <!-- Subtítulo motivacional -->
    <p>Gestiona tu negocio de manera eficiente con EIS System</p>
</div>

<!-- ========== TARJETAS DE MÉTRICAS (KPI) ========== -->
<!-- row de Materialize: sistema de columnas (12 columnas) -->
<div class="row" style="margin-bottom:1.5rem;">

    <!-- Tarjeta 1: Ventas del día -->
    <!-- col s12 m6 l3 = en móvil 12 cols, tablet 6 cols, desktop 3 cols -->
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <!-- Icono de la métrica (icono de pagos) -->
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <!-- Etiqueta descriptiva -->
            <div class="metric-label">Ventas Hoy</div>
            <!-- Valor numérico de la métrica -->
            <div class="metric-value">$1,245.50</div>
            <!-- Información adicional con indicador de tendencia -->
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">
                <i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> 23 transacciones
            </div>
        </div>
    </div>

    <!-- Tarjeta 2: Productos con stock crítico (rojo) -->
    <!-- danger: clase que aplica estilo de advertencia/riesgo -->
    <div class="col s12 m6 l3">
        <div class="metric-card danger">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
            <!-- Valor en rojo usando variable CSS --danger -->
            <div class="metric-value" style="color:var(--danger);">4</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Productos bajo mínimo</div>
        </div>
    </div>

    <!-- Tarjeta 3: Sesiones activas de cybercafé -->
    <div class="col s12 m6 l3">
        <div class="metric-card warning">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">desktop_windows</i></div>
            <div class="metric-label">Sesiones Cyber</div>
            <div class="metric-value">7</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Prom: 45 min/sesión</div>
        </div>
    </div>

    <!-- Tarjeta 4: Solicitudes pendientes a proveedores -->
    <div class="col s12 m6 l3">
        <div class="metric-card info">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">assignment</i></div>
            <div class="metric-label">Solicitudes Pend.</div>
            <!-- Valor en color de advertencia (naranja) -->
            <div class="metric-value" style="color:var(--warning);">3</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Cuentas por pagar</div>
        </div>
    </div>
</div>

<!-- ========== TABLAS INFORMATIVAS ========== -->
<div class="row">

    <!-- Horas pico de ventas (tabla izquierda) -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <!-- Título de la tarjeta con icono de reloj -->
                <span class="card-title"><i class="material-icons left">access_time</i>Horas Pico</span>
                <table>
                    <thead>
                        <tr>
                            <th>Hora</th>                              <!-- Rango horario -->
                            <th class="right-align">Transacciones</th> <!-- Cantidad (alineado derecha) -->
                            <th class="right-align">Tendencia</th>     <!-- Indicador de tendencia -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila 1: 10:00-11:00 con tendencia positiva ↑ 12% -->
                        <tr><td>10:00 - 11:00</td><td class="right-align" style="font-weight:700;">42</td><td class="right-align"><span style="color:var(--success);font-weight:600;">↑ 12%</span></td></tr>
                        <!-- Fila 2: 14:00-15:00 con tendencia positiva ↑ 8% -->
                        <tr><td>14:00 - 15:00</td><td class="right-align" style="font-weight:700;">38</td><td class="right-align"><span style="color:var(--success);font-weight:600;">↑ 8%</span></td></tr>
                        <!-- Fila 3: 18:00-19:00 con tendencia negativa ↓ 5% -->
                        <tr><td>18:00 - 19:00</td><td class="right-align" style="font-weight:700;">31</td><td class="right-align"><span style="color:var(--danger);font-weight:600;">↓ 5%</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Productos sin stock (tabla derecha) -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">inventory</i>Productos Sin Stock</span>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="right-align">Stock</th>
                            <th class="right-align">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Producto 1: Resma A4 con badge rojo "Sin stock" -->
                        <tr><td><strong>Resma A4</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                        <!-- Producto 2: Tóner Negro -->
                        <tr><td><strong>Tóner Negro</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                        <!-- Producto 3: Cable USB-C -->
                        <tr><td><strong>Cable USB-C</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========== ACTIVIDAD RECIENTE ========== -->
<div class="card">
    <div class="card-content">
        <span class="card-title"><i class="material-icons left">history</i>Actividad Reciente</span>

        <!-- Cada activity-item muestra un evento ocurrido recientemente -->
        <!-- Estructura: icono + contenido (título + tiempo) -->

        <!-- Evento 1: Venta procesada -->
        <div class="activity-item">
            <!-- Icono con fondo azul claro -->
            <div class="activity-icon" style="background:#e3f2fd;color:#1565c0;"><i class="material-icons">shopping_cart</i></div>
            <div class="activity-content">
                <div class="activity-title">Venta #V-00142 procesada</div>
                <div class="activity-time">Hace 5 minutos - $245.00</div>
            </div>
        </div>

        <!-- Evento 2: Stock actualizado -->
        <div class="activity-item">
            <div class="activity-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="material-icons">inventory</i></div>
            <div class="activity-content">
                <div class="activity-title">Stock actualizado: Mouse Inalámbrico</div>
                <div class="activity-time">Hace 15 minutos - +50 unidades</div>
            </div>
        </div>

        <!-- Evento 3: Nueva sesión de cyber -->
        <div class="activity-item">
            <div class="activity-icon" style="background:#fff3e0;color:#e65100;"><i class="material-icons">desktop_windows</i></div>
            <div class="activity-content">
                <div class="activity-title">Nueva sesión Cyber iniciada</div>
                <div class="activity-time">Hace 30 minutos - Estación #5</div>
            </div>
        </div>
    </div>
</div>
