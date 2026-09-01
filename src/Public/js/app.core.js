// =====================================================================
// ARCHIVO: app.core.js
// FUNCIÓN: Define el namespace global "EIS" y funciones utilitarias
//          compartidas por todos los módulos del sistema.
// =====================================================================

// Creo el objeto global EIS que servirá como namespace para evitar
// colisiones con otras variables/objetos en el ámbito global
var EIS = {};

// =====================================================================
// FUNCIÓN: debounce(fn, delay)
// PROPÓSITO: Limita la frecuencia de ejecución de una función.
//            Es útil para eventos que se disparan frecuentemente
//            como "keyup", "scroll", "resize".
// PARÁMETROS:
//   fn    - Función a ejecutar
//   delay - Milisegundos de espera después del último disparo
// RETORNA: Una función wrapper que ejecuta fn solo después de que
//          hayan transcurrido 'delay' ms sin una nueva llamada.
// =====================================================================
function debounce(fn, delay) {
    var timer; // Almacena el identificador del setTimeout
    return function () {
        var ctx = this, args = arguments; // Preservo el contexto y los argumentos
        clearTimeout(timer); // Cancelo el timer anterior
        timer = setTimeout(function () { fn.apply(ctx, args); }, delay); // Programo nueva ejecución
    };
}

// =====================================================================
// FUNCIÓN: filtrarTabla(inputSelector, tableSelector, colIndex)
// PROPÓSITO: Filtra las filas de una tabla HTML según el texto ingresado
//            en un campo de búsqueda. Si se especifica colIndex, busca
//            solo en esa columna; si no, busca en toda la fila.
// PARÁMETROS:
//   inputSelector  - Selector CSS del campo de búsqueda
//   tableSelector  - Selector CSS de la tabla a filtrar
//   colIndex       - (Opcional) Índice de la columna (0-based) donde buscar
// =====================================================================
function filtrarTabla(inputSelector, tableSelector, colIndex) {
    // Obtengo el texto de búsqueda en minúsculas para comparación sin sensibilidad a mayúsculas
    var q = $(inputSelector).val().toLowerCase();
    // Recorro cada fila (<tr>) del <tbody> de la tabla
    $(tableSelector + ' tbody tr').each(function () {
        var $row = $(this);
        // Si se especificó colIndex, busco solo en esa columna; si no, en toda la fila
        var text = colIndex !== undefined
            ? $row.find('td').eq(colIndex).text().toLowerCase()
            : $row.text().toLowerCase();
        // Muestro u oculto la fila según si el texto contiene la búsqueda
        $row.toggle(text.indexOf(q) !== -1);
    });
    // Actualizo el contador de resultados visibles vs totales
    var visibles = $(tableSelector + ' tbody tr:visible').length;
    var total = $(tableSelector + ' tbody tr').length;
    $(tableSelector).closest('.card').find('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
}

// =====================================================================
// MÉTODO: EIS.toast(msg, color, icon)
// PROPÓSITO: Muestra una notificación tipo "toast" usando Materialize.
//            Es el método estándar para mostrar mensajes al usuario.
// PARÁMETROS:
//   msg   - Texto del mensaje a mostrar
//   color - Clase CSS de color (ej: 'red', 'green', 'indigo')
//   icon  - Nombre del ícono Material Icons (ej: 'check_circle', 'error')
// =====================================================================
EIS.toast = function (msg, color, icon) {
    color = color || 'indigo'; // Color por defecto: índigo
    icon = icon || 'check_circle'; // Ícono por defecto: check_circle
    // Construyo el HTML interno del toast con ícono + mensaje
    var html = '<i class="material-icons left" style="font-size:1.2rem;">' + icon + '</i>' + msg;
    // Invoco el toast de Materialize con clases redondeadas y duración de 3 segundos
    M.toast({ html: html, classes: color + ' rounded', displayLength: 3000 });
};

// =====================================================================
// INTEGRACIÓN CON JQUERY DATATABLES
// Funciones utilitarias para inicializar y manejar DataTables de forma
// consistente en todos los módulos. Se apoyan en DataTables 1.13.x
// (jquery.dataTables.min.js) y en la integración dataTables.materialize.js
// que ya define los valores por defecto (es/fest, dom, etc.).
// =====================================================================

// Comprueba si la librería DataTables ya está disponible.
function eisDataTablesDisponible() {
    return !!(window.jQuery && $.fn && $.fn.dataTable && $.fn.dataTable.version);
}

// ---------------------------------------------------------------------
// EIS.datatable(selector, opts)
// Inicializa DataTables sobre una tabla existente (o devuelve la API si
// ya está inicializada). Los "opts" permiten sobrescribir la config por
// defecto. Se ignoran las filas de "tabla vacía" (con <td colspan>).
// ---------------------------------------------------------------------
EIS.datatable = function (selector, opts) {
    opts = opts || {};
    var $table = $(selector);
    if (!$table.length) return null;
    if ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
        return $table.DataTable();
    }

    // Opciones por defecto compartidas
    var defaults = {
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        orderClasses: false,
        // Se renderizan filas desde el DOM inicial; DataTables gestiona
        // búsqueda/paginación sobre ellas en memoria.
        searching: true,
        paging: true,
        info: true
    };

    // Filtro el DOM: quito las filas de "sin datos" (colspan) para no
    // romper el conteo de columnas de DataTables.
    $table.find('tbody tr').each(function () {
        if ($(this).find('td[colspan]').length) {
            $(this).remove();
        }
    });

    var config = $.extend(true, {}, defaults, opts);
    return $table.DataTable(config);
};

