$(function () {

    $(document).on('click', '#notifBell', function () {
        var msgs = [
            'Stock crítico: Mouse Inalámbrico',
            'Sesión Cyber #2 finalizada',
            'Nueva solicitud de proveedor'
        ];
        msgs.forEach(function (m) { EIS.toast(m, 'orange', 'notifications'); });
        $('#notifBadge').hide();
    });

    $(document).on('submit', '#formReporte', function (e) {
        e.preventDefault();
        var tipo = $(this).find('select').val() || 'Ventas por fecha';
        var formato = $(this).find('input[name="format"]:checked').val();
        EIS.toast('Generando reporte ' + tipo + ' en formato ' + formato.toUpperCase() + '...', 'indigo', 'download');
        setTimeout(function () {
            EIS.toast('Reporte generado exitosamente', 'green', 'check_circle');
        }, 1200);
    });

    $(document).on('click', '.btn-nuevo', function () {
        var tipo = $(this).data('tipo') || 'elemento';
        EIS.toast('Formulario para nuevo ' + tipo + ' abierto (demo)', 'indigo', 'add_circle');
    });

    $(document).on('click', '.btn-download', function () {
        EIS.toast('Descargando archivo...', 'green', 'file_download');
    });

});
