// =====================================================================
// ARCHIVO: app.ui.js
// FUNCIÓN: Maneja eventos de interfaz de usuario globales:
//          - Notificaciones (campana)
//          - Generación de reportes
//          - Botones de nuevo elemento
//          - Botones de descarga
// =====================================================================

// Espero a que el DOM esté listo para asignar los eventos
$(function () {

    // ================================================================
    // EVENTO: Click en la campana de notificaciones (#notifBell)
    // Cuando el usuario hace clic en la campana de la barra superior,
    // se muestran toasts con las notificaciones simuladas y se oculta
    // el badge contador rojo.
    // ================================================================
    $(document).on('click', '#notifBell', function () {
        // Lista de mensajes de notificación de ejemplo
        var msgs = [
            'Stock crítico: Mouse Inalámbrico',
            'Sesión Cyber #2 finalizada',
            'Nueva solicitud de proveedor'
        ];
        // Muestro cada mensaje como un toast individual con ícono de notificaciones
        msgs.forEach(function (m) { EIS.toast(m, 'orange', 'notifications'); });
        // Oculto el badge rojo con el contador de notificaciones
        $('#notifBadge').hide();
    });

    // ================================================================
    // EVENTO: Submit del formulario de reportes (#formReporte)
    // Genera un reporte simulando el proceso con toasts de feedback.
    // ================================================================
    $(document).on('submit', '#formReporte', function (e) {
        e.preventDefault(); // Evito el envío tradicional del formulario

        // Obtengo el tipo de reporte desde el <select> del formulario
        var tipo = $(this).find('select').val() || 'Ventas por fecha';
        // Obtengo el formato seleccionado (PDF, Excel, etc.) desde los radio buttons
        var formato = $(this).find('input[name="format"]:checked').val();

        // Muestro un toast indicando que se está generando el reporte
        EIS.toast('Generando reporte ' + tipo + ' en formato ' + formato.toUpperCase() + '...', 'indigo', 'download');

        // Simulo el tiempo de generación con un setTimeout de 1.2 segundos
        setTimeout(function () {
            // Muestro un toast de éxito cuando el reporte se ha generado
            EIS.toast('Reporte generado exitosamente', 'green', 'check_circle');
        }, 1200);
    });

    // ================================================================
    // EVENTO: Click en botones con clase .btn-nuevo
    // Abre un formulario para crear un nuevo elemento (producto, orden, etc.)
    // Utiliza el atributo data-tipo para personalizar el mensaje.
    // ================================================================
    $(document).on('click', '.btn-nuevo', function () {
        // Obtengo el tipo de elemento desde data-tipo; por defecto 'elemento'
        var tipo = $(this).data('tipo') || 'elemento';
        // Muestro un toast indicando que se abre el formulario (demo/simulación)
        EIS.toast('Formulario para nuevo ' + tipo + ' abierto (demo)', 'indigo', 'add_circle');
    });

    // ================================================================
    // EVENTO: Click en botones con clase .btn-download
    // Simula la descarga de un archivo mostrando un toast de confirmación.
    // ================================================================
    $(document).on('click', '.btn-download', function () {
        // Muestro un toast verde indicando que se está descargando el archivo
        EIS.toast('Descargando archivo...', 'green', 'file_download');
    });

});
