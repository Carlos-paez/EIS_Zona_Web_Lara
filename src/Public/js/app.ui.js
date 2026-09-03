// =====================================================================
// ARCHIVO: app.ui.js
// FUNCIÓN: Maneja eventos de interfaz de usuario globales:
//          - Notificaciones (campana)
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
    // EVENTO: Click en botones con clase .btn-download
    // Simula la descarga de un archivo mostrando un toast de confirmación.
    // ================================================================
    $(document).on('click', '.btn-download', function () {
        // Muestro un toast verde indicando que se está descargando el archivo
        EIS.toast('Descargando archivo...', 'green', 'file_download');
    });

});
