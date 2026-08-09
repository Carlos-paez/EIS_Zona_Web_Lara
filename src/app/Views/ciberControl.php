<?php
// =============================================================================
// VISTA: CONTROL DE CYBERCAFÉ (ciberControl.php)
// =============================================================================
// Propósito: Muestra el estado de las estaciones de cybercafé con
//            indicadores de disponibles y ocupadas. Permite registrar
//            clientes e iniciar/finalizar sesiones de forma dinámica.
//            Los datos y la lógica son manejados por JS en app.cyber.js.
// =============================================================================
?>

<!-- ===== TARJETAS KPI (MÉTRICAS DEL CYBERCAFÉ) ===== -->
<!-- Fila con margen inferior -->
<div class="row" style="margin-bottom:1.5rem;">
    <!-- Columna: Estaciones disponibles -->
    <div class="col s6 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Disponibles</div>
            <div class="metric-value" style="color:var(--success);" id="countDisponibles">0</div>
        </div>
    </div>
    <!-- Columna: Estaciones ocupadas -->
    <div class="col s6 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">timelapse</i></div>
            <div class="metric-label">Ocupadas</div>
            <div class="metric-value" style="color:var(--warning);" id="countOcupadas">0</div>
        </div>
    </div>
    <!-- Columna: Estaciones en mantenimiento (no gestionado por el módulo) -->
    <div class="col s6 m6 l3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">build</i></div>
            <div class="metric-label">Mantenimiento</div>
            <div class="metric-value" style="color:var(--danger);" id="countMantenimiento">0</div>
        </div>
    </div>
    <!-- Columna: Total de estaciones -->
    <div class="col s6 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">dns</i></div>
            <div class="metric-label">Total Estaciones</div>
            <div class="metric-value" id="countTotal">0</div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE HERRAMIENTAS (FILTROS Y BOTONES DE ACCIÓN) ===== -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-content" style="padding:0.75rem 1.25rem;">
        <div class="row" style="margin-bottom:0;">
            <!-- Botones de filtro rápido por estado -->
            <div class="col s12 m7" style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;padding-top:0.25rem;padding-bottom:0.25rem;">
                <span class="hide-on-small-only" style="font-weight:600;color:var(--text-muted);font-size:0.85rem;margin-right:0.35rem;">FILTRAR:</span>
                <a class="btn-small waves-effect waves-light green filter-btn active" data-filter="all" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Todas</a>
                <a class="btn-small waves-effect waves-light green filter-btn" data-filter="disponible" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Disponibles</a>
                <a class="btn-small waves-effect waves-light orange filter-btn" data-filter="ocupada" style="border-radius:20px;padding:0 0.75rem;font-size:0.7rem;">Ocupadas</a>
            </div>
            <!-- Botón de acción: Nueva sesión -->
            <div class="col s12 m5 right-align" style="padding-top:0.25rem;padding-bottom:0.25rem;">
                <button class="btn-small waves-effect waves-light indigo" id="btnNuevaSesion" style="border-radius:20px;">
                    <i class="material-icons left" style="font-size:1rem;">add</i>Nueva Sesión
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== GRILLA DE ESTACIONES (RENDERIZADA DINÁMICAMENTE POR JS) ===== -->
<div id="cyberGrid">
    <div class="col s12 center-align" style="color:var(--text-muted);padding:2rem 0;">
        <i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">computer</i>
        Cargando estaciones...
    </div>
</div>

<!-- Mensaje informativo sobre la interacción con las estaciones -->
<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:0.5rem;">
    <i class="material-icons left" style="font-size:1rem;">info</i> Haz clic en una estación disponible para iniciar sesión, o en una ocupada para finalizarla
</p>

<!-- ===== MODAL: INICIAR SESIÓN ===== -->
<div id="cyberModal" class="modal" style="max-width:560px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">computer</i> Iniciar Sesión
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>

        <form id="cyberForm">
            <div class="row" style="margin-bottom:0;">
                <!-- Nombre completo del cliente -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" id="cyberCiudadano" name="ciudadano" maxlength="100" placeholder="Nombre y apellido">
                    <label for="cyberCiudadano">Cliente *</label>
                </div>
                <!-- Cédula de identidad -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person_pin</i>
                    <input type="text" id="cyberCedula" name="cedula" maxlength="20" placeholder="Ej: 12345678">
                    <label for="cyberCedula">Cédula *</label>
                </div>
                <!-- Dirección del cliente -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">location_on</i>
                    <input type="text" id="cyberDireccion" name="direccion" maxlength="500" placeholder="Opcional">
                    <label for="cyberDireccion">Dirección</label>
                </div>
                <!-- Teléfono del cliente -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">phone</i>
                    <input type="text" id="cyberTelefono" name="telefono" maxlength="20" placeholder="Opcional">
                    <label for="cyberTelefono">Teléfono</label>
                </div>
                <!-- Estación (select poblado dinámicamente por JS) -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">dns</i>
                    <select id="cyberActivo" name="activo_id"></select>
                    <label>Estación *</label>
                </div>
                <!-- Tarifa -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">payments</i>
                    <select id="cyberTarifa" name="tarifa_id"></select>
                    <label>Tarifa *</label>
                </div>
                <!-- Tiempo de uso -->
                <div class="input-field col s12">
                    <i class="material-icons prefix">schedule</i>
                    <input type="text" id="cyberTiempo" name="tiempo_uso" list="tiempoPresets" maxlength="50" value="01:00:00">
                    <datalist id="tiempoPresets">
                        <option value="00:30:00">
                        <option value="01:00:00">
                        <option value="01:30:00">
                        <option value="02:00:00">
                    </datalist>
                    <label for="cyberTiempo">Tiempo de uso (HH:MM:SS) *</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="submit" form="cyberForm" class="btn waves-effect waves-light indigo" id="btnIniciarSesion" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">play_circle</i> Iniciar</button>
    </div>
</div>
