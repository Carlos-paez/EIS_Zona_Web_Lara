/* ============================================================
   app.reportes.js
   Generador de reportes interactivos y analíticos:
   - Filtros dinámicos según módulo (categoría, cliente, proveedor, status).
   - Presets de fecha rápidos (Hoy, Ayer, 7 días, Este Mes, Año).
   - Tarjetas de resumen ejecutivo (KPIs) por consulta.
   - Tabla interactiva con DataTables y exportación multiplataforma.
   ============================================================ */
(function ($) {
    'use strict';

    $(document).ready(function () {
        var $form = $('#formReporte');
        var $tabla = $('#tablaReporte');
        var $thead = $tabla.find('thead');
        var $tbody = $tabla.find('tbody');
        var $count = $('#reporteCount');
        var $vacio = $('#reporteVacio');
        var $error = $('#reporteError');
        var $loading = $('#reporteLoading');
        var $resumenCards = $('#reporteResumenCards');
        var $tituloVisible = $('#reporteTituloVisible');
        var $subtituloVisible = $('#reporteSubtituloVisible');

        if (!$form.length) {
            return;
        }

        // Inicializar selector de Materialize
        if (typeof M !== 'undefined' && M.FormSelect && window.EIS) {
            EIS.formSelect('#reporteTipo');
        }

        // Cargar catálogos para filtros adicionales vía AJAX
        cargarCatalogosFiltros();

        function tipoSeleccionado() {
            return $form.find('[name="tipo"]').val() || 'ventas';
        }

        function formatoSeleccionado() {
            return $form.find('[name="formato"]:checked').val() || 'pdf';
        }

        function tipoSinFechas() {
            var tipo = tipoSeleccionado();
            return tipo === 'inventario' || tipo === 'stock_bajo' || tipo === 'cyber';
        }

        // Control visual de filtros contextuales
        function actualizarFiltrosContextuales() {
            var tipo = tipoSeleccionado();
            var sinFechas = tipoSinFechas();
            var $desde = $form.find('[name="desde"]');
            var $hasta = $form.find('[name="hasta"]');
            var $rowFechas = $form.find('.fechas-row');

            // Visibilidad de fechas
            if (sinFechas) {
                $rowFechas.hide();
                $desde.removeAttr('required');
                $hasta.removeAttr('required');
            } else {
                $rowFechas.show();
                $desde.attr('required', 'required');
                $hasta.attr('required', 'required');
            }

            // Ocultar todos los filtros extras primero
            $('.filtro-extra').hide();

            // Activar filtros correspondientes según tipo
            if (tipo === 'ventas_por_producto' || tipo === 'inventario' || tipo === 'stock_bajo' || tipo === 'movimientos' || tipo === 'rentabilidad') {
                $('#filtroCategoriaContainer').show();
            }
            if (tipo === 'ventas' || tipo === 'ventas_por_cliente') {
                $('#filtroClienteContainer').show();
            }
            if (tipo === 'proveedores' || tipo === 'compras') {
                $('#filtroProveedorContainer').show();
                $('#filtroStatusContainer').show();
            }
            if (tipo === 'cyber') {
                $('#filtroCyberContainer').show();
            }

            // Actualizar textos informativos del visor
            var textoTipo = $('#reporteTipo option:selected').text();
            $subtituloVisible.text(textoTipo);
        }

        function cargarCatalogosFiltros() {
            $.ajax({
                url: '?pagina=reportes&action=filtros',
                method: 'GET',
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.success && resp.data) {
                        poblarSelect('#filtroCategoria', resp.data.categorias, 'id', 'nombre_categoria', 'Todas las categorías');
                        poblarSelect('#filtroCliente', resp.data.clientes, 'id', 'cliente', 'Todos los clientes');
                        poblarSelect('#filtroProveedor', resp.data.proveedores, 'id', 'nombre', 'Todos los proveedores');
                        poblarSelect('#filtroStatus', resp.data.estados_solicitud, 'id', 'status', 'Todos los estados');
                    }
                }
            });
        }

        function poblarSelect(selector, lista, valKey, textKey, defaultText) {
            var $sel = $(selector);
            if (!$sel.length) return;
            $sel.empty().append($('<option>').val('').text(defaultText));
            (lista || []).forEach(function (item) {
                var text = item[textKey];
                if (item.cedula) text += ' (' + item.cedula + ')';
                if (item.rif) text += ' [' + item.rif + ']';
                $sel.append($('<option>').val(item[valKey]).text(text));
            });
            if (window.EIS && EIS.formSelect) {
                EIS.formSelect(selector);
            }
        }

        // Manejo de botones de presets de fechas
        $('.chip-preset').on('click', function () {
            $('.chip-preset').removeClass('active');
            $(this).addClass('active');

            var range = $(this).data('range');
            var hoy = new Date();
            var yyyy = hoy.getFullYear();
            var mm = String(hoy.getMonth() + 1).padStart(2, '0');
            var dd = String(hoy.getDate()).padStart(2, '0');

            var desde = '';
            var hasta = yyyy + '-' + mm + '-' + dd;

            if (range === 'hoy') {
                desde = hasta;
            } else if (range === 'ayer') {
                var ayer = new Date(hoy);
                ayer.setDate(hoy.getDate() - 1);
                desde = ayer.toISOString().split('T')[0];
                hasta = desde;
            } else if (range === 'semana') {
                var hace7 = new Date(hoy);
                hace7.setDate(hoy.getDate() - 7);
                desde = hace7.toISOString().split('T')[0];
            } else if (range === 'mes') {
                desde = yyyy + '-' + mm + '-01';
            } else if (range === 'mes_anterior') {
                var mesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
                var ultimoDiaAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
                desde = mesAnt.toISOString().split('T')[0];
                hasta = ultimoDiaAnt.toISOString().split('T')[0];
            } else if (range === 'ano') {
                desde = yyyy + '-01-01';
            }

            $('#fechaInicio').val(desde);
            $('#fechaFin').val(hasta);
        });

        function fechasValidas() {
            if (tipoSinFechas()) {
                return true;
            }
            var desde = $form.find('[name="desde"]').val();
            var hasta = $form.find('[name="hasta"]').val();
            if (!desde || !hasta) {
                M.toast({ html: 'Debe seleccionar el rango de fechas', classes: 'red darken-2' });
                return false;
            }
            if (hasta < desde) {
                M.toast({ html: 'La fecha inicial no puede ser posterior a la final', classes: 'red darken-2' });
                return false;
            }
            return true;
        }

        function renderResumenKPIs(resumen) {
            $resumenCards.empty().hide();
            if (!resumen || typeof resumen !== 'object') {
                return;
            }

            var fmtMoneda = function (num) {
                return '$' + Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            var items = [];
            if (resumen.total_monto !== undefined) {
                items.push({ label: 'Monto Total Facturado', val: fmtMoneda(resumen.total_monto), icon: 'attach_money', color: 'indigo' });
            }
            if (resumen.ticket_promedio !== undefined) {
                items.push({ label: 'Ticket Promedio', val: fmtMoneda(resumen.ticket_promedio), icon: 'trending_up', color: 'teal' });
            }
            if (resumen.total_articulos !== undefined) {
                items.push({ label: 'Total Unidades / Artículos', val: Number(resumen.total_articulos).toLocaleString(), icon: 'shopping_bag', color: 'blue' });
            }
            if (resumen.total_unidades !== undefined) {
                items.push({ label: 'Unidades Movidas', val: Number(resumen.total_unidades).toLocaleString(), icon: 'widgets', color: 'cyan' });
            }
            if (resumen.valor_venta !== undefined) {
                items.push({ label: 'Valor Inventario (Venta)', val: fmtMoneda(resumen.valor_venta), icon: 'store', color: 'green' });
            }
            if (resumen.valor_costo !== undefined) {
                items.push({ label: 'Valor Inventario (Costo)', val: fmtMoneda(resumen.valor_costo), icon: 'account_balance_wallet', color: 'blue-grey' });
            }
            if (resumen.margen_potencial !== undefined) {
                items.push({ label: 'Margen Bruto Potencial', val: fmtMoneda(resumen.margen_potencial), icon: 'savings', color: 'amber darken-2' });
            }
            if (resumen.unidades_faltantes !== undefined) {
                items.push({ label: 'Unidades Faltantes', val: Number(resumen.unidades_faltantes).toLocaleString(), icon: 'warning', color: 'red' });
            }
            if (resumen.costo_reposicion !== undefined) {
                items.push({ label: 'Inversión en Reposición', val: fmtMoneda(resumen.costo_reposicion), icon: 'add_shopping_cart', color: 'deep-orange' });
            }
            if (resumen.utilidad_neta !== undefined) {
                items.push({ label: 'Ganancia / Utilidad', val: fmtMoneda(resumen.utilidad_neta), icon: 'monetization_on', color: 'green darken-1' });
            }
            if (resumen.margen_global !== undefined) {
                items.push({ label: 'Margen Global', val: resumen.margen_global + '%', icon: 'percent', color: 'purple' });
            }
            if (resumen.sesiones_activas !== undefined) {
                items.push({ label: 'Sesiones Activas', val: resumen.sesiones_activas, icon: 'computer', color: 'orange darken-2' });
            }
            if (resumen.total_sesiones !== undefined) {
                items.push({ label: 'Total Sesiones', val: resumen.total_sesiones, icon: 'history', color: 'indigo' });
            }

            if (!items.length) {
                return;
            }

            var colSize = items.length <= 2 ? 's12 m6' : (items.length === 3 ? 's12 m4' : 's12 m6 l3');
            var html = '';
            items.slice(0, 4).forEach(function (it) {
                html += '<div class="col ' + colSize + '">';
                html += '  <div class="card-panel z-depth-1" style="border-radius:10px;padding:0.85rem 1rem;display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;background:var(--surface);border-left:4px solid var(--primary);">';
                html += '    <div style="width:40px;height:40px;border-radius:8px;background:rgba(57,73,171,0.12);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;">';
                html += '      <i class="material-icons">' + it.icon + '</i>';
                html += '    </div>';
                html += '    <div style="overflow:hidden;">';
                html += '      <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;">' + $('<span>').text(it.label).html() + '</div>';
                html += '      <div style="font-size:1.25rem;font-weight:700;color:var(--text);">' + $('<span>').text(it.val).html() + '</div>';
                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });

            $resumenCards.html(html).slideDown(200);
        }

        function renderResultado(data) {
            $loading.hide();
            $error.hide();
            $vacio.hide();
            $tabla.show();

            var columnas = data.columnas || [];
            var filas = data.filas || [];

            // Actualizar tarjetas de resumen analítico
            renderResumenKPIs(data.resumen);

            // Destruye DataTables previo para actualizar el número y títulos de columnas
            if (window.EIS && EIS.datatableDestroy) {
                EIS.datatableDestroy('#tablaReporte');
            }

            if (!filas.length) {
                $thead.html('');
                $tbody.html('');
                $count.text('0');
                $tabla.hide();
                $vacio.show().html('<i class="material-icons" style="font-size:3rem;opacity:0.4;display:block;margin-bottom:0.5rem;">find_in_page</i>No se encontraron registros para los criterios y filtros especificados.');
                return;
            }

            var headHtml = '<tr>';
            columnas.forEach(function (col) {
                headHtml += '<th style="white-space:nowrap;">' + $('<span>').text(col).html() + '</th>';
            });
            headHtml += '</tr>';
            $thead.html(headHtml);

            var bodyHtml = '';
            filas.forEach(function (fila) {
                var valores = Array.isArray(fila) ? fila : Object.values(fila);
                bodyHtml += '<tr>';
                columnas.forEach(function (col, i) {
                    var val = valores[i] !== undefined && valores[i] !== null ? String(valores[i]) : '';
                    // Resaltar badges de estados comunes
                    if (val === 'Activa' || val === 'Aprobado' || val === 'Recibido Completo') {
                        val = '<span class="badge green white-text" style="float:none;border-radius:4px;padding:2px 8px;font-size:0.8rem;">' + val + '</span>';
                    } else if (val === 'Finalizada' || val === 'Cancelado') {
                        val = '<span class="badge grey lighten-1 white-text" style="float:none;border-radius:4px;padding:2px 8px;font-size:0.8rem;">' + val + '</span>';
                    } else if (val === 'Pendiente') {
                        val = '<span class="badge orange white-text" style="float:none;border-radius:4px;padding:2px 8px;font-size:0.8rem;">' + val + '</span>';
                    } else {
                        val = $('<span>').text(val).html();
                    }
                    bodyHtml += '<td>' + val + '</td>';
                });
                bodyHtml += '</tr>';
            });
            $tbody.html(bodyHtml);
            $count.text(filas.length);

            // Inicializar DataTables
            if (window.EIS && EIS.datatable) {
                EIS.datatable('#tablaReporte');
            }
        }

        // Listener de cambio de reporte
        actualizarFiltrosContextuales();
        $form.find('[name="tipo"]').on('change', function () {
            actualizarFiltrosContextuales();
        });

        // Submit de consulta
        $form.on('submit', function (e) {
            e.preventDefault();
            if (!fechasValidas()) {
                return;
            }

            $tabla.hide();
            $vacio.hide();
            $error.hide();
            $loading.show();

            var params = {
                tipo: tipoSeleccionado()
            };
            if (!tipoSinFechas()) {
                params.desde = $form.find('[name="desde"]').val();
                params.hasta = $form.find('[name="hasta"]').val();
            }

            // Recoger filtros adicionales si están visibles
            if ($('#filtroCategoriaContainer').is(':visible') && $('#filtroCategoria').val()) {
                params.categoria_id = $('#filtroCategoria').val();
            }
            if ($('#filtroClienteContainer').is(':visible') && $('#filtroCliente').val()) {
                params.cliente_id = $('#filtroCliente').val();
            }
            if ($('#filtroProveedorContainer').is(':visible') && $('#filtroProveedor').val()) {
                params.proveedor_id = $('#filtroProveedor').val();
            }
            if ($('#filtroStatusContainer').is(':visible') && $('#filtroStatus').val()) {
                params.status_id = $('#filtroStatus').val();
            }
            if ($('#filtroCyberContainer').is(':visible') && $('#filtroCyber').val() !== '') {
                params.estado_cyber = $('#filtroCyber').val();
            }

            $.ajax({
                url: '?pagina=reportes&action=consultar',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function (resp) {
                    $loading.hide();
                    if (resp && resp.success) {
                        renderResultado(resp.data);
                    } else {
                        $error.show().text((resp && resp.error) || 'Error al consultar el reporte.');
                    }
                },
                error: function (xhr) {
                    $loading.hide();
                    var errMsg = 'Error de conexión al consultar el reporte.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errMsg = xhr.responseJSON.error;
                    }
                    $error.show().text(errMsg);
                }
            });
        });

        // Exportación de archivos (CSV / Excel / PDF)
        $('#btnExportar').on('click', function () {
            if (!fechasValidas()) {
                return;
            }
            var paramsObj = {
                pagina: 'reportes',
                action: 'exportar',
                tipo: tipoSeleccionado(),
                formato: formatoSeleccionado(),
                csrf_token: (window.EIS && window.EIS.csrfToken) ? window.EIS.csrfToken : ''
            };

            if (!tipoSinFechas()) {
                paramsObj.desde = $form.find('[name="desde"]').val();
                paramsObj.hasta = $form.find('[name="hasta"]').val();
            }

            if ($('#filtroCategoriaContainer').is(':visible') && $('#filtroCategoria').val()) {
                paramsObj.categoria_id = $('#filtroCategoria').val();
            }
            if ($('#filtroClienteContainer').is(':visible') && $('#filtroCliente').val()) {
                paramsObj.cliente_id = $('#filtroCliente').val();
            }
            if ($('#filtroProveedorContainer').is(':visible') && $('#filtroProveedor').val()) {
                paramsObj.proveedor_id = $('#filtroProveedor').val();
            }
            if ($('#filtroStatusContainer').is(':visible') && $('#filtroStatus').val()) {
                paramsObj.status_id = $('#filtroStatus').val();
            }
            if ($('#filtroCyberContainer').is(':visible') && $('#filtroCyber').val() !== '') {
                paramsObj.estado_cyber = $('#filtroCyber').val();
            }

            var params = $.param(paramsObj);
            window.open('?' + params, '_blank');
        });

        // Ejecutar primera consulta automática al cargar
        $form.trigger('submit');
    });
})(jQuery);
