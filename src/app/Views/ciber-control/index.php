<!-- ============================================================
     VISTA: CONTROL DE CYBERCAFÉ
     ============================================================
     Muestra un grid de estaciones de cybercafé agrupadas por
     zona, con su estado (disponible, ocupada, mantenimiento),
     filtros interactivos y métricas en tiempo real.

     Renderizada dentro del layout principal por
     CiberControlController::index().

     Las variables PHP ($zonas, $countDisponibles, etc.) son
     INYECTADAS por el controlador, no definidas aquí.
     En la versión anterior (Views/ciberControl.php) estos datos
     se definían directamente en la vista. Ahora siguen el
     patrón MVC: el Controller prepara los datos, la Vista solo
     los presenta.
     ============================================================ -->

<!-- Comentario visible solo en código fuente que recuerda el origen de las variables -->
<?php
// Variables inyectadas por CiberControlController::index():
//   $zonas              - Array multidimensional con zonas y estaciones
//   $countDisponibles   - Número de estaciones disponibles
//   $countOcupadas      - Número de estaciones ocupadas
//   $countMantenimiento - Número de estaciones en mantenimiento
//   $totalEstaciones    - Total de estaciones
//   $statusLabels       - Traducción de estados (disponible → "Disponible", etc.)
?>

<!-- ========== TARJETAS DE MÉTRICAS ========== -->
<div class="row" style="margin-bottom:1.5rem;">

    <!-- Estaciones Disponibles (verde) -->
    <div class="col s6 m3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Disponibles</div>
            <!-- id="countDisponibles" actualizado por JS si hay cambios dinámicos -->
            <div class="metric-value" style="color:var(--success);" id="countDisponibles"><?= $countDisponibles ?></div>
        </div>
    </div>

    <!-- Estaciones Ocupadas (naranja) -->
    <div class="col s6 m3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">timelapse</i></div>
            <div class="metric-label">Ocupadas</div>
            <div class="metric-value" style="color:var(--warning);" id="countOcupadas"><?= $countOcupadas ?></div>
        </div>
    </div>

    <!-- Estaciones en Mantenimiento (rojo) -->
    <div class="col s6 m3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">build</i></div>
            <div class="metric-label">Mantenimiento</div>
            <div class="metric-value" style="color:var(--danger);" id="countMantenimiento"><?= $countMantenimiento ?></div>
        </div>
    </div>

    <!-- Total de Estaciones -->
    <div class="col s6 m3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">dns</i></div>
            <div class="metric-label">Total Estaciones</div>
            <div class="metric-value" id="countTotal"><?= $totalEstaciones ?></div>
        </div>
    </div>
</div>

<!-- ========== FILTROS Y ACCIONES ========== -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-content" style="padding:0.75rem 1.25rem;">
        <div class="row" style="margin-bottom:0;">

            <!-- Botones de filtro por estado -->
            <div class="col s12 m7" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;padding-top:0.25rem;padding-bottom:0.25rem;">
                <span style="font-weight:600;color:var(--text-muted);font-size:0.85rem;margin-right:0.5rem;">FILTRAR:</span>
                <!-- filter-btn: clase usada por JS en app.js para filtrar estaciones -->
                <!-- active: indica el filtro actualmente seleccionado -->
                <a class="btn-small waves-effect waves-light green filter-btn active" data-filter="all" style="border-radius:20px;padding:0 1rem;">Todas</a>
                <a class="btn-small waves-effect waves-light green filter-btn" data-filter="disponible" style="border-radius:20px;padding:0 1rem;">Disponibles</a>
                <a class="btn-small waves-effect waves-light orange filter-btn" data-filter="ocupada" style="border-radius:20px;padding:0 1rem;">Ocupadas</a>
                <a class="btn-small waves-effect waves-light red filter-btn" data-filter="mantenimiento" style="border-radius:20px;padding:0 1rem;">Mantenimiento</a>
            </div>

            <!-- Botones de acción (Nueva estación, Historial) -->
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

<!-- ========== GRID DE ESTACIONES ========== -->
<div id="cyberGrid">
    <!-- Recorrer cada zona (Zona A, Zona B, Zona C) -->
    <?php foreach ($zonas as $nombreZona => $estaciones): ?>

    <!-- Separador de zona con título -->
    <div class="zone-divider">
        <div class="zone-title"><?= $nombreZona ?></div>
    </div>

    <!-- Grid de estaciones de esta zona -->
    <div class="row">
        <!-- Recorrer cada estación dentro de la zona -->
        <?php foreach ($estaciones as $e): ?>
        <!-- Cada estación ocupa: 6 cols en móvil, 4 en tablet, 3 en desktop, 2 en XL -->
        <div class="col s6 m4 l3 xl2">
            <!-- station-card: clase con estilos según el estado (disponible/ocupada/mantenimiento) -->
            <!-- data-status: usado por el filtro JS para mostrar/ocultar -->
            <div class="station-card <?= $e['status'] ?>" data-status="<?= $e['status'] ?>">
                <div class="station-inner">
                    <!-- Encabezado: número de estación -->
                    <div class="station-header">
                        <span class="station-badge"><?= $e['num'] ?></span>
                        <span class="station-header-label">Estación</span>
                    </div>
                    <!-- Cuerpo: icono, estado y descripción -->
                    <div class="station-body">
                        <!-- Icono dinámico según el estado -->
                        <div class="station-icon"><i class="material-icons"><?= $e['icono'] ?></i></div>
                        <!-- Label legible del estado (ej: "Disponible") -->
                        <div class="station-status"><?= $statusLabels[$e['status']] ?></div>
                        <!-- Descripción (ej: "PC Gaming" o "45 min restantes") -->
                        <div class="station-desc"><?= $e['desc'] ?></div>
                    </div>
                    <!-- Pie: precio si la estación está ocupada -->
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

<!-- Mensaje informativo -->
<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:0.5rem;">
    <i class="material-icons left" style="font-size:1rem;">info</i> Haz clic en una estación para cambiar su estado
</p>

<!-- Script inline para botones de demo -->
<script>
// ============================================
// MANEJADORES DE EVENTOS (DEMO)
// ============================================
// Estos manejadores se ejecutan cuando el DOM está listo
// (función $() de jQuery).
$(function () {
    // Botón "Nueva Estación": muestra un toast de demostración
    $('#btnNuevaEstacion').on('click', function () {
        EIS.toast('Formulario para nueva estación (demo)', 'indigo', 'add_circle');
    });

    // Botón "Historial": muestra un toast de demostración
    $('#btnHistorialCyber').on('click', function () {
        EIS.toast('Abriendo historial de sesiones (demo)', 'indigo', 'history');
    });
});
</script>
