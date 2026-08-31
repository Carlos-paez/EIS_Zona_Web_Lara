<?php

use App\Models\Activo;

$activoModel = new Activo();
$totalActivos = $activoModel->totalActivos();
$tiposActivo = $activoModel->listarTiposActivo();
?>

<!-- ===== TARJETAS KPI ===== -->
<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">devices_other</i></div>
            <div class="metric-label">Total Activos</div>
            <div class="metric-value" id="kpi-total"><?php echo $totalActivos; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Registrados</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">computer</i></div>
            <div class="metric-label">Estaciones Cyber</div>
            <div class="metric-value" id="kpi-ciber">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">is_ciber = 1</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">timer</i></div>
            <div class="metric-label">Ocupadas Ahora</div>
            <div class="metric-value" id="kpi-ocupados" style="color:#ef6c00;">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Sesiones abiertas</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">toggle_off</i></div>
            <div class="metric-label">Inactivos</div>
            <div class="metric-value" id="kpi-inactivos" style="color:#c62828;">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Desactivados</div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE HERRAMIENTAS ===== -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <div class="col s12 m7 l4" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchActivo" placeholder="Buscar por marca, tipo o descripción...">
                    <label for="searchActivo">Buscar activo</label>
                </div>
            </div>
            <div class="col s6 m3 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterTipo">
                        <option value="" selected>Todos los tipos</option>
                        <?php foreach ($tiposActivo as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo['nombre_tipo'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tipo['nombre_tipo'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Filtrar por tipo</label>
                </div>
            </div>
            <div class="col s6 m2 l5 right-align" style="padding:0.5rem 0 0;display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                <button class="btn waves-effect waves-light indigo btn-nuevo-activo"
                    style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Activo</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA DE ACTIVOS ===== -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">devices_other</i> Activos
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalActivos; ?> resultados</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped" id="tabla-activos" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:720px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Marca</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Descripci&oacute;n</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Tipo</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Estado</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Cargando...</td></tr>
                </tbody>
            </table>
        </div>

        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalActivos; ?> resultados</span>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVO/EDITAR ACTIVO ===== -->
<div id="modal-activo" class="modal" style="max-width:560px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">devices_other</i> <span id="modal-activo-title">Nuevo Activo</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <form id="form-activo">
            <input type="hidden" name="id" id="activo-id" value="">
            <div class="row" style="margin-bottom:0;">
                <div class="col s12 m6 input-field" style="margin-bottom:0;">
                    <i class="material-icons prefix">branding_watermark</i>
                    <input type="text" name="marca" id="activo-marca" required maxlength="100" pattern=".{2,100}" title="Entre 2 y 100 caracteres">
                    <label for="activo-marca">Marca</label>
                </div>
                <div class="col s12 m6 input-field" style="margin-bottom:0;">
                    <i class="material-icons prefix">category</i>
                    <select name="tipo_activo_id" id="activo-tipo" required>
                        <option value="" disabled selected>Selecciona un tipo...</option>
                        <?php foreach ($tiposActivo as $tipo): ?>
                            <option value="<?php echo (int)$tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nombre_tipo'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Tipo de activo</label>
                </div>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">notes</i>
                <textarea name="descripcion" id="activo-descripcion" class="materialize-textarea" required maxlength="1000"></textarea>
                <label for="activo-descripcion">Descripción</label>
            </div>
            <div style="display:flex;gap:2.5rem;margin-top:0.5rem;flex-wrap:wrap;">
                <label>
                    <input type="checkbox" name="activa" id="activo-activa" value="1" checked />
                    <span>Activo</span>
                </label>
                <label>
                    <input type="checkbox" name="is_ciber" id="activo-ciber" value="1" />
                    <span style="font-size:0.9rem;">Estaci&oacute;n de Cybercaf&eacute;</span>
                </label>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="submit" form="form-activo" class="btn waves-effect waves-light indigo" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar</button>
    </div>
</div>
