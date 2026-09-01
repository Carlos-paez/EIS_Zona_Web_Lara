$(function () {
    var API = '?pagina=activos&action=';

    function refrescarKPI() {
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return;
            var d = r.data || {};
            $('#kpi-total').text(d.total || 0);
            $('#kpi-ciber').text(d.ciber || 0);
            $('#kpi-ocupados').text(d.ocupados || 0);
            $('#kpi-inactivos').text(d.inactivos || 0);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    function refrescarTabla() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            var tbody = $('#tabla-activos tbody');
            tbody.empty();

            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">devices_other</i>No hay activos registrados</td></tr>');
                EIS.datatableRefresh('#tabla-activos');
                return;
            }

            $.each(r.data, function (i, a) {
                var estado = a.estado === 'ocupada'
                    ? '<span class="new badge orange" data-badge-caption="">Ocupada</span>'
                    : '<span class="new badge green" data-badge-caption="">Activo</span>';
                if (a.activa == 0) {
                    estado = '<span class="new badge red" data-badge-caption="">Inactivo</span>';
                }

                var row = '<tr data-id="' + a.id + '">';

                row += '<td style="padding:0.75rem 1rem;font-size:0.9rem;font-weight:600;">' + $('<span>').text(a.marca).html() + '</td>';

                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(a.descripcion || '-').html() + '</td>';

                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(a.nombre_tipo || '-').html() + '</td>';

                row += '<td style="padding:0.75rem 1rem;">' + estado + '</td>';

                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-activo" data-id="' + a.id + '" data-position="left" data-tooltip="Editar activo"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-activo" data-id="' + a.id + '" data-nombre="' + $('<span>').text(a.marca + ' ' + (a.descripcion || '')).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row);
            });

            $('.tooltipped').tooltip();
            EIS.datatableRefresh('#tabla-activos');
        }).fail(function () {
            EIS.toast('Error al cargar activos', 'red', 'error');
        });
    }

    function aplicarFiltro() {
        if (!(window.jQuery && $.fn.DataTable)) return;
        var dt = $('#tabla-activos').DataTable();
        if (!dt) return;
        var q = $('#searchActivo').val();
        var tipo = $('#filterTipo').val() || '';
        dt.search(q ? q : '');
        dt.column(2).search(tipo ? tipo : '', false, false);
        dt.draw();
    }

    $(document).on('click', '.btn-nuevo-activo', function () {
        $('#activo-id').val('');
        $('#form-activo')[0].reset();
        $('#activo-activa').prop('checked', true);
        $('#activo-ciber').prop('checked', false);
        $('#modal-activo-title').text('Nuevo Activo');
        M.FormSelect.init($('#activo-tipo'));
        M.updateTextFields();
        $('#modal-activo').modal('open');
    });

    $(document).on('click', '.btn-editar-activo', function () {
        var id = $(this).data('id');
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            var a = r.data;
            $('#activo-id').val(a.id);
            $('#activo-marca').val(a.marca);
            $('#activo-descripcion').val(a.descripcion);
            $('#activo-tipo').val(String(a.tipo_activo_id));
            M.FormSelect.init($('#activo-tipo'));
            $('#activo-activa').prop('checked', a.activa == 1);
            $('#activo-ciber').prop('checked', a.is_ciber == 1);
            $('#modal-activo-title').text('Editar Activo: ' + a.marca);
            M.updateTextFields();
            $('#modal-activo').modal('open');
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    $('#form-activo').on('submit', function (e) {
        e.preventDefault();

        var marca       = $('#activo-marca').val().trim();
        var descripcion = $('#activo-descripcion').val().trim();
        var tipo        = $('#activo-tipo').val();

        if (!marca || !descripcion || !tipo) {
            EIS.toast('Marca, descripción y tipo son obligatorios', 'red', 'error');
            return;
        }
        if (marca.length < 2 || marca.length > 100) {
            EIS.toast('La marca debe tener entre 2 y 100 caracteres', 'red', 'error');
            return;
        }
        if (descripcion.length > 1000) {
            EIS.toast('La descripción no puede exceder 1000 caracteres', 'red', 'error');
            return;
        }

        var id = $('#activo-id').val();
        var accion = id ? 'actualizar' : 'crear';
        $.post(API + accion, $(this).serialize(), function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-activo').modal('close');
                refrescarKPI();
                refrescarTabla();
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    $(document).on('click', '.btn-eliminar-activo', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        if (confirm('¿Eliminar el activo "' + nombre + '"?\nNota: No se puede eliminar si tiene sesiones de cybercafé asociadas.')) {
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

    // Búsqueda y filtro por tipo conectados a DataTables
    if (window.EIS && EIS.datatableWireSearch) {
        EIS.datatableWireSearch('#tabla-activos', '#searchActivo');
    } else {
        $('#searchActivo').on('keyup', debounce(function () { aplicarFiltro(); }, 300));
        $('#filterTipo').on('change', function () { aplicarFiltro(); });
    }
    if (window.EIS && EIS.datatableWireColumnFilter && $('#filterTipo').length) {
        $('#filterTipo').off('change.dt').on('change.dt', function () {
            var dt = $('#tabla-activos').DataTable();
            if (!dt) return;
            dt.column(2).search($(this).val() || '', false, false).draw();
        });
    }

    refrescarKPI();
    refrescarTabla();
    $('.modal').modal();
    $('.tooltipped').tooltip();
    M.FormSelect.init($('#filterTipo'));
    EIS.datatable('#tabla-activos');
});
