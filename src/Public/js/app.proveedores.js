// =====================================================================
// ARCHIVO: app.proveedores.js
// FUNCIÓN: Maneja la interactividad del módulo de Proveedores/Solicitudes.
//          Se comunica con el controlador PHP mediante AJAX y permite:
//          - CRUD de solicitudes/órdenes de compra
//          - Gestión de líneas (productos) dentro de cada solicitud
//          - Filtros y búsqueda en tabla de solicitudes
// =====================================================================

$(function () {
    var API = '?pagina=proveedores&action=';

    // ================================================================
    // FUNCIÓN: refrescarKPI()
    // ================================================================
    function refrescarKPI() {
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return;
            $('#kpi-total').text(r.data.total);
            $('#kpi-pendientes').text(r.data.pendientes);
            $('#kpi-recibidas').text(r.data.recibidas);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: refrescarTabla()
    // ================================================================
    function refrescarTabla() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            var tbody = $('#tabla-ordenes tbody');
            tbody.empty();

            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">request_quote</i>No hay solicitudes registradas</td></tr>');
                $('.result-count').text('0 resultados');
                return;
            }

            $.each(r.data, function (i, o) {
                var color = 'grey';
                var estado = o.estado || 'Sin estado';
                var e = estado.toLowerCase();
                if (e.indexOf('pend') !== -1) color = 'orange';
                else if (e.indexOf('recib') !== -1) color = 'green';
                else if (e.indexOf('cancel') !== -1) color = 'red';

                var inits = (o.proveedor_nombre || '?').substring(0, 2).toUpperCase();

                var row = '<tr data-id="' + o.id + '" data-numero="' + $('<span>').text(o.numero_de_orden).html() + '">';
                row += '<td style="padding:0.75rem 1rem;"><strong>#' + $('<span>').text(o.numero_de_orden).html() + '</strong></td>';
                row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#42a5f5);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">' + inits + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(o.proveedor_nombre || 'Sin proveedor').html() + '</div></div></div></td>';
                row += '<td style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">' + $('<span>').text(o.rif || '-').html() + '</td>';
                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(o.fecha).html() + '</td>';
                row += '<td style="padding:0.75rem 1rem;"><span class="new badge ' + color + '" data-badge-caption="">' + $('<span>').text(estado).html() + '</span></td>';
                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-orden" data-id="' + o.id + '" data-position="left" data-tooltip="Editar solicitud"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light grey tooltipped btn-ver-orden" data-id="' + o.id + '" data-position="left" data-tooltip="Ver detalles" style="margin-left:4px;"><i class="material-icons">visibility</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-orden" data-id="' + o.id + '" data-numero="' + $('<span>').text(o.numero_de_orden).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row);
            });

            $('.result-count').text(r.data.length + ' resultados');
            $('.tooltipped').tooltip();
            aplicarFiltro();
        }).fail(function () {
            EIS.toast('Error al cargar solicitudes', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: aplicarFiltro()
    // ================================================================
    function aplicarFiltro() {
        var q = $('#searchProveedor').val().toLowerCase();
        var estadoFiltro = $('#filterEstadoProv').val();

        $('#tabla-ordenes tbody tr').each(function () {
            var mostrar = true;
            var texto = $(this).text().toLowerCase();

            if (q && texto.indexOf(q) === -1) mostrar = false;

            if (estadoFiltro) {
                var badge = $(this).find('td').eq(4).text().trim().toLowerCase();
                if (badge.indexOf(estadoFiltro) === -1) mostrar = false;
            }

            $(this).toggle(mostrar);
        });
    }

    // ================================================================
    // FUNCIÓN: cargarLineas(id)
    // PROPÓSITO: Carga las líneas (productos) de una solicitud
    //            dentro del modal de orden (sección de líneas).
    // ================================================================
    function cargarLineas(id) {
        $('#tabla-lineas tbody').html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Cargando...</td></tr>');
        $('#linea-orden-id').val(id);

        $.getJSON(API + 'lineas&orden_id=' + id, function (r) {
            var tbody = $('#tabla-lineas tbody');
            tbody.empty();

            if (!r.success) {
                tbody.html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Error al cargar detalles</td></tr>');
                return;
            }

            var lineas = r.data.lineas || [];
            if (lineas.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Sin productos agregados</td></tr>');
                $('#detalle-total').text('$0.00');
                return;
            }

            var total = 0;
            $.each(lineas, function (i, l) {
                var subtotal = l.cantidad * l.precio;
                total += subtotal;

                var fila = '<tr data-id="' + l.id + '">';
                fila += '<td style="padding:0.5rem 0.75rem;">' + $('<span>').text(l.producto_nombre || 'Producto #' + l.producto_id).html() + '</td>';
                fila += '<td style="padding:0.5rem 0.75rem;">' + l.cantidad + '</td>';
                fila += '<td style="padding:0.5rem 0.75rem;">$' + parseFloat(l.precio).toFixed(2) + '</td>';
                fila += '<td style="padding:0.5rem 0.75rem;">$' + subtotal.toFixed(2) + '</td>';
                fila += '<td style="padding:0.5rem 0.75rem;text-align:right;"><button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-linea" data-id="' + l.id + '" data-position="left" data-tooltip="Eliminar" style="width:28px;height:28px;line-height:28px;"><i class="material-icons" style="font-size:1rem;">delete</i></button></td>';
                fila += '</tr>';
                tbody.append(fila);
            });

            $('#detalle-total').text('$' + total.toFixed(2));
            $('.tooltipped').tooltip();
        }).fail(function () {
            $('#tabla-lineas tbody').html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Error de conexión</td></tr>');
        });
    }

    // ================================================================
    // FUNCIÓN: abrirModalCrear()
    // PROPÓSITO: Abre el modal en modo creación (sin líneas).
    // ================================================================
    function abrirModalCrear() {
        $('#orden-id').val('');
        $('#form-orden')[0].reset();
        $('#orden-fecha').val(new Date().toISOString().slice(0, 10));
        $('#modal-orden-title').text('Nueva Solicitud');
        $('#orden-numero').attr('readonly', true);
        $('#orden-lineas-section').hide();
        $('#tabla-lineas tbody').empty();
        $('#detalle-total').text('$0.00');

        $.getJSON(API + 'siguienteNumero', function (r) {
            if (r.success) {
                $('#orden-numero').val(r.data.numero);
            }
        });

        $('#modal-orden').modal('open');
        M.updateTextFields();
        $('#orden-proveedor').formSelect();
        $('#orden-status').formSelect();
    }

    // ================================================================
    // FUNCIÓN: abrirModalEditar(id)
    // PROPÓSITO: Abre el modal en modo edición con líneas visibles.
    // ================================================================
    function abrirModalEditar(id) {
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            var o = r.data;

            $('#orden-id').val(o.id);
            $('#orden-numero').val(o.numero_de_orden);
            $('#orden-numero').removeAttr('readonly');
            $('#orden-fecha').val(o.fecha);
            $('#orden-proveedor').val(o.fk_proveedor);
            $('#orden-status').val(o.fk_status);
            $('#modal-orden-title').text('Solicitud #' + o.numero_de_orden);
            $('#orden-lineas-section').show();

            M.updateTextFields();
            $('#orden-proveedor').formSelect();
            $('#orden-status').formSelect();
            $('#modal-orden').modal('open');

            cargarLineas(id);
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    }

    // ================================================================
    // EVENTOS DE BOTONES DE LA TABLA
    // ================================================================

    $(document).on('click', '.btn-nuevo', function () {
        abrirModalCrear();
    });

    $(document).on('click', '.btn-editar-orden', function () {
        abrirModalEditar($(this).data('id'));
    });

    $(document).on('click', '.btn-ver-orden', function () {
        abrirModalEditar($(this).data('id'));
    });

    $(document).on('click', '.btn-eliminar-orden', function () {
        var id = $(this).data('id');
        var num = $(this).data('numero');
        if (confirm('Eliminar la solicitud #' + num + '? También se eliminarán sus productos asociados.')) {
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarTabla();
                    refrescarKPI();
                } else {
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json').fail(function () {
                EIS.toast('Error de conexión', 'red', 'error');
            });
        }
    });

    // ================================================================
    // EVENTOS DE FORMULARIOS
    // ================================================================

    $('#form-orden').on('submit', function (e) {
        e.preventDefault();
        var id = $('#orden-id').val();
        var esNueva = !id;
        var accion = esNueva ? 'crear' : 'actualizar';

        $.post(API + accion, $(this).serialize(), function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');

                if (esNueva && r.data && r.data.id) {
                    $('#orden-id').val(r.data.id);
                    $('#linea-orden-id').val(r.data.id);
                    $('#orden-lineas-section').show();
                    $('#tabla-lineas tbody').empty();
                    $('#detalle-total').text('$0.00');
                    refrescarTabla();
                    refrescarKPI();
                } else {
                    $('#modal-orden').modal('close');
                    refrescarTabla();
                    refrescarKPI();
                }
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // ================================================================
    // EVENTOS DE FILTROS Y BÚSQUEDA
    // ================================================================

    $('#searchProveedor').on('keyup', debounce(function () { aplicarFiltro(); }, 300));
    $('#filterEstadoProv').on('change', function () { aplicarFiltro(); });

    // ================================================================
    // EVENTOS DE LÍNEAS (PRODUCTOS DENTRO DE SOLICITUDES)
    // ================================================================

    $('#linea-producto').on('change', function () {
        var precio = $(this).find('option:selected').data('precio');
        if (precio) $('#linea-precio').val(precio);
    });

    $('#form-linea').on('submit', function (e) {
        e.preventDefault();
        var orden_id = $('#linea-orden-id').val();
        if (!orden_id) { EIS.toast('Seleccione una solicitud primero', 'red', 'error'); return; }

        $.post(API + 'agregarLinea', $(this).serialize() + '&orden_id=' + orden_id, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#linea-producto').val('');
                $('#linea-cantidad').val(1);
                $('#linea-precio').val('');
                $('#linea-producto').formSelect();
                cargarLineas(parseInt(orden_id));
            } else {
                EIS.toast(r.error || 'Error al agregar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    $(document).on('click', '.btn-eliminar-linea', function () {
        var id = $(this).data('id');
        if (confirm('Eliminar este producto de la solicitud?')) {
            $.post(API + 'eliminarLinea', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    var orden_id = $('#linea-orden-id').val();
                    if (orden_id) cargarLineas(parseInt(orden_id));
                } else {
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json').fail(function () {
                EIS.toast('Error de conexión', 'red', 'error');
            });
        }
    });

    // ================================================================
    // INICIALIZACIÓN DE COMPONENTES MATERIALIZE
    // ================================================================

    $('#linea-producto').formSelect();
    $('.modal').modal();
    $('.tooltipped').tooltip();
});