// ---------------------------------------------------------------------
// EIS.datatableRefresh(selector)
// Tras re-renderizar el <tbody> por AJAX (los módulos vacían y llenan
// las filas con HTML), esta función vuelve a cargar las filas desde el
// DOM actual y redibuja la tabla conservando búsqueda/paginación.
// ---------------------------------------------------------------------
EIS.datatableRefresh = function (selector) {
    var $table = $(selector);
    if (!$table.length) return;
    if (!$.fn.DataTable || !$.fn.DataTable.isDataTable($table)) {
        // Si aún no se inicializó, lo hacemos ahora con la config base.
        EIS.datatable(selector);
        return;
    }
    var dt = $table.DataTable();

    // Elimino las filas de "sin datos" (colspan) del DOM para que
    // DataTables maneje el estado vacío con su propios mensajes.
    $table.find('tbody tr').each(function () {
        if ($(this).find('td[colspan]').length) {
            $(this).remove();
        }
    });

    dt.clear();
    dt.rows.add($table.find('tbody tr')).draw();

    // Re-inicializo tooltips Materialize de los nuevos botones.
    if (window.M && M.Tooltip) {
        try { $('.tooltipped').tooltip(); } catch (e) {}
    }
    return dt;
};

// ---------------------------------------------------------------------
// EIS.datatableWireSearch(selector, inputSelector)
// Conecta un input de búsqueda existente a la búsqueda global de
// DataTables (equivale a .search()). Útil para los módulos que ya
// tienen su propia barra de búsqueda.
// ---------------------------------------------------------------------
EIS.datatableWireSearch = function (selector, inputSelector) {
    var $input = $(inputSelector);
    if (!$input.length) return;
    var timer;
    $input.off('keyup.dt search.dt input.dt').on('keyup.dt input.dt', function () {
        clearTimeout(timer);
        var self = this;
        timer = setTimeout(function () {
            var dt = $(selector).DataTable();
            if (dt) {
                dt.search(self.value.trim()).draw();
            }
        }, 250);
    });
};

// ---------------------------------------------------------------------
// EIS.datatableWireColumnFilter(selector, selectSelector, columnIndex)
// Conecta un <select> existente a un filtro de columna de DataTables,
// de modo que al cambiar el filtro se filtre por esa columna.
// ---------------------------------------------------------------------
EIS.datatableWireColumnFilter = function (selector, selectSelector, columnIndex) {
    var $select = $(selectSelector);
    if (!$select.length) return;
    $select.off('change.dt').on('change.dt', function () {
        var dt = $(selector).DataTable();
        if (!dt) return;
        var val = $(this).val();
        dt.column(columnIndex).search(val ? val.toString() : '').draw();
    });
};

// ---------------------------------------------------------------------
// EIS.datatableDestroy(selector)
// Destruye una instancia de DataTables (envuelve la tabla correctamente)
// sin quitar la etiqueta. Útil al reconstruir una tabla por completo.
// ---------------------------------------------------------------------
EIS.datatableDestroy = function (selector) {
    var $table = $(selector);
    if ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
        $table.DataTable().destroy();
        $table.removeClass('dataTable');
        $table.find('.dataTables_empty').remove();
    }
};
