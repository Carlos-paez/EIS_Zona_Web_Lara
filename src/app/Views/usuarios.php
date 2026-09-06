<?php
use App\Models\Usuario;

$userModel = new Usuario();
$usuarios = $userModel->obtenerTodos();
$totalUsuarios = count($usuarios);
$activos = count(array_filter($usuarios, fn($u) => $u['activo'] === '1'));
$inactivos = $totalUsuarios - $activos;

$adminCount = 0;
foreach ($usuarios as $u) {
    $rolLower = strtolower($u['rol_nombre'] ?? $u['rol'] ?? '');
    if (str_contains($rolLower, 'admin') || str_contains($rolLower, 'administrador')) {
        $adminCount++;
    }
}
?>

<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">people</i></div>
            <div class="metric-label">Total Usuarios</div>
            <div class="metric-value" id="kpi-total"><?php echo $totalUsuarios; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Registrados en el sistema</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Activos</div>
            <div class="metric-value" id="kpi-activos" style="color:var(--success);"><?php echo $activos; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Con acceso al sistema</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">block</i></div>
            <div class="metric-label">Inactivos</div>
            <div class="metric-value" id="kpi-inactivos" style="color:var(--warning);"><?php echo $inactivos; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Sin acceso</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">admin_panel_settings</i></div>
            <div class="metric-label">Administradores</div>
            <div class="metric-value" id="kpi-admins" style="color:var(--info);"><?php echo $adminCount; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Con permisos totales</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchUsuario" placeholder="Buscar por nombre, email o rol...">
                    <label for="searchUsuario">Buscar usuario</label>
                </div>
            </div>
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterRol">
                        <option value="" selected>Todos</option>
                    </select>
                    <label>Rol</label>
                </div>
            </div>
            <div class="col s6 m4 l4 right-align" style="padding:0.5rem 0 0;">
                <button class="btn waves-effect waves-light indigo" id="btnNuevoUsuario" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Usuario</span>
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
                <i class="material-icons" style="color:var(--primary);">people</i> Usuarios del Sistema
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><span id="countUsuarios"><?php echo $totalUsuarios; ?></span> usuarios</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped user-table" id="tabla-usuarios" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:720px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Usuario</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Email</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Rol</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Estado</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Username</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span style="color:var(--text-muted);font-size:0.85rem;">Los permisos de cada usuario se definen por su rol en el módulo Roles y Permisos.</span>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVO / EDITAR USUARIO ===== -->
<div id="modal-usuario" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">person_add</i> <span id="modal-usuario-title">Nuevo Usuario</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <form id="form-usuario">
            <input type="hidden" name="id" id="usuario-id" value="">

            <div class="row" style="margin-bottom:0;">
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">person</i>
                    <input type="text" name="nombre" id="usuario-nombre" required maxlength="100" pattern=".{2,100}" title="Entre 2 y 100 caracteres">
                    <label for="usuario-nombre">Nombre</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">person_outline</i>
                    <input type="text" name="apellido" id="usuario-apellido" maxlength="100" title="Máximo 100 caracteres">
                    <label for="usuario-apellido">Apellido</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" name="user_name" id="usuario-username" required maxlength="50" pattern="[A-Za-z0-9._-]{3,50}" title="Entre 3 y 50 caracteres: letras, números, puntos, guiones">
                    <label for="usuario-username">Nombre de usuario</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">email</i>
                    <input type="email" name="email" id="usuario-email" maxlength="100">
                    <label for="usuario-email">Email</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">lock</i>
                    <input type="password" name="password" id="usuario-password" autocomplete="new-password">
                    <label for="usuario-password" id="label-password">Contraseña (mín. 8 caracteres)</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">admin_panel_settings</i>
                    <select name="rol" id="usuario-rol">
                        <option value="">Sin rol</option>
                    </select>
                    <label>Rol del usuario</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">toggle_on</i>
                    <select name="estatus" id="usuario-estatus">
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <label>Estado</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button type="submit" form="form-usuario" class="btn waves-effect waves-light indigo" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar</button>
    </div>
</div>

<style>
.user-table tbody tr:hover { background: var(--surface-hover); }
.user-table td { vertical-align: middle; }
@media only screen and (max-width: 600px) {
    .user-table td, .user-table th { padding: 0.55rem 0.5rem !important; }
    #modal-usuario .modal-content { padding: 1.25rem !important; }
}
</style>