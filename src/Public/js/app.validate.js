// =====================================================================
// ARCHIVO: app.validate.js
// FUNCIÓN: Validación de formularios del lado del cliente (frontend).
//
//   - Aprovecha los atributos HTML5 estándar (required, minlength,
//     maxlength, min, max, pattern, type) y añade reglas reutilizables
//     propias a través de data-validate (cedula, rif, telefono, username,
//     email, nombre, moneda, entero, tiempo).
//   - Valida en tiempo real: al salir de un campo (blur) marca el error y
//     mientras el usuario lo corrige reevalúa en vivo.
//   - Bloquea el envío cuando hay errores y muestra el mismo panel estable
//     que devuelve el backend (EIS.mostrarErroresFormulario), de modo que
//     la experiencia es idéntica tanto si el error viene del navegador
//     como si viene del servidor.
//   - Evita envíos duplicados por doble clic.
//
//   USO: marcar el <form> con el atributo data-validate y añadir los
//   atributos de validación a cada campo. No se necesita JS por página.
// =====================================================================

(function ($) {
    'use strict';

    // Reutilizo el namespace global creado por app.core.js
    var EIS = window.EIS = window.EIS || {};

    // -----------------------------------------------------------------
    // Reglas reutilizables por tipo lógico (se activan con data-validate).
    // Cada regla tiene una expresión regular y un mensaje por defecto.
    // ---------------------------------------------------------------------
    var TIPOS = {
        cedula: {
            regex: /^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/,
            mensaje: 'Ingresa una cédula válida (entre 5 y 20 caracteres).'
        },
        rif: {
            regex: /^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/,
            mensaje: 'Ingresa un RIF válido (entre 5 y 20 caracteres).'
        },
        telefono: {
            regex: /^[0-9+\-()\s.]+$/,
            mensaje: 'Ingresa un teléfono válido (solo números y los signos + - ( ) .).'
        },
        username: {
            regex: /^[A-Za-z0-9][A-Za-z0-9_.\-]{2,49}$/,
            mensaje: 'El usuario debe tener al menos 3 caracteres: letras, números, punto, guion o guion bajo.'
        },
        email: {
            // Coincide con el criterio permisivo del backend (filter_var):
            // exige una arroba con texto a ambos lados, sin espacios.
            regex: /^[^\s@]+@[^\s@]+$/,
            mensaje: 'Ingresa un correo electrónico válido.'
        },
        nombre: {
            // Mismo conjunto de caracteres que Validator::PATTERN_TEXTO_LIBRE
            // (letras, números, espacios y puntuación básica) para no rechazar
            // valores que el servidor sí acepta.
            regex: /^[\p{L}\p{N}\p{M}\s.,;:()\-\/_&'+°#@%*?!]+$/u,
            mensaje: 'El nombre contiene caracteres no permitidos.'
        },
        moneda: {
            regex: /^\d{1,8}(?:\.\d{1,2})?$/,
            mensaje: 'Ingresa un monto válido (hasta 2 decimales).'
        },
        entero: {
            regex: /^-?\d+$/,
            mensaje: 'Ingresa un número entero válido.'
        },
        tiempo: {
            regex: /^\d{1,3}:[0-5]\d:[0-5]\d$/,
            mensaje: 'Usa el formato HH:MM:SS.'
        },
        password: {
            regex: /^(?=.*[A-Za-z])(?=.*\d).+$/,
            mensaje: 'La contraseña debe incluir al menos una letra y un número.'
        }
    };

    // Mensajes genéricos; los que necesitan un dato se construyen con función.
    var MENSAJES = {
        required:  'Este campo es obligatorio.',
        email:     'Ingresa un correo electrónico válido.',
        numero:    'Ingresa un número válido.',
        pattern:   'El formato de este campo no es válido.',
        match:     'Los valores no coinciden.',
        minlength: function (n) { return 'Debe tener al menos ' + n + ' caracteres.'; },
        maxlength: function (n) { return 'No puede exceder ' + n + ' caracteres.'; },
        min:       function (n) { return 'El valor mínimo permitido es ' + n + '.'; },
        max:       function (n) { return 'El valor máximo permitido es ' + n + '.'; }
    };

    var SELECTOR_CAMPOS = 'input, select, textarea';

    // -----------------------------------------------------------------
    // Función interna: devuelve el valor textual de un control.
    // -----------------------------------------------------------------
    function valorDe($el) {
        var v = $el.val();
        if (v === null || v === undefined) return '';
        return (typeof v === 'string') ? v : String(v);
    }

    // -----------------------------------------------------------------
    // Función interna: mensaje para una regla, respetando el override
    // data-msg-<regla> definido en el propio campo.
    // -----------------------------------------------------------------
    function mensaje(clave, $el, arg) {
        var override = $el.attr('data-msg-' + clave);
        if (override) return override;
        var m = MENSAJES[clave];
        return (typeof m === 'function') ? m(arg) : m;
    }

    // -----------------------------------------------------------------
    // Función interna: contenedor visible donde insertar el mensaje de
    // error (misma convención que EIS.mostrarErroresFormulario).
    // -----------------------------------------------------------------
    function contenedorDe($el) {
        if ($el.closest('.select-wrapper').length) return $el.closest('.select-wrapper');
        if ($el.closest('.input-field, .select-wrapper').length) return $el.closest('.input-field, .select-wrapper');
        return $el.parent();
    }

    // -----------------------------------------------------------------
    // EIS.quitarErrorCampo(el)
    // Limpia el mensaje y el resaltado de un único campo.
    // -----------------------------------------------------------------
    EIS.quitarErrorCampo = function (el) {
        var $el = $(el);
        if (!$el.length) return;
        var $wrap = contenedorDe($el);
        $wrap.find('.eis-field-error').remove();
        $wrap.removeClass('eis-invalid invalid');
        $el.removeClass('invalid eis-invalid');
    };

    // -----------------------------------------------------------------
    // EIS.marcarErrorCampo(el, mensaje)
    // Resalta un campo y muestra su mensaje debajo, sin tocar los demás.
    // -----------------------------------------------------------------
    EIS.marcarErrorCampo = function (el, texto) {
        var $el = $(el);
        if (!$el.length) return;
        EIS.quitarErrorCampo($el);

        var $wrap = contenedorDe($el);
        if ($el.is('select')) {
            $wrap.addClass('invalid eis-invalid');
        } else {
            $el.addClass('invalid eis-invalid');
        }
        $('<div class="eis-field-error helper-text" style="color:#c62828;font-weight:500;"></div>')
            .text(texto)
            .appendTo($wrap);
    };

    // -----------------------------------------------------------------
    // EIS.validarCampo(el)
    // Devuelve el mensaje de error del campo o '' si es válido.
    // -----------------------------------------------------------------
    EIS.validarCampo = function (el) {
        var $el = $(el);
        if (!$el.length) return '';
        if ($el.is(':disabled') || $el.is('[readonly]')) return '';
        if ($el.is('[type="hidden"], [type="submit"], [type="button"], [type="reset"]')) return '';

        var v      = valorDe($el);
        var vTrim  = v.trim();
        var vacio  = $el.is('select') ? ($el.val() === null || v === '') : (vTrim === '');

        // Campo obligatorio -------------------------------------------------
        if ($el.is('[required]') && vacio) {
            return mensaje('required', $el);
        }

        // Un campo opcional vacío no sigue validando (salvo selects, que ya
        // se contemplaron arriba).
        if (vacio && !$el.is('select')) {
            return '';
        }

        // Longitudes --------------------------------------------------------
        var minlen = parseInt($el.attr('minlength'), 10);
        if (!isNaN(minlen) && !$el.is('select') && v.length < minlen) {
            return mensaje('minlength', $el, minlen);
        }
        var maxlen = parseInt($el.attr('maxlength'), 10);
        if (!isNaN(maxlen) && v.length > maxlen) {
            return mensaje('maxlength', $el, maxlen);
        }

        var tipo_input = ($el.attr('type') || '').toLowerCase();

        // Numéricos ---------------------------------------------------------
        if (tipo_input === 'number') {
            var num = parseFloat(v);
            if (isNaN(num)) return mensaje('numero', $el);
            var min = parseFloat($el.attr('min'));
            if (!isNaN(min) && num < min) return mensaje('min', $el, min);
            var max = parseFloat($el.attr('max'));
            if (!isNaN(max) && num > max) return mensaje('max', $el, max);
        }

        // Email nativo ------------------------------------------------------
        if (tipo_input === 'email' && !TIPOS.email.regex.test(vTrim)) {
            return mensaje('email', $el);
        }

        // Teléfono nativo ---------------------------------------------------
        if (tipo_input === 'tel' && !TIPOS.telefono.regex.test(vTrim)) {
            return $el.attr('data-msg-telefono') || TIPOS.telefono.mensaje;
        }

        // Reglas propias (data-validate="cedula telefono ...") --------------
        var declaradas = ($el.attr('data-validate') || '').toString().split(/[\s,]+/);
        var aplicoPatron = false;
        for (var i = 0; i < declaradas.length; i++) {
            var clave = declaradas[i];
            if (!clave) continue;
            var tipoDato = TIPOS[clave];
            if (tipoDato) {
                aplicoPatron = true;
                if (!tipoDato.regex.test(vTrim)) {
                    return $el.attr('data-msg-' + clave) || tipoDato.mensaje;
                }
            }
        }

        // Atributo pattern nativo (si ninguna regla propia lo cubrió) -------
        var patron = $el.attr('pattern');
        if (patron && !aplicoPatron) {
            var re;
            try {
                re = new RegExp('^(?:' + patron + ')$');
            } catch (err) {
                re = null;
            }
            if (re && !re.test(v)) {
                return mensaje('pattern', $el);
            }
        }

        // Confirmación de contraseña / campo espejo -------------------------
        var selectorMatch = $el.attr('data-match');
        if (selectorMatch) {
            var $otro = $(selectorMatch).first();
            if ($otro.length && $otro.val() !== $el.val()) {
                return $el.attr('data-msg-match') || MENSAJES.match;
            }
        }

        return '';
    };

    // -----------------------------------------------------------------
    // EIS.validarFormulario(form)
    // Devuelve un mapa { nombreCampo: mensaje } con los errores hallados.
    // -----------------------------------------------------------------
    EIS.validarFormulario = function (form) {
        var $form = $(form);
        var errores = {};
        if (!$form.length) return errores;

        $form.find(SELECTOR_CAMPOS).each(function () {
            var nombre = $(this).attr('name');
            if (!nombre) return;
            var m = EIS.validarCampo(this);
            if (m) errores[nombre] = m;
        });

        return errores;
    };

    // -----------------------------------------------------------------
    // EIS.esFormularioValido(form)
    // Atajo booleano sobre EIS.validarFormulario.
    // -----------------------------------------------------------------
    EIS.esFormularioValido = function (form) {
        return Object.keys(EIS.validarFormulario(form)).length === 0;
    };

    // -----------------------------------------------------------------
    // Validación en vivo: al salir del campo y mientras se corrige.
    // -----------------------------------------------------------------
    $(document)
        .on('focusout', 'form[data-validate] ' + SELECTOR_CAMPOS, function () {
            var $el = $(this);
            if (!$el.attr('name')) return;
            var m = EIS.validarCampo(this);
            if (m) {
                EIS.marcarErrorCampo($el, m);
            } else {
                EIS.quitarErrorCampo($el);
            }
        })
        .on('input change', 'form[data-validate] ' + SELECTOR_CAMPOS, function () {
            var $el = $(this);
            var $wrap = contenedorDe($el);
            // Solo reevalúo en vivo los campos que ya estaban en error.
            var enError = $el.hasClass('eis-invalid') || $wrap.hasClass('eis-invalid') || $wrap.find('.eis-field-error').length;
            if (!enError) return;
            var m = EIS.validarCampo(this);
            if (m) EIS.marcarErrorCampo($el, m);
            else EIS.quitarErrorCampo($el);
        });

    // -----------------------------------------------------------------
    // Bloqueo del envío con errores.
    //
    // Se registra en fase de captura para ejecutarse ANTES de los manejadores
    // de los módulos (que hacen el $.post), pudiendo detener el envío con
    // stopImmediatePropagation cuando el formulario no es válido.
    // -----------------------------------------------------------------
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM' || !form.hasAttribute('data-validate')) return;

        // Evita envíos duplicados por doble clic (ventana de 800 ms).
        var ahora = Date.now();
        var ultimo = parseInt(form.getAttribute('data-eis-enviando') || '0', 10);
        if (ultimo && (ahora - ultimo) < 800) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        var errores = EIS.validarFormulario(form);
        if (Object.keys(errores).length) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (EIS.mostrarErroresFormulario) {
                EIS.mostrarErroresFormulario(form, {
                    error: 'Revisa los campos marcados e intenta de nuevo.',
                    fieldErrors: errores
                });
            } else {
                $.each(errores, function (nombre, texto) {
                    EIS.marcarErrorCampo($(form).find('[name="' + nombre + '"]').first(), texto);
                });
            }
            return;
        }

        // Formulario válido: registro la marca para evitar doble envío y
        // limpio cualquier error previo.
        form.setAttribute('data-eis-enviando', String(ahora));
        if (EIS.limpiarErroresFormulario) {
            EIS.limpiarErroresFormulario(form);
        }
    }, true);

    // -----------------------------------------------------------------
    // Desactivo la validación nativa del navegador en los formularios
    // marcados con data-validate: así el evento submit siempre se dispara y
    // los mensajes se muestran siempre con el mismo formato propio en lugar
    // de las burbujas nativas del navegador.
    // -----------------------------------------------------------------
    $(function () {
        $('form[data-validate]').each(function () { this.noValidate = true; });
    });

    // -----------------------------------------------------------------
    // API pública para extender reglas desde otros módulos.
    //   EIS.reglasValidacion.cedula = { regex: /.../, mensaje: '...' }
    // -----------------------------------------------------------------
    EIS.reglasValidacion = TIPOS;

})(jQuery);
