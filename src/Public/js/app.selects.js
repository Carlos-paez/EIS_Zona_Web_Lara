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
    // FUNCIÓN: inyectarBusqueda(ul)
    // PROPÓSITO: Asegura que el desplegable tenga su barra de búsqueda.
    //            Es idempotente (no duplica la barra).
    // ---------------------------------------------------------------
    function inyectarBusqueda($ul) {
        if ($ul.find('li.eis-select-search').length) return;

        $ul.prepend(
            '<li class="eis-select-search" data-eis-search>'
            + '<span>'
            + '<div class="eis-search-field">'
            + '<i class="material-icons">search</i>'
            + '<input type="text" placeholder="Buscar opción..." autocomplete="off" spellcheck="false">'
            + '</div>'
            + '</span>'
            + '</li>'
        );

        var $bar = $ul.find('li.eis-select-search');

        // Filtro en tiempo real
        $bar.find('input').on('input', function () {
            aplicarFiltro($ul, this.value.trim());
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

    // Aplico de forma global y segura a los selects ya existentes.
    EIS.habilitarBusquedaEnSelects();

    window.EIS.searchableSelectsReady = true;
});