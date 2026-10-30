/* ============================================================
   app.reportes.js
   Generador de reportes dinámicos: filtros contextuales,
   agrupación, orden, límite, resumen ejecutivo y descarga
   en CSV / Excel / PDF.
   ============================================================ */
(function ($) {
    'use strict';

    $(document).ready(function () {
        var $form = $('#formReporte');

        if (!$form.length) {
            return;
        }

        var $tabla = $('#tablaReporte');
        var $thead = $tabla.find('thead');
        var $tbody = $tabla.find('tbody');
        var $count = $('#reporteCount');
        var $vacio = $('#reporteVacio');
        var $error = $('#reporteError');
        var $resumen = $('#reporteResumen');
        var $resumenNota = $('#reporteResumenNota');
        var $contexto = $('#reporteContexto');
        var $presets = $('#reportePresets');

        var config = window.EIS_REPORTES_CONFIG || {};
        var timerConsulta = null;
        var xhrConsulta = null;

        // ------------------------------------------------------------
        // Utilidades
        // ------------------------------------------------------------

        function esc(valor) {
            return $('<span>').text(valor === null || valor === undefined ? '' : String(valor)).html();
        }

        function tipoSeleccionado() {
            return $form.find('[name="tipo"]').val() || 'ventas';
        }

        function configTipo() {
            return config[tipoSeleccionado()] || null;
        }

        function formatoSeleccionado() {
            return $form.find('[name="formato"]:checked').val() || 'pdf';
        }

        function reinitSelect(selector) {
            if (window.EIS && EIS.formSelect) {
                EIS.formSelect(selector);
            }
        }

        function dosDigitos(valor) {
            return valor < 10 ? '0' + valor : String(valor);
        }

        function fechaIso(fecha) {
            return fecha.getFullYear() + '-' + dosDigitos(fecha.getMonth() + 1) + '-' + dosDigitos(fecha.getDate());
        }

        function fechaVisible(valor) {
            var texto = String(valor || '');
            var partes = texto.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            return partes ? partes[3] + '/' + partes[2] + '/' + partes[1] : texto;
        }

        function datosFormulario() {
            var datos = {};
            $form.serializeArray().forEach(function (item) {
                datos[item.name] = item.value;
            });
            return datos;
        }

        function toast(mensaje, color) {
            if (typeof M !== 'undefined' && M.toast) {
                M.toast({
                    html: mensaje,
                    classes: (color || 'indigo') + ' rounded',
                    displayLength: 3000
                });
            }
        }

        // ------------------------------------------------------------
        // Controles dinámicos: agrupación, orden y filtros visibles
        // ------------------------------------------------------------

        function actualizarOrden() {
            var cfg = configTipo();
            if (!cfg) {
                return;
            }

            var grupo = $('#reporteAgrupar').val();
            var definicion = (cfg.grupos && cfg.grupos[grupo]) || null;
            var ordenes = (definicion && definicion.orden) || {};
            var claves = Object.keys(ordenes);
            var actual = $('#reporteOrden').val();

            if (claves.indexOf(actual) === -1) {
                actual = claves.length ? claves[0] : '';
            }

            var html = '';
            claves.forEach(function (clave) {
                html += '<option value="' + esc(clave) + '">' + esc(ordenes[clave]) + '</option>';
            });

            $('#reporteOrden').html(html).val(actual);
            reinitSelect('#reporteOrden');
        }

        function alternarFiltros() {
            var cfg = configTipo();
            if (!cfg) {
                return;
            }

            var filtros = cfg.filtros || [];

            $form.find('[data-filtro]').each(function () {
                var clave = $(this).attr('data-filtro');
                var visible = clave === 'fechas'
                    ? !!cfg.fechas
                    : filtros.indexOf(clave) !== -1;

                $(this).toggleClass('activo', visible);
            });
        }

        function actualizarControles() {
            var cfg = configTipo();
            if (!cfg) {
                return;
            }

            var grupos = cfg.grupos || {};
            var claves = Object.keys(grupos);
            var actual = $('#reporteAgrupar').val();

            if (claves.indexOf(actual) === -1) {
                actual = claves.length ? claves[0] : '';
            }

            var html = '';
            claves.forEach(function (clave) {
                html += '<option value="' + esc(clave) + '">' + esc(grupos[clave].label || clave) + '</option>';
            });

            $('#reporteAgrupar').html(html).val(actual);
            reinitSelect('#reporteAgrupar');

            actualizarOrden();
            alternarFiltros();
        }

        // ------------------------------------------------------------
        // Presets de fechas
        // ------------------------------------------------------------

        function aplicarPreset(preset) {
            var hoy = new Date();
            var inicio = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
            var fin = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

            switch (preset) {
                case 'hoy':
                    break;

                case '7dias':
                    inicio.setDate(inicio.getDate() - 6);
                    break;

                case '30dias':
                    inicio.setDate(inicio.getDate() - 29);
                    break;

                case 'mes_anterior':
                    inicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
                    fin = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
                    break;

                case 'ano':
                    inicio = new Date(hoy.getFullYear(), 0, 1);
                    break;

                case 'mes':
                default:
                    inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    break;
            }

            $('#fechaInicio').val(fechaIso(inicio));
            $('#fechaFin').val(fechaIso(fin));

            $presets.find('.date-preset').removeClass('active');
            $presets.find('.date-preset[data-preset="' + preset + '"]').addClass('active');

            programarConsulta();
        }

        // ------------------------------------------------------------
        // Consulta AJAX
        // ------------------------------------------------------------

        function fechasValidas(mostrarMensaje) {
            var cfg = configTipo();
            if (!cfg || !cfg.fechas) {
                return true;
            }

            var desde = $form.find('[name="desde"]').val();
            var hasta = $form.find('[name="hasta"]').val();

            if (!desde || !hasta) {
                if (mostrarMensaje) {
                    toast('Debe seleccionar el rango de fechas', 'red');
                }
                return false;
            }

            if (hasta < desde) {
                if (mostrarMensaje) {
                    toast('La fecha inicial no puede ser posterior a la final', 'red');
                }
                return false;
            }

            return true;
        }

        function programarConsulta() {
            clearTimeout(timerConsulta);
            timerConsulta = setTimeout(function () {
                ejecutarConsulta(true);
            }, 420);
        }

        function ejecutarConsulta(silenciosa) {
            if (!fechasValidas(!silenciosa)) {
                return;
            }

            var datos = datosFormulario();
            delete datos.formato;

            if (xhrConsulta) {
                xhrConsulta.abort();
            }

            $error.hide().text('');
            $count.text('Consultando...');

            xhrConsulta = $.ajax({
                url: '?pagina=reportes&action=consultar',
                method: 'GET',
                data: datos,
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.success) {
                        renderResultado(resp.data || {});
                    } else {
                        $error.show().text((resp && resp.error) || 'Error al consultar el reporte');
                    }
                },
                error: function (xhr, estado) {
                    if (estado === 'abort') {
                        return;
                    }
                    $error.show().text('Error de conexión al consultar el reporte.');
                },
                complete: function (xhr, estado) {
                    if (estado !== 'abort') {
                        $form.removeClass('reporte-cargando');
                    }
                }
            });
        }

        // ------------------------------------------------------------
        // Formato de celdas y resumen
        // ------------------------------------------------------------

        function esMoneda(columna) {
            return /(total|monto|valor|costo|venta|precio|promedio|ticket)/i.test(columna);
        }

        function esEntero(columna) {
            return /(stock|unidades|ordenes|órdenes|compras|artículos|articulos|productos|críticos|criticos|agotados|sesiones|activas|finalizadas|líneas|lineas|cantidad)/i.test(columna);
        }

        function esNumerico(valor) {
            return valor !== '' && valor !== null && valor !== undefined && isFinite(Number(valor));
        }

        function numeroLocal(numero, decimales) {
            var partes = Number(numero || 0).toFixed(decimales).split('.');
            partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return decimales > 0 ? partes[0] + ',' + partes[1] : partes[0];
        }

        function formatearResumen(item) {
            var valor = item.valor === null || item.valor === undefined ? '' : item.valor;

            switch (item.formato) {
                case 'money':
                    return '$' + numeroLocal(valor, 2);
                case 'int':
                    return numeroLocal(valor, 0);
                case 'decimal':
                    return numeroLocal(valor, 2);
                default:
                    return String(valor);
            }
        }

        function formatearCelda(valor, columna) {
            if (valor === null || valor === undefined) {
                return '';
            }

            var texto = String(valor).trim();
            if (texto === '') {
                return '';
            }

            if (/^(fecha|día|dia)$/i.test(columna) && /^\d{4}-\d{2}-\d{2}$/.test(texto)) {
                return fechaVisible(texto);
            }

            if (esMoneda(columna) && esNumerico(texto)) {
                return '$' + numeroLocal(texto, 2);
            }

            if (esEntero(columna) && esNumerico(texto)) {
                return numeroLocal(texto, 0);
            }

            return texto;
        }

        function badgeEstado(valor, columna) {
            if (!/estado|stock|sesión|sesion/i.test(columna) || valor === null || valor === undefined) {
                return '';
            }

            var clave = String(valor).trim().toLowerCase();
            var tonos = {
                'agotado': 'danger',
                'bajo': 'warning',
                'disponible': 'success',
                'activa': 'success',
                'finalizada': 'info',
                'pendiente': 'warning',
                'aprobado': 'info',
                'en tránsito': 'info',
                'en transito': 'info',
                'recibido parcial': 'success',
                'recibido completo': 'success',
                'rechazado': 'danger',
                'cancelado': 'danger',
                'crítico': 'danger',
                'critico': 'danger'
            };

            if (!tonos[clave]) {
                return '';
            }

            return '<span class="reporte-badge ' + tonos[clave] + '">' + esc(valor) + '</span>';
        }

        function valoresFila(fila) {
            if (Array.isArray(fila)) {
                return fila;
            }

            var valores = [];
            for (var clave in fila) {
                if (Object.prototype.hasOwnProperty.call(fila, clave)) {
                    valores.push(fila[clave]);
                }
            }
            return valores;
        }

        function celdaHtml(valor, columna) {
            var badge = badgeEstado(valor, columna);
            var clases = [];

            if (badge) {
                return '<td>' + badge + '</td>';
            }

            if (esMoneda(columna) || esEntero(columna)) {
                clases.push('reporte-numero');
            }

            return '<td' + (clases.length ? ' class="' + clases.join(' ') + '"' : '') + '>' +
                esc(formatearCelda(valor, columna)) + '</td>';
        }

        function renderResumen(items) {
            if (!Array.isArray(items) || !items.length) {
                $resumen.empty();
                $resumenNota.hide();
                return;
            }

            var tonos = ['primary', 'success', 'warning', 'info', 'danger'];
            var html = '';

            items.forEach(function (item) {
                if (!item || !item.label) {
                    return;
                }

                var tono = tonos.indexOf(item.tono) !== -1 ? item.tono : 'primary';
                var icono = /^[a-z0-9_]+$/i.test(item.icono || '') ? item.icono : 'insights';

                html += '<div class="reporte-resumen-card ' + tono + '">' +
                    '<i class="material-icons reporte-resumen-icon">' + icono + '</i>' +
                    '<div class="reporte-resumen-label">' + esc(item.label) + '</div>' +
                    '<div class="reporte-resumen-value">' + esc(formatearResumen(item)) + '</div>' +
                    '</div>';
            });

            $resumen.html(html);
            $resumenNota.toggle(!!html);
        }

        function iconoContexto(texto) {
            var clave = String(texto).toLowerCase();

            if (clave.indexOf('periodo') === 0) return 'date_range';
            if (clave.indexOf('agrupación') === 0) return 'account_tree';
            if (clave.indexOf('cliente') === 0) return 'person';
            if (clave.indexOf('vendedor') === 0) return 'badge';
            if (clave.indexOf('categoría') === 0) return 'category';
            if (clave.indexOf('producto') === 0) return 'inventory_2';
            if (clave.indexOf('proveedor') === 0) return 'local_shipping';
            if (clave.indexOf('estación') === 0) return 'desktop_windows';
            if (clave.indexOf('stock') === 0 || clave.indexOf('estado') === 0) return 'flag';
            if (clave.indexOf('monto') === 0) return 'payments';
            if (clave.indexOf('límite') === 0) return 'filter_list';
            if (clave.indexOf('reporte') === 0) return 'assessment';

            return 'tune';
        }

        function renderContexto(data) {
            var items = Array.isArray(data.contexto) ? data.contexto.slice() : [];
            var cfg = configTipo();
            var tipoNombre = $('#reporteTipo option:selected').text().trim();

            if (tipoNombre) {
                items.unshift('Reporte: ' + tipoNombre);
            }

            if (cfg && cfg.fechas) {
                var desde = $form.find('[name="desde"]').val();
                var hasta = $form.find('[name="hasta"]').val();

                if (desde && hasta) {
                    items.unshift('Periodo: ' + fechaVisible(desde) + ' al ' + fechaVisible(hasta));
                }
            }

            var html = '';
            items.forEach(function (texto) {
                html += '<span class="chip">' +
                    '<i class="material-icons">' + iconoContexto(texto) + '</i>' +
                    esc(texto) +
                    '</span>';
            });

            $contexto.html(html);
        }

        // ------------------------------------------------------------
        // Render de la tabla
        // ------------------------------------------------------------

        function renderResultado(data) {
            $error.hide().text('');
            $vacio.hide();

            var columnas = Array.isArray(data.columnas) ? data.columnas : [];
            var filas = Array.isArray(data.filas) ? data.filas : [];

            renderResumen(data.resumen);
            renderContexto(data);

            if (window.EIS && EIS.datatableDestroy) {
                EIS.datatableDestroy('#tablaReporte');
            }

            $thead.html('');
            $tbody.html('');

            if (!filas.length) {
                var textoVacio = filas.length + (filas.length === 1 ? ' resultado' : ' resultados');
                if (Number(data.limite) > 0) {
                    textoVacio += ' · límite ' + data.limite;
                }

                $count.text(textoVacio);
                $vacio.show().text('No hay datos para los criterios seleccionados.');
                return;
            }

            var headHtml = '<tr>';
            columnas.forEach(function (columna) {
                var clase = (esMoneda(columna) || esEntero(columna)) ? ' class="reporte-numero"' : '';
                headHtml += '<th' + clase + '>' + esc(columna) + '</th>';
            });
            headHtml += '</tr>';
            $thead.html(headHtml);

            var bodyHtml = '';
            filas.forEach(function (fila) {
                var valores = valoresFila(fila);
                bodyHtml += '<tr>';

                columnas.forEach(function (columna, indice) {
                    bodyHtml += celdaHtml(valores[indice], columna);
                });

                bodyHtml += '</tr>';
            });
            $tbody.html(bodyHtml);

            var texto = filas.length + (filas.length === 1 ? ' resultado' : ' resultados');
            if (Number(data.limite) > 0) {
                texto += ' · límite ' + data.limite;
            }
            $count.text(texto);

            if (window.EIS && EIS.datatable) {
                // order: [] conserva el orden estratégico devuelto por el servidor.
                EIS.datatable('#tablaReporte', { order: [] });
            }
        }

        // ------------------------------------------------------------
        // Eventos
        // ------------------------------------------------------------

        $form.on('submit', function (evento) {
            evento.preventDefault();
            ejecutarConsulta(false);
        });

        $form.on('change', 'select, input[type="radio"], input[type="date"], input[type="number"]', function () {
            var nombre = this.name;

            if (nombre === 'tipo') {
                actualizarControles();
                programarConsulta();
                return;
            }

            if (nombre === 'agrupar') {
                actualizarOrden();
                programarConsulta();
                return;
            }

            if (nombre === 'formato') {
                return;
            }

            if (this.type === 'date') {
                $presets.find('.date-preset').removeClass('active');
            }

            programarConsulta();
        });

        $form.on('input', 'input[type="date"], input[type="number"]', function () {
            if (this.type === 'date') {
                $presets.find('.date-preset').removeClass('active');
            }
            programarConsulta();
        });

        $presets.on('click', '.date-preset', function (evento) {
            evento.preventDefault();
            aplicarPreset($(this).attr('data-preset'));
        });

        $('#btnExportar').on('click', function () {
            if (!fechasValidas(true)) {
                return;
            }

            var params = $.extend({}, datosFormulario(), {
                pagina: 'reportes',
                action: 'exportar',
                csrf_token: (window.EIS && window.EIS.csrfToken) || ''
            });

            window.open('?' + $.param(params), '_blank');
        });

        $('#btnLimpiarReporte').on('click', function () {
            $form[0].reset();

            $form.find('select').each(function () {
                this.selectedIndex = 0;
            });

            reinitSelect($form.find('select'));
            actualizarControles();

            $presets.find('.date-preset').removeClass('active');
            $presets.find('.date-preset[data-preset="mes"]').addClass('active');

            $contexto.empty();
            $resumen.empty();
            $resumenNota.hide();
            $count.text('—');
            $thead.html('');
            $tbody.html('');
            $error.hide().text('');
            $vacio.show().text('Selecciona un tipo de reporte y consulta para ver los datos.');

            if (window.EIS && EIS.datatableDestroy) {
                EIS.datatableDestroy('#tablaReporte');
            }

            ejecutarConsulta(false);
        });

        // ------------------------------------------------------------
        // Inicio
        // ------------------------------------------------------------

        actualizarControles();
        timerConsulta = setTimeout(function () {
            ejecutarConsulta(true);
        }, 250);
    });
})(jQuery);
