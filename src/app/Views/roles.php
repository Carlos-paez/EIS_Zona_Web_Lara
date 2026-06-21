<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">admin_panel_settings</i></div>
            <div class="metric-label">Total Roles</div>
            <div class="metric-value" id="kpi-roles">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Roles registrados</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">vpn_key</i></div>
            <div class="metric-label">Permisos</div>
            <div class="metric-value" id="kpi-permisos" style="color:var(--success);">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Permisos del sistema</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">people</i></div>
            <div class="metric-label">Usuarios</div>
            <div class="metric-value" id="kpi-usuarios" style="color:var(--warning);">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Usuarios en el sistema</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">supervisor_account</i></div>
            <div class="metric-label">Con Rol</div>
            <div class="metric-value" id="kpi-con-rol" style="color:var(--info);">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Usuarios con rol asignado</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchRol" placeholder="Buscar rol por nombre...">
                    <label for="searchRol">Buscar rol</label>
                </div>
            </div>
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterRolEstado">
                        <option value="" selected>Todos</option>
                        <option value="con-usuarios">Con usuarios</option>
                        <option value="sin-usuarios">Sin usuarios</option>
                    </select>
                    <label>Filtro</label>
                </div>
            </div>
            <div class="col s12 m4 l4 right-align" style="padding:0.5rem 0 0;display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                <button class="btn waves-effect waves-light green" id="btnAsignarRolHeader" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">supervisor_account</i>
                    <span class="hide-on-small-only">Asignar Rol</span>
                    <span class="hide-on-med-and-up">Asignar</span>
                </button>
                <button class="btn waves-effect waves-light indigo" id="btnNuevoRol" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Rol</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content" style="padding:0;">
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">admin_panel_settings</i> Roles del Sistema
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;">0 roles</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped rol-table" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:600px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Rol</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Descripci&oacute;n</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Usuarios</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Creado</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acci&oacute;n</th>
                    </tr>
                </thead>
                <tbody id="tabla-roles-body">
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">admin_panel_settings</i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalRol" class="modal" style="max-width:500px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);" id="modal-rol-icon">add</i>
                <span id="modal-rol-title">Nuevo Rol</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <form id="formRol">
            <input type="hidden" id="rol-id" value="">
            <div class="row" style="margin-bottom:0;">
                <div class="col s12 input-field">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" id="rol-nombre" required>
                    <label for="rol-nombre">Nombre del rol</label>
                </div>
                <div class="col s12 input-field">
                    <i class="material-icons prefix">description</i>
                    <textarea id="rol-descripcion" class="materialize-textarea"></textarea>
                    <label for="rol-descripcion">Descripci&oacute;n</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarRol" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar</button>
    </div>
</div>

<div id="modalPermisos" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">security</i> Permisos: <span id="permisoRolNombre" style="color:var(--text);"></span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <p style="color:var(--text-muted);margin-bottom:1.25rem;font-size:0.9rem;">Selecciona los m&oacute;dulos a los que este rol tendr&aacute; acceso:</p>
        <input type="hidden" id="permisos-rol-id" value="">
        <div id="permisos-container" style="display:flex;flex-direction:column;gap:0.5rem;">
        </div>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarPermisos" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar Permisos</button>
    </div>
</div>

<div id="modalAsignar" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">supervisor_account</i> Asignar Rol
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <div class="row" style="margin-bottom:0;">
            <div class="col s12 input-field">
                <i class="material-icons prefix">person</i>
                <select id="asignar-usuario">
                    <option value="" disabled selected>Seleccionar usuario</option>
                </select>
                <label>Usuario</label>
            </div>
            <div class="col s12 input-field">
                <i class="material-icons prefix">admin_panel_settings</i>
                <select id="asignar-rol">
                    <option value="" disabled selected>Seleccionar rol</option>
                </select>
                <label>Rol</label>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnAsignarRol" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">supervisor_account</i> Asignar Rol</button>
    </div>
</div>

<style>
.rol-table tbody tr:hover { background: var(--surface-hover); }
.rol-table td { vertical-align: middle; }
@media only screen and (max-width: 600px) {
    .rol-table td, .rol-table th { padding: 0.55rem 0.5rem !important; }
}
</style>
