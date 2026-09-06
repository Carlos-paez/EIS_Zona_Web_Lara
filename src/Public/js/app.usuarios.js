$(function () {
    var API = '?pagina=usuarios&action=';
    var rolNorm = function (key) { return key || ''; };

    function buildUserRow(u) {
        var activo = (u.activo === '1');
        var selfId = (window.EIS && window.EIS.userId) || 0;
        var isSelf = String(u.id) === String(selfId);
        var rolnombre = u.rol_nombre || u.rol || 'Sin rol';
        var inits = ((u.nombre || '?')[0] + (u.apellido || '?')[0]).toUpperCase();

        var row = '<tr data-id="' + u.id + '" data-rol="' + (u.fk_rol_usuario || '') + '">';

        row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7b1fa2,#ba68c8);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">' + inits + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(u.nombre || '').html() + ' ' + $('<span>').text(u.apellido || '').html() + ' ' + (isSelf ? '<span style="font-size:0.7rem;color:var(--text-muted);">(tú)</span>' : '') + '</div></div></div></td>';

        row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(u.email || '-').html() + '</td>';

        row += '<td style="padding:0.75rem 1rem;"><span class="badge-rol">' + $('<span>').text(rolnombre).html() + '</span></td>';

        var estadoBadge = activo
            ? '<span style="display:inline-flex;align-items:center;gap:0.3rem;background:rgba(46,160,67,0.12);color:#2ea043;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.75rem;font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:#2ea043;display:inline-block;"></span>Activo</span>'
            : '<span style="display:inline-flex;align-items:center;gap:0.3rem;background:rgba(158,54,54,0.12);color:#9e3636;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.75rem;font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:#9e3636;display:inline-block;"></span>Inactivo</span>';
        row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;">' + estadoBadge + '</td>';

        row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.85rem;font-family:monospace;">' + $('<span>').text(u.user_name || '').html() + '</td>';

        row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
        row += '<a href="?pagina=roles" class="btn-floating waves-effect waves-light indigo tooltipped" data-position="top" data-tooltip="Roles y permisos" style="width:36px;height:36px;line-height:36px;"><i class="material-icons" style="font-size:1rem;">shield</i></a>';
        row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-usuario" data-id="' + u.id + '" data-position="left" data-tooltip="Editar usuario" style="margin-left:4px;"><i class="material-icons">edit</i></button>';
        if (!isSelf) {
            row += activo
                ? '<button class="btn-floating waves-effect waves-light amber darken-2 tooltipped btn-estado-usuario" data-id="' + u.id + '" data-activo="1" data-position="left" data-tooltip="Desactivar" style="margin-left:4px;"><i class="material-icons">power_settings_new</i></button>'
                : '<button class="btn-floating waves-effect waves-light teal tooltipped btn-estado-usuario" data-id="' + u.id + '" data-activo="0" data-position="left" data-tooltip="Activar" style="margin-left:4px;"><i class="material-icons">play_arrow</i></button>';
            row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-usuario" data-id="' + u.id + '" data-nombre="' + $('<span>').text(u.nombre + ' ' + u.apellido).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
        }
        row += '</td></tr>';

        return row;
    }

    function refrescarKPI() {
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return;
            $('#kpi-total').text(r.data.total);
            $('#kpi-activos').text(r.data.activos);
            $('#kpi-inactivos').text(r.data.inactivos);
            $('#kpi-admins').text(r.data.administradores);
            $('#countUsuarios').text(r.data.total);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    function refrescarTabla() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            var tbody = $('#tabla-usuarios tbody');
            tbody.empty();

            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">people_outline</i>No hay usuarios registrados</td></tr>');
                EIS.datatableRefresh('#tabla-usuarios');
                return;
            }

            $.each(r.data, function (i, u) {
                tbody.append(buildUserRow(u));
            });

            $('.tooltipped').tooltip();
            EIS.datatableRefresh('#tabla-usuarios');
        }).fail(function () {
            EIS.toast('Error al cargar usuarios', 'red', 'error');
        });
    }

    function cargarRoles() {
        $.getJSON(API + 'roles', function (r) {
            if (!r.success) return;
            var $filter = $('#filterRol');
            var $modal = $('#usuario-rol');
            $.each(r.data, function (i, rol) {
                var id = rol.id;
                var nombre = '#' + rol.id + ' ' + (rol.nombre || rol.rol || '');
                $filter.append($('<option>').val(id).text(nombre));
                $modal.append($('<option>').val(id).text(nombre));
            });
            M.FormSelect.init($filter);
            M.FormSelect.init($modal);
        }).fail(function () {
            EIS.toast('Error al cargar roles', 'red', 'error');
        });
    }

    function aplicarFiltro() {
        if (!(window.jQuery && $.fn.DataTable)) return;
        var dt = $('#tabla-usuarios').DataTable();
        if (!dt) return;
        dt.search($('#searchUsuario').val() || '');
        $.fn.dataTable.ext.search.push(function (settings, row, idx) {
            var rol = $('#filterRol').val() || '';
            if (!rol) return true;
            return $(settings.aoData[idx].nTr).attr('data-rol') === rol;
        });
        dt.draw();
        $.fn.dataTable.ext.search.pop();
    }

    function abrirNuevo() {
        $('#form-usuario')[0].reset();
        $('#usuario-id').val('');
        $('#usuario-username').prop('readonly', false);
        $('#usuario-username').removeAttr('disabled');
        $('#label-password').text('Contraseña (mín. 8 caracteres)');
        $('#usuario-estatus').val('1');
        if (window.M) {
            M.FormSelect.init($('#usuario-rol'));
            M.FormSelect.init($('#usuario-estatus'));
        }
        $('#modal-usuario-title').text('Nuevo Usuario');
        $('#modal-usuario').modal('open');
    }

    function abrirEditar(id) {
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            var u = r.data;
            $('#form-usuario')[0].reset();
            $('#usuario-id').val(u.id);
            $('#usuario-nombre').val(u.nombre);
            $('#usuario-apellido').val(u.apellido);
            $('#usuario-username').val(u.user_name);
            $('#usuario-username').prop('readonly', true);
            $('#usuario-email').val(u.email);
            $('#usuario-password').val('');
            $('#label-password').text('Nueva contraseña (dejar vacío para no cambiar)');
            $('#usuario-rol').val(u.fk_rol_usuario || '');
            $('#usuario-estatus').val((u.estatus === '1' || u.estatus === 'activo') ? '1' : '0');
            $('#modal-usuario-title').text('Editar Usuario: ' + u.nombre + ' ' + u.apellido);
            M.updateTextFields();
            M.textareaAutoResize && M.textareaAutoResize($('#usuario-nombre'));
            M.FormSelect.init($('#usuario-rol'));
            M.FormSelect.init($('#usuario-estatus'));
            $('#modal-usuario').modal('open');
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    }

    $(document).on('click', '#btnNuevoUsuario', abrirNuevo);
    $(document).on('click', '.btn-editar-usuario', function () {
        abrirEditar($(this).data('id'));
    });

    $(document).on('click', '.btn-estado-usuario', function () {
        var $btn = $(this);
        var id = $btn.data('id');
        var activo = $btn.data('activo');
        var accion = activo === 1 ? 'desactivar' : 'activar';
        if (confirm('¿' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' este usuario?')) {
            $.post(API + 'estado', { id: id, activo: activo }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarKPI();
                    refrescarTabla();
                } else {
                    EIS.toast(r.error || 'Error al cambiar el estado', 'red', 'error');
                }
            }, 'json').fail(function () {
                EIS.toast('Error de conexión', 'red', 'error');
            });
        }
    });

    $(document).on('click', '.btn-eliminar-usuario', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        if (confirm('¿Eliminar el usuario "' + nombre + '"?\nNota: No se puede eliminar si tiene registros asociados.')) {
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarKPI();
                    refrescarTabla();
                } else {
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json').fail(function () {
                EIS.toast('Error de conexión', 'red', 'error');
            });
        }
    });

    $('#form-usuario').on('submit', function (e) {
        e.preventDefault();

        var nombre   = $('#usuario-nombre').val().trim();
        var username = $('#usuario-username').val().trim();
        var password = $('#usuario-password').val();
        var email    = $('#usuario-email').val().trim();
        var id       = $('#usuario-id').val();

        if (!nombre || !username) {
            EIS.toast('Nombre y nombre de usuario son obligatorios', 'red', 'error');
            return;
        }
        if (nombre.length < 2 || nombre.length > 100) {
            EIS.toast('El nombre debe tener entre 2 y 100 caracteres', 'red', 'error');
            return;
        }
        if (username.length < 3 || username.length > 50) {
            EIS.toast('El nombre de usuario debe tener entre 3 y 50 caracteres', 'red', 'error');
            return;
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            EIS.toast('El email no es válido', 'red', 'error');
            return;
        }
        if (!id && (!password || password.length < 8)) {
            EIS.toast('La contraseña debe tener al menos 8 caracteres', 'red', 'error');
            return;
        }
        if (id && password && password.length < 8) {
            EIS.toast('La contraseña debe tener al menos 8 caracteres', 'red', 'error');
            return;
        }

        var accion = id ? 'actualizar' : 'crear';
        $.post(API + accion, $(this).serialize(), function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-usuario').modal('close');
                refrescarKPI();
                refrescarTabla();
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    if (window.EIS && EIS.datatableWireSearch) {
        EIS.datatableWireSearch('#tabla-usuarios', '#searchUsuario');
    } else {
        $('#searchUsuario').on('keyup', debounce(function () { aplicarFiltro(); }, 300));
    }
    $('#filterRol').on('change', aplicarFiltro);

    cargarRoles();
    refrescarKPI();
    refrescarTabla();
    $('.modal').modal();
    $('.tooltipped').tooltip();
    EIS.datatable('#tabla-usuarios');
});