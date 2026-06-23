<!-- ============================================================
     VISTA: PANEL DE CONTROL (DASHBOARD)
     Muestra métricas clave del negocio: ventas del día, stock
     crítico, sesiones de cybercafé, solicitudes pendientes,
     horas pico, productos sin stock y actividad reciente.
     NOTA: Todos los datos son estáticos (simulados) - UI prototype.
     ============================================================ -->

<!-- ================================================================ -->
<!-- BANNER DE BIENVENIDA -->
<!-- Encabezado principal que saluda al usuario al ingresar al dashboard -->
<!-- ================================================================ -->
<div class="welcome-banner">
    <!-- Título grande visible solo en pantallas medianas y grandes (hide-on-small-only) -->
    <h2 class="hide-on-small-only">¡Bienvenido de nuevo a EIS One Manager!</h2>
    <!-- Título alternativo más corto visible solo en pantallas pequeñas (hide-on-med-and-up) -->
    <h2 class="hide-on-med-and-up">¡Bienvenido!</h2>
    <!-- Descripción del propósito del sistema -->
    <p>Gestiona tu negocio de manera eficiente con EIS System</p>
</div>

<!-- ================================================================ -->
<!-- SECCIÓN: TARJETAS DE MÉTRICAS CLAVE (KPI) -->
<!-- Cuatro tarjetas en una fila responsive que muestran indicadores principales -->
<!-- ================================================================ -->
<div class="row" style="margin-bottom:1.5rem;">

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 1: VENTAS DEL DÍA -->
    <!-- Muestra el total de ventas acumulado en el día actual -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <!-- Ícono representativo (pagos/dinero) -->
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <!-- Etiqueta descriptiva de la métrica -->
            <div class="metric-label">Ventas Hoy</div>
            <!-- Valor numérico de la métrica (dato simulado) -->
            <div class="metric-value">$1,245.50</div>
            <!-- Sub-texto con detalle adicional (cantidad de transacciones y flecha de tendencia) -->
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> 23 transacciones</div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 2: STOCK CRÍTICO -->
    <!-- Alerta roja indicando productos por debajo del stock mínimo -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <!-- Clase "danger" aplica estilo rojo a la tarjeta -->
        <div class="metric-card danger">
            <!-- Ícono de advertencia -->
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
            <!-- Valor en color rojo (variable CSS --danger) para llamar la atención -->
            <div class="metric-value" style="color:var(--danger);">4</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Productos bajo mínimo</div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 3: SESIONES CYBER -->
    <!-- Muestra la cantidad de sesiones activas de cybercafé -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <!-- Clase "warning" aplica estilo amarillo/naranja a la tarjeta -->
        <div class="metric-card warning">
            <!-- Ícono de computadora/escritorio -->
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">desktop_windows</i></div>
            <div class="metric-label">Sesiones Cyber</div>
            <div class="metric-value">7</div>
            <!-- Detalle: promedio de duración por sesión -->
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Prom: 45 min/sesión</div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 4: SOLICITUDES PENDIENTES -->
    <!-- Solicitudes de compra o cuentas por pagar a proveedores -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <!-- Clase "info" aplica estilo azul a la tarjeta -->
        <div class="metric-card info">
            <!-- Ícono de asignación/documento -->
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">assignment</i></div>
            <div class="metric-label">Solicitudes Pend.</div>
            <!-- Valor en color warning (naranja) -->
            <div class="metric-value" style="color:var(--warning);">3</div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Cuentas por pagar</div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- SECCIÓN: TABLAS INFORMATIVAS -->
