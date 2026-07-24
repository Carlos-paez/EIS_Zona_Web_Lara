<?php

use App\Models\Cliente;

$clienteModel = new Cliente();
$totalClientes = $clienteModel->totalClientes();
?>

<!-- ===== TARJETAS KPI ===== -->
<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">badge</i></div>
            <div class="metric-label">Total Clientes</div>
            <div class="metric-value" id="kpi-total"><?php echo $totalClientes; ?></div>
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
                    <input type="text" id="searchCliente" placeholder="Buscar por nombre, cédula o teléfono...">
                    <label for="searchCliente">Buscar cliente</label>
                </div>
            </div>
            <div class="col s12 m4 l7 right-align" style="padding:0.5rem 0 0;display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                <button class="btn waves-effect waves-light indigo btn-nuevo-cliente"
                    style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Cliente</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA DE CLIENTES ===== -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">badge</i> Clientes
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalClientes; ?> resultados</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped" id="tabla-clientes" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:600px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">C&eacute;dula</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Cliente</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Direcci&oacute;n</th>
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
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalClientes; ?> resultados</span>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVO/EDITAR CLIENTE ===== -->
<div id="modal-cliente" class="modal" style="max-width:520px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">badge</i> <span id="modal-cliente-title">Nuevo Cliente</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <form id="form-cliente">
            <input type="hidden" name="id" id="cliente-id" value="">
            <div class="input-field">
                <i class="material-icons prefix">badge</i>
                <input type="text" name="cedula" id="cliente-cedula" required>
                <label for="cliente-cedula">Cédula</label>
            </div>
            <div class="row" style="margin-bottom:0;">
                <div class="col s6 input-field" style="margin-bottom:0;">
                    <i class="material-icons prefix">person</i>
                    <input type="text" name="nombre" id="cliente-nombre" required>
                    <label for="cliente-nombre">Nombre</label>
                </div>
                <div class="col s6 input-field" style="margin-bottom:0;">
                    <input type="text" name="apellido" id="cliente-apellido" required>
                    <label for="cliente-apellido">Apellido</label>
                </div>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">location_on</i>
                <input type="text" name="direccion" id="cliente-direccion" required>
                <label for="cliente-direccion">Dirección</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">phone</i>
                <input type="text" name="telefono" id="cliente-telefono" required>
                <label for="cliente-telefono">Teléfono</label>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="submit" form="form-cliente" class="btn waves-effect waves-light indigo" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar</button>
    </div>
</div>
