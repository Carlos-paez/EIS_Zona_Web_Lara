$(function () {
    var API = '?pagina=clientes&action=';

    function refrescarKPI() {
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return;
            $('#kpi-total').text(r.data.total);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    function refrescarTabla() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            var tbody = $('#tabla-clientes tbody');
            tbody.empty();

            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">badge</i>No hay clientes registrados</td></tr>');
                $('.result-count').text('0 resultados');
                return;
            }

            $.each(r.data, function (i, c) {
                var inits = ((c.nombre || '?')[0] + (c.apellido || '?')[0]).toUpperCase();
                var row = '<tr data-id="' + c.id + '">';

                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;font-weight:600;color:var(--text-muted);">' + $('<span>').text(c.cedula).html() + '</td>';

                row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#42a5f5);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">' + inits + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(c.nombre).html() + ' ' + $('<span>').text(c.apellido).html() + '</div></div></div></td>';

                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(c.direccion || '-').html() + '</td>';

                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(c.telefono || '-').html() + '</td>';

                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-cliente" data-id="' + c.id + '" data-position="left" data-tooltip="Editar cliente"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-cliente" data-id="' + c.id + '" data-nombre="' + $('<span>').text(c.nombre + ' ' + c.apellido).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row);
            });

            $('.result-count').text(r.data.length + ' resultados');
            $('.tooltipped').tooltip();
            aplicarFiltro();
        }).fail(function () {
            EIS.toast('Error al cargar clientes', 'red', 'error');
        });
    }

    function aplicarFiltro() {
        var q = $('#searchCliente').val().toLowerCase();

        $('#tabla-clientes tbody tr').each(function () {
            var mostrar = true;
            var texto = $(this).text().toLowerCase();
            if (q && texto.indexOf(q) === -1) mostrar = false;
            $(this).toggle(mostrar);
        });

        var visibles = $('#tabla-clientes tbody tr:visible').length;
        var total = $('#tabla-clientes tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    }

    $(document).on('click', '.btn-nuevo-cliente', function () {
        $('#cliente-id').val('');
        $('#form-cliente')[0].reset();
        $('#modal-cliente-title').text('Nuevo Cliente');
        M.updateTextFields();
        $('#modal-cliente').modal('open');
    });

    $(document).on('click', '.btn-editar-cliente', function () {
        var id = $(this).data('id');
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            var c = r.data;
            $('#cliente-id').val(c.id);
            $('#cliente-cedula').val(c.cedula);
            $('#cliente-nombre').val(c.nombre);
            $('#cliente-apellido').val(c.apellido);
            $('#cliente-direccion').val(c.direccion);
            $('#cliente-telefono').val(c.telefono);
            $('#modal-cliente-title').text('Editar Cliente: ' + c.nombre + ' ' + c.apellido);
            M.updateTextFields();
            $('#modal-cliente').modal('open');
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    $('#form-cliente').on('submit', function (e) {
        e.preventDefault();
        var id = $('#cliente-id').val();
        var accion = id ? 'actualizar' : 'crear';
        $.post(API + accion, $(this).serialize(), function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-cliente').modal('close');
                refrescarKPI();
                refrescarTabla();
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    $(document).on('click', '.btn-eliminar-cliente', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        if (confirm('¿Eliminar el cliente "' + nombre + '"?\nNota: No se puede eliminar si tiene asesorías, ventas o sesiones asociadas.')) {
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

    $('#searchCliente').on('keyup', debounce(function () { aplicarFiltro(); }, 300));

    refrescarTabla();
    $('.modal').modal();
    $('.tooltipped').tooltip();
});
