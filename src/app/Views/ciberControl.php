<?php

// ciber control no ciber cafe recordatorio de cambias eso 

//configuracion de la vase de datos  
require_once __DIR__ . '/../../Config/database.php';

//obtener estaciones con su estado actual y sesión activa (si la hay dd: )
$sql = "SELECT 
            ec.id,
            ec.nombre,
            ec.estado,
            ec.especificaciones,
            ec.tarifa_id,
            t.nombre as tarifa_nombre,
            t.precio_por_hora,
            sc.id as sesion_id,
            sc.cliente_nombre,
            sc.hora_inicio,
            TIMESTAMPDIFF(MINUTE, sc.hora_inicio, NOW()) as minutos_transcurridos,
            CASE 
                WHEN sc.hora_inicio IS NOT NULL THEN 
                    ROUND(TIMESTAMPDIFF(MINUTE, sc.hora_inicio, NOW()) / 60.0 * t.precio_por_hora, 2)
                ELSE NULL
            END as costo_estimado
        FROM estaciones_cyber ec
        LEFT JOIN tarifas_cyber t ON ec.tarifa_id = t.id
        LEFT JOIN sesiones_cyber sc ON ec.id = sc.estacion_id AND sc.estado = 'activa'
        ORDER BY ec.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

//numero de estaciones por estado para las métricas
//siempre dejarlas en 0 al iniciar para evitar errores de undefined index si no hay estaciones

$countDisponibles = 0;
$countOcupadas = 0;
$countMantenimiento = 0;

foreach ($estaciones as $e) {
    switch ($e['estado']) {
        case 'Disponible':
            $countDisponibles++;
            break;
        case 'Ocupada':
            $countOcupadas++;
            break;
        case 'Mantenimiento':
            $countMantenimiento++;
            break;
    }
}
$totalEstaciones = count($estaciones);

//estados de las estaciones switch por si sale alguna otra clase (no voy a usar un if)
function getEstadoClase($estado) {
    switch ($estado) {
        case 'Disponible': return 'disponible';
        case 'Ocupada': return 'ocupada';
        case 'Mantenimiento': return 'mantenimiento';
        default: return 'disponible';
    }
}

function getEstadoTexto($estado) {
    switch ($estado) {
        case 'Disponible': return 'Disponible';
        case 'Ocupada': return 'Ocupada';
        case 'Mantenimiento': return 'Mantenimiento';
        default: return $estado;
    }
}

function getEstadoIcono($estado) {
    switch ($estado) {
        case 'Disponible': return 'check_circle';
        case 'Ocupada': return 'timelapse';
        case 'Mantenimiento': return 'build';
        default: return 'help';
    }
}

// Agrupar estaciones por tipo para mostrar zonas (opcional aunque aun me pregunto por que no lo hice desde la consulta SQL con un campo tipo o algo asi, pero bueno, esto funciona y es mas flexible para cambios futuros como siempre d: demasidas comas)
$estacionesGaming = array_filter($estaciones, function($e) {
    return strpos($e['especificaciones'] ?? '', 'Gaming') !== false || $e['tarifa_nombre'] === 'Gaming';
});
$estacionesOficina = array_filter($estaciones, function($e) {
    return strpos($e['especificaciones'] ?? '', 'Estándar') !== false || $e['tarifa_nombre'] === 'Oficina';
});
$estacionesPremium = array_filter($estaciones, function($e) {
    return $e['tarifa_nombre'] === 'Premium';
});

// Si no hay suficientes para zonas, mostrar todas juntas
$usarZonas = count($estacionesGaming) > 0 || count($estacionesOficina) > 0 || count($estacionesPremium) > 0;
?>


// vista del ciber control, aqui se muestra el estado de las estaciones y se pueden cambiar (solo visualmente por ahora, en la fase 2 se agregara persistencia con AJAX)

<div class="row" style="margin-bottom:1.5rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Disponibles</div>
            <div class="metric-value" style="color:var(--success);" id="countDisponibles"><?= $countDisponibles ?></div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">timelapse</i></div>
            <div class="metric-label">Ocupadas</div>
            <div class="metric-value" style="color:var(--warning);" id="countOcupadas"><?= $countOcupadas ?></div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">build</i></div>
            <div class="metric-label">Mantenimiento</div>
            <div class="metric-value" style="color:var(--danger);" id="countMantenimiento"><?= $countMantenimiento ?></div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">dns</i></div>
            <div class="metric-label">Total Estaciones</div>
            <div class="metric-value" id="countTotal"><?= $totalEstaciones ?></div>
        </div>
    </div>
