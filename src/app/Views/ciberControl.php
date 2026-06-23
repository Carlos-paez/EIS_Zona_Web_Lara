<?php
// =============================================================================
// VISTA: CONTROL DE CYBERCAFÉ (ciberControl.php)
// =============================================================================
// Propósito: Muestra el estado de las estaciones de cybercafé organizadas
//            por zonas, con indicadores de disponibles, ocupadas y en
//            mantenimiento. Incluye filtros rápidos para visualización.
// =============================================================================

// Datos estáticos (simulados) de las estaciones organizadas por zonas
$zonas = [
    // Zona A: 4 estaciones (PCs)
    'Zona A' => [
        // Estación 1: disponible (PC Gaming)
        ['num' => 1,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
        // Estación 2: ocupada, con precio y tiempo restante
        ['num' => 2,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '45 min restantes',  'precio' => 2.50],
        // Estación 3: disponible (PC Estándar)
        ['num' => 3,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Estándar'],
        // Estación 4: en mantenimiento por teclado dañado
        ['num' => 4,  'status' => 'mantenimiento', 'icono' => 'build',       'desc' => 'Teclado dañado'],
    ],
    // Zona B: 3 estaciones
    'Zona B' => [
        // Estación 5: ocupada con tiempo restante
        ['num' => 5,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '1h 20 min restantes', 'precio' => 4.50],
        // Estación 6: disponible (PC Gaming)
        ['num' => 6,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
        // Estación 7: ocupada con tiempo restante
        ['num' => 7,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '30 min restantes',   'precio' => 1.50],
    ],
    // Zona C: 3 estaciones
    'Zona C' => [
        // Estación 8: disponible (PC Estándar)
        ['num' => 8,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Estándar'],
        // Estación 9: disponible (PC Gaming)
        ['num' => 9,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
        // Estación 10: ocupada con precio
        ['num' => 10, 'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '2h restantes',       'precio' => 6.00],
    ],
];

// Aplana el array multidimensional a un solo nivel para hacer cálculos
// array_values($zonas) obtiene solo los arrays internos, array_merge los combina en uno solo
$todasEstaciones = array_merge(...array_values($zonas));
// Cuenta cuántas estaciones están en estado 'disponible'
$countDisponibles  = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'disponible'));
// Cuenta cuántas estaciones están en estado 'ocupada'
$countOcupadas     = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'ocupada'));
// Cuenta cuántas estaciones están en estado 'mantenimiento'
$countMantenimiento = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'mantenimiento'));
// Total general de estaciones
$totalEstaciones   = count($todasEstaciones);

// Mapa de estados a etiquetas legibles para mostrar en la UI
$statusLabels = [
    'disponible'   => 'Disponible',
    'ocupada'      => 'Ocupada',
    'mantenimiento' => 'Mantenimiento',
];
?>

