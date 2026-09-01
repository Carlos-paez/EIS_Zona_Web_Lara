/* ============================================================
   app.reportes.js
   Generador de reportes: consulta AJAX y descarga de resultados
   en CSV / Excel / PDF.
   ============================================================ */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Inicializa el select de Materialize
        if (typeof M !== 'undefined' && M.FormSelect) {
            $('#reporteTipo').formSelect();
        }

        var $form = $('#formReporte');
        var $tabla = $('#tablaReporte');
        var $thead = $tabla.find('thead');
        var $tbody = $tabla.find('tbody');
        var $count = $('#reporteCount');
        var $vacio = $('#reporteVacio');
        var $error = $('#reporteError');

        if (!$form.length) {
            return;
        }

        function tipoSeleccionado() {
            return $form.find('[name="tipo"]').val() || 'ventas';
        }

        function formatoSeleccionado() {
            return $form.find('[name="formato"]:checked').val() || 'pdf';
        }

        function fechasValidas() {
            var desde = $form.find('[name="desde"]').val();
            var hasta = $form.find('[name="hasta"]').val();
            if (!desde || !hasta) {
                M.toast({ html: 'Debe seleccionar el rango de fechas', classes: 'red' });
                return false;
            }
            if (hasta < desde) {
                M.toast({ html: 'La fecha inicial no puede ser posterior a la final', classes: 'red' });
                return false;
            }
            return true;
        }

        function renderResultado(data) {
            $error.hide();
            $vacio.hide();
            var columnas = data.columnas || [];
            var filas = data.filas || [];

            if (!filas.length) {
                $thead.html('');
                $tbody.html('');
                $count.text('0 resultados');
                $vacio.show().text('No hay datos para los criterios seleccionados.');
                return;
            }

            var headHtml = '<tr>';
            columnas.forEach(function (col) {
                headHtml += '<th>' + $('<span>').text(col).html() + '</th>';
            });
            headHtml += '</tr>';
            $thead.html(headHtml);

            var bodyHtml = '';
            filas.forEach(function (fila) {
                bodyHtml += '<tr>';
                columnas.forEach(function (col, i) {
                    var value = fila[i] !== undefined ? fila[i] : '';
                    bodyHtml += '<td>' + $('<span>').text(value).html() + '</td>';
                });
                bodyHtml += '</tr>';
            });
            $tbody.html(bodyHtml);
            $count.text(filas.length + ' resultados');
        }

        $form.on('submit', function (e) {
            e.preventDefault();
            if (!fechasValidas()) {
                return;
            }
            if (typeof M !== 'undefined' && M.toast) {
                M.toast({ html: 'Consultando...', displayLength: 800, classes: 'indigo' });
            }

            $.ajax({
                url: '?pagina=reportes&action=consultar',
                method: 'GET',
                data: {
                    tipo: tipoSeleccionado(),
                    desde: $form.find('[name="desde"]').val(),
                    hasta: $form.find('[name="hasta"]').val()
                },
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.success) {
                        renderResultado(resp.data);
                    } else {
                        $error.show().text((resp && resp.error) || 'Error al consultar el reporte');
                    }
                },
                error: function () {
                    $error.show().text('Error de conexión al consultar el reporte.');
                }
            });
        });

        $('#btnExportar').on('click', function () {
            if (!fechasValidas()) {
                return;
            }
            var params = $.param({
                pagina: 'reportes',
                action: 'exportar',
                tipo: tipoSeleccionado(),
                formato: formatoSeleccionado(),
                desde: $form.find('[name="desde"]').val(),
                hasta: $form.find('[name="hasta"]').val(),
                csrf_token: window.EIS.csrfToken
            });
            window.open('?' + params, '_blank');
        });
    });
})(jQuery);
