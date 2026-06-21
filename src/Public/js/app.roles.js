$(function () {
    var API = '?pagina=roles&action=';

    function refrescarKPI() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success || !r.data) return;
            var totalRoles = r.data.length;
            var totalUsuarios = 0;
            var conRol = 0;
            $.each(r.data, function (i, rol) {
                totalUsuarios += parseInt(rol.total_usuarios) || 0;
                if (parseInt(rol.total_usuarios) > 0) conRol++;
            });
            $('#kpi-roles').text(totalRoles);
            $('#kpi-usuarios').text(totalUsuarios);
            $('#kpi-con-rol').text(conRol);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });

        $.getJSON(API + 'permisos', function (r) {
            if (r.success && r.data) {
                $('#kpi-permisos').text(r.data.length);
            }
        }).fail(function () {
            EIS.toast('Error al cargar permisos', 'red', 'error');
        });
    }

    function refrescarTabla() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            var tbody = $('#tabla-roles-body');
            tbody.empty();

            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">admin_panel_settings</i>No hay roles registrados</td></tr>');
                $('.result-count').text('0 roles');
                return;
            }

            $.each(r.data, function (i, rol) {
                var tieneUsuarios = parseInt(rol.total_usuarios) > 0;
                var usuariosBadge = tieneUsuarios
                    ? '<span style="font-weight:600;color:var(--success);">' + rol.total_usuarios + '</span>'
                    : '<span style="color:var(--text-muted);">0</span>';

                var colors = ['#3949ab', '#43a047', '#fb8c00', '#e53935', '#00acc1', '#8e24aa', '#6d4c41', '#546e7a'];
                var color = colors[i % colors.length];
                var inicial = (rol.nombre || 'R').charAt(0).toUpperCase();

                var row = '<tr data-id="' + rol.id + '" data-nombre="' + $('<span>').text(rol.nombre).html() + '">';
                row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,' + color + ',' + color + 'cc);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.9rem;">' + inicial + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(rol.nombre).html() + '</div></div></div></td>';
                row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">' + $('<span>').text(rol.descripcion || 'Sin descripci&oacute;n').html() + '</td>';
                row += '<td style="padding:0.75rem 1rem;">' + usuariosBadge + '</td>';
                row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.8rem;">' + (rol.created_at || '-') + '</td>';
                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-rol" data-id="' + rol.id + '" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light grey tooltipped btn-permisos-rol" data-id="' + rol.id + '" data-position="left" data-tooltip="Permisos" style="margin-left:4px;"><i class="material-icons">security</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-rol" data-id="' + rol.id + '" data-nombre="' + $('<span>').text(rol.nombre).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row);
            });

            $('.result-count').text(r.data.length + ' roles');
            $('.tooltipped').tooltip();
            aplicarFiltro();
        }).fail(function () {
            EIS.toast('Error al cargar la tabla', 'red', 'error');
        });
    }

    function aplicarFiltro() {
        var q = $('#searchRol').val().toLowerCase();
        var filtro = $('#filterRolEstado').val();

        $('#tabla-roles-body tr').each(function () {
            var mostrar = true;
            var $row = $(this);
            var nombre = $row.data('nombre') || '';
            if (q && nombre.toLowerCase().indexOf(q) === -1) mostrar = false;

            if (filtro === 'con-usuarios') {
                var users = parseInt($row.find('td').eq(2).text().trim()) || 0;
                if (users === 0) mostrar = false;
            } else if (filtro === 'sin-usuarios') {
                var users = parseInt($row.find('td').eq(2).text().trim()) || 0;
                if (users > 0) mostrar = false;
            }

            $row.toggle(mostrar);
        });

        var visibles = $('#tabla-roles-body tr:visible').length;
        var total = $('#tabla-roles-body tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    }

    function abrirModalRol(titulo, datos) {
        $('#modal-rol-title').text(titulo);
        $('#modal-rol-icon').text(titulo === 'Nuevo Rol' ? 'add' : 'edit');
        $('#formRol')[0].reset();
        $('#rol-id').val('');

        if (datos) {
            $('#rol-id').val(datos.id);
            $('#rol-nombre').val(datos.nombre);
            $('#rol-descripcion').val(datos.descripcion);
        }

        $('#modalRol').modal('open');
        M.updateTextFields();
    }

    function abrirModalPermisos(rol_id, rol_nombre) {
        $('#permisoRolNombre').text(rol_nombre);
        $('#permisos-rol-id').val(rol_id);
        $('#permisos-container').html('<p style="color:var(--text-muted);text-align:center;">Cargando...</p>');
        $('#modalPermisos').modal('open');

        $.getJSON(API + 'permisos', function (rPermisos) {
            if (!rPermisos.success || !rPermisos.data) {
                $('#permisos-container').html('<p style="color:var(--danger);text-align:center;">Error al cargar permisos</p>');
                return;
            }

            $.getJSON(API + 'permisosRol&rol_id=' + rol_id, function (rAsignados) {
                var asignados = rAsignados.success ? (rAsignados.data || []) : [];
                var html = '';
                $.each(rPermisos.data, function (i, p) {
                    var checked = asignados.indexOf(p.id) !== -1;
                    var icono = p.icono || 'lock_open';
                    html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">';
                    html += '<span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">' + icono + '</i> ' + $('<span>').text(p.nombre).html() + '</span>';
                    html += '<div class="switch"><label><input type="checkbox" class="permiso-checkbox" value="' + p.id + '"' + (checked ? ' checked' : '') + '><span class="lever"></span></label></div>';
                    html += '</div>';
                });
                $('#permisos-container').html(html);
            }).fail(function () {
                $('#permisos-container').html('<p style="color:var(--danger);text-align:center;">Error al cargar permisos del rol</p>');
            });
        }).fail(function () {
            $('#permisos-container').html('<p style="color:var(--danger);text-align:center;">Error al cargar permisos</p>');
        });
    }

    function abrirModalAsignar() {
        $('#asignar-usuario').empty().append('<option value="" disabled selected>Seleccionar usuario</option>');
        $('#asignar-rol').empty().append('<option value="" disabled selected>Seleccionar rol</option>');

        $.getJSON(API + 'usuarios', function (r) {
            if (!r.success || !r.data) return;

            $.each(r.data.usuarios, function (i, u) {
                var estado = u.activo ? '' : ' (Inactivo)';
                $('#asignar-usuario').append('<option value="' + u.id + '">' + $('<span>').text(u.nombre).html() + ' (' + $('<span>').text(u.username).html() + ') - ' + $('<span>').text(u.rol || 'Sin rol').html() + estado + '</option>');
            });

            $.each(r.data.roles, function (i, rol) {
                $('#asignar-rol').append('<option value="' + rol.id + '">' + $('<span>').text(rol.nombre).html() + '</option>');
            });

            $('#asignar-usuario').formSelect();
            $('#asignar-rol').formSelect();
            $('#modalAsignar').modal('open');
        }).fail(function () {
            EIS.toast('Error al cargar usuarios', 'red', 'error');
        });
    }

    $(document).on('click', '#btnNuevoRol', function () {
        abrirModalRol('Nuevo Rol', null);
    });

    $(document).on('click', '.btn-editar-rol', function () {
        var id = $(this).data('id');
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (r.success) {
                abrirModalRol('Editar Rol', r.data);
            } else {
                EIS.toast(r.error || 'Error al cargar rol', 'red', 'error');
            }
        }).fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    $(document).on('click', '.btn-eliminar-rol', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        if (confirm('¿Est&aacute; seguro de eliminar el rol "' + nombre + '"?')) {
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarTabla();
                    refrescarKPI();
                } else {
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json').fail(function () {
                EIS.toast('Error de conexi&oacute;n', 'red', 'error');
            });
        }
    });

    $(document).on('click', '.btn-permisos-rol', function () {
        var id = $(this).data('id');
        var nombre = $(this).closest('tr').data('nombre') || '';
        abrirModalPermisos(id, nombre);
    });

    $('#btnGuardarRol').on('click', function () {
        var id = $('#rol-id').val();
        var accion = id ? 'actualizar' : 'crear';
        var data = {
            id: id,
            nombre: $('#rol-nombre').val(),
            descripcion: $('#rol-descripcion').val()
        };

        if (!data.nombre.trim()) {
            EIS.toast('El nombre del rol es obligatorio', 'red', 'error');
            return;
        }

        $.post(API + accion, data, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modalRol').modal('close');
                refrescarTabla();
                refrescarKPI();
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    $('#btnGuardarPermisos').on('click', function () {
        var rol_id = $('#permisos-rol-id').val();
        var permisos = [];
        $('.permiso-checkbox:checked').each(function () {
            permisos.push($(this).val());
        });

        $.post(API + 'guardarPermisos', { rol_id: rol_id, permisos: permisos }, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modalPermisos').modal('close');
            } else {
                EIS.toast(r.error || 'Error al guardar permisos', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    $('#btnAsignarRol').on('click', function () {
        var usuario_id = $('#asignar-usuario').val();
        var rol_id = $('#asignar-rol').val();

        if (!usuario_id || !rol_id) {
            EIS.toast('Seleccione un usuario y un rol', 'red', 'error');
            return;
        }

        $.post(API + 'asignarRol', { usuario_id: usuario_id, rol_id: rol_id }, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modalAsignar').modal('close');
                refrescarTabla();
                refrescarKPI();
            } else {
                EIS.toast(r.error || 'Error al asignar rol', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    $(document).on('click', '#btnAsignarRolHeader', function () {
        abrirModalAsignar();
    });

    $('#searchRol').on('keyup', debounce(function () {
        aplicarFiltro();
    }, 300));

    $('#filterRolEstado').on('change', function () {
        aplicarFiltro();
    });

    $('.tooltipped').tooltip();
    $('select').formSelect();
    $('.modal').modal();

    refrescarKPI();
    refrescarTabla();
});
