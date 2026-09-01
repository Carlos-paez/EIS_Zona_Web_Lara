<?php

use App\Models\Dashboard;

// Datos reales desde la base de datos
$dash = new Dashboard();
$hoy = $dash->ventasHoy();
$stockCritico = $dash->stockCritico();
$sesionesCyber = $dash->sesionesCyberActivas();
$solicitudesPend = $dash->solicitudesPendientes();
$ventasPorDia = $dash->ventasPorDia();
$productosStock = $dash->productosStockCritico();
$actividad = $dash->actividadReciente();

$fmt = fn($v) => '$' . number_format((float)$v, 2);
?>

<!-- ============================================================
     VISTA: PANEL DE CONTROL (DASHBOARD)
     Muestra métricas reales del negocio calculadas desde la base de
     datos: ventas del día, stock crítico, sesiones de cybercafé,
     solicitudes pendientes, ventas por día, productos sin stock y
     actividad reciente.
     ============================================================ -->

<!-- ===== BANNER DE BIENVENIDA ===== -->
<div class="welcome-banner">
    <h2 class="hide-on-small-only">¡Bienvenido de nuevo a EIS One Manager!</h2>
    <h2 class="hide-on-med-and-up">¡Bienvenido!</h2>
    <p>Gestiona tu negocio de manera eficiente con EIS System</p>
</div>

<!-- ===== TARJETAS DE MÉTRICAS CLAVE (KPI) ===== -->
<div class="row" style="margin-bottom:1.5rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">payments</i></div>
            <div class="metric-label">Ventas Hoy</div>
            <div class="metric-value"><?php echo $fmt($hoy['total']); ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;"><i class="material-icons left" style="font-size:1rem;margin:0;">trending_up</i> <?php echo (int)$hoy['transacciones']; ?> transacciones</div>
        </div>
    </div>

    <div class="col s12 m6 l3">
        <div class="metric-card danger">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
            <div class="metric-value" style="color:var(--danger);"><?php echo (int)$stockCritico; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Productos bajo mínimo</div>
        </div>
    </div>

    <div class="col s12 m6 l3">
        <div class="metric-card warning">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">desktop_windows</i></div>
            <div class="metric-label">Sesiones Cyber</div>
            <div class="metric-value"><?php echo (int)$sesionesCyber; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Sesiones activas</div>
        </div>
    </div>

    <div class="col s12 m6 l3">
        <div class="metric-card info">
            <div class="metric-icon"><i class="material-icons" style="font-size:2.5rem;">assignment</i></div>
            <div class="metric-label">Solicitudes Pend.</div>
            <div class="metric-value" style="color:var(--warning);"><?php echo (int)$solicitudesPend; ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.5rem;">Cuentas por pagar</div>
        </div>
    </div>
</div>

<!-- ===== TABLAS INFORMATIVAS ===== -->
<div class="row">
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">access_time</i>Ventas por Día (7 días)</span>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th class="right-align">Transacciones</th>
                            <th class="right-align">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($ventasPorDia)): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:1rem;">Sin ventas en los últimos 7 días</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventasPorDia as $dia): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dia['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="right-align" style="font-weight:700;"><?php echo (int)$dia['transacciones']; ?></td>
                            <td class="right-align" style="color:var(--success);font-weight:600;"><?php echo $fmt($dia['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">inventory</i>Stock Crítico</span>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="right-align">Stock</th>
                            <th class="right-align">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($productosStock)): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:1rem;">Sin productos bajo mínimo</td></tr>
                    <?php else: ?>
                        <?php foreach ($productosStock as $p): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td class="right-align"><?php echo (int)$p['stock']; ?></td>
                            <td class="right-align"><span class="new badge red" data-badge-caption=""><?php echo (int)$p['stock'] <= 0 ? 'Sin stock' : 'Bajo'; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== ACTIVIDAD RECIENTE ===== -->
<div class="card">
    <div class="card-content">
        <span class="card-title"><i class="material-icons left">history</i>Actividad Reciente</span>

        <?php if (empty($actividad)): ?>
            <div style="color:var(--text-muted);padding:1rem;text-align:center;">Sin actividad registrada</div>
        <?php else: ?>
            <?php foreach ($actividad as $item): ?>
            <div class="activity-item">
                <div class="activity-icon" style="background:<?php echo $item['fondo']; ?>;color:<?php echo $item['color']; ?>;"><i class="material-icons"><?php echo $item['icono']; ?></i></div>
                <div class="activity-content">
                    <div class="activity-title"><?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="activity-time"><?php echo htmlspecialchars($item['detalle'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
