// =====================================================================
// ARCHIVO: app.proveedores.js
// FUNCIÓN: Maneja la interactividad del módulo de Proveedores/Solicitudes.
//          Se comunica con el controlador PHP mediante AJAX y permite:
//          - CRUD de solicitudes/órdenes de compra
//          - Gestión de líneas (productos) dentro de cada solicitud
//          - Filtros y búsqueda en tabla de solicitudes
//          - Carga de detalle de solicitud con productos asociados
// =====================================================================

// Espero a que el DOM esté listo para ejecutar el código
$(function () {
    // URL base de la API del módulo de proveedores
    var API = '?pagina=proveedores&action=';

    // ================================================================
    // FUNCIÓN: refrescarKPI()
    // PROPÓSITO: Actualiza los indicadores (KPIs) de la cabecera:
    //            total de solicitudes, pendientes, recibidas y
    //            proveedores registrados.
    // ================================================================
    function refrescarKPI() {
        // GET request al servidor para obtener los KPIs
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return; // Si hay error, salgo
            // Actualizo cada tarjeta KPI con los valores recibidos
            $('#kpi-total').text(r.data.total);           // Total de solicitudes
            $('#kpi-pendientes').text(r.data.pendientes); // Solicitudes pendientes
            $('#kpi-recibidas').text(r.data.recibidas);   // Solicitudes recibidas
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: refrescarTabla()
    // PROPÓSITO: Recarga la tabla de solicitudes desde el servidor
    //            vía AJAX. Renderiza cada fila con número de orden,
    //            proveedor, RIF, fecha, estado y botones de acción.
    // ================================================================
    function refrescarTabla() {
        // GET request para obtener todas las solicitudes
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return; // Si hay error, salgo

            var tbody = $('#tabla-ordenes tbody'); // <tbody> de la tabla
            tbody.empty(); // Limpio el contenido actual

            // Si no hay solicitudes, muestro mensaje de tabla vacía
            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">request_quote</i>No hay solicitudes registradas</td></tr>');
                $('.result-count').text('0 resultados');
                return;
            }

            // Recorro cada solicitud recibida
            $.each(r.data, function (i, o) {
                // Determino el color del badge según el estado
                var color = 'grey';
                var estado = o.estado || 'Sin estado';
                var e = estado.toLowerCase();
                if (e.indexOf('pend') !== -1) color = 'orange';     // Pendiente -> naranja
                else if (e.indexOf('recib') !== -1) color = 'green'; // Recibida -> verde
                else if (e.indexOf('cancel') !== -1) color = 'red'; // Cancelada -> rojo

                // Iniciales del proveedor para el avatar circular
                var inits = (o.proveedor_nombre || '?').substring(0, 2).toUpperCase();

                // --- CONSTRUCCIÓN DE LA FILA ---
                var row = '<tr data-id="' + o.id + '" data-numero="' + $('<span>').text(o.numero_de_orden).html() + '">';

                // Columna 1: Número de orden
                row += '<td style="padding:0.75rem 1rem;"><strong>#' + $('<span>').text(o.numero_de_orden).html() + '</strong></td>';

                // Columna 2: Avatar + Nombre del proveedor
                row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#42a5f5);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">' + inits + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(o.proveedor_nombre || 'Sin proveedor').html() + '</div></div></div></td>';

                // Columna 3: RIF del proveedor
                row += '<td style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">' + $('<span>').text(o.rif || '-').html() + '</td>';

                // Columna 4: Fecha de la solicitud
                row += '<td style="padding:0.75rem 1rem;font-size:0.85rem;">' + $('<span>').text(o.fecha).html() + '</td>';

                // Columna 5: Badge de estado con color
                row += '<td style="padding:0.75rem 1rem;"><span class="new badge ' + color + '" data-badge-caption="">' + $('<span>').text(estado).html() + '</span></td>';

                // Columna 6: Botones de acción (Editar, Detalle, Eliminar)
                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-orden" data-id="' + o.id + '" data-position="left" data-tooltip="Editar solicitud"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light grey tooltipped btn-detalle-orden" data-id="' + o.id + '" data-position="left" data-tooltip="Ver detalles" style="margin-left:4px;"><i class="material-icons">visibility</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-orden" data-id="' + o.id + '" data-numero="' + $('<span>').text(o.numero_de_orden).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row); // Agrego la fila al tbody
            });

            // Actualizo contador de resultados
            $('.result-count').text(r.data.length + ' resultados');
            // Reinicio tooltips para los nuevos botones
            $('.tooltipped').tooltip();
            // Aplico filtros activos
            aplicarFiltro();
        }).fail(function () {
            EIS.toast('Error al cargar solicitudes', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: aplicarFiltro()
    // PROPÓSITO: Filtra las filas de la tabla según el texto de
    //            búsqueda y el filtro de estado seleccionado.
    // ================================================================
    function aplicarFiltro() {
        var q = $('#searchProveedor').val().toLowerCase(); // Texto de búsqueda
        var estadoFiltro = $('#filterEstadoProv').val();   // Filtro de estado

        // Recorro cada fila de la tabla de solicitudes
        $('#tabla-ordenes tbody tr').each(function () {
            var mostrar = true; // Por defecto se muestra
            var texto = $(this).text().toLowerCase(); // Texto completo de la fila

            // Filtro por texto de búsqueda
            if (q && texto.indexOf(q) === -1) mostrar = false;

            // Filtro por estado (columna 4, índice 4)
            if (estadoFiltro) {
                var badge = $(this).find('td').eq(4).text().trim().toLowerCase();
                if (badge.indexOf(estadoFiltro) === -1) mostrar = false;
            }

            $(this).toggle(mostrar); // Muestro/oculto según condiciones
        });
    }

    // ================================================================
    // FUNCIÓN: cargarDetalle(id)
    // PROPÓSITO: Abre el modal de detalle de una solicitud y carga
    //            las líneas (productos) asociadas vía AJAX.
    // PARÁMETROS:
    //   id - ID de la solicitud a mostrar
    // ================================================================
    function cargarDetalle(id) {
        // Muestro mensaje de carga en el tbody de líneas
        $('#tabla-lineas tbody').html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Cargando...</td></tr>');
        $('#modal-detalle').modal('open'); // Abro el modal de detalle
        $('#linea-orden-id').val(id); // Guardo el ID de la orden para agregar líneas

        // GET request para obtener las líneas de la solicitud
        $.getJSON(API + 'lineas&orden_id=' + id, function (r) {
            var tbody = $('#tabla-lineas tbody');
            tbody.empty(); // Limpio el contenido actual

            if (!r.success) {
                tbody.html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Error al cargar detalles</td></tr>');
                return;
            }

            // Muestro información de la cabecera de la orden
            if (r.data.orden) {
                $('#modal-detalle-title').text('Solicitud #' + r.data.orden.numero_de_orden);
                $('#modal-detalle-info').text('Proveedor: ' + (r.data.orden.proveedor_nombre || 'N/A') + ' | Estado: ' + (r.data.orden.estado || 'N/A'));
            }

            var lineas = r.data.lineas || []; // Arreglo de líneas/productos
            if (lineas.length === 0) {
                // Si no hay productos, muestro mensaje
                tbody.html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Sin productos agregados</td></tr>');
                $('#detalle-total').text('$0.00');
                return;
            }

            var total = 0; // Acumulador del total de la solicitud
            // Recorro cada línea/producto
            $.each(lineas, function (i, l) {
                var subtotal = l.cantidad * l.precio; // Calculo subtotal
                total += subtotal; // Acumulo al total general

                // Construyo la fila con los datos del producto
                var fila = '<tr data-id="' + l.id + '">';
                fila += '<td style="padding:0.5rem 0.75rem;">' + $('<span>').text(l.producto_nombre || 'Producto #' + l.producto_id).html() + '</td>'; // Nombre del producto
                fila += '<td style="padding:0.5rem 0.75rem;">' + l.cantidad + '</td>'; // Cantidad
                fila += '<td style="padding:0.5rem 0.75rem;">$' + parseFloat(l.precio).toFixed(2) + '</td>'; // Precio unitario
                fila += '<td style="padding:0.5rem 0.75rem;">$' + subtotal.toFixed(2) + '</td>'; // Subtotal
                // Botón eliminar línea
                fila += '<td style="padding:0.5rem 0.75rem;text-align:right;"><button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-linea" data-id="' + l.id + '" data-position="left" data-tooltip="Eliminar" style="width:28px;height:28px;line-height:28px;"><i class="material-icons" style="font-size:1rem;">delete</i></button></td>';
                fila += '</tr>';
                tbody.append(fila);
            });

            // Muestro el total general formateado
            $('#detalle-total').text('$' + total.toFixed(2));
            // Reinicio tooltips para los nuevos botones
            $('.tooltipped').tooltip();
        }).fail(function () {
            $('#tabla-lineas tbody').html('<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Error de conexión</td></tr>');
        });
    }

    // ================================================================
    // EVENTOS DE BOTONES DE LA TABLA
    // ================================================================

    // Botón "Nuevo" - Abre modal para crear nueva solicitud
$(document).on('click', '.btn-nuevo', function () {
    $('#orden-id').val('');
    $('#form-orden')[0].reset();
    $('#orden-fecha').val(new Date().toISOString().slice(0, 10));
    $('#modal-orden-title').text('Nueva Solicitud');
    // --- AQUÍ VA LA LLAMADA AJAX ---
    $.getJSON(API + 'siguienteNumero', function (r) {
        if (r.success) {
            $('#orden-numero').val(r.data.numero);  // Auto-rellena el campo
            $('#orden-numero').attr('readonly', true);
        }
    });
    $('#modal-orden').modal('open');
    M.updateTextFields();
    $('#orden-proveedor').formSelect();
    $('#orden-status').formSelect();
});

    // Botón "Editar" en cada fila - Carga datos y abre modal de edición
    $(document).on('click', '.btn-editar-orden', function () {
        var id = $(this).data('id');
        // Cargo los datos de la solicitud vía AJAX
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            var o = r.data; // Datos de la solicitud
            // Lleno los campos del formulario
            $('#orden-id').val(o.id);                     // ID de la orden
            $('#orden-numero').val(o.numero_de_orden);    // Número de orden
            $('#orden-numero').removeAttr('readonly');
            $('#orden-fecha').val(o.fecha);               // Fecha
            $('#orden-proveedor').val(o.fk_proveedor);    // Proveedor (FK)
            $('#orden-status').val(o.fk_status);          // Estado (FK)
            $('#modal-orden-title').text('Editar Solicitud #' + o.numero_de_orden); // Título
            M.updateTextFields(); // Actualizo labels
            $('#orden-proveedor').formSelect(); // Inicializo selects
            $('#orden-status').formSelect();
            $('#modal-orden').modal('open'); // Abro modal
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // Botón "Detalle" en cada fila - Abre modal con productos de la solicitud
    $(document).on('click', '.btn-detalle-orden', function () {
        cargarDetalle($(this).data('id'));
    });

    // Botón "Eliminar" en cada fila - Confirma y elimina la solicitud
    $(document).on('click', '.btn-eliminar-orden', function () {
        var id = $(this).data('id');
        var num = $(this).data('numero');
        // Confirmación con advertencia de eliminación en cascada
        if (confirm('Eliminar la solicitud #' + num + '? También se eliminarán sus productos asociados.')) {
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarTabla(); // Recargo tabla
                    refrescarKPI();   // Actualizo KPIs
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

    // Submit del formulario de solicitud (crear o actualizar)
    $('#form-orden').on('submit', function (e) {
        e.preventDefault(); // Evito envío tradicional
        var id = $('#orden-id').val();
        var accion = id ? 'actualizar' : 'crear'; // Determino acción por ID
        // POST request para crear o actualizar
        $.post(API + accion, $(this).serialize(), function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-orden').modal('close'); // Cierro modal
                refrescarTabla(); // Recargo tabla
                refrescarKPI();   // Actualizo KPIs
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

    // Búsqueda en tiempo real en campo #searchProveedor
    $('#searchProveedor').on('keyup', debounce(function () { aplicarFiltro(); }, 300));
    // Cambio en filtro de estado de solicitudes
    $('#filterEstadoProv').on('change', function () { aplicarFiltro(); });

    // ================================================================
    // EVENTOS DE LÍNEAS (PRODUCTOS DENTRO DE SOLICITUDES)
    // ================================================================

    // Cuando se selecciona un producto en el select, autocompleto el precio
    $('#linea-producto').on('change', function () {
        var precio = $(this).find('option:selected').data('precio'); // Obtengo precio del data-precio
        if (precio) $('#linea-precio').val(precio); // Si existe, lo pongo en el campo
    });

    // Submit del formulario para agregar línea/producto a una solicitud
    $('#form-linea').on('submit', function (e) {
        e.preventDefault();
        var orden_id = $('#linea-orden-id').val(); // ID de la orden actual
        if (!orden_id) { EIS.toast('Seleccione una solicitud primero', 'red', 'error'); return; }

        // POST request para agregar la línea
        $.post(API + 'agregarLinea', $(this).serialize() + '&orden_id=' + orden_id, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                // Limpio el formulario de línea
                $('#linea-producto').val('');
                $('#linea-cantidad').val(1);
                $('#linea-precio').val('');
                $('#linea-producto').formSelect(); // Reinicio el select
                cargarDetalle(parseInt(orden_id)); // Recargo el detalle
            } else {
                EIS.toast(r.error || 'Error al agregar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // Click en botón eliminar línea/producto
    $(document).on('click', '.btn-eliminar-linea', function () {
        var id = $(this).data('id');
        if (confirm('Eliminar este producto de la solicitud?')) {
            $.post(API + 'eliminarLinea', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    var orden_id = $('#linea-orden-id').val();
                    if (orden_id) cargarDetalle(parseInt(orden_id)); // Recargo detalle
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