</div>

// Filtros y acciones (solo visuales por ahora, en la fase 2 se agregara funcionalidad con AJAX)
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-content" style="padding:0.75rem 1.25rem;">
        <div class="row" style="margin-bottom:0;">
            <div class="col s12 m7" style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;padding-top:0.25rem;padding-bottom:0.25rem;">
                <span class="hide-on-small-only" style="font-weight:600;color:var(--text-muted);font-size:0.85rem;margin-right:0.35rem;">FILTRAR:</span>
                <a class="btn-small waves-effect waves-light green filter-btn active" data-filter="all" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Todas</a>
                <a class="btn-small waves-effect waves-light green filter-btn" data-filter="disponible" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Disponibles</a>
                <a class="btn-small waves-effect waves-light orange filter-btn" data-filter="ocupada" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Ocupadas</a>
                <a class="btn-small waves-effect waves-light red filter-btn" data-filter="mantenimiento" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Mantenimiento</a>
            </div>
            <div class="col s12 m5 right-align" style="padding-top:0.25rem;padding-bottom:0.25rem;">
                <button class="btn-small waves-effect waves-light indigo" id="btnNuevaEstacion" style="border-radius:20px;">
                    <i class="material-icons left" style="font-size:1rem;">add</i>Nueva
                </button>
                <button class="btn-small waves-effect waves-light grey darken-1 hide-on-small-only" id="btnHistorialCyber" style="border-radius:20px;margin-left:0.25rem;">
                    <i class="material-icons left" style="font-size:1rem;">history</i>Historial
                </button>
                <button class="btn-small waves-effect waves-light grey darken-1 hide-on-med-and-up" id="btnHistorialCyberMobile" style="border-radius:20px;" title="Historial">
                    <i class="material-icons" style="font-size:1rem;">history</i>
                </button>
            </div>
        </div>
    </div>
</div>


<div id="cyberGrid">
    <?php if ($usarZonas && count($estacionesGaming) > 0): ?>
       //zona gaming
        <div class="zone-divider">
            <div class="zone-title">🎮 Zona Gaming</div>
        </div>
        <div class="row">
            <?php foreach ($estacionesGaming as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos']): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Estándar') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada' && $e['costo_estimado']): ?>
                        <div class="station-footer">
                            <div class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($usarZonas && count($estacionesOficina) > 0): ?>
        
        <div class="zone-divider">
            <div class="zone-title">Zona Oficina</div>
        </div>
        <div class="row">
            <?php foreach ($estacionesOficina as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos']): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Estándar') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada' && $e['costo_estimado']): ?>
                        <div class="station-footer">
                            <div class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($usarZonas && count($estacionesPremium) > 0): ?>
 //Recordatorio de que esto es solo visual. dispuesto a cambios otra vez 
        <div class="zone-divider">
            <div class="zone-title">⭐ Zona Premium</div>
        </div>
        <div class="row">
            <?php foreach ($estacionesPremium as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos']): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Premium') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada' && $e['costo_estimado']): ?>
                        <div class="station-footer">
                            <div class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$usarZonas): ?>
        <!-- Todas las estaciones juntas (sin zonas) -->
        <div class="row">
            <?php foreach ($estaciones as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos']): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Estándar') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada' && $e['costo_estimado']): ?>
                        <div class="station-footer">
                            <div class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:0.5rem;">
    <i class="material-icons left" style="font-size:1rem;">info</i> Los cambios aún NO persisten en BD (solo UI demo). En la Fase 2 agregaremos persistencia.
</p>

<script>
$(function () {
    // Actualizar contadores desde PHP (ya están calculados)
    // Los filtros y toggles son solo visuales por ahora (Fase 2 será con AJAX)
    
    $('#btnNuevaEstacion').on('click', function () {
        EIS.toast('Formulario para nueva estación (próximamente)', 'indigo', 'add_circle');
    });

    $('#btnHistorialCyber, #btnHistorialCyberMobile').on('click', function () {
        EIS.toast('Historial de sesiones (próximamente)', 'indigo', 'history');
    });
    
    // Mostrar mensaje de que los cambios son solo visuales
    $(document).on('click', '.station-card', function () {
        var $card = $(this);
        var status = $card.data('status');
        var nombre = $card.data('nombre');
        
        EIS.toast('Cambio de estado para ' + nombre + ' (próximamente con persistencia)', 'indigo', 'info');
    });
});
</script>
