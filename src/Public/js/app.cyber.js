// =====================================================================
// ARCHIVO: app.cyber.js
// FUNCION: Maneja la interactividad del modulo de Control Cyber
//          (administracion de estaciones de cybercafe).
//          Permite:
//          - Cambiar el estado de las estaciones (disponible/ocupada/mantenimiento)
//          - Filtrar la vista por estado de estacion
//          - Actualizar contadores de estaciones en tiempo real
// =====================================================================

// Espero a que el DOM este listo para ejecutar el codigo
$(function () {

    // ================================================================
    // FUNCION: actualizarCyberContadores()
    // PROPOSITO: Recorre todas las tarjetas de estaciones y actualiza
    //            los contadores de disponibles, ocupadas y en
    //            mantenimiento en la barra superior.
    // ================================================================
    function actualizarCyberContadores() {
        var total = $('.station-card').length;                 // Total de estaciones en el DOM
        var disp = $('.station-card.disponible').length;      // Estaciones con clase "disponible"
        var ocup = $('.station-card.ocupada').length;         // Estaciones con clase "ocupada"
        var mant = $('.station-card.mantenimiento').length;   // Estaciones con clase "mantenimiento"

        // Actualizo los spans contadores en la interfaz
        $('#countDisponibles').text(disp);     // Badge de estaciones disponibles
        $('#countOcupadas').text(ocup);        // Badge de estaciones ocupadas
        $('#countMantenimiento').text(mant);   // Badge de estaciones en mantenimiento
    }

    // ================================================================
    // EVENTO: Click en una tarjeta de estacion (.station-card)
    // Cambia el estado de la estacion segun su estado actual:
    //   - disponible -> ocupada (iniciar sesion)
    //   - ocupada    -> disponible (finalizar sesion)
    //   - mantenimiento -> no hace nada (solo muestra toast informativo)
    // ================================================================
    $(document).on('click', '.station-card', function () {
        var $card = $(this);                              // Referencia a la tarjeta clickeada
        var status = $card.data('status');                // Estado actual de la estacion (data-status)
        var num = $card.find('.station-badge').text();    // Numero de la estacion desde el badge

        // CASO 1: Estacion disponible -> Iniciar sesion
        if (status === 'disponible') {
            if (confirm('Iniciar sesion en estacion ' + num + '?')) {
                // Cambio la clase CSS y el atributo data-status a "ocupada"
                $card.removeClass('disponible').addClass('ocupada').data('status', 'ocupada');
                // Cambio el icono a "timelapse" (reloj)
                $card.find('.station-icon .material-icons').text('timelapse');
                // Cambio el texto del estado
                $card.find('.station-status').text('Ocupada');
                // Animacion de escala al cambiar el estado
                $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 }).animate({ transform: 'scale(1)', opacity: 1 }, 300);
                actualizarCyberContadores(); // Actualizo los contadores
                EIS.toast('Sesion iniciada en ' + num, 'green', 'play_circle');
            }

        // CASO 2: Estacion ocupada -> Finalizar sesion
        } else if (status === 'ocupada') {
            if (confirm('Finalizar sesion en estacion ' + num + '?')) {
                // Cambio la clase CSS y el atributo data-status a "disponible"
                $card.removeClass('ocupada').addClass('disponible').data('status', 'disponible');
                // Cambio el icono a "check_circle" (verde)
                $card.find('.station-icon .material-icons').text('check_circle');
                // Cambio el texto del estado
                $card.find('.station-status').text('Disponible');
                // Animacion de escala al cambiar el estado
                $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 }).animate({ transform: 'scale(1)', opacity: 1 }, 300);
                actualizarCyberContadores(); // Actualizo los contadores
                EIS.toast('Sesion finalizada en ' + num, 'orange', 'stop_circle');
            }

        // CASO 3: Estacion en mantenimiento -> Solo informo
        } else {
            EIS.toast('Estacion ' + num + ' en mantenimiento', 'red', 'build');
        }
    });

    // ================================================================
    // EVENTO: Click en botones de filtro (.filter-btn)
    // Filtra las estaciones visibles segun su estado:
    //   - "all" -> muestra todas
    //   - "disponible" -> solo disponibles
    //   - "ocupada" -> solo ocupadas
    //   - "mantenimiento" -> solo en mantenimiento
    // ================================================================
    $(document).on('click', '.filter-btn', function () {
        // Remuevo la clase 'active' de todos los botones de filtro
        $('.filter-btn').removeClass('active');
        // Agrego 'active' solo al boton clickeado
        $(this).addClass('active');

        var filter = $(this).data('filter'); // Tipo de filtro: all, disponible, ocupada, mantenimiento

        // Recorro cada tarjeta de estacion
        $('.station-card').each(function () {
            var $col = $(this).closest('.col'); // La columna contenedora de la tarjeta

            if (filter === 'all') {
                // Si el filtro es "todos", muestro todas con animacion
                $col.slideDown(200);
            } else {
                // Si hay un filtro especifico, comparo con el data-status de la tarjeta
                var match = $(this).data('status') === filter;
                if (match) {
                    $col.slideDown(200); // Coincide -> muestro
                } else {
                    $col.hide(); // No coincide -> oculto
                }
            }
        });

        // Muestro un toast indicando el filtro aplicado
        var label = $(this).text().trim();
        EIS.toast('Mostrando: ' + label, 'indigo', 'filter_alt');
    });

});
