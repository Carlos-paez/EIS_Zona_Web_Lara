<?php
// ============================================================
// VISTA: Control de Cybercafé
// La grilla y toda la interactividad se renderizan con
// app.cyber.js (Public/js/app.cyber.js) vía el API AJAX
// ?pagina=ciberControl&action=...
// ============================================================

use App\Models\CiberControl;

$ciberModel = new CiberControl();

$totalEstaciones = 0;
try {
    $totalEstaciones = count($ciberModel->listarEstaciones());
} catch (\Throwable $e) {
    $totalEstaciones = 0;
}

$tiposActivo = [];
try {
    $tiposActivo = $ciberModel->listarTiposActivo();
} catch (\Throwable $e) {
    $tiposActivo = [];
}
?>

<!-- Tarjetas de métricas -->
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
    <!-- Columna: Estaciones en mantenimiento (desactivadas) -->
    <div class="col s6 m6 l3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">build</i></div>
            <div class="metric-label">Mantenimiento</div>
            <div class="metric-value" style="color:var(--danger);" id="countMantenimiento">0</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">dns</i></div>
            <div class="metric-label">Total PCs</div>
            <div class="metric-value" id="countTotal"><?= (int)$totalEstaciones ?></div>
        </div>
    </div>
</div>

<!-- Barra de filtros y acciones -->
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
            <!-- Botones de acción -->
            <div class="col s12 m5 right-align" style="padding-top:0.25rem;padding-bottom:0.25rem;">
                <button class="btn-small waves-effect waves-light indigo" id="btnNuevaPC" style="border-radius:20px;">
                    <i class="material-icons left" style="font-size:1rem;">add</i>Nueva PC
                </button>
                <button class="btn-small waves-effect waves-light indigo" id="btnNuevaSesion" style="border-radius:20px;">
                    <i class="material-icons left" style="font-size:1rem;">play_arrow</i>Nueva Sesión
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

<!-- Grid de estaciones (renderizado por app.cyber.js) -->
<div id="cyberGrid">
    <div class="row"><div class="col s12 center-align" style="color:var(--text-muted);padding:2rem 0;"><i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">hourglass_empty</i>Cargando estaciones...</div></div>
</div>

<!-- Modal: Iniciar Sesión -->
<div id="cyberModal" class="modal" style="max-width:480px;">
    <div class="modal-content">
        <h4 style="font-weight:700;margin-bottom:1.5rem;">
            <i class="material-icons left" style="color:var(--success);">play_circle</i>
            Iniciar Sesión
        </h4>
        <form id="cyberForm">
            <div class="input-field">
                <i class="material-icons prefix">person</i>
                <input type="text" id="cyberCiudadano" name="ciudadano" class="form-control" placeholder="Nombre completo del cliente" required>
                <label for="cyberCiudadano" class="active">Cliente</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">badge</i>
                <input type="text" id="cyberCedula" name="cedula" class="form-control" placeholder="Ej: V-12345678" required>
                <label for="cyberCedula" class="active">Cédula</label>
                <span class="helper-text" style="font-size:0.8rem;color:var(--text-muted);">Al salir de este campo se precargan los datos del cliente</span>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">phone</i>
                <input type="text" id="cyberTelefono" name="telefono" class="form-control" placeholder="Opcional">
                <label for="cyberTelefono" class="active">Teléfono</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">place</i>
                <input type="text" id="cyberDireccion" name="direccion" class="form-control" placeholder="Opcional">
                <label for="cyberDireccion" class="active">Dirección</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">dns</i>
                <select id="cyberActivo" required>
                    <option value="" disabled selected>Cargando estaciones...</option>
                </select>
                <label for="cyberActivo">Estación</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">attach_money</i>
                <select id="cyberTarifa" required>
                    <option value="" disabled selected>Cargando tarifas...</option>
                </select>
                <label for="cyberTarifa">Tarifa</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">timer</i>
                <input type="text" id="cyberTiempo" name="tiempo_uso" value="01:00:00" class="form-control" required>
                <label for="cyberTiempo" class="active">Tiempo (HH:MM:SS)</label>
                <span class="helper-text" style="font-size:0.8rem;color:var(--text-muted);">Ej: 01:30:00 = 1 hora 30 minutos</span>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 1.5rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="button" class="btn waves-effect waves-light green" id="btnIniciarSesion" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;">
            <i class="material-icons left" style="margin:0;">play_arrow</i> Iniciar
        </button>
    </div>
