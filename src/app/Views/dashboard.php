<!-- ============================================================
     VISTA: PANEL DE CONTROL (DASHBOARD)
     Muestra métricas clave del negocio: ventas del día, stock
     crítico, sesiones de cybercafé, solicitudes pendientes,
     horas pico, productos sin stock y actividad reciente.
     NOTA: Todos los datos son estáticos (simulados) - UI prototype.
     ============================================================ -->

<!-- Banner de bienvenida -->
<div class="welcome-banner">
    <h2 class="hide-on-small-only">¡Bienvenido de nuevo a EIS One Manager!</h2>
    <h2 class="hide-on-med-and-up">¡Bienvenido!</h2>
    <p>Gestiona tu negocio de manera eficiente con EIS System</p>
</div>

<!-- === TARJETAS DE MÉTRICAS (KPI) === -->
<div class="row" style="margin-bottom:1.5rem;">
    <!-- Tarjeta 1: Ventas del día -->
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <div class="metric-label">Ventas Hoy</div>
            <div class="metric-value">$1,245.50</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> 23 transacciones</div>
        </div>
    </div>
    <!-- Tarjeta 2: Productos con stock crítico (rojo) -->
    <div class="col s12 m6 l3">
        <div class="metric-card danger">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
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
            <div class="metric-value" style="color:var(--warning);">3</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Cuentas por pagar</div>
        </div>
    </div>
</div>

<!-- === TABLAS INFORMATIVAS === -->
<div class="row">
    <!-- Horas pico de ventas -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">access_time</i>Horas Pico</span>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th class="right-align">Transacciones</th>
                            <th class="right-align">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>10:00 - 11:00</td><td class="right-align" style="font-weight:700;">42</td><td class="right-align"><span style="color:var(--success);font-weight:600;">↑ 12%</span></td></tr>
                        <tr><td>14:00 - 15:00</td><td class="right-align" style="font-weight:700;">38</td><td class="right-align"><span style="color:var(--success);font-weight:600;">↑ 8%</span></td></tr>
                        <tr><td>18:00 - 19:00</td><td class="right-align" style="font-weight:700;">31</td><td class="right-align"><span style="color:var(--danger);font-weight:600;">↓ 5%</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Productos sin stock -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">inventory</i>Productos Sin Stock</span>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="right-align">Stock</th>
                            <th class="right-align">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Resma A4</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                        <tr><td><strong>Tóner Negro</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                        <tr><td><strong>Cable USB-C</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- === ACTIVIDAD RECIENTE === -->
<div class="card">
    <div class="card-content">
        <span class="card-title"><i class="material-icons left">history</i>Actividad Reciente</span>
        <!-- Cada activity-item muestra un evento ocurrido recientemente -->
        <div class="activity-item">
            <div class="activity-icon" style="background:#e3f2fd;color:#1565c0;"><i class="material-icons">shopping_cart</i></div>
            <div class="activity-content">
                <div class="activity-title">Venta #V-00142 procesada</div>
                <div class="activity-time">Hace 5 minutos - $245.00</div>
            </div>
        </div>
        <div class="activity-item">
            <div class="activity-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="material-icons">inventory</i></div>
            <div class="activity-content">
                <div class="activity-title">Stock actualizado: Mouse Inalámbrico</div>
                <div class="activity-time">Hace 15 minutos - +50 unidades</div>
            </div>
        </div>
        <div class="activity-item">
            <div class="activity-icon" style="background:#fff3e0;color:#e65100;"><i class="material-icons">desktop_windows</i></div>
            <div class="activity-content">
                <div class="activity-title">Nueva sesión Cyber iniciada</div>
                <div class="activity-time">Hace 30 minutos - Estación #5</div>
            </div>
        </div>
    </div>
</div>
