// =====================================================================
// ARCHIVO: app.tables.js
// FUNCIÓN: Maneja la interactividad de las tablas en toda la aplicación:
//          - Búsqueda en tabla de productos (input #searchProducto)
//          - Búsqueda en tabla de activos (input #searchActivo)
//          - Filtro por estado (select #filterEstado y #filterEstadoProv)
//          - Paginación de tablas (clicks en .pagination)
// =====================================================================

// Espero a que el DOM esté listo para asignar los eventos
$(function () {

    // ================================================================
    // EVENTO: Búsqueda en tiempo real en tabla de productos
    // Cuando el usuario escribe en #searchProducto, filtro la tabla
    // .striped por la columna 1 (nombre del producto).
    // Uso debounce con 300ms para no sobrecargar el render.
    // ================================================================
    $(document).on('input', '#searchProducto', debounce(function () {
        // Llamo a filtrarTabla: busca en columna 1 (índice 1 = nombre)
        filtrarTabla('#searchProducto', '.striped', 1);
    }, 300));

    // ================================================================
    // EVENTO: Búsqueda en tiempo real en tabla de activos
    // Cuando el usuario escribe en #searchActivo, filtro la tabla
    // .striped por la columna 0 (código o nombre del activo).
    // ================================================================
    $(document).on('input', '#searchActivo', debounce(function () {
        // Llamo a filtrarTabla: busca en columna 0 (índice 0 = código/activo)
        filtrarTabla('#searchActivo', '.striped', 0);
    }, 300));

    // ================================================================
    // EVENTO: Cambio en los filtros de estado (selectores)
    // Filtra las filas de la tabla según el texto del badge de estado
    // que aparece en cada fila (columna con clase .badge, .new-badge).
    // ================================================================
    $(document).on('change', '#filterEstado, #filterEstadoProv', function () {
        // Obtengo el valor seleccionado en minúsculas
        var val = $(this).val().toLowerCase();
        // Busco la tabla asociada: primero intento la siguiente .card después del contenedor
        var table = $(this).closest('.card').next('.card').find('table');
        // Si no encuentro, busco en el .row hermano (estructura alternativa)
        if (!table.length) table = $(this).closest('.row').siblings('.card').find('table');

        // Recorro cada fila del <tbody> de la tabla
        table.find('tbody tr').each(function () {
            // Obtengo el texto del badge de estado (primero .new-badge, luego .new.badge, luego .badge)
            var badge = $(this).find('.new-badge, .new.badge, .badge').text().trim().toLowerCase();
            // Si no hay filtro seleccionado o el badge contiene el filtro, muestro la fila
            if (!val || badge.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide(); // Si no coincide, oculto la fila
            }
        });

        // Actualizo el contador de resultados visibles vs totales
        var visibles = table.find('tbody tr:visible').length;
        var total = table.find('tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    });

    // ================================================================
    // EVENTO: Click en enlaces de paginación
    // Maneja la navegación entre páginas de la tabla simulada.
    // Marca el <li> activo y muestra un toast de navegación.
    // ================================================================
    $(document).on('click', '.pagination li:not(.disabled):not(.active) a', function (e) {
        e.preventDefault(); // Evito que el enlace recargue la página

        var $li = $(this).closest('li'); // El <li> que contiene el enlace clickeado
        var $ul = $li.closest('.pagination'); // El <ul> de paginación

        // Remuevo la clase 'active' e 'indigo' del <li> actualmente activo
        $ul.find('li.active').removeClass('active indigo');
        // Marco el <li> clickeado como activo con color índigo
        $li.addClass('active indigo');

        // Obtengo el número de página desde el texto del enlace
        var page = $(this).text().trim();
        // Muestro un toast indicando la navegación (simulación)
        EIS.toast('Navegando a página ' + page, 'indigo', 'chevron_right');
    });

});