</div>

<!-- Modal: Crear/Editar PC -->
<div id="modalPCForm" class="modal" style="max-width:550px;">
    <div class="modal-content">
        <h4 style="font-weight:700;margin-bottom:1.5rem;">
            <i class="material-icons left" style="color:var(--primary);" id="modalPCTitleIcon">computer</i>
            <span id="modalPCTitle">Nueva PC</span>
        </h4>
        <form id="formPC">
            <input type="hidden" id="pcId" value="">
            <div class="input-field">
                <i class="material-icons prefix">branding_watermark</i>
                <input type="text" id="pcMarca" name="marca" class="form-control" placeholder="Ej: HP, Dell, Lenovo" required>
                <label for="pcMarca" class="active">Marca</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">description</i>
                <input type="text" id="pcDescripcion" name="descripcion" class="form-control" placeholder="Ej: Intel i5, 8GB RAM, 256GB SSD" required>
                <label for="pcDescripcion" class="active">Descripción</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">devices</i>
                <select id="pcTipo" required>
                    <option value="" disabled selected>Seleccionar tipo</option>
                    <?php foreach ($tiposActivo as $tipo): ?>
                        <option value="<?= (int)$tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre_tipo']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="pcTipo">Tipo de PC</label>
            </div>
            <div class="input-field" style="margin-top:1.5rem;">
                <div style="display:flex;gap:2rem;align-items:center;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="radio" name="pcEstado" value="1" checked>
                        <span>Activa</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="radio" name="pcEstado" value="0">
                        <span>Mantenimiento</span>
                    </label>
                </div>
                <label style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;display:block;">Estado de la PC</label>
            </div>
            <div id="pcFormError" class="card-panel red lighten-4 red-text text-darken-4" style="display:none;border-radius:8px;padding:0.75rem;margin-top:1rem;">
                <i class="material-icons left" style="font-size:1.2rem;">error</i>
                <span id="pcFormErrorMessage">Error al guardar</span>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 1.5rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="button" class="btn waves-effect waves-light indigo" id="btnGuardarPC" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;">
            <i class="material-icons left" style="margin:0;">save</i> Guardar PC
        </button>
    </div>
</div>

<!-- Modal: Confirmar Eliminación -->
<div id="modalConfirmarEliminar" class="modal" style="max-width:450px;">
    <div class="modal-content">
        <h4 style="font-weight:700;margin-bottom:1rem;color:var(--danger);">
            <i class="material-icons left" style="color:var(--danger);">warning</i>
            Confirmar Eliminación
        </h4>
        <p style="font-size:1.05rem;margin-bottom:0.5rem;">
            ¿Estás seguro de eliminar la PC <strong id="confirmarPcNombre"></strong>?
        </p>
        <p style="color:var(--text-muted);font-size:0.9rem;">
            <i class="material-icons left" style="font-size:1rem;">info</i>
            Esta acción eliminará la PC permanentemente.
            <strong>Solo se permite si no tiene sesiones registradas.</strong>
        </p>
        <input type="hidden" id="confirmarPcId" value="">
    </div>
    <div class="modal-footer" style="padding:1rem 1.5rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="button" class="btn waves-effect waves-light red" id="btnConfirmarEliminar" style="border-radius:24px;">
            <i class="material-icons left">delete_forever</i> Eliminar
        </button>
    </div>
</div>

<!-- Modal: Historial -->
<div id="modalHistorial" class="modal" style="max-width:800px;">
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
                <p style="color:var(--text-muted);margin-top:1rem;">Cargando estaciones...</p>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="padding:0.75rem 1.5rem;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cerrar</button>
    </div>
</div>

<style>
.station-actions {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
    margin: 0.25rem 0;
}
.station-actions .btn-floating.btn-small {
    width: 28px !important;
    height: 28px !important;
    line-height: 28px !important;
    min-width: unset !important;
    min-height: unset !important;
}
.station-actions .btn-floating.btn-small i {
    font-size: 0.9rem !important;
    line-height: 28px !important;
}
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
</style>
