<?php
$zonas = [
    'Zona A' => [
        ['num' => 1,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
        ['num' => 2,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '45 min restantes',  'precio' => 2.50],
        ['num' => 3,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Estándar'],
        ['num' => 4,  'status' => 'mantenimiento', 'icono' => 'build',       'desc' => 'Teclado dañado'],
    ],
    'Zona B' => [
        ['num' => 5,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '1h 20 min restantes', 'precio' => 4.50],
        ['num' => 6,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
        ['num' => 7,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '30 min restantes',   'precio' => 1.50],
    ],
    'Zona C' => [
        ['num' => 8,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Estándar'],
        ['num' => 9,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
        ['num' => 10, 'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '2h restantes',       'precio' => 6.00],
    ],
];

$todasEstaciones = array_merge(...array_values($zonas));
$countDisponibles  = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'disponible'));
$countOcupadas     = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'ocupada'));
$countMantenimiento = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'mantenimiento'));
$totalEstaciones   = count($todasEstaciones);

$statusLabels = [
    'disponible'   => 'Disponible',
    'ocupada'      => 'Ocupada',
    'mantenimiento' => 'Mantenimiento',
];
?>

<div class="row" style="margin-bottom:1.5rem;">
    <div class="col s6 m3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Disponibles</div>
            <div class="metric-value" style="color:var(--success);" id="countDisponibles"><?= $countDisponibles ?></div>
        </div>
    </div>
    <div class="col s6 m3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">timelapse</i></div>
            <div class="metric-label">Ocupadas</div>
            <div class="metric-value" style="color:var(--warning);" id="countOcupadas"><?= $countOcupadas ?></div>
        </div>
    </div>
    <div class="col s6 m3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">build</i></div>
            <div class="metric-label">Mantenimiento</div>
            <div class="metric-value" style="color:var(--danger);" id="countMantenimiento"><?= $countMantenimiento ?></div>
        </div>
    </div>
    <div class="col s6 m3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">dns</i></div>
            <div class="metric-label">Total Estaciones</div>
            <div class="metric-value" id="countTotal"><?= $totalEstaciones ?></div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-content" style="padding:0.75rem 1.25rem;">
        <div class="row" style="margin-bottom:0;">
            <div class="col s12 m7" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;padding-top:0.25rem;padding-bottom:0.25rem;">
                <span style="font-weight:600;color:var(--text-muted);font-size:0.85rem;margin-right:0.5rem;">FILTRAR:</span>
                <a class="btn-small waves-effect waves-light green filter-btn active" data-filter="all" style="border-radius:20px;padding:0 1rem;">Todas</a>
                <a class="btn-small waves-effect waves-light green filter-btn" data-filter="disponible" style="border-radius:20px;padding:0 1rem;">Disponibles</a>
                <a class="btn-small waves-effect waves-light orange filter-btn" data-filter="ocupada" style="border-radius:20px;padding:0 1rem;">Ocupadas</a>
                <a class="btn-small waves-effect waves-light red filter-btn" data-filter="mantenimiento" style="border-radius:20px;padding:0 1rem;">Mantenimiento</a>
            </div>
            <div class="col s12 m5 right-align" style="padding-top:0.25rem;padding-bottom:0.25rem;">
                <button class="btn-small waves-effect waves-light indigo" id="btnNuevaEstacion" style="border-radius:20px;">
                    <i class="material-icons left" style="font-size:1rem;">add</i>Nueva
                </button>
                <button class="btn-small waves-effect waves-light grey darken-1" id="btnHistorialCyber" style="border-radius:20px;margin-left:0.25rem;">
                    <i class="material-icons left" style="font-size:1rem;">history</i>Historial
                </button>
            </div>
        </div>
    </div>
</div>

<div id="cyberGrid">
    <?php foreach ($zonas as $nombreZona => $estaciones): ?>
    <div class="zone-divider">
        <div class="zone-title"><?= $nombreZona ?></div>
    </div>
    <div class="row">
        <?php foreach ($estaciones as $e): ?>
        <div class="col s6 m4 l3 xl2">
            <div class="station-card <?= $e['status'] ?>" data-status="<?= $e['status'] ?>">
                <div class="station-inner">
                    <div class="station-header">
                        <span class="station-badge"><?= $e['num'] ?></span>
                        <span class="station-header-label">Estación</span>
                    </div>
                    <div class="station-body">
                        <div class="station-icon"><i class="material-icons"><?= $e['icono'] ?></i></div>
                        <div class="station-status"><?= $statusLabels[$e['status']] ?></div>
                        <div class="station-desc"><?= $e['desc'] ?></div>
                    </div>
                    <?php if (!empty($e['precio'])): ?>
                    <div class="station-footer">
                        <div class="station-price">$<?= number_format($e['precio'], 2) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:0.5rem;">
    <i class="material-icons left" style="font-size:1rem;">info</i> Haz clic en una estación para cambiar su estado
</p>

<script>
$(function () {
    $('#btnNuevaEstacion').on('click', function () {
        EIS.toast('Formulario para nueva estación (demo)', 'indigo', 'add_circle');
    });

    $('#btnHistorialCyber').on('click', function () {
        EIS.toast('Abriendo historial de sesiones (demo)', 'indigo', 'history');
    });
});
</script>
