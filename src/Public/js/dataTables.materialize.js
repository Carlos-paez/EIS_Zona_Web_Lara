/* =====================================================================
   ARCHIVO: dataTables.materialize.js
   FUNCIÓN: Integración ligera de jQuery DataTables (v1.13.x) con el
            look de la aplicación. Define valores por defecto en
            español, envuelve cada tabla en el wrapper .eis-dt-wrap
            (estilizado en dataTables.materialize.css) y ajusta la
            estructura del DOM generado. No agrega la barra de búsqueda
            nativa por defecto (cada módulo conecta su propio input).
   ===================================================================== */

(function ($) {
    'use strict';

    $.extend(true, $.fn.dataTable.defaults, {
        // Idioma en español
        language: {
            processing: 'Procesando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ ',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ resultados',
            infoEmpty: 'Mostrando 0 a 0 de 0 resultados',
            infoFiltered: '(filtrado de _MAX_ totales)',
            infoPostFix: '',
            loadingRecords: 'Cargando registros...',
            zeroRecords: 'Sin resultados',
            emptyTable: 'No hay datos en la tabla',
            paginate: {
                first: 'Primero',
                previous: '‹',
                next: '›',
                last: 'Último'
            },
            aria: {
                sortAscending: ': activar para ordenar la columna ascendente',
                sortDescending: ': activar para ordenar la columna descendente'
            }
        },
        // Estructura visible: longitud (l) arriba, luego la tabla (t)
        // y al final info (i) + paginación (p). Cada módulo conecta su
        // propio input de búsqueda/filtros a la API, por lo que aquí
        // NO se renderiza la barra de búsqueda nativa (f).
        dom: '<"eis-dt-wrap"l>t<"eis-dt-wrap"ip>'
    });

    // Función de utilidad para integrar el plugin sin romper la
    // inicialización de Materialize (método de jQuery DataTables).
    $.fn.dataTable.materialize = function (opts) {
        opts = opts || {};
        return this.each(function () {
            var $table = $(this);
            if ($table.hasClass('dataTable')) return; // ya inicializada
            var defaults = {
                orderClasses: false,
                // Cada módulo conecta el buscador propio déspues
                stateSave: false
            };
            $table.DataTable($.extend({}, defaults, opts));
        });
    };

})(jQuery);