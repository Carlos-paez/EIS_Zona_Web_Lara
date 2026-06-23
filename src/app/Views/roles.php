<!-- ===== TARJETAS KPI (MÉTRICAS DE ROLES Y PERMISOS) ===== -->
<div class="row" style="margin-bottom:1.25rem;">
    <!-- Tarjeta: Total de roles registrados -->
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">admin_panel_settings</i></div>
            <div class="metric-label">Total Roles</div>
            <!-- Valor cargado dinámicamente por JS -->
            <div class="metric-value" id="kpi-roles">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Roles registrados</div>
        </div>
    </div>
    <!-- Tarjeta: Total de permisos del sistema -->
    <div class="col s12 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">vpn_key</i></div>
            <div class="metric-label">Permisos</div>
            <div class="metric-value" id="kpi-permisos" style="color:var(--success);">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Permisos del sistema</div>
        </div>
    </div>
    <!-- Tarjeta: Total de usuarios en el sistema -->
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">people</i></div>
            <div class="metric-label">Usuarios</div>
            <div class="metric-value" id="kpi-usuarios" style="color:var(--warning);">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Usuarios en el sistema</div>
        </div>
    </div>
    <!-- Tarjeta: Usuarios que tienen un rol asignado -->
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">supervisor_account</i></div>
            <div class="metric-label">Con Rol</div>
            <div class="metric-value" id="kpi-con-rol" style="color:var(--info);">0</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Usuarios con rol asignado</div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE HERRAMIENTAS (BÚSQUEDA, FILTRO, ACCIONES) ===== -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <!-- Campo de búsqueda de roles -->
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchRol" placeholder="Buscar rol por nombre...">
                    <label for="searchRol">Buscar rol</label>
                </div>
            </div>
            <!-- Selector de filtro: roles con/sin usuarios -->
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterRolEstado">
                        <!-- Opción por defecto: todos -->
                        <option value="" selected>Todos</option>
                        <option value="con-usuarios">Con usuarios</option>
                        <option value="sin-usuarios">Sin usuarios</option>
                    </select>
                    <label>Filtro</label>
                </div>
            </div>
            <!-- Botones de acción: Asignar Rol y Nuevo Rol -->
            <div class="col s12 m4 l4 right-align" style="padding:0.5rem 0 0;display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                <!-- Botón para asignar un rol a un usuario -->
                <button class="btn waves-effect waves-light green" id="btnAsignarRolHeader" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">supervisor_account</i>
                    <span class="hide-on-small-only">Asignar Rol</span>
                    <span class="hide-on-med-and-up">Asignar</span>
                </button>
                <!-- Botón para crear un nuevo rol -->
                <button class="btn waves-effect waves-light indigo" id="btnNuevoRol" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Rol</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA DE ROLES DEL SISTEMA ===== -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <!-- Encabezado de la tabla con título y contador de roles -->
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">admin_panel_settings</i> Roles del Sistema
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;">0 roles</span>
        </div>

        <!-- Contenedor con scroll horizontal -->
        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped rol-table" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:600px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Rol</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Descripción</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Usuarios</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Creado</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acción</th>
                    </tr>
                </thead>
                <!-- Cuerpo de la tabla: las filas se generan dinámicamente con JavaScript (AJAX) -->
                <tbody id="tabla-roles-body">
                    <!-- Mensaje de carga mientras se obtienen los datos -->
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">admin_panel_settings</i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVO/EDITAR ROL ===== -->
<div id="modalRol" class="modal" style="max-width:500px;">
    <div class="modal-content" style="padding:2rem;">
        <!-- Encabezado con ícono dinámico según sea nuevo o edición -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);" id="modal-rol-icon">add</i>
                <span id="modal-rol-title">Nuevo Rol</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <!-- Formulario de rol -->
        <form id="formRol">
            <!-- Campo oculto para el ID del rol (vacío si es nuevo) -->
            <input type="hidden" id="rol-id" value="">
            <div class="row" style="margin-bottom:0;">
                <!-- Campo: Nombre del rol -->
                <div class="col s12 input-field">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" id="rol-nombre" required>
                    <label for="rol-nombre">Nombre del rol</label>
                </div>
                <!-- Campo: Descripción del rol -->
                <div class="col s12 input-field">
                    <i class="material-icons prefix">description</i>
                    <textarea id="rol-descripcion" class="materialize-textarea"></textarea>
                    <label for="rol-descripcion">Descripción</label>
                </div>
            </div>
        </form>
    </div>
    <!-- Footer del modal: Cancelar y Guardar -->
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarRol" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar</button>
    </div>
</div>

<!-- ===== MODAL: PERMISOS DEL ROL ===== -->
<div id="modalPermisos" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <!-- Encabezado con el nombre del rol -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">security</i> Permisos: <span id="permisoRolNombre" style="color:var(--text);"></span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <!-- Descripción -->
        <p style="color:var(--text-muted);margin-bottom:1.25rem;font-size:0.9rem;">Selecciona los módulos a los que este rol tendrá acceso:</p>
        <!-- Campo oculto para el ID del rol -->
        <input type="hidden" id="permisos-rol-id" value="">
        <!-- Contenedor donde se renderizan los switches de permisos (generado dinámicamente por JS) -->
        <div id="permisos-container" style="display:flex;flex-direction:column;gap:0.5rem;">
        </div>
    </div>
    <!-- Footer del modal de permisos -->
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarPermisos" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar Permisos</button>
    </div>
</div>

<!-- ===== MODAL: ASIGNAR ROL A USUARIO ===== -->
<div id="modalAsignar" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <!-- Encabezado -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">supervisor_account</i> Asignar Rol
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <div class="row" style="margin-bottom:0;">
            <!-- Selector de usuario -->
            <div class="col s12 input-field">
                <i class="material-icons prefix">person</i>
                <select id="asignar-usuario">
                    <option value="" disabled selected>Seleccionar usuario</option>
                </select>
                <label>Usuario</label>
            </div>
            <!-- Selector de rol -->
            <div class="col s12 input-field">
                <i class="material-icons prefix">admin_panel_settings</i>
                <select id="asignar-rol">
                    <option value="" disabled selected>Seleccionar rol</option>
                </select>
                <label>Rol</label>
            </div>
        </div>
    </div>
    <!-- Footer del modal: Cancelar y Asignar -->
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnAsignarRol" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">supervisor_account</i> Asignar Rol</button>
    </div>
</div>

<!-- ===== ESTILOS CSS EMBEBIDOS ===== -->
<style>
/* Efecto hover en las filas de la tabla de roles */
.rol-table tbody tr:hover { background: var(--surface-hover); }
/* Alineación vertical centrada en las celdas */
.rol-table td { vertical-align: middle; }
/* Media query para pantallas pequeñas: reduce el padding de las celdas */
@media only screen and (max-width: 600px) {
    .rol-table td, .rol-table th { padding: 0.55rem 0.5rem !important; }
}
</style>
