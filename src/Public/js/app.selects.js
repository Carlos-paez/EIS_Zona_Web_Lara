// =====================================================================
// ARCHIVO: app.selects.js
// FUNCIÓN: Agrega una barra de búsqueda a los menús desplegables
//          (selects de Materialize formSelect) de toda la aplicación.
//          Al desplegar un select, se muestra un campo de texto que
//          permite escribir y filtrar en tiempo real las opciones
//          disponibles por el texto ingresado.
// =====================================================================

// Espero a que el DOM esté listo. Como este archivo se carga DESPUÉS de
// app.init.js (que llama a $('select').formSelect()), el <ul> generado por
// Materialize (ul.dropdown-content.select-dropdown) ya existe.
$(function () {

    // Evito duplicar la lógica si el archivo se cargara dos veces.
    if (window.EIS && EIS.searchableSelectsReady) return;

    // ---------------------------------------------------------------
    // ESTILOS de la barra de búsqueda dentro del desplegable.
    // Se inyectan una sola vez para no depender de editar styles.css.
    // ---------------------------------------------------------------
    if (!$('#eisSelectSearchStyles').length) {
        var css = ''
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-search {\n'
            + '  position: sticky; top: 0; z-index: 2; background:#fff; padding: 0.5rem 0.6rem; border-bottom: 1px solid #e0e0e0;\n'
            + '}\n'
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-search > span { padding: 0; }\n'
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-search .eis-search-field {\n'
            + '  display: flex; align-items: center; gap: 0.4rem; border: 1px solid #c9d2e0; border-radius: 6px;\n'
            + '  padding: 0 0.5rem; background: #fff;\n'
            + '}\n'
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-search .eis-search-field i {\n'
            + '  font-size: 1.1rem; color: #607d8b; line-height: normal;\n'
            + '}\n'
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-search input {\n'
            + '  border: none !important; box-shadow: none !important; height: 2rem !important; margin: 0 !important;\n'
            + '  padding: 0 !important; font-size: 0.9rem; color: #333; background: transparent;\n'
            + '}\n'
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-search input:focus { border: none !important; box-shadow: none !important; }\n'
            + '[data-theme="dark"] .dropdown-content.select-dropdown li.eis-select-search { background: #21242c; }\n'
            + '[data-theme="dark"] .dropdown-content.select-dropdown li.eis-select-search .eis-search-field { background: #16181d; border-color: #333; }\n'
            + '[data-theme="dark"] .dropdown-content.select-dropdown li.eis-select-search input { color: #e0e0e0; }\n'
            + '.select-wrapper .dropdown-content.select-dropdown li.eis-select-no-results { color: #9e9e9e; font-style: italic; text-align: center; pointer-events: none; }\n'
            + '@media (max-width: 600px){ .select-wrapper .dropdown-content.select-dropdown li.eis-select-search input { font-size: 0.85rem; } }\n';
        $('<style id="eisSelectSearchStyles">' + css + '</style>').appendTo('head');
    }

    // ---------------------------------------------------------------
    // FUNCIÓN: inyectarBusqueda(ul, placeholder)
    // PROPÓSITO: Asegura que el desplegable tenga su barra de búsqueda.
    //            Es idempotente (no duplica la barra); si ya existe,
    //            solo actualiza el placeholder solicitado.
    // ---------------------------------------------------------------
    function inyectarBusqueda($ul, placeholder) {
        var hayPlaceholder = typeof placeholder === 'string';
        placeholder = hayPlaceholder ? placeholder : 'Buscar opción...';

        var $existente = $ul.find('li.eis-select-search');
        if ($existente.length) {
            // Si ya existe la barra, respeto su placeholder salvo que se
            // solicite uno específico (p. ej. el del selector de clientes
            // del POS, que pide "Buscar cliente por nombre o cédula...").
            if (hayPlaceholder) {
                $existente.find('input').attr('placeholder', placeholder);
            }
            return;
        }

        $ul.prepend(
            '<li class="eis-select-search" data-eis-search>'
            + '<span>'
            + '<div class="eis-search-field">'
            + '<i class="material-icons">search</i>'
            + '<input type="text" placeholder="' + placeholder + '" autocomplete="off" spellcheck="false">'
            + '</div>'
            + '</span>'
            + '</li>'
        );

        var $bar = $ul.find('li.eis-select-search');

        // Filtro en tiempo real
        $bar.find('input').on('input', function () {
            aplicarFiltro($ul, this.value.trim());
        });

        // Evito que el typeahead del Dropdown de Materialize robe el foco.
        // Al teclear, el handler _handleDropdownKeydown del <ul> busca la
        // primera opción que empiece con la letra y llama _focusFocusedItem(),
        // lo que saca el cursor del input de búsqueda. Como el input está
        // DENTRO del <ul>, detengo la propagación de keydown/keyup para que
        // el teclado no llegue al dropdown y el texto se escriba normalmente.
        $bar.find('input').on('keydown keyup', function (e) {
            e.stopPropagation();
        });

        // Evito que el desplegable se cierre al interactuar con la barra.
        // Materialize cierra el menú (closeOnClick) mediante un handler de
        // "click" en document, así que detengo la propagación de ese click
        // para poder escribir. Uso stopPropagation (NO preventDefault) en
        // mousedown/touchstart para permitir que el input reciba el foco.
        $bar.on('mousedown touchstart', function (e) {
            e.stopPropagation();
        });
        $bar.on('click', function (e) {
            e.stopPropagation();
        });
    }

    // ---------------------------------------------------------------
    // FUNCIÓN: restablecerBusqueda(ul)
    // PROPÓSITO: Al abrir un desplegable, limpia la búsqueda previa y
    //            restaura las opciones. Evita que un filtro anterior
    //            quede "pegado" (con filas ocultas) al reabrir el menú.
    // ---------------------------------------------------------------
    function restablecerBusqueda($ul) {
        var $bar = $ul.find('li.eis-select-search');
        if (!$bar.length) return;

        var $input = $bar.find('input');
        if ($input.val() !== '') {
            $input.val('');
        }
        // Restauro la visibilidad de todas las opciones y quito avisos.
        $ul.find('> li').not('.eis-select-search').css('display', '');
        $ul.find('li.eis-select-no-results').remove();
    }

    // ---------------------------------------------------------------
    // FUNCIÓN: aplicarFiltro($ul, texto)
    // PROPÓSITO: Muestra/oculta las opciones según el texto buscado.
    // ---------------------------------------------------------------
    function aplicarFiltro($ul, texto) {
        var q = texto.toLowerCase();
        var $rows = $ul.find('> li').not('.eis-select-search');
        var visibles = 0;

        $rows.each(function () {
            var $li = $(this);
            var coincide = q === '' || ($li.text() || '').toLowerCase().indexOf(q) !== -1;

            if (q === '') {
                // Búsqueda vacía: restauro el estado natural de la fila.
                $li.css('display', '');
                visibles++;
            } else if (coincide) {
                $li.css('display', 'block');
                visibles++;
            } else {
                $li.css('display', 'none');
            }
        });

        // Muestra/oculta el aviso de "sin resultados"
        $ul.find('li.eis-select-no-results').remove();
        if (q !== '' && visibles === 0) {
            $ul.append('<li class="eis-select-no-results"><span>Sin resultados</span></li>');
        }
    }

    // ---------------------------------------------------------------
    // BLOQUEO DE CIERRE EN FASE DE CAPTURA
    // Materialize 1.0.0 cierra los desplegables con un listener de
    // "click" en document.body en FASE DE CAPTURA (tercer argumento
    // true). Como la captura baja de document -> body -> ... -> li,
    // ese handler se ejecuta ANTES de nuestros stopPropagation del li
    // (fase de burbuja) y el menú se cierra al hacer clic en la barra
    // de búsqueda. Para evitarlo, intercepto el evento en document
    // (ancestro de body) con captura: si el clic/tap proviene de una
    // barra .eis-select-search, detengo la propagación antes de que
    // el handler de Materialize pueda programar el close().
    // ---------------------------------------------------------------
    ['click', 'touchend'].forEach(function (tipoEvento) {
        document.addEventListener(tipoEvento, function (e) {
            var objetivo = e.target;
            if (objetivo && objetivo.closest && objetivo.closest('.eis-select-search')) {
                e.stopPropagation();
            }
        }, true);
    });

    // ---------------------------------------------------------------
    // EVENTO: Al enfocar/abrir cualquier select de Materialize,
    //         aseguro inyectar su barra de búsqueda (idempotente) y
    //         restablezco el filtro para que cada vez que se abra el
    //         menú las opciones se vean completas. Se usa focusin
    //         (en vez de solo click) para cubrir selects regenerados.
    // ---------------------------------------------------------------
    $(document).on('focusin click', '.select-wrapper input.select-dropdown', function () {
        var $wrapper = $(this).closest('.select-wrapper');
        var $ul = $wrapper.find('ul.dropdown-content.select-dropdown');
        if (!$ul.length) return;
        inyectarBusqueda($ul);
        restablecerBusqueda($ul);
    });

    // ---------------------------------------------------------------
    // UTILIDAD PÚBLICA: EIS.habilitarBusquedaEnSelects()
    // Permite re-aplicar la barra de búsqueda si un módulo reconstruye
    // los selects dinámicamente (p. ej. después de formSelect()).
    // ---------------------------------------------------------------
    EIS.habilitarBusquedaEnSelects = function () {
        $('select').parent('.select-wrapper').each(function () {
            inyectarBusqueda($(this).find('ul.dropdown-content.select-dropdown'));
        });
    };

    // ---------------------------------------------------------------
    // UTILIDAD PÚBLICA: EIS.activarBusquedaEnSelect(selector, placeholder)
    // Inserta (o actualiza) la barra de búsqueda con un placeholder
    // específico en el desplegable de un select concreto. Útil para
    // selects regenerados vía formSelect() (p. ej. el de clientes del
    // POS) y para personalizar el texto guía del filtro.
    // ---------------------------------------------------------------
    EIS.activarBusquedaEnSelect = function (selector, placeholder) {
        $(selector).each(function () {
            var $wrapper = $(this).parent('.select-wrapper');
            if (!$wrapper.length) return;
            var $ul = $wrapper.find('ul.dropdown-content.select-dropdown');
            if (!$ul.length) return;
            inyectarBusqueda($ul, placeholder);
        });
    };

    // Aplico de forma global y segura a los selects ya existentes.
    EIS.habilitarBusquedaEnSelects();

    window.EIS.searchableSelectsReady = true;
});