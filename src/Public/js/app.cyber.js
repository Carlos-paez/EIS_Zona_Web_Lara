$(function () {

    function actualizarCyberContadores() {
        var total = $('.station-card').length;
        var disp = $('.station-card.disponible').length;
        var ocup = $('.station-card.ocupada').length;
        var mant = $('.station-card.mantenimiento').length;
        $('#countDisponibles').text(disp);
        $('#countOcupadas').text(ocup);
        $('#countMantenimiento').text(mant);
    }

    $(document).on('click', '.station-card', function () {
        var $card = $(this);
        var status = $card.data('status');
        var num = $card.find('.station-badge').text();

        if (status === 'disponible') {
            if (confirm('¿Iniciar sesión en estación ' + num + '?')) {
                $card.removeClass('disponible').addClass('ocupada').data('status', 'ocupada');
                $card.find('.station-icon .material-icons').text('timelapse');
                $card.find('.station-status').text('Ocupada');
                $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 }).animate({ transform: 'scale(1)', opacity: 1 }, 300);
                actualizarCyberContadores();
                EIS.toast('Sesión iniciada en ' + num, 'green', 'play_circle');
            }
        } else if (status === 'ocupada') {
            if (confirm('¿Finalizar sesión en estación ' + num + '?')) {
                $card.removeClass('ocupada').addClass('disponible').data('status', 'disponible');
                $card.find('.station-icon .material-icons').text('check_circle');
                $card.find('.station-status').text('Disponible');
                $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 }).animate({ transform: 'scale(1)', opacity: 1 }, 300);
                actualizarCyberContadores();
                EIS.toast('Sesión finalizada en ' + num, 'orange', 'stop_circle');
            }
        } else {
            EIS.toast('Estación ' + num + ' en mantenimiento', 'red', 'build');
        }
    });

    $(document).on('click', '.filter-btn', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');
        $('.station-card').each(function () {
            var $col = $(this).closest('.col');
            if (filter === 'all') {
                $col.slideDown(200);
            } else {
                var match = $(this).data('status') === filter;
                if (match) { $col.slideDown(200); } else { $col.hide(); }
            }
        });
        var label = $(this).text().trim();
        EIS.toast('Mostrando: ' + label, 'indigo', 'filter_alt');
    });

});
