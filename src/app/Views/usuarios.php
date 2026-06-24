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
            <div class="metric-value"><?php echo $totalUsuarios; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Registrados en el sistema</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Activos</div>
            <div class="metric-value" style="color:var(--success);"><?php echo $activos; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Con acceso al sistema</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">block</i></div>
            <div class="metric-label">Inactivos</div>
            <div class="metric-value" style="color:var(--warning);"><?php echo $inactivos; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Sin acceso</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">admin_panel_settings</i></div>
            <div class="metric-label">Administradores</div>
            <div class="metric-value" style="color:var(--info);"><?php echo $adminCount; ?></div>
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
                        <option value="admin">Admin</option>
                        <option value="editor">Editor</option>
                        <option value="visor">Visor</option>
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
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalUsuarios; ?> usuarios</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped user-table" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:600px;">
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
                <tbody>
                    <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">
                            <i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">people</i>
                            No hay usuarios registrados
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $i => $u):
                            $inicial = strtoupper(substr($u['nombre'], 0, 1));
                            $gradientes = [
                                'linear-gradient(135deg,#3949ab,#5c6bc0)',
                                'linear-gradient(135deg,#43a047,#66bb6a)',
                                'linear-gradient(135deg,#fb8c00,#ffa726)',
                                'linear-gradient(135deg,#e53935,#ef5350)',
                                'linear-gradient(135deg,#8e24aa,#ab47bc)',
                                'linear-gradient(135deg,#00acc1,#26c6da)',
                            ];
                            $gIdx = $i % count($gradientes);
                            $esActivo = $u['activo'] === '1';
                            $rolDisplay = $u['rol_nombre'] ?? $u['rol'] ?? 'Sin rol';
                        ?>
                        <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                            <td style="padding:0.75rem 1rem;">
                                <div style="display:flex;align-items:center;gap:0.75rem;">
                                    <div style="width:38px;height:38px;border-radius:50%;background:<?php echo $gradientes[$gIdx]; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.9rem;"><?php echo $inicial; ?></div>
                                    <div>
                                        <div style="font-weight:600;font-size:0.9rem;"><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></div>
                                        <span class="hide-on-med-and-up" style="font-size:0.7rem;color:var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td style="padding:0.75rem 1rem;">
                                <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.5rem;border-radius:4px;font-size:0.75rem;font-weight:600;background:#e8eaf6;color:#283593;"><i class="material-icons" style="font-size:0.85rem;">admin_panel_settings</i> <?php echo htmlspecialchars($rolDisplay); ?></span>
                            </td>
                            <td class="hide-on-small-only" style="padding:0.75rem 1rem;">
                                <?php if ($esActivo): ?>
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.8rem;color:var(--success);"><span style="width:8px;height:8px;border-radius:50%;background:var(--success);display:inline-block;"></span> Activo</span>
                                <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.8rem;color:var(--text-muted);"><span style="width:8px;height:8px;border-radius:50%;background:var(--text-muted);display:inline-block;"></span> Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.8rem;"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">
                                <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-usuario" data-id="<?php echo $u['id']; ?>" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalUsuarios; ?> usuarios</span>
        </div>
    </div>
</div>

<!-- ===== MODAL: EDITAR USUARIO ===== -->
<div id="modalEditarUsuario" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <!-- Encabezado del modal con ícono y botón de cierre -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">edit</i> Editar Usuario
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <!-- Formulario de edición de usuario -->
        <form id="formEditarUsuario">
            <div class="row" style="margin-bottom:0;">
                <!-- Campo: Nombre completo -->
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">person</i>
                    <input type="text" id="editNombre" value="Admin Principal">
                    <label for="editNombre" class="active">Nombre completo</label>
                </div>
                <!-- Campo: Nombre de usuario -->
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" id="editUsuario" value="admin">
                    <label for="editUsuario" class="active">Usuario</label>
                </div>
                <!-- Campo: Email -->
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">email</i>
                    <input type="email" id="editEmail" value="admin@eis.com">
                    <label for="editEmail" class="active">Email</label>
                </div>
                <!-- Campo: Nueva contraseña (placeholder indica que puede dejarse vacío) -->
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">lock</i>
                    <input type="password" id="editPassword" placeholder="Dejar vacío para mantener">
                    <label for="editPassword">Nueva contraseña</label>
                </div>
                <!-- Campo: Rol del usuario -->
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">admin_panel_settings</i>
                    <select id="editRol">
                        <option value="admin" selected>Administrador</option>
                        <option value="editor">Editor</option>
                        <option value="visor">Visor</option>
                    </select>
                    <label>Rol del usuario</label>
                </div>
                <!-- Campo: Estado (Activo/Inactivo) -->
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">toggle_off</i>
                    <select id="editEstado">
                        <option value="activo" selected>Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                    <label>Estado</label>
                </div>
            </div>
        </form>
    </div>
    <!-- Footer del modal: botones Cancelar y Guardar Cambios -->
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarUsuario" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar Cambios</button>
    </div>
</div>

