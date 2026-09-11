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
// MÉTODO: EIS.formSelect(sel)
// PROPÓSITO: (Re)inicializa de forma SEGURA un <select> de Materialize.
//            A diferencia de $('select').formSelect(), destruye primero
//            la instancia existente para evitar que Materialize vuelva a
//            envolver el <select> (lo que deja barras desplegables
//            duplicadas y huérfanas en la página, sobre todo en modales).
//            Útil cuando un formulario/modal repuebla las opciones de un
//            select dinámicamente (destroy + formSelect).
// PARÁMETROS:
//   sel - Selector o elemento jQuery del <select> a (re)inicializar
// =====================================================================
EIS.formSelect = function (sel) {
    var $sel = $(sel);
    $sel.each(function () {
        var inst = M.FormSelect.getInstance(this);
        if (inst) {
            inst.destroy();
        }
        $(this).formSelect();
    });
    // Re-inyecto la barra de búsqueda en las barras desplegables recién
    // creadas (app.selects.js expone habilitarBusquedaEnSelects).
    if (window.EIS && EIS.habilitarBusquedaEnSelects) {
        EIS.habilitarBusquedaEnSelects();
    }
    return $sel;
};

// =====================================================================
// MÉTODO: EIS.limpiarErroresFormulario(form)
// PROPÓSITO: Elimina los mensajes de error y los resaltados que dejó la
//            función EIS.mostrarErroresFormulario en un formulario.
//            Se invoca antes de cada envío y cuando el usuario corrige.
// PARÁMETROS:
//   form - Elemento <form> (o selector/objeto jQuery del formulario)
// =====================================================================
EIS.limpiarErroresFormulario = function (form) {
    var $form = $(form);
    if (!$form.length) return;
    // Quito mensajes por campo e identifico los elementos adornados
    $form.find('.eis-field-error').remove();
    // Quito la clase "invalid" que añadimos a inputs y select-wrapers
    $form.find('.eis-invalid').each(function () {
        var $el = $(this).removeClass('eis-invalid');
        if ($el.is('input, textarea')) {
            $el.removeClass('invalid');
            // Si no hay otro contexto, elimino el helper rojo de Materialize
            $el.removeAttr('aria-invalid');
        }
    });
    // Elimino el panel de error global del formulario
    $form.find('.eis-form-error').remove();
};

// =====================================================================
// MÉTODO: EIS.mostrarErroresFormulario(form, data)
// PROPÓSITO: Muestra en pantalla los errores de validación que devolvió
//            el servidor (PHP). Si la respuesta trae "fieldErrors" (mapa
//            campo => mensaje), marca cada campo en rojo con su mensaje;
//            siempre muestra además un panel rojo con el mensaje general.
//            El usuario ve el error de forma persistente, no como toast.
// PARÁMETROS:
//   form - Elemento <form> donde se mostrarán los errores
//   data - Objeto de respuesta del servidor (JSON.parse ya hecho)
// =====================================================================
EIS.mostrarErroresFormulario = function (form, data) {
    var $form = $(form);
    if (!$form.length) return;

    EIS.limpiarErroresFormulario($form);

    // --- Errores por campo --------------------------------------------
    var fieldErrors = (data && data.fieldErrors) ? data.fieldErrors : {};
    $.each(fieldErrors, function (name, msg) {
        var $field = $form.find('[name="' + name + '"]').first();
        if (!$field.length) return;

        // Determino el contenedor visible (input-field o select-wrapper)
        var $wrap = $field.closest('.select-wrapper').length
            ? $field.closest('.select-wrapper')
            : $field.closest('.input-field, .select-wrapper').length
                ? $field.closest('.input-field, .select-wrapper')
                : $field.parent();

        // Marca visual en rojo
        if ($field.is('select')) {
            $wrap.addClass('invalid eis-invalid');
        } else {
            $field.addClass('invalid eis-invalid');
        }

        // Inserto el mensaje debajo del campo
        $('<div class="eis-field-error helper-text" style="color:#c62828;font-weight:500;">' + msg + '</div>').appendTo($wrap);
    });

    // --- Panel de error global ----------------------------------------
    var msg = (data && data.error) ? data.error : 'Completa los campos obligatorios antes de continuar.';
    var $panel = $(
        '<div class="card-panel eis-form-error" style="background:#fce4ec;color:#b71c1c;' +
        'border-radius:8px;padding:0.75rem 1rem;margin:0 0 1rem;display:flex;align-items:center;gap:0.5rem;">' +
        '<i class="material-icons" style="font-size:1.2rem;">error</i><span></span></div>'
    );
    $panel.find('span').text(msg);
    $form.prepend($panel);

    // Enfoque el primer campo con error para que el usuario lo corrija rápido
    var $firstError = $form.find('.eis-invalid').first();
    if ($firstError.length) {
        setTimeout(function () { $firstError.trigger('focus'); }, 150);
    }
};

// =====================================================================
// MÉTODO: EIS.mostrarErrorAnexo(form, data)
// PROPÓSITO: Similar a EIS.mostrarErroresFormulario pero para formularios
//            que ya tienen su propio contenedor de error (p. ej. el
//            cybercafé con <div id="pcFormError">). Rellena ese contenedor
//            con el mensaje del servidor y lo desliza a la vista.
// PARÁMETROS:
//   form      - Elemento <form> que contiene el contenedor de error
//   errorSelector - Selector del contenedor de error dentro del form
//   data      - Objeto de respuesta del servidor
// =====================================================================
EIS.mostrarErrorAnexo = function (form, errorSelector, data) {
    var $form = $(form);
    if (!$form.length) return;
    var $panel = $form.find(errorSelector);
    if (!$panel.length) return;
    var msg = (data && data.error) ? data.error : 'Ha ocurrido un error. Verifica los datos e intenta de nuevo.';
    $panel.find('.pc-form-error-message').text(msg);
    $panel.find('.pc-form-error-message').html(msg.replace(/\n/g, '<br>'));
    $panel.slideDown(250);
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
