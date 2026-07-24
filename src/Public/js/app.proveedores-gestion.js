// =====================================================================
// ARCHIVO: app.proveedores-gestion.js
// FUNCIÓN: Maneja la interactividad del módulo de Gestión de Proveedores.
//          CRUD completo de proveedores: listar, crear, editar, eliminar.
// =====================================================================

$(function () {
    var API = '?pagina=proveedores-gestion&action=';

    // ================================================================
    // FUNCIÓN: refrescarKPI()
    // PROPÓSITO: Actualiza el indicador del total de proveedores.
    // ================================================================
    function refrescarKPI() {
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return;
            $('#kpi-total').text(r.data.total);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: refrescarTabla()
    // PROPÓSITO: Recarga la tabla de proveedores desde el servidor.
    // ================================================================
    function refrescarTabla() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            var tbody = $('#tabla-proveedores tbody');
            tbody.empty();

            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">store</i>No hay proveedores registrados</td></tr>');
                $('.result-count').text('0 resultados');
                return;
            }

            $.each(r.data, function (i, p) {
                var inits = (p.nombre || '?').substring(0, 2).toUpperCase();
                var row = '<tr data-id="' + p.id + '" data-nombre="' + $('<span>').text(p.nombre).html() + '">';

                // Columna 1: Avatar + Nombre
                row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#42a5f5);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">' + inits + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(p.nombre).html() + '</div></div></div></td>';

                // Columna 2: RIF
                row += '<td style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">' + $('<span>').text(p.rif || '-').html() + '</td>';

                // Columna 3: Email
                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(p.email || '-').html() + '</td>';

                // Columna 4: Teléfono
                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(p.telefono || '-').html() + '</td>';

                // Columna 5: Acciones
                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-proveedor" data-id="' + p.id + '" data-position="left" data-tooltip="Editar proveedor"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-proveedor" data-id="' + p.id + '" data-nombre="' + $('<span>').text(p.nombre).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row);
            });

            $('.result-count').text(r.data.length + ' resultados');
            $('.tooltipped').tooltip();
            aplicarFiltro();
        }).fail(function () {
            EIS.toast('Error al cargar proveedores', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: aplicarFiltro()
    // PROPÓSITO: Filtra filas según texto de búsqueda.
    // ================================================================
    function aplicarFiltro() {
        var q = $('#searchProveedorGestion').val().toLowerCase();

        $('#tabla-proveedores tbody tr').each(function () {
            var mostrar = true;
            var texto = $(this).text().toLowerCase();
            if (q && texto.indexOf(q) === -1) mostrar = false;
            $(this).toggle(mostrar);
        });

        var visibles = $('#tabla-proveedores tbody tr:visible').length;
        var total = $('#tabla-proveedores tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    }

    // ================================================================
    // EVENTOS
    // ================================================================

    // Botón "Nuevo Proveedor"
    $(document).on('click', '.btn-nuevo-proveedor', function () {
        $('#proveedor-id').val('');
        $('#form-proveedor')[0].reset();
        $('#modal-proveedor-title').text('Nuevo Proveedor');
        M.updateTextFields();
        $('#modal-proveedor').modal('open');
    });

    // Botón "Editar" en cada fila
    $(document).on('click', '.btn-editar-proveedor', function () {
        var id = $(this).data('id');
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            var p = r.data;
            $('#proveedor-id').val(p.id);
            $('#proveedor-rif').val(p.rif);
            $('#proveedor-nombre').val(p.nombre);
            $('#proveedor-email').val(p.email);
            $('#proveedor-telefono').val(p.telefono);
            $('#modal-proveedor-title').text('Editar Proveedor: ' + p.nombre);
            M.updateTextFields();
            $('#modal-proveedor').modal('open');
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // Submit del formulario (crear o actualizar)
    $('#form-proveedor').on('submit', function (e) {
        e.preventDefault();

        var rif      = $('#proveedor-rif').val().trim();
        var nombre   = $('#proveedor-nombre').val().trim();
        var email    = $('#proveedor-email').val().trim();
        var telefono = $('#proveedor-telefono').val().trim();

        if (!rif || !nombre) {
            EIS.toast('RIF y Nombre son obligatorios', 'red', 'error');
            return;
        }

        if (rif.length < 5 || rif.length > 20) {
            EIS.toast('El RIF debe tener entre 5 y 20 caracteres', 'red', 'error');
            return;
        }

        if (nombre.length < 2 || nombre.length > 100) {
            EIS.toast('El nombre debe tener entre 2 y 100 caracteres', 'red', 'error');
            return;
        }

        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            EIS.toast('El formato del email no es válido', 'red', 'error');
            return;
        }

        if (email.length > 100) {
            EIS.toast('El email no puede exceder 100 caracteres', 'red', 'error');
            return;
        }

        if (telefono && telefono.length > 20) {
            EIS.toast('El teléfono no puede exceder 20 caracteres', 'red', 'error');
            return;
        }

        var id = $('#proveedor-id').val();
        var accion = id ? 'actualizar' : 'crear';
        $.post(API + accion, $(this).serialize(), function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-proveedor').modal('close');
                refrescarKPI();
                refrescarTabla();
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // Botón "Eliminar" en cada fila
    $(document).on('click', '.btn-eliminar-proveedor', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        if (confirm('Eliminar el proveedor "' + nombre + '"?\nNota: No se puede eliminar si tiene solicitudes asociadas.')) {
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

    // Búsqueda en tiempo real
    $('#searchProveedorGestion').on('keyup', debounce(function () { aplicarFiltro(); }, 300));

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    refrescarTabla();
    $('.modal').modal();
    $('.tooltipped').tooltip();
});
