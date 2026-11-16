// =====================================================================
// ARCHIVO: app.ajax.js
// FUNCIÓN: Plantilla única para todas las peticiones AJAX del sistema.
//
// OBJETIVO:
//   Evitar repetir en cada módulo la misma estructura de $.ajax
//   (url, método, dataType, manejo de éxito, manejo de error, toasts,
//   errores de formulario, etc.). Los módulos solo describen SU petición
//   y dejan que esta plantilla haga el trabajo común.
//
// USO BÁSICO:
//   EIS.ajax.get(API + 'listar', {
//       onSuccess: function (r) { ... },
//       errorMessage: 'No se pudieron cargar los datos.'
//   });
//
//   EIS.ajax.post(API + 'eliminar', { id: id }, {
//       successToast: true,
//       onSuccess: function () { refrescarTabla(); }
//   });
//
//   EIS.ajax.form(API + 'crear', $form, {
//       successToast: true,
//       onSuccess: function (r) { ... }
//   });
//
// PARÁMETROS ACEPTADOS POR EIS.ajax (y por get/post/form):
//   url              - URL de la petición (obligatorio)
//   method / type    - 'GET' (por defecto) o 'POST'
//   data             - Datos a enviar (objeto, string serializado, etc.)
//   dataType         - Tipo de respuesta; por defecto 'json'
//   form             - Formulario (selector o elemento jQuery). Si se indica:
//                        * Se serializa automáticamente si no se pasó "data".
//                        * Si el servidor responde success:false, se muestran
//                          sus fieldErrors con EIS.mostrarErroresFormulario.
//   onSuccess        - Callback que se ejecuta SOLO si la respuesta es
//                      correcta (r.success !== false).
//   onError          - Callback para errores del servidor o de red.
//                      Recibe (respuesta, xhr, textStatus). Si se indica,
//                      la plantilla NO muestra el toast automático.
//   successToast     - true para mostrar r.message en verde.
//                      También acepta un texto alternativo si r.message
//                      no viene en la respuesta.
//   successIcon      - Ícono Material para el toast de éxito.
//   errorMessage     - Mensaje para errores de conexión (y respaldo si el
//                      servidor falla sin traer "error").
//   serverErrorMessage - Mensaje de respaldo si el servidor falla sin "error".
//   showServerError  - false para no mostrar toast cuando el servidor
//                      responde HTTP 200 con success:false.
//   showNetworkError - false para no mostrar toast cuando la petición falla
//                      (red o HTTP 4xx/5xx).
//
// NOTA: si se indica "form", los fieldErrors del servidor se pintan en el
// formulario tanto en respuestas 200 como en errores HTTP 4xx/5xx.
//
// RETORNA:
//   El objeto jqXHR de jQuery, por lo que se puede encadenar .always(),
//   .done(), etc. (por ejemplo para rehabilitar un botón al terminar).
// =====================================================================

