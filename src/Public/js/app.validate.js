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
        maxlength: function (n) { return 'No puede exceder ' + n + ' caracteres.'; },
        min:       function (n) { return 'El valor mínimo permitido es ' + n + '.'; },
        max:       function (n) { return 'El valor máximo permitido es ' + n + '.'; }
    };

    var SELECTOR_CAMPOS = 'input, select, textarea';

    var TIPOS_CONTROL = {
        hidden: true, submit: true, button: true, reset: true, image: true, file: true
    };

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
        if ($el.closest('.select-wrapper').length) return $el.closest('.select-wrapper');
        if ($el.closest('.input-field, .select-wrapper').length) return $el.closest('.input-field, .select-wrapper');
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
        if ($el.is('select') || esCheckable($el)) {
            $wrap.addClass('invalid eis-invalid');
        } else {
            $el.addClass('invalid eis-invalid');
        }
        $('<div class="eis-field-error helper-text" style="color:#c62828;font-weight:500;"></div>')
            .text(texto)
            .appendTo($wrap);
    };

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
    //   EIS.reglasValidacion.mi_regla = { regex: /.../, mensaje: '...' }
    // -----------------------------------------------------------------
    EIS.resolverReglaValidacion = resolverRegla;

})(jQuery);
