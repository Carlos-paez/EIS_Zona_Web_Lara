<?php
// ============================================================
// VISTA: CONTROL DE CYBERCAFÉ
// Los datos vienen del controlador CiberController::index()
// Variables disponibles:
// - $estaciones: array con todas las estaciones
// - $estadisticas: array con estadísticas
// - $countDisponibles, $countOcupadas, $countMantenimiento
// - $totalEstaciones, $usarZonas
// - $estacionesGaming, $estacionesOficina, $estacionesPremium
// - Funciones auxiliares: $getEstadoClase, $getEstadoTexto, $getEstadoIcono
// ============================================================
?>

<!-- Tarjetas de métricas -->
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

<!-- Barra de filtros y acciones -->
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
                <button class="btn-small waves-effect waves-light green darken-1 hide-on-small-only" id="btnRefrescar" style="border-radius:20px;margin-left:0.25rem;">
                    <i class="material-icons left" style="font-size:1rem;">refresh</i>Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Grid de estaciones -->
<div id="cyberGrid">
    <?php if ($usarZonas && count($estacionesGaming) > 0): ?>
        <!-- Zona Gaming -->
        <div class="zone-divider">
            <div class="zone-title">🎮 Zona Gaming</div>
        </div>
        <div class="row" id="zona-gaming">
            <?php foreach ($estacionesGaming as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= $getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>"
                     data-tarifa="<?= htmlspecialchars($e['tarifa_nombre'] ?? '') ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= $getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= $getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos'] !== null): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                    <?php if ($e['costo_estimado']): ?>
                                        <br><small class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Estándar') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light red btn-finalizar-sesion" 
                                    data-sesion-id="<?= $e['sesion_id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">stop</i>Finalizar
                            </button>
                        </div>
                        <?php elseif ($e['estado'] === 'Disponible'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light green btn-iniciar-sesion" 
                                    data-estacion-id="<?= $e['id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">play_arrow</i>Iniciar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($usarZonas && count($estacionesOficina) > 0): ?>
        <!-- Zona Oficina -->
        <div class="zone-divider">
            <div class="zone-title">📋 Zona Oficina</div>
        </div>
        <div class="row" id="zona-oficina">
            <?php foreach ($estacionesOficina as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= $getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>"
                     data-tarifa="<?= htmlspecialchars($e['tarifa_nombre'] ?? '') ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= $getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= $getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos'] !== null): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                    <?php if ($e['costo_estimado']): ?>
                                        <br><small class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Estándar') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light red btn-finalizar-sesion" 
                                    data-sesion-id="<?= $e['sesion_id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">stop</i>Finalizar
                            </button>
                        </div>
                        <?php elseif ($e['estado'] === 'Disponible'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light green btn-iniciar-sesion" 
                                    data-estacion-id="<?= $e['id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">play_arrow</i>Iniciar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($usarZonas && count($estacionesPremium) > 0): ?>
        <!-- Zona Premium -->
        <div class="zone-divider">
            <div class="zone-title"> Zona Premium</div>
        </div>
        <div class="row" id="zona-premium">
            <?php foreach ($estacionesPremium as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= $getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>"
                     data-tarifa="<?= htmlspecialchars($e['tarifa_nombre'] ?? '') ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= $getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= $getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos'] !== null): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                    <?php if ($e['costo_estimado']): ?>
                                        <br><small class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Premium') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light red btn-finalizar-sesion" 
                                    data-sesion-id="<?= $e['sesion_id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">stop</i>Finalizar
                            </button>
                        </div>
                        <?php elseif ($e['estado'] === 'Disponible'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light green btn-iniciar-sesion" 
                                    data-estacion-id="<?= $e['id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">play_arrow</i>Iniciar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$usarZonas): ?>
        <!-- Todas las estaciones juntas -->
        <div class="row">
            <?php foreach ($estaciones as $e): ?>
            <div class="col s6 m4 l3 xl2">
                <div class="station-card <?= $getEstadoClase($e['estado']) ?>" 
                     data-estacion-id="<?= $e['id'] ?>"
                     data-sesion-id="<?= $e['sesion_id'] ?? '' ?>"
                     data-status="<?= strtolower($e['estado']) ?>"
                     data-nombre="<?= htmlspecialchars($e['nombre']) ?>"
                     data-tarifa="<?= htmlspecialchars($e['tarifa_nombre'] ?? '') ?>">
                    <div class="station-inner">
                        <div class="station-header">
                            <span class="station-badge"><?= htmlspecialchars($e['nombre']) ?></span>
                            <span class="station-header-label">Estación</span>
                        </div>
                        <div class="station-body">
                            <div class="station-icon">
                                <i class="material-icons"><?= $getEstadoIcono($e['estado']) ?></i>
                            </div>
                            <div class="station-status"><?= $getEstadoTexto($e['estado']) ?></div>
                            <div class="station-desc">
                                <?php if ($e['estado'] === 'Ocupada' && $e['minutos_transcurridos'] !== null): ?>
                                    <?= htmlspecialchars($e['cliente_nombre'] ?? 'Cliente') ?><br>
                                    <small><?= floor($e['minutos_transcurridos'] / 60) ?>h <?= $e['minutos_transcurridos'] % 60 ?>min</small>
                                    <?php if ($e['costo_estimado']): ?>
                                        <br><small class="station-price">$<?= number_format($e['costo_estimado'], 2) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['especificaciones'] ?? 'PC Estándar') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($e['estado'] === 'Ocupada'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light red btn-finalizar-sesion" 
                                    data-sesion-id="<?= $e['sesion_id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">stop</i>Finalizar
                            </button>
                        </div>
                        <?php elseif ($e['estado'] === 'Disponible'): ?>
                        <div class="station-footer">
                            <button class="btn-small waves-effect waves-light green btn-iniciar-sesion" 
                                    data-estacion-id="<?= $e['id'] ?>"
                                    data-estacion="<?= htmlspecialchars($e['nombre']) ?>"
                                    style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">
                                <i class="material-icons left" style="font-size:0.8rem;">play_arrow</i>Iniciar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para iniciar sesión -->
<div id="modalIniciarSesion" class="modal" style="max-width:450px;">
    <div class="modal-content">
        <h4 style="font-weight:700;margin-bottom:1.5rem;">
            <i class="material-icons left" style="color:var(--success);">play_circle</i>
            Iniciar Sesión
        </h4>
        <form id="formIniciarSesion">
            <input type="hidden" id="modalEstacionId" value="">
            <div class="input-field">
                <i class="material-icons prefix">dns</i>
                <input type="text" id="modalEstacionNombre" value="" readonly disabled style="color:var(--text);">
                <label for="modalEstacionNombre" class="active">Estación</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">person</i>
                <input type="text" id="modalClienteNombre" required autofocus>
                <label for="modalClienteNombre">Nombre del Cliente</label>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 1.5rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="button" class="btn waves-effect waves-light green" id="btnConfirmarInicio" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;">
            <i class="material-icons left" style="margin:0;">play_arrow</i> Iniciar
        </button>
    </div>
</div>

<!-- Modal para mostrar historial -->
<div id="modalHistorial" class="modal" style="max-width:700px;">
    <div class="modal-content">
        <h4 style="font-weight:700;margin-bottom:1.5rem;">
            <i class="material-icons left" style="color:var(--primary);">history</i>
            Historial de Sesiones
        </h4>
        <div id="historialContenido">
            <div class="center-align" style="padding:2rem 0;">
                <div class="preloader-wrapper small active">
                    <div class="spinner-layer spinner-green-only">
                        <div class="circle-clipper left"><div class="circle"></div></div>
                        <div class="gap-patch"><div class="circle"></div></div>
                        <div class="circle-clipper right"><div class="circle"></div></div>
                    </div>
                </div>
                <p style="color:var(--text-muted);margin-top:1rem;">Cargando historial...</p>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="padding:0.75rem 1.5rem;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cerrar</button>
    </div>
</div>

<!-- Scripts específicos del módulo -->
<script>
$(function() {
    // ============================================================
    // INICIAR SESIÓN
    // ============================================================
    $(document).on('click', '.btn-iniciar-sesion', function(e) {
        e.stopPropagation();
        var estacionId = $(this).data('estacion-id');
        var estacionNombre = $(this).data('estacion');
        
        $('#modalEstacionId').val(estacionId);
        $('#modalEstacionNombre').val(estacionNombre);
        $('#modalClienteNombre').val('');
        
        var instance = M.Modal.getInstance($('#modalIniciarSesion'));
        if (!instance) {
            $('#modalIniciarSesion').modal();
            instance = M.Modal.getInstance($('#modalIniciarSesion'));
        }
        instance.open();
        setTimeout(function() {
            $('#modalClienteNombre').focus();
        }, 300);
    });

    $('#btnConfirmarInicio').on('click', function() {
        var estacionId = $('#modalEstacionId').val();
        var clienteNombre = $('#modalClienteNombre').val().trim();
        
        if (!clienteNombre) {
            EIS.toast('Ingresa el nombre del cliente', 'red', 'error');
            $('#modalClienteNombre').focus();
            return;
        }
        
        // Deshabilitar botón para evitar múltiples envíos
        $(this).prop('disabled', true).html('<i class="material-icons left" style="margin:0;">hourglass_top</i> Procesando...');
        
        $.ajax({
            url: '?pagina=ciberControl&accion=iniciar',
            method: 'POST',
            data: {
                estacion_id: estacionId,
                cliente_nombre: clienteNombre
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    EIS.toast('Sesión iniciada en ' + $('#modalEstacionNombre').val(), 'green', 'play_circle');
                    $('#modalIniciarSesion').modal('close');
                    actualizarCyberUI(response.data.estacion);
                    actualizarContadores();
                } else {
                    EIS.toast(response.message || 'Error al iniciar sesión', 'red', 'error');
                }
            },
            error: function() {
                EIS.toast('Error de conexión con el servidor', 'red', 'error');
            },
            complete: function() {
                $('#btnConfirmarInicio').prop('disabled', false).html('<i class="material-icons left" style="margin:0;">play_arrow</i> Iniciar');
            }
        });
    });

    // ============================================================
    // FINALIZAR SESIÓN
    // ============================================================
    $(document).on('click', '.btn-finalizar-sesion', function(e) {
        e.stopPropagation();
        var sesionId = $(this).data('sesion-id');
        var estacionNombre = $(this).data('estacion');
        
        if (!sesionId) {
            EIS.toast('Error: ID de sesión no encontrado', 'red', 'error');
            return;
        }
        
        if (!confirm('¿Finalizar sesión en ' + estacionNombre + '?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="material-icons left" style="font-size:0.8rem;">hourglass_top</i>');
        
        $.ajax({
            url: '?pagina=ciberControl&accion=finalizar',
            method: 'POST',
            data: {
                sesion_id: sesionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var mensaje = 'costo final de la estación: ' + estacionNombre;
                    if (data.costo_total) {
                       mensaje += ' - Total: $' + parseFloat(data.costo_total).toFixed(2);
                    }
                    EIS.toast(mensaje, 'green', 'stop_circle');
                    actualizarCyberUI(data.estacion);
                    actualizarContadores();
                } else {
                    EIS.toast(response.message || 'Error al finalizar sesión', 'red', 'error');
                }
            },
            error: function() {
                EIS.toast('Error de conexión con el servidor', 'red', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="material-icons left" style="font-size:0.8rem;">stop</i>Finalizar');
            }
        });
    });

    // ============================================================
    // FUNCIÓN: Actualizar UI de una estación
    // ============================================================
    function actualizarCyberUI(estacion) {
        if (!estacion) return;

        var $card = $('.station-card[data-estacion-id="' + estacion.id + '"]');
        if (!$card.length) return;

        var estado = estacion.estado;
        var estadoLower = estado.toLowerCase();

        $card.removeClass('disponible ocupada mantenimiento').addClass(estadoLower);
        $card.data('status', estadoLower);
        $card.attr('data-sesion-id', estacion.sesion_id || '');

        var iconos = {
            'Disponible': 'check_circle',
            'Ocupada': 'timelapse',
            'Mantenimiento': 'build'
        };
        $card.find('.station-icon .material-icons').text(iconos[estado] || 'help');
        $card.find('.station-status').text(estado);

        var $desc = $card.find('.station-desc');
        if (estado === 'Ocupada') {
            var minutos = estacion.minutos_transcurridos || 0;
            var horas = Math.floor(minutos / 60);
            var mins = minutos % 60;
            var html = (estacion.cliente_nombre || 'Cliente') + '<br><small>' + horas + 'h ' + mins + 'min</small>';
            if (estacion.costo_estimado) {
                html += '<br><small class="station-price">$' + parseFloat(estacion.costo_estimado).toFixed(2) + '</small>';
            }
            $desc.html(html);
        } else if (estado === 'Disponible') {
            $desc.html(estacion.especificaciones || 'PC Estándar');
        } else {
            $desc.html(estacion.especificaciones || 'En mantenimiento');
        }

        var $footer = $card.find('.station-footer');
        if (!$footer.length) {
            $footer = $('<div class="station-footer"></div>');
            $card.find('.station-inner').append($footer);
        }

        var nombreSeguro = $('<div>').text(estacion.nombre || '').html();
        var footerHtml = '';

        if (estado === 'Ocupada') {
            footerHtml = '<button class="btn-small waves-effect waves-light red btn-finalizar-sesion" '
                + 'data-sesion-id="' + (estacion.sesion_id || '') + '" '
                + 'data-estacion="' + nombreSeguro + '" '
                + 'style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">'
                + '<i class="material-icons left" style="font-size:0.8rem;">stop</i>Finalizar'
                + '</button>';
        } else if (estado === 'Disponible') {
            footerHtml = '<button class="btn-small waves-effect waves-light green btn-iniciar-sesion" '
                + 'data-estacion-id="' + estacion.id + '" '
                + 'data-estacion="' + nombreSeguro + '" '
                + 'style="border-radius:16px;font-size:0.65rem;padding:0 0.5rem;width:100%;">'
                + '<i class="material-icons left" style="font-size:0.8rem;">play_arrow</i>Iniciar'
                + '</button>';
        }

        $footer.html(footerHtml);
        $('.tooltipped').tooltip();
    }

    // ============================================================
    // FUNCIÓN: Actualizar contadores
    // ============================================================
    function actualizarContadores() {
        var disponibles = $('.station-card.disponible').length;
        var ocupadas = $('.station-card.ocupada').length;
        var mantenimiento = $('.station-card.mantenimiento').length;
        var total = $('.station-card').length;
        
        $('#countDisponibles').text(disponibles);
        $('#countOcupadas').text(ocupadas);
        $('#countMantenimiento').text(mantenimiento);
        $('#countTotal').text(total);
    }

    // ============================================================
    // FILTROS
    // ============================================================
    $(document).on('click', '.filter-btn', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');
        
        $('.station-card').each(function() {
            var $col = $(this).closest('.col');
            if (filter === 'all') {
                $col.slideDown(200);
            } else {
                var match = $(this).data('status') === filter;
                if (match) {
                    $col.slideDown(200);
                } else {
                    $col.slideUp(200);
                }
            }
        });
    });

    // ============================================================
    // HISTORIAL
    // ============================================================
  // ============================================================
// HISTORIAL DE SESIONES - VERSIÓN CORREGIDA
// ============================================================

// Variable para almacenar el ID de la estación seleccionada
var estacionSeleccionadaId = null;

// Abrir modal de historial
$(document).on('click', '#btnHistorialCyber, #btnHistorialCyberMobile', function() {
    var instance = M.Modal.getInstance($('#modalHistorial'));
    if (!instance) {
        $('#modalHistorial').modal({
            onCloseStart: function() {
                // Limpiar al cerrar
                $('#historialResultados').html('');
                estacionSeleccionadaId = null;
            }
        });
        instance = M.Modal.getInstance($('#modalHistorial'));
    }
    instance.open();
    
    // Mostrar selector de estaciones
    mostrarSelectorEstaciones();
});

// Función para mostrar el selector de estaciones
function mostrarSelectorEstaciones() {
    var estaciones = $('.station-card');
    var totalEstaciones = estaciones.length;
    
    if (totalEstaciones === 0) {
        $('#historialContenido').html(`
            <div class="center-align" style="padding:2rem 0;">
                <i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">computer</i>
                <p style="color:var(--text-muted);">No hay estaciones registradas</p>
            </div>
        `);
        return;
    }
    
    var html = `
        <div style="margin-bottom:1.5rem;">
            <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:0.75rem;">
                <i class="material-icons left" style="font-size:1.1rem;">info</i>
                Selecciona una estación para ver su historial de sesiones
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;">
    `;
    
    // Generar botones para cada estación
    estaciones.each(function() {
        var id = $(this).data('estacion-id');
        var nombre = $(this).data('nombre') || 'PC-' + id;
        var estado = $(this).data('status') || 'disponible';
        var estadoClass = estado === 'disponible' ? 'green' : 
                          estado === 'ocupada' ? 'orange' : 'red';
        
        html += `
            <button class="btn-small waves-effect waves-light ${estadoClass} btn-ver-historial" 
                    data-estacion-id="${id}" 
                    style="border-radius:16px;min-width:60px;text-transform:capitalize;display:inline-flex;align-items:center;gap:0.25rem;">
                <span style="width:8px;height:8px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                ${nombre}
            </button>
        `;
    });
    
    html += `
            </div>
        </div>
        <div id="historialResultados" style="margin-top:1.5rem;"></div>
    `;
    
    $('#historialContenido').html(html);
    
    // Si hay estaciones, seleccionar la primera automáticamente
    if (totalEstaciones > 0) {
        var primerId = estaciones.first().data('estacion-id');
        setTimeout(function() {
            $('.btn-ver-historial[data-estacion-id="' + primerId + '"]').click();
        }, 300);
    }
}

// ============================================================
// CARGAR HISTORIAL DE UNA ESTACIÓN
// ============================================================

$(document).on('click', '.btn-ver-historial', function() {
    var estacionId = $(this).data('estacion-id');
    var estacionNombre = $(this).text().trim();
    
    // Resaltar botón seleccionado
    $('.btn-ver-historial').removeClass('active indigo darken-2');
    $(this).addClass('active indigo darken-2');
    
    estacionSeleccionadaId = estacionId;
    cargarHistorialEstacion(estacionId, estacionNombre);
});

function cargarHistorialEstacion(estacionId, estacionNombre) {
    var $resultados = $('#historialResultados');
    
    // Mostrar loader
    $resultados.html(`
        <div class="center-align" style="padding:2rem 0;">
            <div class="preloader-wrapper small active">
                <div class="spinner-layer spinner-green-only">
                    <div class="circle-clipper left"><div class="circle"></div></div>
                    <div class="gap-patch"><div class="circle"></div></div>
                    <div class="circle-clipper right"><div class="circle"></div></div>
                </div>
            </div>
            <p style="color:var(--text-muted);margin-top:1rem;">Cargando historial de ${estacionNombre}...</p>
        </div>
    `);
    
    // Hacer petición AJAX
    $.ajax({
        url: '?pagina=ciberControl&accion=historial',
        method: 'GET',
        data: { 
            estacion_id: estacionId, 
            limit: 20 
        },
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            if (response.success) {
                if (response.data && response.data.length > 0) {
                    mostrarTablaHistorial(response.data, estacionNombre);
                } else {
                    mostrarMensajeSinHistorial(estacionNombre);
                }
            } else {
                mostrarError('Error al cargar el historial: ' + (response.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', status, error);
            mostrarError('Error de conexión al servidor. Por favor, intenta nuevamente.');
        }
    });
}

// ============================================================
// FUNCIONES PARA MOSTRAR EL HISTORIAL
// ============================================================

function mostrarTablaHistorial(datos, estacionNombre) {
    var $resultados = $('#historialResultados');
    
    // Construir tabla
    var html = `
        <div style="overflow-x:auto;border-radius:8px;border:1px solid var(--border-light);">
            <table class="striped responsive-table" style="margin-bottom:0;font-size:0.9rem;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.6rem 0.8rem;">#</th>
                        <th style="padding:0.6rem 0.8rem;">Cliente</th>
                        <th style="padding:0.6rem 0.8rem;">Inicio</th>
                        <th style="padding:0.6rem 0.8rem;">Fin</th>
                        <th style="padding:0.6rem 0.8rem;">Duración</th>
                        <th style="padding:0.6rem 0.8rem;text-align:right;">Costo</th>
                        <th style="padding:0.6rem 0.8rem;">Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    datos.forEach(function(s, i) {
        // Formatear duración
        var duracion = '-';
        if (s.duracion_minutos !== null && s.duracion_minutos !== undefined) {
            var horas = Math.floor(s.duracion_minutos / 60);
            var mins = s.duracion_minutos % 60;
            duracion = horas + 'h ' + mins + 'min';
        }
        
        // Formatear costo
        var costo = s.costo_total !== null && s.costo_total !== undefined 
            ? '$' + parseFloat(s.costo_total).toFixed(2) 
            : '-';
        
        // Estado con color
        var estadoBadge = '';
        if (s.estado === 'cerrada') {
            estadoBadge = '<span class="new badge green" style="background:#43a047;">Cerrada</span>';
        } else if (s.estado === 'activa') {
            estadoBadge = '<span class="new badge orange" style="background:#fb8c00;">Activa</span>';
        } else {
            estadoBadge = '<span class="new badge red" style="background:#e53935;">' + s.estado + '</span>';
        }
        
        // Formatear fechas
        var horaInicio = s.hora_inicio ? s.hora_inicio.replace('T', ' ').slice(0, 16) : '-';
        var horaFin = s.hora_fin ? s.hora_fin.replace('T', ' ').slice(0, 16) : '-';
        
        html += `
            <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:0.5rem 0.8rem;font-weight:600;color:var(--text-muted);">${i + 1}</td>
                <td style="padding:0.5rem 0.8rem;font-weight:500;">${s.cliente_nombre || 'Anónimo'}</td>
                <td style="padding:0.5rem 0.8rem;font-size:0.85rem;color:var(--text-muted);">${horaInicio}</td>
                <td style="padding:0.5rem 0.8rem;font-size:0.85rem;color:var(--text-muted);">${horaFin}</td>
                <td style="padding:0.5rem 0.8rem;font-weight:500;">${duracion}</td>
                <td style="padding:0.5rem 0.8rem;text-align:right;font-weight:700;color:var(--primary);">${costo}</td>
                <td style="padding:0.5rem 0.8rem;">${estadoBadge}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        <div style="margin-top:0.75rem;text-align:right;color:var(--text-muted);font-size:0.8rem;">
            <i class="material-icons left" style="font-size:0.9rem;">info</i>
            Mostrando ${datos.length} sesiones de ${estacionNombre}
        </div>
    `;
    
    $resultados.html(html);
}

function mostrarMensajeSinHistorial(estacionNombre) {
    $('#historialResultados').html(`
        <div class="center-align" style="padding:2rem 0;">
            <i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.3;">hourglass_empty</i>
            <p style="color:var(--text-muted);font-size:1rem;">
                <strong>${estacionNombre}</strong> no tiene sesiones registradas
            </p>
            <p style="color:var(--text-muted);font-size:0.85rem;">Las sesiones aparecerán aquí cuando se finalicen</p>
        </div>
    `);
}

function mostrarError(mensaje) {
    $('#historialResultados').html(`
        <div class="card-panel red lighten-4 red-text text-darken-4" style="border-radius:8px;padding:1rem;">
            <i class="material-icons left" style="font-size:1.3rem;">error</i>
            ${mensaje}
        </div>
    `);
}

// ============================================================
// ACTUALIZAR CONTADORES DESPUÉS DE FINALIZAR SESIÓN
// ============================================================

// Si el historial está abierto, actualizarlo automáticamente
// cuando se finaliza una sesión
$(document).on('click', '.btn-finalizar-sesion', function() {
    // El código existente ya maneja la finalización
    // Después de finalizar, si el historial está abierto, refrescar
    var modalAbierto = $('#modalHistorial').hasClass('open');
    if (modalAbierto && estacionSeleccionadaId) {
        // Recargar el historial después de 1 segundo
        setTimeout(function() {
            var estacionNombre = $('.btn-ver-historial.active').text().trim();
            if (estacionNombre) {
                cargarHistorialEstacion(estacionSeleccionadaId, estacionNombre);
            }
        }, 1500);
    }
});
    // ============================================================
    // ACTUALIZAR (Refrescar)
    // ============================================================
    $('#btnRefrescar').on('click', function() {
        $(this).prop('disabled', true).html('<i class="material-icons left" style="font-size:1rem;">hourglass_top</i>');
        location.reload();
    });

    // ============================================================
    // NUEVA ESTACIÓN
    // ============================================================
    $('#btnNuevaEstacion').on('click', function() {
        EIS.toast('Formulario para nueva estación (próximamente)', 'indigo', 'add_circle');
    });

    // ============================================================
    // TOOLTIPS
    // ============================================================
    $('.tooltipped').tooltip();
});
</script>

<style>
/* Estilos específicos para el módulo Cyber */
.station-card .station-footer .btn-small {
    line-height: 28px;
    height: 28px;
    font-size: 0.65rem;
}
.station-card .station-footer .btn-small i {
    font-size: 0.8rem;
    line-height: 28px;
}
.station-price {
    color: var(--warning);
    font-weight: 700;
}
#modalIniciarSesion .modal-content {
    padding: 2rem;
}
#modalHistorial .modal-content {
    padding: 1.5rem 2rem;
}
@media only screen and (max-width: 600px) {
    .station-card .station-footer .btn-small {
        font-size: 0.55rem;
        padding: 0 0.35rem;
        line-height: 24px;
        height: 24px;
    }
    .station-card .station-footer .btn-small i {
        font-size: 0.65rem;
        line-height: 24px;
    }
    #modalIniciarSesion .modal-content,
    #modalHistorial .modal-content {
        padding: 1.25rem;
    }
}
</style>