(function (window, $) {
    'use strict';

    var EIS = window.EIS = window.EIS || {};

    // -----------------------------------------------------------------
    // EIS.ajax(opciones)
    // Plantilla base. Centraliza los valores por defecto y el manejo
    // común de respuestas y errores.
    // -----------------------------------------------------------------
    EIS.ajax = function (opciones) {
        opciones = opciones || {};

        var $form = opciones.form ? $(opciones.form) : $();
        var onSuccess = opciones.onSuccess || opciones.success || $.noop;
        var onError = opciones.onError || opciones.error || null;
        var method = String(opciones.method || opciones.type || 'GET').toUpperCase();

        // Mensajes y comportamiento por defecto
        var errorMessage = opciones.errorMessage ||
            'No se pudo conectar con el servidor. Intenta de nuevo.';
        var serverErrorMessage = opciones.serverErrorMessage ||
            opciones.errorMessage ||
            'No se pudo completar la operación. Intenta de nuevo.';
        var showServerError = opciones.showServerError !== false;
        var showNetworkError = opciones.showNetworkError !== false;
        var successToast = opciones.successToast || false;
        var successIcon = opciones.successIcon || 'check_circle';

        // Clono las opciones para no modificar el objeto del módulo.
        var config = $.extend({}, opciones);

        // Quito las opciones propias de la plantilla: jQuery no las conoce.
        delete config.form;
        delete config.onSuccess;
        delete config.onError;
        delete config.success;
        delete config.error;
        delete config.successToast;
        delete config.successIcon;
        delete config.errorMessage;
        delete config.serverErrorMessage;
        delete config.showServerError;
        delete config.showNetworkError;

        // Valores estándar de todas las peticiones del sistema.
        config.type = method;
        config.method = method;
        config.dataType = opciones.dataType || 'json';

        // Si se indicó un formulario y no se enviaron datos, se serializa.
        if ($form.length && config.data === undefined) {
            config.data = $form.serialize();
        }

        // --- Respuesta HTTP correcta ---------------------------------
        config.success = function (respuesta, textStatus, xhr) {
            // El backend del sistema usa { success: true|false, ... }.
            // Cuando success es false se trata como error de negocio.
            if (respuesta && respuesta.success === false) {
                // Si hay formulario, se pintan los errores por campo.
                if ($form.length && typeof EIS.mostrarErroresFormulario === 'function') {
                    EIS.mostrarErroresFormulario($form, respuesta);
                }

                if (typeof onError === 'function') {
                    onError(respuesta, null, textStatus);
                } else if (!$form.length && showServerError) {
                    EIS.toast(respuesta.error || serverErrorMessage, 'red', 'error');
                }
                return;
            }

            // Toast automático de éxito (opcional).
            if (successToast) {
                var mensaje = (respuesta && respuesta.message)
                    ? respuesta.message
                    : (typeof successToast === 'string'
                        ? successToast
                        : 'Operación realizada correctamente.');
                EIS.toast(mensaje, 'green', successIcon);
            }

            onSuccess(respuesta, textStatus, xhr);
        };

        // --- Error de red / HTTP -------------------------------------
        // El backend puede responder con HTTP 4xx/5xx y un JSON
        // { success:false, error, fieldErrors }. Si hay formulario, esos
        // errores se pintan por campo igual que en una respuesta 200.
        config.error = function (xhr, textStatus, errorThrown) {
            var respuesta = (xhr && xhr.responseJSON) ? xhr.responseJSON : null;

            if (respuesta && $form.length && typeof EIS.mostrarErroresFormulario === 'function') {
                EIS.mostrarErroresFormulario($form, respuesta);
                return;
            }

            if (typeof onError === 'function') {
                onError(respuesta, xhr, textStatus);
                return;
            }

            // showNetworkError controla los fallos de la petición (red o
            // HTTP 4xx/5xx); showServerError controla success:false con 200.
            if (showNetworkError) {
                EIS.toast(
                    (respuesta && respuesta.error) ||
                        (respuesta ? serverErrorMessage : errorMessage),
                    'red',
                    'error'
                );
            }
        };

        return $.ajax(config);
    };

    // -----------------------------------------------------------------
    // EIS.ajax.get(url, opciones)
    // Atajo para peticiones GET.
    // -----------------------------------------------------------------
    EIS.ajax.get = function (url, opciones) {
        return EIS.ajax($.extend({}, opciones, {
            url: url,
            method: 'GET'
        }));
    };

    // -----------------------------------------------------------------
    // EIS.ajax.post(url, data, opciones)
    // Atajo para peticiones POST.
    // -----------------------------------------------------------------
    EIS.ajax.post = function (url, data, opciones) {
        return EIS.ajax($.extend({}, opciones, {
            url: url,
            method: 'POST',
            data: data
        }));
    };

    // -----------------------------------------------------------------
    // EIS.ajax.form(url, form, opciones)
    // Atajo para enviar un formulario por POST. Serializa el formulario
    // y activa el pintado automático de errores por campo.
    // -----------------------------------------------------------------
    EIS.ajax.form = function (url, form, opciones) {
        opciones = opciones || {};
        var $form = $(form);

        return EIS.ajax($.extend({}, opciones, {
            url: url,
            method: opciones.method || 'POST',
            form: $form,
            data: opciones.data !== undefined ? opciones.data : $form.serialize()
        }));
    };

})(window, window.jQuery);
