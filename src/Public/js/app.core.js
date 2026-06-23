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