<!-- ===== TARJETAS KPI (MÉTRICAS DEL CYBERCAFÉ) ===== -->
<!-- Fila con margen inferior -->
<div class="row" style="margin-bottom:1.5rem;">
    <!-- Columna: Estaciones disponibles -->
    <div class="col s12 m6 l3">
        <!-- Tarjeta con estilo de éxito -->
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Disponibles</div>
            <!-- Valor numérico en color success, con ID para actualizar via JS -->
            <div class="metric-value" style="color:var(--success);" id="countDisponibles"><?= $countDisponibles ?></div>
        </div>
    </div>
    <!-- Columna: Estaciones ocupadas -->
    <div class="col s12 m6 l3">
        <!-- Tarjeta con estilo de advertencia -->
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">timelapse</i></div>
            <div class="metric-label">Ocupadas</div>
            <div class="metric-value" style="color:var(--warning);" id="countOcupadas"><?= $countOcupadas ?></div>
        </div>
    </div>
    <!-- Columna: Estaciones en mantenimiento -->
    <div class="col s12 m6 l3">
        <!-- Tarjeta con estilo de peligro -->
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">build</i></div>
            <div class="metric-label">Mantenimiento</div>
            <div class="metric-value" style="color:var(--danger);" id="countMantenimiento"><?= $countMantenimiento ?></div>
        </div>
    </div>
    <!-- Columna: Total de estaciones -->
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">dns</i></div>
            <div class="metric-label">Total Estaciones</div>
            <div class="metric-value" id="countTotal"><?= $totalEstaciones ?></div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE HERRAMIENTAS (FILTROS Y BOTONES DE ACCIÓN) ===== -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-content" style="padding:0.75rem 1.25rem;">
        <div class="row" style="margin-bottom:0;">
            <!-- Botones de filtro rápido por estado -->
            <div class="col s12 m7" style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;padding-top:0.25rem;padding-bottom:0.25rem;">
                <!-- Etiqueta "FILTRAR" visible solo en pantallas grandes -->
                <span class="hide-on-small-only" style="font-weight:600;color:var(--text-muted);font-size:0.85rem;margin-right:0.35rem;">FILTRAR:</span>
                <!-- Botón para mostrar todas las estaciones (activo por defecto) -->
                <a class="btn-small waves-effect waves-light green filter-btn active" data-filter="all" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Todas</a>
                <!-- Botón para filtrar solo disponibles -->
                <a class="btn-small waves-effect waves-light green filter-btn" data-filter="disponible" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Disponibles</a>
                <!-- Botón para filtrar solo ocupadas -->
                <a class="btn-small waves-effect waves-light orange filter-btn" data-filter="ocupada" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Ocupadas</a>
                <!-- Botón para filtrar solo en mantenimiento -->
                <a class="btn-small waves-effect waves-light red filter-btn" data-filter="mantenimiento" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Mantenimiento</a>
            </div>
            <!-- Botones de acción: Nueva estación e Historial -->
            <div class="col s12 m5 right-align" style="padding-top:0.25rem;padding-bottom:0.25rem;">
                <!-- Botón para agregar nueva estación -->
                <button class="btn-small waves-effect waves-light indigo" id="btnNuevaEstacion" style="border-radius:20px;">
                    <i class="material-icons left" style="font-size:1rem;">add</i>Nueva
                </button>
                <!-- Botón para ver historial (visible en pantallas grandes) -->
                <button class="btn-small waves-effect waves-light grey darken-1 hide-on-small-only" id="btnHistorialCyber" style="border-radius:20px;margin-left:0.25rem;">
                    <i class="material-icons left" style="font-size:1rem;">history</i>Historial
                </button>
                <!-- Botón para ver historial (visible solo en móviles, solo ícono) -->
                <button class="btn-small waves-effect waves-light grey darken-1 hide-on-med-and-up" id="btnHistorialCyberMobile" style="border-radius:20px;" title="Historial">
                    <i class="material-icons" style="font-size:1rem;">history</i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== GRILLA DE ESTACIONES ORGANIZADAS POR ZONAS ===== -->
<div id="cyberGrid">
    <!-- Itera sobre cada zona (Zona A, Zona B, Zona C) -->
    <?php foreach ($zonas as $nombreZona => $estaciones): ?>
    <!-- Divisor de zona con el nombre de la zona -->
    <div class="zone-divider">
        <div class="zone-title"><?= $nombreZona ?></div>
    </div>
    <!-- Fila contenedora de las estaciones de esta zona -->
    <div class="row">
        <!-- Itera sobre cada estación dentro de la zona actual -->
        <?php foreach ($estaciones as $e): ?>
        <!-- Cada estación ocupa columnas responsivas (6/4/3/2 según pantalla) -->
        <div class="col s6 m4 l3 xl2">
            <!-- Tarjeta de estación: la clase CSS se define según el status -->
            <div class="station-card <?= $e['status'] ?>" data-status="<?= $e['status'] ?>">
                <div class="station-inner">
                    <!-- Encabezado de la tarjeta: número y etiqueta -->
                    <div class="station-header">
                        <span class="station-badge"><?= $e['num'] ?></span>
                        <span class="station-header-label">Estación</span>
                    </div>
                    <!-- Cuerpo de la tarjeta: ícono, estado y descripción -->
                    <div class="station-body">
                        <!-- Ícono dinámico según el estado -->
                        <div class="station-icon"><i class="material-icons"><?= $e['icono'] ?></i></div>
                        <!-- Etiqueta legible del estado -->
                        <div class="station-status"><?= $statusLabels[$e['status']] ?></div>
                        <!-- Descripción adicional (ej: tiempo restante, detalle) -->
                        <div class="station-desc"><?= $e['desc'] ?></div>
                    </div>
                    <!-- Si la estación tiene un precio definido, muestra el footer con el costo -->
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

<!-- Mensaje informativo sobre la interacción con las estaciones -->
<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:0.5rem;">
    <i class="material-icons left" style="font-size:1rem;">info</i> Haz clic en una estación para cambiar su estado
</p>

<!-- ===== SCRIPTS JAVASCRIPT ===== -->
<script>
// Ejecuta el código cuando el DOM esté listo (jQuery)
$(function () {
    // Manejador de clic para el botón "Nueva Estación"
    $('#btnNuevaEstacion').on('click', function () {
        // Muestra un toast de demostración (simulado)
        EIS.toast('Formulario para nueva estación (demo)', 'indigo', 'add_circle');
    });

    // Manejador de clic para ambos botones de historial (escritorio y móvil)
    $('#btnHistorialCyber, #btnHistorialCyberMobile').on('click', function () {
        // Muestra un toast de demostración
        EIS.toast('Abriendo historial de sesiones (demo)', 'indigo', 'history');
    });
});
</script>
