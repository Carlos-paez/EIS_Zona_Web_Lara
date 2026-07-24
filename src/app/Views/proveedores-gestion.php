<?php
// =============================================================================
// VISTA: GESTIÓN DE PROVEEDORES
// =============================================================================
// Muestra tarjeta KPI, búsqueda, tabla de proveedores y modales para
// crear/editar proveedores. Módulo dedicado a la gestión de proveedores.
// =============================================================================

use App\Models\ProveedorGestion;

$gpModel = new ProveedorGestion();
$totalProv = $gpModel->totalProveedores();
?>

<!-- ===== TARJETAS KPI ===== -->
<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">people</i></div>
            <div class="metric-label">Total Proveedores</div>
            <div class="metric-value" id="kpi-total"><?php echo $totalProv; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Registrados</div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE HERRAMIENTAS ===== -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <div class="col s12 m8 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchProveedorGestion" placeholder="Buscar por nombre, RIF o email...">
                    <label for="searchProveedorGestion">Buscar proveedor</label>
                </div>
            </div>
            <div class="col s12 m4 l7 right-align" style="padding:0.5rem 0 0;display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                <button class="btn waves-effect waves-light indigo btn-nuevo-proveedor"
                    style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Proveedor</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA DE PROVEEDORES ===== -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">people</i> Proveedores
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalProv; ?> resultados</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped" id="tabla-proveedores" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:500px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Proveedor</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">RIF</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Email</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Tel&eacute;fono</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Cargando...</td></tr>
                </tbody>
            </table>
        </div>

        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalProv; ?> resultados</span>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVO/EDITAR PROVEEDOR ===== -->
<div id="modal-proveedor" class="modal" style="max-width:500px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">store</i> <span id="modal-proveedor-title">Nuevo Proveedor</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <form id="form-proveedor">
            <input type="hidden" name="id" id="proveedor-id" value="">
            <div class="input-field">
                <i class="material-icons prefix">badge</i>
                <input type="text" name="rif" id="proveedor-rif" required maxlength="20" pattern=".{5,20}" title="Entre 5 y 20 caracteres">
                <label for="proveedor-rif">RIF</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">store</i>
                <input type="text" name="nombre" id="proveedor-nombre" required maxlength="100" pattern=".{2,100}" title="Entre 2 y 100 caracteres">
                <label for="proveedor-nombre">Nombre</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">email</i>
                <input type="email" name="email" id="proveedor-email" maxlength="100">
                <label for="proveedor-email">Email</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">phone</i>
                <input type="text" name="telefono" id="proveedor-telefono" maxlength="20">
                <label for="proveedor-telefono">Teléfono</label>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="submit" form="form-proveedor" class="btn waves-effect waves-light indigo" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar</button>
    </div>
</div>
