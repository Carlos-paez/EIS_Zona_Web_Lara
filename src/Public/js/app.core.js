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
// MÉTODO: EIS.toast(msg, color, icon, options)
// PROPÓSITO: Muestra una notificación tipo "toast" usando jQuery Toast.
//            Es el método estándar para mostrar mensajes al usuario.
// PARÁMETROS:
//   msg     - Texto del mensaje a mostrar
//   color   - Clase CSS de color (ej: 'red', 'green', 'indigo')
//   icon    - Nombre del ícono Material Icons (ej: 'check_circle', 'error')
//   options - (Opcional) Ajustes finos del toast:
//             heading  : título corto que aparece encima del mensaje
//             hideAfter: milisegundos antes de ocultarse (false = fijo)
//             position : 'top-right' | 'top-left' | 'bottom-right' | ...
//             stack    : máximo de toasts visibles apilados
//             className: clase CSS extra para el toast
//             loader   : muestra la barra de progreso inferior
// =====================================================================
EIS.toast = function (msg, color, icon, options) {
    color = color || 'indigo'; // Color por defecto: índigo
    icon = icon || 'check_circle'; // Ícono por defecto: check_circle
    options = options || {};

    // jQuery Toast Plugin (local): estándar visual en todo el sistema.
    if (window.jQuery && typeof $.toast === 'function') {
        var tipo = 'info';
        var fondo = '#1a237e';
        var c = String(color).toLowerCase();

        if (c.indexOf('green') !== -1 || c.indexOf('success') !== -1) {
            tipo = 'success';
            fondo = '#1b5e20';
        } else if (c.indexOf('red') !== -1 || c.indexOf('danger') !== -1) {
            tipo = 'error';
            fondo = '#b71c1c';
        } else if (c.indexOf('orange') !== -1 || c.indexOf('amber') !== -1 ||
                   c.indexOf('yellow') !== -1 || c.indexOf('warning') !== -1) {
            tipo = 'warning';
            fondo = '#e65100';
        } else if (c.indexOf('indigo') !== -1 || c.indexOf('blue') !== -1 ||
                   c.indexOf('info') !== -1) {
            tipo = 'info';
            fondo = '#1a237e';
        }

        $.toast({
            text: msg,
            heading: options.heading || '',
            icon: tipo,
            position: options.position || 'top-right',
            hideAfter: (options.hideAfter !== undefined) ? options.hideAfter : 3500,
            stack: (options.stack !== undefined) ? options.stack : 5,
            loader: options.loader === true,
            loaderBg: '#ffffff',
            allowToastClose: true,
            showHideTransition: 'slide',
            bgColor: fondo,
            textColor: '#ffffff',
            textAlign: 'left',
            'class': options.className || ''
        });
        return;
    }

    // Respaldo: toast de Materialize si el plugin no está disponible.
    var titulo = options.heading
        ? '<strong style="display:block;margin-bottom:0.15rem;">' + options.heading + '</strong>'
        : '';
    var html = '<i class="material-icons left" style="font-size:1.2rem;">' + icon + '</i>' +
        titulo + msg;
    M.toast({
        html: html,
        classes: color + ' rounded',
        displayLength: (options.hideAfter !== undefined) ? options.hideAfter : 3000
    });
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

    // Quito los mensajes por campo (validación propia, del plugin o del servidor)
    $form.find('.eis-field-feedback, .eis-field-error, .eis-field-hint').remove();

    // Quito todos los estados visuales que añade la validación
    $form.find('.eis-field-status').remove();
    $form.find('.eis-has-status').removeClass('eis-has-status');
    $form.find('.eis-invalid, .eis-valid, .invalid').each(function () {
        $(this)
            .removeClass('eis-invalid eis-valid invalid')
            .removeAttr('aria-invalid')
            .removeAttr('aria-describedby');
    });

    // Elimino el panel de errores global del formulario
    $form.find('.eis-form-error').remove();
    $form.removeData('eisToastTs eisToastMsg');
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

    // Guardo el último aviso antes de limpiar: limpiarErroresFormulario
    // reinicia la memoria de toasts y aquí se decide si repetirlo.
    var previoToast = $form.data('eisToastTs') || 0;
    var previoMsg = $form.data('eisToastMsg') || '';

    EIS.limpiarErroresFormulario($form);

    // --- Errores por campo --------------------------------------------
    // Se aceptan tanto a nivel raíz (fieldErrors) como anidados en data
    // (data.fieldErrors) para tolerar distintos formatos de respuesta.
    var fieldErrors = (data && data.fieldErrors)
        ? data.fieldErrors
        : (data && data.data && data.data.fieldErrors) ? data.data.fieldErrors : {};

    var nombres = Object.keys(fieldErrors);

    $.each(fieldErrors, function (name, msg) {
        var $field = $form.find('[name="' + name + '"]').first();
        if (!$field.length) return;

        // Uso el mismo pintado que la validación en tiempo real para que el
        // error del servidor se vea exactamente igual que el del navegador.
        if (typeof EIS.marcarErrorCampo === 'function') {
            EIS.marcarErrorCampo($field, msg);
            return;
        }

        // Respaldo si app.validate.js no estuviera cargado.
        var $wrap = $field.closest('.input-field').length
            ? $field.closest('.input-field')
            : $field.closest('.select-wrapper').length
                ? $field.closest('.select-wrapper')
                : $field.parent();

        if ($field.is('select, input[type="checkbox"], input[type="radio"]')) {
            $wrap.addClass('invalid eis-invalid');
        } else {
            $field.addClass('invalid eis-invalid');
        }

        $('<div class="eis-field-error helper-text"></div>')
            .text(msg)
            .appendTo($wrap);
    });

    // --- Panel de error global ----------------------------------------
    var msg = (data && data.error) ? data.error : 'Completa los campos obligatorios antes de continuar.';
    var $panel = $('<div class="card-panel eis-form-error" role="alert" aria-live="assertive"></div>');
    var $icono = $(
        '<div class="eis-form-error-icon" aria-hidden="true">' +
        '<i class="material-icons">error_outline</i></div>'
    );
    var $cuerpo = $('<div class="eis-form-error-body"></div>');
    $('<strong class="eis-form-error-title"></strong>').text(msg).appendTo($cuerpo);

    if (nombres.length) {
        var $lista = $('<ul class="eis-form-error-list"></ul>');
        $.each(nombres.slice(0, 6), function (_, name) {
            var $field = $form.find('[name="' + name + '"]').first();
            var etiqueta = '';

            if (typeof EIS.etiquetaCampo === 'function' && $field.length) {
                etiqueta = EIS.etiquetaCampo($field[0]);
            }
            if (!etiqueta) etiqueta = String(name).replace(/[_\-]+/g, ' ');

            var $item = $('<li></li>');
            $('<button type="button" class="eis-form-error-link"></button>')
                .attr('data-field', name)
                .text(etiqueta + ': ' + fieldErrors[name])
                .appendTo($item);
            $lista.append($item);
        });
        if (nombres.length > 6) {
            $('<li class="eis-form-error-more"></li>')
                .text('y ' + (nombres.length - 6) + ' campo(s) más con errores.')
                .appendTo($lista);
        }
        $cuerpo.append($lista);
    }

    $panel.append($icono, $cuerpo);
    $form.prepend($panel);

    // Al pulsar un error del resumen, llevo el foco al campo correspondiente.
    $panel.on('click', '.eis-form-error-link', function (e) {
        e.preventDefault();
        var $field = $form.find('[name="' + $(this).attr('data-field') + '"]').first();
        if (!$field.length) return;
        $field.trigger('focus');
        if ($field.is('select')) {
            var dropdown = $field.closest('.select-wrapper').find('input.select-dropdown').first();
            if (dropdown.length) dropdown.trigger('focus');
        }
    });

    // Notificación no intrusiva con jQuery Toast Plugin. Se evita repetir el
    // mismo aviso si el usuario vuelve a enviar en pocos segundos.
    var ahoraToast = Date.now();
    if (EIS.toast) {
        if (msg !== previoMsg || (ahoraToast - previoToast) > 3000) {
            EIS.toast(msg, 'red', 'error', {
                heading: 'Revisa el formulario',
                hideAfter: 4500,
                className: 'eis-toast-form',
                stack: 3
            });
        }
        $form.data('eisToastTs', ahoraToast);
        $form.data('eisToastMsg', msg);
    }

    // Enfoque el primer campo con error para que el usuario lo corrija rápido
    var $firstError = $form.find('.eis-invalid').first();
    if ($firstError.length) {
        var $target = $firstError.is('input, select, textarea')
            ? $firstError
            : $firstError.find('input, select, textarea').first();
        if ($target.length) {
            setTimeout(function () { $target.trigger('focus'); }, 150);
        }
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
