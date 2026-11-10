// =====================================================================
// ARCHIVO: app.validate.js
// FUNCIÓN: Validación de formularios del lado del cliente (frontend).
//
//   - Usa como fuente única de patrones el módulo EIS.Regex, generado
//     desde regex_validations.json (src/Public/js/regex_validations.js).
//   - Aprovecha los atributos HTML5 estándar (required, minlength,
//     maxlength, min, max, pattern, type) y añade reglas reutilizables
//     propias a través de data-validate.
//   - data-validate acepta alias cortos (cedula, rif, telefono, email,
//     fecha, hora, moneda...) y también claves completas del JSON
//     (contacto.email, aplicacion.descripcion, seguridad.password_media).
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
    var Regex = EIS.Regex || null;

    // Si el plugin jQuery Validation está cargado, lo usamos como motor de
    // validación. La validación propia se conserva como respaldo y como
    // fuente única de reglas (regex_validations.json).
    var USAR_JQUERY_VALIDATE = !!(window.jQuery && $.fn && typeof $.fn.validate === 'function');

    if (!Regex && window.console && console.warn) {
        console.warn('app.validate.js: EIS.Regex no está disponible. Carga regex_validations.js antes de este archivo; solo se aplicarán las validaciones HTML5.');
    }

    // -----------------------------------------------------------------
    // Alias cortos -> clave del JSON de validaciones.
    //
    // Los alias conservan la compatibilidad con los formularios ya
    // existentes. Cualquier clave del JSON también puede usarse
    // directamente en data-validate.
    // -----------------------------------------------------------------
    var ALIASES = {
        // Identificación
        cedula:                 'identificacion_venezuela.cedula_venezolana',
        rif:                    'identificacion_venezuela.rif_venezolano',
        pasaporte:              'identificacion_venezuela.pasaporte',

        // Contacto
        email:                  'contacto.email',
        correo:                 'contacto.email',
        email_simple:           'contacto.email_simple',
        telefono:               'contacto.telefono_flexible',
        telefono_movil:         'contacto.telefono_venezuela',
        telefono_fijo:          'contacto.telefono_fijo_venezuela',
        telefono_internacional: 'contacto.telefono_internacional',
        whatsapp:               'contacto.whatsapp',
        url:                    'contacto.sitio_web',
        sitio_web:              'contacto.sitio_web',
        dominio:                'contacto.dominio',

        // Textos y personas
        username:               'texto_y_personas.nombre_usuario',
        nombre:                 'aplicacion.texto_libre',
        nombre_persona:         'texto_y_personas.nombre_persona',
        nombre_completo:        'texto_y_personas.nombre_completo',
        texto:                  'aplicacion.texto_libre',
        texto_alfabetico:       'texto_y_personas.texto_alfabetico',
        descripcion:            'aplicacion.descripcion',
        direccion:              'aplicacion.direccion',
        codigo:                 'aplicacion.codigo_producto',
        numero_orden:           'aplicacion.numero_orden',
        nombre_rol:             'aplicacion.nombre_rol',

        // Fechas y horas
        fecha:                  'fechas_y_horas.fecha_iso',
        fecha_latina:           'fechas_y_horas.fecha_latina',
        fecha_hora:             'fechas_y_horas.fecha_hora_iso',
        hora:                   'fechas_y_horas.hora_24',
        hora_12:                'fechas_y_horas.hora_12',
        tiempo:                 'aplicacion.tiempo_uso_hms',

        // Números
        entero:                 'aplicacion.entero',
        decimal:                'numerico.decimal',
        decimal_positivo:       'numerico.decimal_positivo',
        moneda:                 'aplicacion.monto',
        porcentaje:             'numerico.porcentaje',
        codigo_postal:          'numerico.codigo_postal',

        // Seguridad
        password:               'seguridad.password_media',
        password_basica:        'seguridad.password_basica',
        password_media:         'seguridad.password_media',
        password_fuerte:        'seguridad.password_fuerte',
        password_muy_fuerte:    'seguridad.password_muy_fuerte',
        pin:                    'seguridad.password_pin',

        // Redes y desarrollo
        ipv4:                   'redes.ipv4',
        ipv6:                   'redes.ipv6',
        mac:                    'redes.mac_address',
        puerto:                 'redes.puerto',
        uuid:                   'seguridad.uuid_v4',
        slug:                   'desarrollo.slug',
        color:                  'desarrollo.hex_color',
        version:                'desarrollo.version_semver'
    };

    // Mensajes genéricos; los que necesitan un dato se construyen con función.
    var MENSAJES = {
        required:  'Este campo es obligatorio.',
        email:     'Ingresa un correo electrónico válido.',
        numero:    'Ingresa un número válido.',
        pattern:   'El formato de este campo no es válido.',
        match:     'Los valores no coinciden.',
        minlength: function (n) { return 'Debe tener al menos ' + n + ' caracteres.'; },
        maxlength: function (n) { return 'No puede superar los ' + n + ' caracteres.'; },
        min:       function (n) { return 'El valor mínimo permitido es ' + n + '.'; },
        max:       function (n) { return 'El valor máximo permitido es ' + n + '.'; }
    };

    var SELECTOR_CAMPOS = 'input, select, textarea';

    var TIPOS_CONTROL = {
        hidden: true, submit: true, button: true, reset: true, image: true, file: true
    };

    // Íconos Material usados en el feedback inline. Se mantienen en un solo
    // lugar para que el lenguaje visual sea consistente en todo el sistema.
    var ICONO_ERROR = 'error_outline';
    var ICONO_PISTA = 'lightbulb_outline';
    var ICONO_OK    = 'check_circle';

    // Tipos que admiten un ícono de estado a la derecha del campo. Los
    // selects ya tienen su propio caret y los date/time su propio ícono.
    var TIPOS_CON_ESTADO = {
        '': true, text: true, search: true, email: true, tel: true,
        url: true, password: true, number: true
    };

    var secuenciaFeedback = 0;
    var ultimosToasts = {};

    // -----------------------------------------------------------------
    // Utilidades internas
    // -----------------------------------------------------------------
    function valorDe($el) {
        var v = $el.val();
        if (v === null || v === undefined) return '';
        if ($.isArray(v)) return v.join(',');
        return (typeof v === 'string') ? v : String(v);
    }

    function aEntero(valor) {
        var n = parseInt(valor, 10);
        return isNaN(n) ? null : n;
    }

    function normalizarFlags(flags) {
        // JavaScript solo admite estos modificadores para nuestros patrones.
        // No se usan g/y para evitar el estado de lastIndex.
        var permitidos = 'imsu';
        var salida = '';
        var texto = (typeof flags === 'string') ? flags : '';
        for (var i = 0; i < texto.length; i++) {
            var flag = texto.charAt(i).toLowerCase();
            if (permitidos.indexOf(flag) !== -1 && salida.indexOf(flag) === -1) {
                salida += flag;
            }
        }
        if (salida.indexOf('u') === -1) salida += 'u';
        return salida;
    }

    function compilarRegex(patron, flags) {
        try {
            return new RegExp(patron, normalizarFlags(flags));
        } catch (e) {
            return null;
        }
    }

    function regexDeRegla(regla) {
        if (!regla) return null;
        if (regla.regex instanceof RegExp) {
            // Copia defensiva para no arrastrar lastIndex si el usuario usó /g.
            return new RegExp(regla.regex.source, normalizarFlags(regla.regex.flags));
        }
        if (typeof regla.pattern === 'string' && regla.pattern !== '') {
            return compilarRegex(regla.pattern, regla.flags);
        }
        return null;
    }

    function mensaje(clave, $el, arg) {
        var override = $el.attr('data-msg-' + clave);
        if (override) return override;
        var m = MENSAJES[clave];
        return (typeof m === 'function') ? m(arg) : m;
    }

    function overrideRegla($el, token, clave) {
        var texto = $el.attr('data-msg-' + token);
        if (texto) return texto;
        if (clave && clave !== token) {
            texto = $el.attr('data-msg-' + clave);
            if (texto) return texto;
        }
        return '';
    }

    function contenedorDe($el) {
        // Prefiero el .input-field porque contiene la etiqueta flotante y es
        // donde Materialize espera los mensajes; el .select-wrapper queda
        // como respaldo para selects sin .input-field.
        if ($el.closest('.input-field').length) return $el.closest('.input-field');
        if ($el.closest('.select-wrapper').length) return $el.closest('.select-wrapper');
        // Checkbox/radio suelen vivir dentro de <label>; el mensaje debe
        // quedar fuera de la etiqueta para no heredar su comportamiento de
        // clic ni mezclarse con el texto de la opción.
        if (esCheckable($el)) {
            var $grupo = $el.closest('p, .form-group, .checkbox-group');
            if ($grupo.length) return $grupo;
        }
        return $el.parent();
    }

    function tipoDe($el) {
        return ($el.attr('type') || '').toLowerCase();
    }

    function esPassword($el) {
        return tipoDe($el) === 'password';
    }

    function esCheckable($el) {
        var tipo = tipoDe($el);
        return tipo === 'checkbox' || tipo === 'radio';
    }

    function elementosDelGrupo(el) {
        if (!el || !el.form || !el.name) return [el];
        var lista = el.form.elements.namedItem(el.name);
        if (!lista) return [el];
        if (typeof lista.length === 'number' && !lista.tagName) {
            return Array.prototype.slice.call(lista);
        }
        return [lista];
    }

    function grupoTieneRequerido(el) {
        var grupo = elementosDelGrupo(el);
        for (var i = 0; i < grupo.length; i++) {
            if (grupo[i].required) return true;
        }
        return false;
    }

    function grupoMarcado(el) {
        var grupo = elementosDelGrupo(el);
        for (var i = 0; i < grupo.length; i++) {
            if (grupo[i].checked) return true;
        }
        return false;
    }

    // -----------------------------------------------------------------
    // Reglas de compatibilidad: se exponen como EIS.reglasValidacion y
    // permiten que otros módulos añadan o sobrescriban reglas con la
    // forma { regex: /.../, mensaje: '...' }.
    // -----------------------------------------------------------------
    function crearReglasCompat() {
        var reglas = {};

        if (!Regex) return reglas;

        $.each(ALIASES, function (alias, clave) {
            var regla = Regex.get(clave);
            if (!regla || typeof regla.pattern !== 'string') return;

            var regex = compilarRegex(regla.pattern, regla.flags);
            if (!regex) return;

            reglas[alias] = {
                regex: regex,
                mensaje: regla.mensaje,
                label: regla.label,
                ejemplo: regla.ejemplo_valido || '',
                minlength: regla.minlength,
                maxlength: regla.maxlength,
                clave: clave
            };
        });

        return reglas;
    }

    EIS.reglasValidacion = $.extend({}, crearReglasCompat(), EIS.reglasValidacion || {});

    // -----------------------------------------------------------------
    // Resuelve una regla declarada en data-validate a un objeto con:
    //   regex, mensaje, minlength, maxlength, label y clave (clave JSON).
    // Devuelve null si la regla no existe, para no bloquear por errores
    // de tipeo en el atributo.
    // -----------------------------------------------------------------
    function resolverRegla(clave) {
        if (!clave) return null;

        // 1) Reglas registradas/sobrescritas manualmente (compatibilidad).
        var manual = EIS.reglasValidacion[clave];
        if (manual && (manual.regex instanceof RegExp || typeof manual.pattern === 'string')) {
            return {
                regex: regexDeRegla(manual),
                mensaje: manual.mensaje || '',
                label: manual.label || clave,
                ejemplo: manual.ejemplo || manual.ejemplo_valido || '',
                minlength: aEntero(manual.minlength),
                maxlength: aEntero(manual.maxlength),
                clave: manual.clave || clave
            };
        }

        if (!Regex) return null;

        // 2) Alias corto -> clave del JSON.
        var claveJson = ALIASES[clave] || null;

        // 3) Clave completa del JSON usada directamente.
        if (!claveJson && Regex.has(clave)) {
            claveJson = clave;
        }

        if (!claveJson) return null;

        var regla = Regex.get(claveJson);
        if (!regla || typeof regla.pattern !== 'string') return null;

        var regex = compilarRegex(regla.pattern, regla.flags);
        if (!regex) return null;

        return {
            regex: regex,
            mensaje: regla.mensaje || '',
            label: regla.label || claveJson,
            ejemplo: regla.ejemplo_valido || '',
            minlength: aEntero(regla.minlength),
            maxlength: aEntero(regla.maxlength),
            clave: claveJson
        };
    }

    // -----------------------------------------------------------------
    // Validaciones semánticas que un patrón no puede cubrir por sí solo
    // (por ejemplo, 2026-02-31 cumple el formato pero no existe).
    // -----------------------------------------------------------------
    function esFechaISOReal(texto) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(texto);
        if (!m) return false;
        return esFechaValida(+m[1], +m[2], +m[3]);
    }

    function esFechaLatinaReal(texto) {
        var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(texto);
        if (!m) return false;
        return esFechaValida(+m[3], +m[2], +m[1]);
    }

    function esFechaHoraISOReal(texto) {
        var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})$/.exec(texto);
        if (!m) return false;
        if (!esFechaValida(+m[1], +m[2], +m[3])) return false;
        return (+m[4] <= 23) && (+m[5] <= 59);
    }

    function esFechaValida(anio, mes, dia) {
        if (mes < 1 || mes > 12 || dia < 1 || dia > 31) return false;
        var fecha = new Date(Date.UTC(anio, mes - 1, dia));
        return fecha.getUTCFullYear() === anio
            && fecha.getUTCMonth() === mes - 1
            && fecha.getUTCDate() === dia;
    }

    function validarSemantica(clave, valor) {
        if (!clave) return true;

        if (clave === 'fechas_y_horas.fecha_iso') return esFechaISOReal(valor);
        if (clave === 'fechas_y_horas.fecha_latina') return esFechaLatinaReal(valor);
        if (clave === 'fechas_y_horas.fecha_hora_iso') return esFechaHoraISOReal(valor);

        return true;
    }

    // -----------------------------------------------------------------
    // Utilidades de feedback inline
    // -----------------------------------------------------------------
    function escaparHtml(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function idFeedback(el) {
        if (!el.__eisFeedbackId) {
            el.__eisFeedbackId = 'eis-feedback-' + (++secuenciaFeedback);
        }
        return el.__eisFeedbackId;
    }

    function puedeLlevarEstado($el) {
        if ($el.is('select, textarea')) return false;
        return !!TIPOS_CON_ESTADO[tipoDe($el)];
    }

    function quitarEstadoVisual($el) {
        var $wrap = contenedorDe($el);
        $wrap.find('.eis-field-status').remove();
        $wrap.removeClass('eis-has-status');
    }

    function ponerEstadoVisual($el, estado, icono) {
        if (!puedeLlevarEstado($el)) return;

        // Solo dentro de contenedores Materialize posicionados, para que el
        // ícono no termine flotando en un lugar inesperado de la página.
        var $wrap = contenedorDe($el);
        if (!$wrap.is('.input-field, .select-wrapper')) return;

        quitarEstadoVisual($el);
        $('<span class="eis-field-status eis-status-' + estado + '" aria-hidden="true">' +
            '<i class="material-icons">' + icono + '</i></span>')
            .appendTo($wrap);
        $wrap.addClass('eis-has-status');
    }

    // Calcula el texto del contador de caracteres. Devuelve null cuando el
    // campo no tiene maxlength o no conviene mostrarlo todavía.
    function datosContador($el, forzar) {
        var max = aEntero($el.attr('maxlength'));
        if (max === null || max <= 0) return null;
        // Controles donde el contador no aporta (opciones, archivos) y
        // contraseñas, donde el límite suele ser técnico y no de contenido.
        if ($el.is('select, input[type="checkbox"], input[type="radio"], input[type="file"]')) return null;
        if (esPassword($el)) return null;

        var largo = valorDe($el).length;
        var mostrar = (forzar === true) || $el.is(':focus') || largo > 0;
        if (!mostrar) return null;

        var clase = '';
        if (largo >= max) clase = 'eis-counter-limit';
        else if (largo >= Math.max(1, Math.floor(max * 0.8))) clase = 'eis-counter-warn';

        return {
            texto: largo + ' / ' + max,
            clase: clase,
            restantes: max - largo
        };
    }

    // Construye (o actualiza) la fila de feedback debajo del campo:
    //   [icono] mensaje                        12 / 100
    // tipo: 'error' | 'hint' | '' (sin mensaje, solo contador).
    function actualizarFeedback($el, tipo, texto, forzarContador) {
        var $wrap = contenedorDe($el);
        var contador = datosContador($el, forzarContador);
        var $fb = $wrap.children('.eis-field-feedback').first();

        if (!texto && !contador) {
            $fb.remove();
            $el.removeAttr('aria-describedby');
            return;
        }

        if (!$fb.length) {
            $fb = $('<div class="eis-field-feedback" aria-live="polite"></div>').appendTo($wrap);
        }

        var anterior = $fb.attr('data-eis-mensaje') || '';
        var cambio = (texto || '') !== anterior;

        $fb
            .removeClass('eis-field-error eis-field-hint eis-feedback-enter')
            .addClass(texto ? 'eis-field-' + tipo : '')
            .attr('data-eis-mensaje', texto || '');

        $fb.empty();

        if (texto) {
            var icono = (tipo === 'error') ? ICONO_ERROR : ICONO_PISTA;
            var $linea = $('<span class="eis-feedback-text"></span>');
            $('<i class="material-icons eis-feedback-icon" aria-hidden="true"></i>')
                .text(icono)
                .appendTo($linea);
            $('<span class="eis-feedback-message"></span>')
                .text(texto)
                .appendTo($linea);
            $fb.append($linea);

            $fb.attr({
                id: idFeedback($el[0]),
                role: (tipo === 'error') ? 'alert' : 'status'
            });
            $el.attr('aria-describedby', idFeedback($el[0]));
        } else {
            $fb.removeAttr('id role');
            $el.removeAttr('aria-describedby');
        }

        if (contador) {
            $('<span class="eis-field-counter"></span>')
                .addClass(contador.clase)
                .attr('title', contador.restantes + ' caracteres disponibles')
                .text(contador.texto)
                .appendTo($fb);
        }

        if (cambio) {
            $fb.addClass('eis-feedback-enter').one('animationend', function () {
                $(this).removeClass('eis-feedback-enter');
            });
        }
    }

    // Pinta la pista de formato inline (solo si no hay un error visible).
    function mostrarPistaInline($el) {
        var $wrap = contenedorDe($el);
        if ($wrap.children('.eis-field-error').length) return;
        var pista = pistaFormato($el[0]);
        actualizarFeedback($el, pista ? 'hint' : '', pista || '', true);
    }

    // -----------------------------------------------------------------
    // EIS.quitarErrorCampo(el)
    // Limpia el mensaje, el resaltado y el estado visual de un único campo.
    // -----------------------------------------------------------------
    EIS.quitarErrorCampo = function (el) {
        var $el = $(el);
        if (!$el.length) return;

        var $wrap = contenedorDe($el);
        $wrap.children('.eis-field-feedback, .eis-field-error').remove();
        quitarEstadoVisual($el);

        $wrap.removeClass('eis-invalid eis-valid invalid');
        $el.removeClass('invalid eis-invalid eis-valid')
            .removeAttr('aria-invalid')
            .removeAttr('aria-describedby');

        if ($el.is('select')) {
            $el.closest('.select-wrapper').removeClass('eis-invalid eis-valid invalid');
        }
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
        if ($el.is('select') || esCheckable($el)) {
            $wrap.addClass('invalid eis-invalid');
            if ($el.is('select')) $el.closest('.select-wrapper').addClass('invalid eis-invalid');
        } else {
            $el.addClass('invalid eis-invalid');
        }

        $el.attr('aria-invalid', 'true');
        actualizarFeedback($el, 'error', texto, true);
        ponerEstadoVisual($el, 'error', ICONO_ERROR);
    };

    // -----------------------------------------------------------------
    // EIS.marcarCampoValido(el)
    // Confirma visualmente que el campo cumple su formato.
    // -----------------------------------------------------------------
    EIS.marcarCampoValido = function (el) {
        var $el = $(el);
        if (!$el.length) return;
        EIS.quitarErrorCampo($el);

        var $wrap = contenedorDe($el);
        $wrap.addClass('eis-valid');
        $el.addClass('eis-valid').attr('aria-invalid', 'false');
        if ($el.is('select')) $el.closest('.select-wrapper').addClass('eis-valid');

        actualizarFeedback($el, '', '', true);
        ponerEstadoVisual($el, 'valid', ICONO_OK);
    };

    EIS.actualizarFeedbackCampo = actualizarFeedback;

    // -----------------------------------------------------------------
    // Comprueba una regla concreta. Devuelve el mensaje de error o ''.
    // -----------------------------------------------------------------
    function comprobarRegla($el, token, valor) {
        var regla = resolverRegla(token);
        if (!regla || !regla.regex) return '';

        // El valor que se prueba es el mismo que usa el backend (trim),
        // salvo en contraseñas, donde los espacios son significativos.
        var valorPrueba = valor;

        // datetime-local usa 'T' como separador; el patrón JSON usa espacio.
        if (regla.clave === 'fechas_y_horas.fecha_hora_iso' && tipoDe($el) === 'datetime-local') {
            valorPrueba = valor.replace('T', ' ');
        }

        if (!regla.regex.test(valorPrueba)) {
            return overrideRegla($el, token, regla.clave)
                || regla.mensaje
                || mensaje('pattern', $el);
        }

        if (!validarSemantica(regla.clave, valorPrueba)) {
            return overrideRegla($el, token, regla.clave)
                || regla.mensaje
                || mensaje('pattern', $el);
        }

        return '';
    }

    // -----------------------------------------------------------------
    // EIS.validarCampo(el)
    // Devuelve el mensaje de error del campo o '' si es válido.
    // -----------------------------------------------------------------
    EIS.validarCampo = function (el) {
        var $el = $(el);
        if (!$el.length) return '';
        if ($el.is(':disabled') || $el.is('[readonly]')) return '';

        var tipo = tipoDe($el);
        if (TIPOS_CONTROL[tipo]) return '';

        var esCheck = esCheckable($el);
        var esRadio = tipo === 'radio';
        var requerido = $el.is('[required]') || (esCheck && grupoTieneRequerido(el));

        // Checkbox / radio ------------------------------------------------
        if (esCheck) {
            var marcado = esRadio ? grupoMarcado(el) : el.checked;
            if (!marcado) {
                return requerido ? mensaje('required', $el) : '';
            }
            // Si está marcado, se valida su value (normalmente 'on').
        }

        var valor = valorDe($el);
        var password = esPassword($el);
        var texto = password ? valor : valor.trim();
        var vacio = esCheck ? false : (texto === '');

        // Select sin opción elegida.
        if ($el.is('select') && (valor === '' || valor === null)) {
            return requerido ? mensaje('required', $el) : '';
        }

        // Campo obligatorio ----------------------------------------------
        if (requerido && vacio) {
            return mensaje('required', $el);
        }

        // Un campo opcional vacío no sigue validando.
        if (vacio) return '';

        // Longitudes -----------------------------------------------------
        // Se aplican tanto los atributos HTML5 como los metadatos del JSON.
        var minAttr = aEntero($el.attr('minlength'));
        var maxAttr = aEntero($el.attr('maxlength'));
        var minJson = null;
        var maxJson = null;

        var declaradas = ($el.attr('data-validate') || '').toString().split(/[\s,]+/);
        var aplicadas = [];
        for (var i = 0; i < declaradas.length; i++) {
            var token = declaradas[i];
            if (!token) continue;
            var regla = resolverRegla(token);
            if (!regla || !regla.regex) continue;
            aplicadas.push({ token: token, regla: regla });
            if (regla.minlength !== null && (minJson === null || regla.minlength > minJson)) minJson = regla.minlength;
            if (regla.maxlength !== null && (maxJson === null || regla.maxlength < maxJson)) maxJson = regla.maxlength;
        }

        var min = (minAttr !== null) ? minAttr : minJson;
        if (min !== null && texto.length < min) {
            return mensaje('minlength', $el, min);
        }

        // Si el atributo no existe pero el JSON sí impone un mínimo más
        // estricto, también se respeta.
        if (minJson !== null && minJson !== min && texto.length < minJson) {
            return mensaje('minlength', $el, minJson);
        }

        var max = (maxAttr !== null) ? maxAttr : maxJson;
        if (max !== null && texto.length > max) {
            return mensaje('maxlength', $el, max);
        }

        // Si el atributo no existe pero el JSON sí impone un máximo más
        // estricto, también se respeta (p. ej. contraseñas de 72 caracteres).
        if (maxJson !== null && maxJson !== max && texto.length > maxJson) {
            return mensaje('maxlength', $el, maxJson);
        }

        // Numéricos -------------------------------------------------------
        if (tipo === 'number') {
            if (valor !== '' && !/^-?\d*\.?\d*$/.test(valor)) {
                return mensaje('numero', $el);
            }
            var num = parseFloat(valor);
            if (isNaN(num)) return mensaje('numero', $el);
            var minNum = parseFloat($el.attr('min'));
            if (!isNaN(minNum) && num < minNum) return mensaje('min', $el, minNum);
            var maxNum = parseFloat($el.attr('max'));
            if (!isNaN(maxNum) && num > maxNum) return mensaje('max', $el, maxNum);
        }

        // Tipos nativos HTML5 --------------------------------------------
        if (tipo === 'email') {
            var errorEmail = comprobarRegla($el, 'email', texto);
            if (errorEmail) return errorEmail;
        }

        if (tipo === 'tel') {
            var errorTel = comprobarRegla($el, 'telefono', texto);
            if (errorTel) return errorTel;
        }

        if (tipo === 'url') {
            var errorUrl = comprobarRegla($el, 'url', texto);
            if (errorUrl) return errorUrl;
        }

        // Reglas declaradas en data-validate -----------------------------
        for (var j = 0; j < aplicadas.length; j++) {
            var error = comprobarRegla($el, aplicadas[j].token, texto);
            if (error) return error;
        }

        // Atributo pattern nativo (solo si ninguna regla propia lo cubrió) -
        var patron = $el.attr('pattern');
        if (patron && aplicadas.length === 0) {
            var re = compilarRegex('^(?:' + patron + ')$', 'u');
            if (re && !re.test(valor)) {
                return mensaje('pattern', $el);
            }
        }

        // Confirmación de contraseña / campo espejo -----------------------
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

        // Motor principal: jQuery Validation Plugin (si está disponible).
        // checkForm() valida sin pintar errores; el pintado lo hace
        // EIS.mostrarErroresFormulario o el propio plugin en tiempo real.
        if (USAR_JQUERY_VALIDATE && EIS.initValidacionJQuery) {
            EIS.initValidacionJQuery($form[0]);
            var validator = $form.data('validator');
            if (validator) {
                var valido = validator.checkForm();
                if (!valido) {
                    $.each(validator.errorList || [], function (_, error) {
                        var nombre = $(error.element).attr('name');
                        if (!nombre) return;
                        if (Object.prototype.hasOwnProperty.call(errores, nombre)) return;
                        errores[nombre] = error.message;
                    });
                }
                return errores;
            }
        }

        // Respaldo: validación propia (también se usa si el plugin no cargó).
        $form.find(SELECTOR_CAMPOS).each(function () {
            var nombre = $(this).attr('name');
            if (!nombre) return;
            if (Object.prototype.hasOwnProperty.call(errores, nombre)) return;
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
    // FILTRADO ESTRICTO EN TIEMPO REAL
    // -----------------------------------------------------------------

    // Mapa de filtros por regla para bloquear caracteres inválidos al escribir
    var FILTROS_TIPO = {
        entero: function (v) { return v.replace(/[^0-9-]/g, ''); },
        decimal: function (v) { return v.replace(/[^0-9.-]/g, ''); },
        moneda: function (v) { return v.replace(/[^0-9.]/g, ''); },
        numero: function (v) { return v.replace(/[^0-9]/g, ''); },
        cedula: function (v) { return v.replace(/[^0-9VEJPGvejpg-]/g, ''); },
        rif: function (v) { return v.replace(/[^0-9VEJPGvejpg-]/g, ''); },
        telefono: function (v) { return v.replace(/[^0-9+\-\s()]/g, ''); },
        username: function (v) { return v.replace(/[^A-Za-z0-9._-]/g, ''); },
        codigo: function (v) { return v.replace(/[^A-Za-z0-9_.\-\/#\s]/g, ''); },
        numero_orden: function (v) { return v.replace(/[^A-Za-z0-9._\-\/\s]/g, ''); },
        nombre_usuario: function (v) { return v.replace(/[^A-Za-z0-9._-]/g, ''); },
        ipv4: function (v) { return v.replace(/[^0-9.]/g, ''); },
        puerto: function (v) { return v.replace(/[^0-9]/g, ''); }
    };

    function obtenerFiltro($el) {
        var declaradas = ($el.attr('data-validate') || '').toString().split(/[\s,]+/);
        for (var i = 0; i < declaradas.length; i++) {
            var token = declaradas[i].toLowerCase();
            if (FILTROS_TIPO[token]) return FILTROS_TIPO[token];
            var regla = resolverRegla(token);
            if (regla && regla.clave) {
                if (regla.clave === 'aplicacion.entero') return FILTROS_TIPO.entero;
                if (regla.clave === 'aplicacion.monto') return FILTROS_TIPO.moneda;
                if (regla.clave === 'numerico.decimal') return FILTROS_TIPO.decimal;
                if (regla.clave === 'identificacion_venezuela.cedula_venezolana') return FILTROS_TIPO.cedula;
                if (regla.clave === 'identificacion_venezuela.rif_venezolano') return FILTROS_TIPO.rif;
                if (regla.clave === 'contacto.telefono_flexible') return FILTROS_TIPO.telefono;
                if (regla.clave === 'texto_y_personas.nombre_usuario') return FILTROS_TIPO.username;
                if (regla.clave === 'aplicacion.codigo_producto') return FILTROS_TIPO.codigo;
                if (regla.clave === 'aplicacion.numero_orden') return FILTROS_TIPO.numero_orden;
            }
        }
        var tipo = tipoDe($el);
        if (tipo === 'number') return function (v) { return v.replace(/[^0-9.-]/g, ''); };
        return null;
    }

    // Con jQuery Validation activo, el plugin se encarga de la validación en
    // tiempo real (blur/keyup/click) y de pintar los mensajes. Solo se usa
    // esta capa propia cuando el plugin no está disponible.
    if (!USAR_JQUERY_VALIDATE) {
    $(document)
        .on('keypress', 'form[data-validate] input:not([type=checkbox]):not([type=radio]):not([type=password]):not([type=file]):not([type=hidden]):not([type=submit]):not([type=button])', function (e) {
            var $el = $(this);
            var filtro = obtenerFiltro($el);
            if (!filtro) return;
            var char = String.fromCharCode(e.which);
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            if (char === '\b' || char === '\t') return;
            var nuevo = ($el.val() || '') + char;
            var filtrado = filtro(nuevo);
            if (filtrado !== nuevo) {
                e.preventDefault();
                return false;
            }
        })
        .on('paste', 'form[data-validate] input:not([type=checkbox]):not([type=radio]):not([type=password]):not([type=file]):not([type=hidden])', function (e) {
            var $el = $(this);
            var filtro = obtenerFiltro($el);
            if (!filtro) return;
            setTimeout(function () {
                var val = $el.val() || '';
                var filtrado = filtro(val);
                if (filtrado !== val) {
                    $el.val(filtrado);
                    var m = EIS.validarCampo($el[0]);
                    if (m) { EIS.marcarErrorCampo($el[0], m); } else { EIS.quitarErrorCampo($el[0]); }
                }
            }, 0);
        })
        .on('input change', 'form[data-validate] ' + SELECTOR_CAMPOS, function () {
            var $el = $(this);
            // Aplicar filtro en caso de que haya pasado algo
            if (!$el.is('select') && !$el.is('textarea') && !esPassword($el) && !esCheckable($el)) {
                var filtro = obtenerFiltro($el);
                if (filtro) {
                    var val = $el.val() || '';
                    var filtrado = filtro(val);
                    if (filtrado !== val) {
                        var pos = this.selectionStart;
                        $el.val(filtrado);
                        if (pos !== null && this.setSelectionRange) {
                            try { this.setSelectionRange(pos - 1, pos - 1); } catch (ex) {}
                        }
                    }
                }
            }
            var m = EIS.validarCampo(this);
            if (m) {
                EIS.marcarErrorCampo($el, m);
            } else {
                EIS.quitarErrorCampo($el);
            }
        })
        .on('keyup', 'form[data-validate] ' + SELECTOR_CAMPOS, function () {
            var $el = $(this);
            var m = EIS.validarCampo(this);
            if (m) {
                EIS.marcarErrorCampo($el, m);
            } else {
                EIS.quitarErrorCampo($el);
            }
        })
        .on('focusout', 'form[data-validate] ' + SELECTOR_CAMPOS, function () {
            var $el = $(this);
            if (!$el.attr('name')) return;
            var m = EIS.validarCampo(this);
            if (m) {
                EIS.marcarErrorCampo($el, m);
            } else {
                EIS.quitarErrorCampo($el);
            }
        });
    }

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

    // =================================================================
    // INTEGRACIÓN CON JQUERY VALIDATION PLUGIN
    //
    // El plugin se encarga del ciclo de vida (blur, keyup, click, submit),
    // del foco al primer error y de pintar los mensajes. Las reglas siguen
    // saliendo de regex_validations.json a través de EIS.validarCampo, de
    // modo que cliente y servidor comparten la misma fuente.
    // =================================================================
    if (USAR_JQUERY_VALIDATE) {

        // Mensajes en español para las reglas nativas del plugin.
        $.extend($.validator.messages, {
            required: 'Este campo es obligatorio.',
            email: 'Ingresa un correo electrónico válido.',
            url: 'Ingresa una URL válida.',
            number: 'Ingresa un número válido.',
            digits: 'Solo se permiten dígitos.',
            minlength: $.validator.format('Debe tener al menos {0} caracteres.'),
            maxlength: $.validator.format('No puede superar los {0} caracteres.'),
            rangelength: $.validator.format('Debe tener entre {0} y {1} caracteres.'),
            min: $.validator.format('El valor mínimo permitido es {0}.'),
            max: $.validator.format('El valor máximo permitido es {0}.'),
            equalTo: 'Los valores no coinciden.',
            date: 'Ingresa una fecha válida.',
            dateISO: 'Ingresa una fecha válida (AAAA-MM-DD).',
            step: $.validator.format('El valor debe ser múltiplo de {0}.')
        });

        // Regla puente: toda la lógica de regex_validations.json se ejecuta
        // dentro del plugin a través de EIS.validarCampo.
        $.validator.addMethod('eisRegla', function (value, element) {
            return EIS.validarCampo(element) === '';
        }, function (param, element) {
            return EIS.validarCampo(element) || 'El valor no es válido.';
        });

        function tokensDe($el) {
            var texto = ($el.attr('data-validate') || '').toString();
            var partes = texto.split(/[\s,]+/);
            var tokens = [];
            for (var i = 0; i < partes.length; i++) {
                if (partes[i]) tokens.push(partes[i]);
            }
            return tokens;
        }

        function metadatosDe($el) {
            var minimo = aEntero($el.attr('minlength'));
            var maximo = aEntero($el.attr('maxlength'));
            var tokens = tokensDe($el);

            for (var i = 0; i < tokens.length; i++) {
                var regla = resolverRegla(tokens[i]);
                if (!regla) continue;
                if (regla.minlength !== null) {
                    minimo = (minimo === null) ? regla.minlength : Math.max(minimo, regla.minlength);
                }
                if (regla.maxlength !== null) {
                    maximo = (maximo === null) ? regla.maxlength : Math.min(maximo, regla.maxlength);
                }
            }

            return { minlength: minimo, maxlength: maximo, tokens: tokens };
        }

        // Construye el mapa de reglas del plugin a partir de los atributos
        // HTML5 y de data-validate. Así cualquier formulario nuevo queda
        // cubierto sin escribir JavaScript adicional.
        function construirReglas($form) {
            var rules = {};
            var messages = {};

            $form.find('input, select, textarea').each(function () {
                var $el = $(this);
                var nombre = $el.attr('name');
                if (!nombre) return;

                var tipo = tipoDe($el);
                if (TIPOS_CONTROL[tipo] || tipo === 'hidden') return;

                var meta = metadatosDe($el);
                var reglas = rules[nombre] || {};

                if ($el.is('[required]') || $el.prop('required')) {
                    reglas.required = true;
                }
                if (meta.minlength !== null && meta.minlength > 0) {
                    reglas.minlength = meta.minlength;
                }
                if (meta.maxlength !== null && meta.maxlength > 0) {
                    reglas.maxlength = meta.maxlength;
                }

                if (tipo === 'email') reglas.email = true;
                if (tipo === 'url') reglas.url = true;
                if (tipo === 'number') {
                    reglas.number = true;
                    var minNum = parseFloat($el.attr('min'));
                    var maxNum = parseFloat($el.attr('max'));
                    if (!isNaN(minNum)) reglas.min = minNum;
                    if (!isNaN(maxNum)) reglas.max = maxNum;
                }

                var selectorIgual = $el.attr('data-match');
                if (selectorIgual) reglas.equalTo = selectorIgual;

                // La regla puente cubre data-validate, los tipos email/tel/url,
                // el atributo pattern y la confirmación de contraseña.
                if (meta.tokens.length || tipo === 'email' || tipo === 'tel' ||
                    tipo === 'url' || $el.attr('pattern') || selectorIgual) {
                    reglas.eisRegla = true;
                }

                rules[nombre] = reglas;

                // Mensajes personalizados por campo (data-msg-*).
                var mensajesCampo = messages[nombre] || {};
                var claves = ['required', 'email', 'url', 'number', 'minlength',
                    'maxlength', 'min', 'max', 'equalTo', 'eisRegla'];
                for (var k = 0; k < claves.length; k++) {
                    var override = $el.attr('data-msg-' + claves[k]);
                    if (override) mensajesCampo[claves[k]] = override;
                }
                if (Object.keys(mensajesCampo).length) {
                    messages[nombre] = mensajesCampo;
                }
            });

            return { rules: rules, messages: messages };
        }

        function inicializarFormulario(form) {
            var $form = $(form);
            if (!$form.length) return null;

            var existente = $form.data('validator');
            if (existente) return existente;

            var config = construirReglas($form);

            var validator = $form.validate({
                rules: config.rules,
                messages: config.messages,
                errorElement: 'div',
                errorClass: 'eis-field-error',
                validClass: 'eis-valid',
                // Incluye los <select> nativos aunque Materialize los oculte,
                // pero excluye campos realmente ocultos y deshabilitados.
                ignore: function (index, element) {
                    if (element.disabled) return true;
                    var tag = element.tagName.toLowerCase();
                    if (tag === 'select' || tag === 'textarea') return false;
                    return $(element).is(':hidden');
                },
                // La capa propia (eventos input/change/focusout) gobierna el
                // feedback en tiempo real. El plugin se usa como motor de
                // validación al enviar y para EIS.validarFormulario.
                onkeyup: false,
                onfocusout: false,
                onclick: false,
                // Cuando el plugin valida (submit o checkForm), pinta con el
                // mismo diseño inline que usa el resto del sistema.
                showErrors: function (errorMap, errorList) {
                    var form = this.currentForm;
                    var conError = {};

                    $.each(errorList || [], function (_, error) {
                        var nombre = $(error.element).attr('name');
                        if (nombre) conError[nombre] = true;
                    });

                    // Limpio los campos que ya no tienen error y repinto los
                    // que sí, sin duplicar mensajes.
                    $(form).find(SELECTOR_CAMPOS).each(function () {
                        var nombre = $(this).attr('name');
                        if (!nombre || !conError[nombre]) {
                            EIS.quitarErrorCampo(this);
                        }
                    });
                    $.each(errorList || [], function (_, error) {
                        EIS.marcarErrorCampo(error.element, error.message);
                    });
                },
                invalidHandler: function (event, val) {
                    if (!EIS.toast) return;
                    var total = (val.errorList || []).length;
                    if (!total) return;
                    EIS.toast(
                        total > 1
                            ? 'Revisa los ' + total + ' campos marcados e intenta de nuevo.'
                            : 'Revisa el campo marcado e intenta de nuevo.',
                        'red',
                        'error',
                        {
                            heading: 'No se pudo enviar',
                            hideAfter: 4200,
                            className: 'eis-toast-form',
                            stack: 3
                        }
                    );
                },
                submitHandler: function (formEl, event) {
                    // Los formularios AJAX no tienen action: los módulos hacen
                    // su propio POST (algunos con delegación en document). Por
                    // eso NO devolvemos false aquí: solo prevenimos el envío
                    // nativo y dejamos que el evento siga propagándose.
                    if ($(formEl).attr('action')) {
                        formEl.submit();
                        return false;
                    }
                    if (event && event.preventDefault) {
                        event.preventDefault();
                    }
                    return true;
                }
            });

            return validator;
        }

        // API pública para inicializar formularios creados dinámicamente.
        EIS.initValidacionJQuery = function (form) {
            var $form = $(form);
            $form.each(function () { inicializarFormulario(this); });
            return $form;
        };

        $(function () {
            $('form[data-validate]').each(function () {
                inicializarFormulario(this);
            });
        });

        // Formularios inyectados después del DOM ready: se inicializan en el
        // primer foco/clic sobre alguno de sus campos.
        $(document).on('focusin click', 'form[data-validate] input, form[data-validate] select, form[data-validate] textarea', function () {
            if (this.form) inicializarFormulario(this.form);
        });
    }

    // =================================================================
    // VALIDACIÓN EN TIEMPO REAL + ALERTAS TOAST
    //
    // Este bloque funciona TANTO con el jQuery Validation Plugin activo
    // como sin él, porque no depende del ciclo de vida del plugin: se
    // apoya en la delegación de los eventos input/change/focus sobre
    // document y en EIS.validarCampo (fuente única: regex_validations.json).
    //
    // Flujo de la experiencia:
    //   1. Al enfocar un campo con formato exigido aparece una pista
    //      inline (data-hint, title, regla + ejemplo) y un solo toast
    //      informativo con el formato esperado.
    //   2. Mientras escribe, el borde y el mensaje inline se actualizan
    //      al instante; el contador de caracteres se tiñe al acercarse
    //      al máximo.
    //   3. Tras una pausa o al salir del campo se lanza un toast de error
    //      solo si el mensaje cambió (sin repetir en cada tecla).
    //   4. Al corregir el campo se confirma en verde con un toast breve.
    //   5. Los checkbox/radio se validan al marcarse, limpiando el error
    //      de todo el grupo.
    // =================================================================

    var RETRASO_TOAST = 600;   // ms de espera tras la última tecla
    var relojesToast = {};     // clave de campo -> id de setTimeout
    var ultimoAviso = {};      // clave -> mensaje ya mostrado al usuario
    var pistasDadas = {};      // clave -> la pista de formato ya se entregó
    var pistasTexto = {};      // clave -> texto de esa pista (para no repetirlo)
    var camposTocados = {};    // clave -> el usuario ya interactuó con él
    var secuenciaCampo = 0;    // evita colisiones entre formularios

    // Campos sobre los que aplica el tiempo real. Se excluyen checkbox,
    // radio, file, hidden y botones: no se "tipean" y su aviso se gestiona
    // en su propio evento change (SEL_CHECK) o en el envío.
    var SEL_TIEMPO_REAL = 'form[data-validate] input' +
        ':not([type=checkbox]):not([type=radio]):not([type=hidden])' +
        ':not([type=submit]):not([type=button])' +
        ':not([type=reset]):not([type=image]):not([type=file])' +
        ', form[data-validate] select, form[data-validate] textarea';

    var SEL_CHECK = 'form[data-validate] input[type=checkbox], ' +
        'form[data-validate] input[type=radio]';

    // Tipos nativos cuyo formato ya garantiza el navegador: no se les
    // ofrece pista automática (aunque sí si declaran data-hint o title).
    var SIN_PISTA_AUTO = {
        date: 1, datetime: 1, 'datetime-local': 1, month: 1,
        week: 1, time: 1, number: 1, range: 1, color: 1
    };

    // Identificador estable por elemento (no por nombre de campo), para
    // que dos formularios con campos homónimos no compartan estado.
    function claveCampo(el) {
        if (!el.__eisClave) {
            el.__eisClave = 'c' + (++secuenciaCampo) + '::' + (el.name || el.id || 'anon');
        }
        return el.__eisClave;
    }

    // Etiqueta legible del campo, usada para encabezar el toast y que
    // varias alertas apiladas sean identificables de un vistazo.
    function etiquetaCampo(el) {
        var $el = $(el);
        var texto = '';

        if (el.id) {
            $('label[for]').each(function () {
                if (this.getAttribute('for') === el.id) {
                    texto = $(this).text();
                    return false;
                }
            });
        }
        if (!texto) {
            texto = $el.closest('.input-field, .select-wrapper').find('label').first().text();
        }
        // Checkbox/radio envueltos por su propia etiqueta.
        if (!texto) texto = $el.closest('label').text();
        if (!texto) texto = $el.attr('placeholder') || '';
        if (!texto) texto = (el.name || '').replace(/[_\-]/g, ' ');

        return String(texto).replace(/\*/g, '').replace(/[:\s]+$/, '').trim();
    }

    // Indica si una clave del JSON describe un formato estricto (cédula,
    // RIF, correo, teléfono, contraseña, fecha, monto, extensión...).
    // Solo a esos campos se les ofrece pista: los textos libres (nombre,
    // descripción, dirección) no la necesitan y solo añadirían ruido.
    function esFormatoExigido(clave) {
        if (!clave) return false;
        return /^(identificacion_venezuela|contacto|seguridad|archivos|fechas_y_horas|numerico|redes|desarrollo)\./.test(clave)
            || clave === 'aplicacion.codigo_producto'
            || clave === 'aplicacion.monto'
            || clave === 'aplicacion.entero'
            || clave === 'texto_y_personas.nombre_usuario'
            || clave === 'texto_y_personas.iniciales';
    }

    // Devuelve el mensaje de una regla enriquecido con un ejemplo válido.
    function textoPista(regla) {
        if (!regla || !regla.mensaje) return '';
        var texto = regla.mensaje;
        if (regla.ejemplo && texto.indexOf(regla.ejemplo) === -1) {
            texto += ' Ejemplo: ' + regla.ejemplo + '.';
        }
        return texto;
    }

    // Devuelve la pista de formato a mostrar al empezar a tipear, o ''
    // si el campo no debe recibir ninguna.
    function pistaFormato(el) {
        var $el = $(el);

        // Guía explícita declarada por el desarrollador de la vista.
        var explicita = ($el.attr('data-hint') || '').trim();
        if (explicita) return explicita;

        // Los <select> no se tipean: se les valida en change, sin pista.
        if (String(el.tagName).toUpperCase() === 'SELECT') return '';

        var tipo = tipoDe($el);
        if (SIN_PISTA_AUTO[tipo]) return '';

        // Tipos nativos sin data-validate.
        if (tipo === 'email') { var re = resolverRegla('email'); if (re) return textoPista(re); }
        if (tipo === 'tel')   { var rt = resolverRegla('telefono'); if (rt) return textoPista(rt); }
        if (tipo === 'url')   { var ru = resolverRegla('url'); if (ru) return textoPista(ru); }

        // Reglas declaradas en data-validate (alias o clave completa).
        var tokens = ($el.attr('data-validate') || '').toString().split(/[\s,]+/);
        for (var i = 0; i < tokens.length; i++) {
            if (!tokens[i]) continue;
            var regla = resolverRegla(tokens[i]);
            if (regla && regla.mensaje && esFormatoExigido(regla.clave)) return textoPista(regla);
        }

        // Último recurso: el title del campo (p. ej. "Entre 2 y 100 caracteres").
        return ($el.attr('title') || '').trim();
    }

    // Indica si la pista merece un toast al enfocar. Los textos genéricos
    // del atributo title se quedan solo como pista inline para no llenar la
    // pantalla de notificaciones al recorrer un formulario.
    function pistaEsFuerte(el) {
        var $el = $(el);
        if (($el.attr('data-hint') || '').trim()) return true;

        var tipo = tipoDe($el);
        if (tipo === 'email' || tipo === 'tel' || tipo === 'url') return true;
        if (SIN_PISTA_AUTO[tipo]) return false;

        var tokens = ($el.attr('data-validate') || '').toString().split(/[\s,]+/);
        for (var i = 0; i < tokens.length; i++) {
            if (!tokens[i]) continue;
            var regla = resolverRegla(tokens[i]);
            if (regla && esFormatoExigido(regla.clave)) return true;
        }
        return false;
    }

    // -----------------------------------------------------------------
    // EIS.pintarEnVivo(el)
    // Refresca el estado visual del campo y devuelve su mensaje de error.
    // La pintura es siempre la misma (feedback propio), de modo que el
    // usuario vea el mismo diseño venga el error del navegador o de PHP.
    // -----------------------------------------------------------------
    function pintarEnVivo(el) {
        var $el = $(el);
        var clave = claveCampo(el);
        var msg = EIS.validarCampo(el) || '';

        if (!msg) {
            var valor = valorDe($el);
            var vacio = valor.trim() === '';

            if (camposTocados[clave] && !vacio) {
                EIS.marcarCampoValido(el);
            } else {
                EIS.quitarErrorCampo(el);
                actualizarFeedback($el, '', '', true);
            }
            return '';
        }

        EIS.marcarErrorCampo(el, msg);
        return msg;
    }

    // Toast contextual: título = nombre del campo, cuerpo = explicación.
    // Se evita repetir el mismo aviso para el mismo campo en pocos segundos.
    function avisarCampo(el, texto, tipo, opciones) {
        if (!texto || typeof EIS.toast !== 'function') return;

        var clave = claveCampo(el);
        var ahora = Date.now();
        var previo = ultimosToasts[clave];
        tipo = tipo || 'warning';

        if (previo && previo.texto === texto && previo.tipo === tipo &&
            (ahora - previo.ts) < 4000) {
            return;
        }
        ultimosToasts[clave] = { texto: texto, tipo: tipo, ts: ahora };

        var colores = { info: 'indigo', warning: 'orange', success: 'green' };
        var iconos = { info: 'info', warning: 'warning', success: 'success' };
        var etiqueta = etiquetaCampo(el);
        var heading = 'Validación';

        if (opciones && opciones.heading) {
            heading = opciones.heading;
        } else if (etiqueta) {
            heading = etiqueta;
        }
        if (opciones && opciones.incluirEtiqueta && etiqueta) {
            heading += ': ' + etiqueta;
        }

        EIS.toast(escaparHtml(texto), colores[tipo] || 'indigo', iconos[tipo] || 'info', {
            heading: escaparHtml(heading),
            hideAfter: (opciones && opciones.hideAfter !== undefined)
                ? opciones.hideAfter
                : (tipo === 'success' ? 2400 : 4200),
            className: 'eis-toast-field eis-toast-' + tipo,
            stack: 3
        });
    }

    function quitarResumenFormulario(el) {
        if (!el.form) return;
        var $panel = $(el.form).find('.eis-form-error');
        if (!$panel.length) return;
        $panel.stop(true, true).slideUp(120, function () { $(this).remove(); });
    }

    // Aplica el filtro de caracteres al valor actual (pegar, arrastrar o
    // escribir). Mantiene la posición del cursor cuando es posible.
    function filtrarValorCampo(el) {
        var $el = $(el);
        if ($el.is('select, textarea') || esPassword($el) || esCheckable($el)) return;
        if (tipoDe($el) === 'number' || tipoDe($el) === 'file') return;

        var filtro = obtenerFiltro($el);
        if (!filtro) return;

        var valor = $el.val() || '';
        var filtrado = filtro(valor);
        if (filtrado === valor) return;

        var pos = el.selectionStart || 0;
        $el.val(filtrado);
        if (el.setSelectionRange) {
            try {
                var nueva = Math.max(0, Math.min(pos, filtrado.length));
                el.setSelectionRange(nueva, nueva);
            } catch (ex) { /* campos sin selección */ }
        }
    }

    function limpiarGrupoCheckable(el) {
        var grupo = elementosDelGrupo(el);
        for (var i = 0; i < grupo.length; i++) {
            EIS.quitarErrorCampo(grupo[i]);
        }
    }

    // Evaluación diferida: decide qué toast corresponde (si es que algo
    // cambió) una vez que el usuario dejó de escribir o salió del campo.
    function evaluarErrorTiempoReal(el) {
        var clave = claveCampo(el);
        if (!camposTocados[clave]) return;

        var msg = pintarEnVivo(el);

        if (!msg) {
            // Campo corregido: solo se confirma si antes hubo un error.
            if (ultimoAviso[clave]) {
                delete ultimoAviso[clave];
                if (valorDe($(el)).trim() !== '') {
                    avisarCampo(el, 'Campo completado correctamente.', 'success', {
                        heading: 'Listo'
                    });
                }
            }
            return;
        }

        if (ultimoAviso[clave] === msg) return;   // mismo error: no repetir

        // Si el error coincide con la pista ya entregada, se registra para
        // poder confirmar la corrección, pero no se vuelve a avisar.
        if (pistasTexto[clave] && pistasTexto[clave] === msg) {
            ultimoAviso[clave] = msg;
            return;
        }

        ultimoAviso[clave] = msg;
        avisarCampo(el, msg, 'warning', {
            heading: 'Revisa este campo',
            incluirEtiqueta: true,
            hideAfter: 4600
        });
    }

    $(document)
        // Al entrar al campo: pista inline + un único toast con el formato.
        .on('focusin', SEL_TIEMPO_REAL, function () {
            var el = this;
            var $el = $(el);
            var clave = claveCampo(el);

            mostrarPistaInline($el);
            actualizarFeedback($el, '', '', true);

            if (!pistasDadas[clave]) {
                pistasDadas[clave] = true;
                var pista = pistaFormato(el);
                if (pista && pistaEsFuerte(el)) {
                    pistasTexto[clave] = pista;
                    avisarCampo(el, pista, 'info', {
                        heading: 'Formato esperado',
                        incluirEtiqueta: true,
                        hideAfter: 5200
                    });
                } else if (pista) {
                    // La pista genérica queda solo inline.
                    pistasTexto[clave] = pista;
                }
            }
        })
        // Al salir del campo: se valida y se avisa sin esperar más tecleo.
        .on('focusout', SEL_TIEMPO_REAL, function () {
            var el = this;
            var clave = claveCampo(el);

            camposTocados[clave] = true;
            clearTimeout(relojesToast[clave]);
            evaluarErrorTiempoReal(el);
        })
        // Mientras se escribe: feedback inmediato y toast solo tras una pausa.
        .on('input change', SEL_TIEMPO_REAL, function (e) {
            var el = this;
            var clave = claveCampo(el);

            if (e.type === 'input') {
                camposTocados[clave] = true;
                filtrarValorCampo(el);
            } else if (!camposTocados[clave]) {
                // Los `change` programáticos (Materialize al inicializar un
                // select) no deben marcar el campo como tocado. Solo se
                // considera interacción real si el control está enfocado o
                // ya hay un valor elegido.
                var enfocado = (document.activeElement === el);
                if (!enfocado && (el.value === '' || el.value === null)) return;
                camposTocados[clave] = true;
            }

            quitarResumenFormulario(el);
            pintarEnVivo(el);

            clearTimeout(relojesToast[clave]);
            if (e.type === 'change') {
                evaluarErrorTiempoReal(el);
                return;
            }
            relojesToast[clave] = setTimeout(function () {
                evaluarErrorTiempoReal(el);
            }, RETRASO_TOAST);
        })
        // Checkboxes y radios: no se tipean, pero sí deben confirmar su
        // estado al marcarse y limpiar el error del grupo completo.
        .on('change', SEL_CHECK, function () {
            var el = this;
            var clave = claveCampo(el);

            camposTocados[clave] = true;
            quitarResumenFormulario(el);
            limpiarGrupoCheckable(el);

            var msg = EIS.validarCampo(el) || '';
            if (msg) {
                EIS.marcarErrorCampo(el, msg);
                avisarCampo(el, msg, 'warning', {
                    heading: 'Revisa este campo',
                    incluirEtiqueta: true,
                    hideAfter: 4600
                });
            } else {
                EIS.marcarCampoValido(el);
            }
        });

    // -----------------------------------------------------------------
    // API pública para extender reglas desde otros módulos y para que el
    // núcleo (app.core.js) pueda reutilizar la etiqueta legible del campo
    // en el resumen de errores del servidor.
    //   EIS.reglasValidacion.mi_regla = { regex: /.../, mensaje: '...' }
    // -----------------------------------------------------------------
    EIS.resolverReglaValidacion = resolverRegla;
    EIS.etiquetaCampo = etiquetaCampo;
    EIS.actualizarEstadoCampo = pintarEnVivo;

})(jQuery);