<!-- ===== MODAL: PERMISOS DE USUARIO ===== -->
<div id="modalPermisos" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <!-- Encabezado con el nombre del usuario -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">security</i> Permisos: <span id="permisoUsuarioNombre" style="color:var(--text);">Admin Principal</span>
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <!-- Descripción -->
        <p style="color:var(--text-muted);margin-bottom:1.25rem;font-size:0.9rem;">Define los módulos a los que este usuario tendrá acceso:</p>
        <!-- Formulario de permisos con switches para cada módulo -->
        <form id="formPermisos">
            <div style="display:flex;flex-direction:column;gap:0.5rem;">
                <!-- Permiso: Dashboard -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">dashboard</i> Dashboard</span>
                    <!-- Switch de Materialize marcado por defecto -->
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Inventario -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">inventory_2</i> Inventario</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Ventas (POS) -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">shopping_cart</i> Ventas (POS)</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Proveedores -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">request_quote</i> Proveedores</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Cyber Control -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">computer</i> Cyber Control</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Reportes -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">bar_chart</i> Reportes</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Activos -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">build</i> Activos</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Asesoría Legal -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">gavel</i> Asesoría Legal</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
                <!-- Permiso: Usuarios -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">
                    <span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">settings</i> Usuarios</span>
                    <div class="switch"><label><input type="checkbox" checked><span class="lever"></span></label></div>
                </div>
            </div>
        </form>
    </div>
    <!-- Footer del modal de permisos -->
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarPermisos" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar Permisos</button>
    </div>
</div>

<!-- ===== ESTILOS CSS EMBEBIDOS ===== -->
<style>
/* Efecto hover en las filas de la tabla de usuarios */
.user-table tbody tr:hover { background: var(--surface-hover); }
/* Alineación vertical centrada en las celdas */
.user-table td { vertical-align: middle; }
/* Media query para pantallas pequeñas (máx. 600px) */
@media only screen and (max-width: 600px) {
    /* Reduce el padding de celdas en la tabla */
    .user-table td, .user-table th { padding: 0.55rem 0.5rem !important; }
    /* Reduce el gap en contenedores flex dentro de celdas */
    .user-table td > div[style*="flex"] { gap: 0.5rem !important; }
    /* Reduce el padding de los modales en móvil */
    #modalEditarUsuario .modal-content,
    #modalPermisos .modal-content { padding: 1.25rem !important; }
}
</style>

<!-- ===== SCRIPTS JAVASCRIPT ===== -->
<script>
// Ejecuta cuando el DOM está listo (jQuery)
$(function () {
    // Filtro de búsqueda en la tabla de usuarios (filtra por texto en la fila)
    $('#searchUsuario').on('input', function () {
        var q = $(this).val().toLowerCase(); // Obtiene el texto de búsqueda en minúsculas
        // Itera sobre cada fila del cuerpo de la tabla
        $('.user-table tbody tr').each(function () {
            var text = $(this).text().toLowerCase(); // Texto completo de la fila
            $(this).toggle(text.indexOf(q) !== -1);  // Muestra/oculta según coincidencia
        });
    });

    // Manejador de clic para abrir el modal de edición de usuario
    $(document).on('click', '.btn-editar-usuario', function () {
        var instance = M.Modal.getInstance($('#modalEditarUsuario'));
        // Si el modal aún no está inicializado, lo inicializa y obtiene la instancia
        if (!instance) { $('#modalEditarUsuario').modal(); instance = M.Modal.getInstance($('#modalEditarUsuario')); }
        instance.open(); // Abre el modal
    });

    // Manejador de clic para abrir el modal de permisos
    $(document).on('click', '.btn-permisos-usuario', function () {
        var idx = $(this).data('index'); // Obtiene el índice del usuario desde data-index
        // Busca el nombre del usuario en la fila correspondiente
        var nombre = $('.user-table tbody tr').eq(idx).find('div[style*="font-weight:600;font-size:0.9rem"]').text().trim();
        $('#permisoUsuarioNombre').text(nombre || 'Usuario'); // Actualiza el nombre en el modal
        var instance = M.Modal.getInstance($('#modalPermisos'));
        if (!instance) { $('#modalPermisos').modal(); instance = M.Modal.getInstance($('#modalPermisos')); }
        instance.open(); // Abre el modal
    });

    // Manejador de clic para el botón "Nuevo Usuario"
    $('#btnNuevoUsuario').on('click', function () {
        // Limpia todos los campos del formulario de edición
        $('#editNombre').val(''); $('#editUsuario').val(''); $('#editEmail').val(''); $('#editPassword').val('');
        // Establece valores por defecto
        $('#editRol').val('visor'); $('#editEstado').val('activo');
        M.updateTextFields(); // Actualiza las etiquetas flotantes de Materialize
        $('select').formSelect(); // Reinicia los selects
        var instance = M.Modal.getInstance($('#modalEditarUsuario'));
        if (!instance) { $('#modalEditarUsuario').modal(); instance = M.Modal.getInstance($('#modalEditarUsuario')); }
        instance.open(); // Abre el modal para crear nuevo usuario
    });

    // Manejador de clic para los botones "Guardar" (usuario y permisos)
    $('#btnGuardarUsuario, #btnGuardarPermisos').on('click', function () {
        // Muestra un toast de confirmación
        EIS.toast('Cambios guardados correctamente', 'green', 'check_circle');
        $(this).closest('.modal').modal('close'); // Cierra el modal actual
    });
});
</script>