<!-- Dos tablas lado a lado: Horas pico y Productos sin stock -->
<!-- ================================================================ -->
<div class="row">

    <!-- ---------------------------------------------------------------- -->
    <!-- TABLA 1: HORAS PICO DE VENTAS -->
    <!-- Muestra los horarios con mayor actividad de ventas y su tendencia -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <!-- Título de la tarjeta con ícono de reloj -->
                <span class="card-title"><i class="material-icons left">access_time</i>Horas Pico</span>
                <!-- Tabla responsive (se adapta a pantallas pequeñas) -->
                <table class="responsive-table">
                    <!-- Encabezados de columna -->
                    <thead>
                        <tr>
                            <!-- Columna: Rango horario -->
                            <th>Hora</th>
                            <!-- Columna: Cantidad de transacciones (alineada a la derecha) -->
                            <th class="right-align">Transacciones</th>
                            <!-- Columna: Tendencia (alineada a la derecha) -->
                            <th class="right-align">Tendencia</th>
                        </tr>
                    </thead>
                    <!-- Cuerpo de la tabla con datos simulados -->
                    <tbody>
                        <!-- Fila 1: 10:00-11:00, 42 transacciones, tendencia al alza 12% -->
                        <tr><td>10:00 - 11:00</td><td class="right-align" style="font-weight:700;">42</td><td class="right-align"><span style="color:var(--success);font-weight:600;">↑ 12%</span></td></tr>
                        <!-- Fila 2: 14:00-15:00, 38 transacciones, tendencia al alza 8% -->
                        <tr><td>14:00 - 15:00</td><td class="right-align" style="font-weight:700;">38</td><td class="right-align"><span style="color:var(--success);font-weight:600;">↑ 8%</span></td></tr>
                        <!-- Fila 3: 18:00-19:00, 31 transacciones, tendencia a la baja 5% (color rojo) -->
                        <tr><td>18:00 - 19:00</td><td class="right-align" style="font-weight:700;">31</td><td class="right-align"><span style="color:var(--danger);font-weight:600;">↓ 5%</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TABLA 2: PRODUCTOS SIN STOCK -->
    <!-- Lista de productos que están agotados (stock = 0) -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <!-- Título de la tarjeta con ícono de inventario -->
                <span class="card-title"><i class="material-icons left">inventory</i>Productos Sin Stock</span>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <!-- Columna: Nombre del producto -->
                            <th>Producto</th>
                            <!-- Columna: Cantidad de stock (alineada a la derecha) -->
                            <th class="right-align">Stock</th>
                            <!-- Columna: Estado del producto (alineada a la derecha) -->
                            <th class="right-align">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Producto: Resma A4 (stock 0, badge rojo "Sin stock") -->
                        <tr><td><strong>Resma A4</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                        <!-- Producto: Tóner Negro (stock 0) -->
                        <tr><td><strong>Tóner Negro</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                        <!-- Producto: Cable USB-C (stock 0) -->
                        <tr><td><strong>Cable USB-C</strong></td><td class="right-align">0</td><td class="right-align"><span class="new badge red" data-badge-caption="">Sin stock</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- SECCIÓN: ACTIVIDAD RECIENTE -->
<!-- Timeline de eventos recientes: ventas, actualizaciones de stock, sesiones -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-content">
        <!-- Título de la tarjeta con ícono de historial -->
        <span class="card-title"><i class="material-icons left">history</i>Actividad Reciente</span>

        <!-- ---------------------------------------------------------------- -->
        <!-- ITEM 1: Venta procesada -->
        <!-- Muestra la última venta realizada con su monto y tiempo transcurrido -->
        <!-- ---------------------------------------------------------------- -->
        <div class="activity-item">
            <!-- Ícono con fondo azul claro y color azul oscuro representando compra -->
            <div class="activity-icon" style="background:#e3f2fd;color:#1565c0;"><i class="material-icons">shopping_cart</i></div>
            <div class="activity-content">
                <!-- Título del evento -->
                <div class="activity-title">Venta #V-00142 procesada</div>
                <!-- Marca de tiempo relativa ("Hace 5 minutos") y monto -->
                <div class="activity-time">Hace 5 minutos - $245.00</div>
            </div>
        </div>

        <!-- ---------------------------------------------------------------- -->
        <!-- ITEM 2: Actualización de stock -->
        <!-- Se agregaron 50 unidades de Mouse Inalámbrico al inventario -->
        <!-- ---------------------------------------------------------------- -->
        <div class="activity-item">
            <!-- Ícono con fondo verde claro y color verde oscuro representando inventario -->
            <div class="activity-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="material-icons">inventory</i></div>
            <div class="activity-content">
                <div class="activity-title">Stock actualizado: Mouse Inalámbrico</div>
                <div class="activity-time">Hace 15 minutos - +50 unidades</div>
            </div>
        </div>

        <!-- ---------------------------------------------------------------- -->
        <!-- ITEM 3: Nueva sesión Cyber iniciada -->
        <!-- Un usuario inició sesión en la estación #5 del cybercafé -->
        <!-- ---------------------------------------------------------------- -->
        <div class="activity-item">
            <!-- Ícono con fondo naranja claro y color naranja oscuro representando cyber -->
            <div class="activity-icon" style="background:#fff3e0;color:#e65100;"><i class="material-icons">desktop_windows</i></div>
            <div class="activity-content">
                <div class="activity-title">Nueva sesión Cyber iniciada</div>
                <div class="activity-time">Hace 30 minutos - Estación #5</div>
            </div>
        </div>
    </div>
</div>
