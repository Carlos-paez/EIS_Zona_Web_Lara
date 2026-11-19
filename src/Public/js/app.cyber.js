// =====================================================================
// ARCHIVO: app.cyber.js
// FUNCIÓN: Maneja la interactividad del módulo Control Cyber.
//          Gestiona el estado de las estaciones de cybercafé conectado
//          al backend:
//          - Carga estaciones y tarifas via AJAX
//          - Renderiza la grilla de estaciones agrupadas por tipo
//          - Inicia sesiones (registra o actualiza el cliente)
//          - Finaliza sesiones activas
//          - Actualiza contadores y chips de la cabecera en tiempo real
//          - Renderizado seguro de todo contenido dinámico (escHtml)
// =====================================================================

$(function () {

    // URL base de la API del módulo de control cyber
    var API = '?pagina=ciberControl&action=';

    function escHtml(str) {
        return $('<span>').text(str).html();
    }

    function truncar(texto, max) {
        texto = String(texto || '');
        if (texto.length <= max) return texto;
        return texto.substring(0, max - 1) + '…';
    }

    // ================================================================
    // ESTADO DEL MÓDULO
    // ================================================================

    var estaciones = [];
    var tarifas = [];
    var clientes = [];            // Clientes registrados para el selector del formulario
    var filtroActual = 'all';

    // ================================================================
    // FUNCIÓN: refrescarSelect($sel)
    // PROPÓSITO: Destruye y recrea un <select> de Materialize para
    //            reflejar las opciones recién actualizadas.
    // ================================================================
    function refrescarSelect($sel) {
        var el = $sel[0];
        if (!el) return;
        var inst = M.FormSelect.getInstance(el);
        if (inst) inst.destroy();
        $sel.formSelect();
    }

    // ================================================================
    // FUNCIÓN: refrescarSelectCliente()
    // PROPÓSITO: Recrea el selector de clientes y le re-inyecta la barra
    //            de búsqueda por nombre o cédula (formSelect regenera el
    //            <ul> y elimina la barra inyectada previamente).
    // ================================================================
    function refrescarSelectCliente() {
        var $sel = $('#cyberClienteSelect');
        refrescarSelect($sel);
        if (window.EIS && EIS.activarBusquedaEnSelect) {
            EIS.activarBusquedaEnSelect('#cyberClienteSelect', 'Buscar cliente por nombre o cédula...');
        }
    }

    // ================================================================
    // FUNCIÓN: poblarSelectClientes()
    // PROPÓSITO: Llena el selector de clientes registrados. La opción
    //            inicial es un marcador y "Crear nuevo cliente" permite
    //            capturar los datos manualmente.
    // ================================================================
    function poblarSelectClientes() {
        var html = '<option value="">Selecciona un cliente...</option>'
                 + '<option value="__nuevo__">── Crear nuevo cliente ──</option>';
        clientes.forEach(function (c) {
            var nombreCompleto = (c.nombre || '') + ((c.apellido || '') ? ' ' + c.apellido : '');
            html += '<option value="' + escHtml(c.cedula) + '">' + escHtml(nombreCompleto + ' (' + c.cedula + ')') + '</option>';
        });
        $('#cyberClienteSelect').html(html).val('');
        refrescarSelectCliente();
    }

    // ================================================================
    // FUNCIÓN: cargarClientes()
    // PROPÓSITO: Solicita al backend la lista de clientes registrados.
    // ================================================================
    function cargarClientes() {
        EIS.ajax.get(API + 'clientes', {
            onSuccess: function (r) {
                clientes = r.data || [];
                poblarSelectClientes();
            },
            errorMessage: 'No se pudieron cargar los clientes. Intenta de nuevo.'
        });
    }

    // ================================================================
    // FUNCIÓN: aplicarCliente(c)
    // PROPÓSITO: Precarga los datos de un cliente en el formulario,
    //            bloquea la cédula y sincroniza el selector.
    // ================================================================
    function aplicarCliente(c) {
        var nombreCompleto = (c.nombre || '') + ((c.apellido || '') ? ' ' + c.apellido : '');
        if (nombreCompleto) {
            $('#cyberCiudadano').val(nombreCompleto);
        }
        $('#cyberCiudadanoWrapper').hide();
        $('#cyberCedula').val(c.cedula || '').prop('readonly', true);
        $('#cyberDireccion').val(c.direccion || '');
        $('#cyberTelefono').val(c.telefono || '');
        if (c.cedula) {
            $('#cyberClienteSelect').val(c.cedula);
            refrescarSelectCliente();
        }
        M.updateTextFields();
    }

    // ================================================================
    // FUNCIÓN: limpiarCamposCliente()
    // PROPÓSITO: Limpia los datos del cliente y oculta el campo de nombre
    //            (modo "seleccionar de la lista").
    // ================================================================
    function limpiarCamposCliente() {
        $('#cyberCiudadano').val('');
        $('#cyberCedula').val('').prop('readonly', false);
        $('#cyberDireccion').val('');
        $('#cyberTelefono').val('');
        $('#cyberCiudadanoWrapper').hide();
        M.updateTextFields();
    }

    // ================================================================
    // FUNCIÓN: limpiarCamposNuevoCliente()
    // PROPÓSITO: Deja el formulario listo para capturar un cliente nuevo
    //            (muestra el campo de nombre y habilita la cédula).
    // ================================================================
    function limpiarCamposNuevoCliente() {
        limpiarCamposCliente();
        $('#cyberCiudadanoWrapper').show();
        M.updateTextFields();
    }

    // ================================================================
    // FUNCIÓN: limpiarClienteForm()
    // PROPÓSITO: Reinicia por completo el formulario del cliente y el
    //            selector a su estado inicial (sin cliente seleccionado).
    // ================================================================
    function limpiarClienteForm() {
        limpiarCamposCliente();
        $('#cyberClienteSelect').val('');
        refrescarSelectCliente();
    }

    // ================================================================
    // FUNCIÓN: actualizarContadores()
    // PROPÓSITO: Actualiza las tarjetas KPI y los chips de la cabecera
    //            con los conteos actuales de estaciones.
    // ================================================================
    function actualizarContadores() {
        var disp = estaciones.filter(function (e) { return e.estado === 'disponible'; }).length;
        var ocup = estaciones.filter(function (e) { return e.estado === 'ocupada'; }).length;
        var total = estaciones.length;

        $('#countDisponibles').text(disp);
        $('#countOcupadas').text(ocup);
        $('#countMantenimiento').text(0);
        $('#countTotal').text(total);

        $('#hdrDisponibles').text(disp + ' Disponibles');
        $('#hdrOcupadas').text(ocup + ' Ocupadas');
    }

    // ================================================================
    // FUNCIÓN: tarjetaEstacion(e)
    // PROPÓSITO: Construye el HTML de una tarjeta de estación.
    // ================================================================
    function tarjetaEstacion(e) {
        var ocupada = e.estado === 'ocupada';
        var icono = ocupada ? 'timelapse' : 'check_circle';
        var estadoLabel = ocupada ? 'Ocupada' : 'Disponible';
        var desc = (e.marca || '') + ((e.nombre_tipo || '') ? ' · ' + e.nombre_tipo : '');
        var nombre = (e.marca || '') + ' ' + (e.descripcion || '');

        var actionsHTML = '<div class="station-actions">'
            + '<button class="btn-floating btn-small waves-effect waves-light blue tooltipped btn-editar-pc" data-id="' + e.id + '" data-position="top" data-tooltip="Editar PC" style="width:28px;height:28px;line-height:28px;"><i class="material-icons" style="font-size:0.9rem;line-height:28px;">edit</i></button>'
            + '<button class="btn-floating btn-small waves-effect waves-light red tooltipped btn-eliminar-pc" data-id="' + e.id + '" data-nombre="' + escHtml(nombre) + '" data-position="top" data-tooltip="Eliminar PC" style="width:28px;height:28px;line-height:28px;"><i class="material-icons" style="font-size:0.9rem;line-height:28px;">delete</i></button>'
            + '</div>';

        var footer = '';
        if (ocupada) {
            footer = '<div class="station-footer">'
                + '<div style="font-size:0.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(e.cliente || 'Cliente') + '</div>'
                + '<div style="font-size:0.75rem;color:var(--text-muted);">' + escHtml(e.tiempo_uso || '') + '</div>'
                + '<div class="station-price">$' + escHtml(parseFloat(e.precio_tiempo || 0).toFixed(2)) + '</div>'
                + '</div>';
        }

        return '<div class="col s6 m4 l3 xl2">'
            + '<div class="station-card ' + e.estado + '" data-status="' + e.estado + '" data-id="' + e.id + '" data-sesion="' + (e.sesion_id || '') + '">'
            + '<div class="station-inner">'
            + '<div class="station-header">'
            + '<span class="station-badge">' + e.id + '</span>'
            + '<span class="station-header-label">Estación</span>'
            + '</div>'
            + '<div class="station-body">'
            + '<div class="station-icon"><i class="material-icons">' + icono + '</i></div>'
            + '<div class="station-status">' + estadoLabel + '</div>'
            + '<div class="station-desc" style="font-size:0.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">' + escHtml(desc) + '</div>'
            + '<div class="station-desc" style="font-size:0.7rem;color:var(--text-muted);">' + escHtml(truncar(e.descripcion, 40)) + '</div>'
            + '</div>'
            + actionsHTML
            + footer
            + '</div>'
            + '</div>'
            + '</div>';
    }

    // ================================================================
    // FUNCIÓN: renderGrid()
    // PROPÓSITO: Renderiza la grilla de estaciones agrupadas por tipo
    //            de activo, aplicando el filtro de estado actual.
    // ================================================================
    function renderGrid() {
        var $grid = $('#cyberGrid');

        var visibles = estaciones.filter(function (e) {
            if (filtroActual === 'all') return true;
            return e.estado === filtroActual;
        });

        if (visibles.length === 0) {
            $grid.html('<div class="row"><div class="col s12 center-align" style="color:var(--text-muted);padding:2rem 0;"><i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">search_off</i>No hay estaciones con este estado</div></div>');
            return;
        }

        // Agrupa las estaciones visibles por tipo de activo
        var grupos = {};
        visibles.forEach(function (e) {
            var tipo = e.nombre_tipo || 'Estaciones';
            if (!grupos[tipo]) grupos[tipo] = [];
            grupos[tipo].push(e);
        });

        var html = '';
        Object.keys(grupos).sort().forEach(function (tipo) {
            html += '<div class="zone-divider"><div class="zone-title">' + escHtml(tipo) + '</div></div>';
            html += '<div class="row">';
            grupos[tipo].forEach(function (e) {
                html += tarjetaEstacion(e);
            });
            html += '</div>';
        });

        $grid.html(html);
    }

    // ================================================================
    // FUNCIÓN: poblarSelects(estacionPreseleccionada)
    // PROPÓSITO: Llena los selects de estación (solo disponibles) y
    //            tarifa, preseleccionando la estación indicada.
    // ================================================================
    function poblarSelects(estacionPreseleccionada) {
        var disponibles = estaciones.filter(function (e) { return e.estado === 'disponible'; });

        var $activo = $('#cyberActivo');
        var htmlActivo = '';
        if (disponibles.length === 0) {
            htmlActivo = '<option value="" disabled selected>No hay estaciones disponibles</option>';
        } else {
            disponibles.forEach(function (e) {
                htmlActivo += '<option value="' + e.id + '">Estación ' + e.id + ' — ' + escHtml((e.marca || '') + ' ' + (e.nombre_tipo || '')) + '</option>';
            });
        }
        $activo.html(htmlActivo);
        // Si no se indicó una estación (botón "Nueva Sesión"), se preselecciona
        // la primera disponible para que el formulario siempre envíe un valor.
        var estacionSeleccionada = estacionPreseleccionada;
        if (!estacionSeleccionada && disponibles.length > 0) {
            estacionSeleccionada = disponibles[0].id;
        }
        if (estacionSeleccionada && disponibles.some(function (e) { return e.id == estacionSeleccionada; })) {
            $activo.val(String(estacionSeleccionada));
        }
        refrescarSelect($activo);

        var $tarifa = $('#cyberTarifa');
        var htmlTarifa = tarifas.length === 0
            ? '<option value="" disabled selected>Sin tarifas registradas</option>'
            : tarifas.map(function (t) {
                return '<option value="' + t.id + '">$' + parseFloat(t.tarifa_hora).toFixed(2) + '/hora — $' + parseFloat(t.precio_tiempo).toFixed(2) + ' (tiempo)</option>';
            }).join('');
        $tarifa.html(htmlTarifa);
        // Igual que la estación: sin selección explícita se usa la primera
        // tarifa para que el POST lleve siempre tarifa_id.
        if (tarifas.length > 0) {
            $tarifa.val(String(tarifas[0].id));
        }
        refrescarSelect($tarifa);
    }

    // ================================================================
    // FUNCIÓN: cargarEstado()
    // PROPÓSITO: Carga el estado actual de estaciones y tarifas.
    // ================================================================
    function cargarEstado() {
        $.when(
            EIS.ajax.get(API + 'estaciones', { showServerError: false, showNetworkError: false }),
            EIS.ajax.get(API + 'tarifas', { showServerError: false, showNetworkError: false })
        ).done(function (r1, r2) {
            if (r1[0] && r1[0].success) {
                estaciones = r1[0].data || [];
            } else {
                EIS.toast((r1[0] && r1[0].error) || 'No se pudieron cargar las estaciones. Intenta de nuevo.', 'red', 'error');
            }
            if (r2[0] && r2[0].success) {
                tarifas = r2[0].data || [];
            }
            renderGrid();
            actualizarContadores();
        }).fail(function () {
            $('#cyberGrid').html('<div class="row"><div class="col s12 center-align" style="color:var(--text-muted);padding:2rem 0;"><i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">cloud_off</i> No se pudo conectar con el servidor. Intenta de nuevo.</div></div>');
            EIS.toast('No se pudo conectar con el servidor. Intenta de nuevo.', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: abrirModalIniciar(estacionId)
    // PROPÓSITO: Prepara y abre el modal para iniciar una sesión.
    // ================================================================
    function abrirModalIniciar(estacionId) {
        EIS.limpiarErroresFormulario('#cyberForm');
        $('#cyberForm')[0].reset();
        limpiarClienteForm();
        $('#cyberTiempo').val('01:00:00');
        poblarSelects(estacionId);
        M.updateTextFields();
        $('#cyberModal').modal('open');
    }

    // ================================================================
    // FUNCIÓN: buscarCliente(cedula)
    // PROPÓSITO: Precarga los datos del cliente desde el backend.
    // ================================================================
    function buscarCliente(cedula) {
        if (!cedula) return;
        EIS.ajax.get(API + 'buscarCliente', {
            data: { cedula: cedula },
            showServerError: false,
            onSuccess: function (r) {
                if (!r.data) return;
                aplicarCliente(r.data);
                EIS.toast('Cliente encontrado. Se precargaron sus datos.', 'indigo', 'person');
            },
            errorMessage: 'No se pudo buscar el cliente. Intenta de nuevo.'
        });
    }

    // ================================================================
    // EVENTO: Click en una tarjeta de estación (.station-card)
    //   - disponible → abre el modal para iniciar sesión
    //   - ocupada    → confirma y finaliza la sesión activa
    // ================================================================
    $(document).on('click', '.station-card', function () {
        var $card = $(this);
        var status = $card.data('status');
        var id = $card.data('id');
        var sesionId = $card.data('sesion');

        if (status === 'disponible') {
            abrirModalIniciar(id);
        } else if (status === 'ocupada') {
            if (confirm('¿Finalizar la sesión en la estación ' + id + '?')) {
                EIS.ajax.post(API + 'finalizar', { sesion_id: sesionId }, {
                    successToast: true,
                    successIcon: 'stop_circle',
                    onSuccess: function () {
                        cargarEstado();
                    },
                    errorMessage: 'No se pudo conectar con el servidor. Intenta de nuevo.'
                });
            }
        }
    });

    // ================================================================
    // EVENTO: Click en botón "Nueva Sesión" (#btnNuevaSesion)
    // ================================================================
    $(document).on('click', '#btnNuevaSesion', function () {
        var disponibles = estaciones.filter(function (e) { return e.estado === 'disponible'; });
        if (disponibles.length === 0) {
            EIS.toast('No hay estaciones disponibles en este momento.', 'red', 'error');
            return;
        }
        abrirModalIniciar(null);
    });

    // ================================================================
    // EVENTO: Blur de la cédula → precarga datos del cliente
    // ================================================================
    $(document).on('blur', '#cyberCedula', function () {
        if ($(this).prop('readonly')) return;
        buscarCliente($(this).val().trim());
    });

    // ================================================================
    // EVENTO: Cambio en el selector de clientes registrados
    //   - "Crear nuevo cliente" → limpia y habilita la captura manual
    //   - Cliente registrado    → precarga y bloquea la cédula
    // ================================================================
    $(document).on('change', '#cyberClienteSelect', function () {
        var valor = $(this).val();
        if (!valor) {
            limpiarCamposCliente();
            return;
        }
        if (valor === '__nuevo__') {
            limpiarCamposNuevoCliente();
            return;
        }
        var c = clientes.find(function (x) { return x.cedula === valor; });
        if (c) aplicarCliente(c);
    });

    // ================================================================
    // EVENTO: Click en "Iniciar" (#btnIniciarSesion)
    // El botón está en el footer del modal (fuera del <form>), por lo que
    // se asocia con form="cyberForm". Este manejador garantiza el envío
    // mediante requestSubmit(), disparando la validación del sistema
    // (app.validate.js) y el submit AJAX de abajo.
    // ================================================================
    $(document).on('click', '#btnIniciarSesion', function (e) {
        e.preventDefault();
        var form = document.getElementById('cyberForm');
        if (!form) return;
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            $(form).trigger('submit');
        }
    });

    // ================================================================
    // EVENTO: Submit del formulario de inicio de sesión (#cyberForm)
    // ================================================================
    $(document).on('submit', '#cyberForm', function (e) {
        e.preventDefault();

        // Si no se eligió un cliente de la lista ni se capturó el nombre
        // de un cliente nuevo, se detiene el envío.
        if (!$('#cyberCiudadano').val().trim()) {
            EIS.toast('Selecciona un cliente registrado o elige "Crear nuevo cliente".', 'orange', 'person');
            return;
        }

        // El envío ya fue validado en el cliente por app.validate.js; el
        // servidor vuelve a validar (fieldErrors) por seguridad.
        var $form = $(this);
        var $btn = $('#btnIniciarSesion');
        $btn.prop('disabled', true);

        EIS.ajax.form(API + 'iniciar', $form, {
            successToast: 'Sesión iniciada',
            successIcon: 'play_circle',
            onSuccess: function () {
                $('#cyberModal').modal('close');
                cargarEstado();
            },
            errorMessage: 'No se pudo conectar con el servidor. Intenta de nuevo.'
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    // ================================================================
    // EVENTO: Click en botones de filtro (.filter-btn)
    // ================================================================
    $(document).on('click', '.filter-btn', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filtroActual = $(this).data('filter');
        renderGrid();

        var label = $(this).text().trim();
        EIS.toast('Mostrando: ' + label, 'indigo', 'filter_alt');
    });

    // ================================================================
    // CRUD DE PC
    // ================================================================

    // EVENTO: Click en "Nueva PC" (#btnNuevaPC)
    $(document).on('click', '#btnNuevaPC', function () {
        $('#modalPCTitle').text('Nueva PC');
        $('#modalPCTitleIcon').text('computer');
        $('#pcId').val('');
        $('#formPC')[0].reset();
        EIS.limpiarErroresFormulario('#formPC');
        $('#pcTipo').val('');
        refrescarSelect($('#pcTipo'));
        $('input[name="activa"][value="1"]').prop('checked', true);
        $('#pcFormError').hide();
        $('#btnGuardarPC').removeClass('orange').addClass('indigo');
        $('#btnGuardarPC').html('<i class="material-icons left" style="margin:0;">save</i> Guardar PC');
        M.updateTextFields();
        $('#modalPCForm').modal('open');
        setTimeout(function () { $('#pcMarca').focus(); }, 300);
    });

    // EVENTO: Click en "Editar PC" (.btn-editar-pc)
    $(document).on('click', '.btn-editar-pc', function (e) {
        e.stopPropagation();
        var id = $(this).data('id');
        $('#modalPCTitle').text('Editar PC');
        $('#modalPCTitleIcon').text('edit');
        $('#pcFormError').hide();
        EIS.limpiarErroresFormulario('#formPC');
        $('#btnGuardarPC').removeClass('indigo').addClass('orange');
        $('#btnGuardarPC').html('<i class="material-icons left" style="margin:0;">save</i> Actualizar PC');

        EIS.ajax.get(API + 'obtenerPC', {
            data: { id: id },
            onSuccess: function (r) {
                if (!r.data) {
                    EIS.toast('No se pudo cargar la PC. Intenta de nuevo.', 'red', 'error');
                    return;
                }
                var pc = r.data;
                $('#pcId').val(pc.id);
                $('#pcMarca').val(pc.marca || '');
                $('#pcDescripcion').val(pc.descripcion || '');
                $('#pcTipo').val(String(pc.tipo_activo_id || ''));
                refrescarSelect($('#pcTipo'));
                $('input[name="activa"][value="' + (pc.activa ? 1 : 0) + '"]').prop('checked', true);
                M.updateTextFields();
                $('#modalPCForm').modal('open');
            },
            errorMessage: 'No se pudo conectar con el servidor al cargar la PC. Intenta de nuevo.'
        });
    });

    // EVENTO: Click en "Eliminar PC" (.btn-eliminar-pc)
    $(document).on('click', '.btn-eliminar-pc', function (e) {
        e.stopPropagation();
        var id = $(this).data('id');
        var nombre = $(this).data('nombre') || 'PC-' + id;
        $('#confirmarPcId').val(id);
        $('#confirmarPcNombre').text(nombre);
        $('#modalConfirmarEliminar').modal('open');
    });

    // EVENTO: Click en "Confirmar Eliminar" (#btnConfirmarEliminar)
    $(document).on('click', '#btnConfirmarEliminar', function () {
        var id = $('#confirmarPcId').val();
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="material-icons left">hourglass_top</i> Eliminando...');

        EIS.ajax.post(API + 'eliminarPC', { id: id }, {
            successToast: true,
            successIcon: 'delete',
            onSuccess: function () {
                $('#modalConfirmarEliminar').modal('close');
                cargarEstado();
            },
            errorMessage: 'No se pudo conectar con el servidor. Intenta de nuevo.'
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="material-icons left">delete_forever</i> Eliminar');
        });
    });

    // EVENTO: Click en "Guardar PC" (#btnGuardarPC) — crear o actualizar
    $(document).on('click', '#btnGuardarPC', function () {
        var $form = $('#formPC');
        var id = $('#pcId').val();

        // Validación del lado del cliente: si hay errores, se muestran y no se
        // envía la petición (el servidor vuelve a validar por seguridad).
        EIS.limpiarErroresFormulario($form);
        if (EIS.validarFormulario) {
            var errores = EIS.validarFormulario($form);
            if (Object.keys(errores).length) {
                EIS.mostrarErroresFormulario($form, {
                    error: 'Revisa los campos marcados e intenta de nuevo.',
                    fieldErrors: errores
                });
                return;
            }
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="material-icons left" style="margin:0;">hourglass_top</i> Guardando...');
        $('#pcFormError').hide();

        var url = id ? API + 'actualizarPC' : API + 'crearPC';

        EIS.ajax.form(url, $form, {
            successToast: true,
            onSuccess: function () {
                $('#modalPCForm').modal('close');
                cargarEstado();
            },
            onError: function (r, xhr) {
                if (xhr) {
                    $('#pcFormErrorMessage').text('No se pudo conectar con el servidor. Intenta de nuevo.');
                    $('#pcFormError').slideDown(300);
                }
            }
        }).always(function () {
            $btn.prop('disabled', false);
            $('#btnGuardarPC').removeClass('orange').addClass('indigo');
            $('#btnGuardarPC').html('<i class="material-icons left" style="margin:0;">save</i> Guardar PC');
        });
    });

    // ================================================================
    // HISTORIAL
    // ================================================================

    // EVENTO: Click en "Historial" (#btnHistorialCyber y móvil)
    $(document).on('click', '#btnHistorialCyber, #btnHistorialCyberMobile', function () {
        $('#modalHistorial').modal('open');
        mostrarSelectorEstaciones();
    });

    function mostrarSelectorEstaciones() {
        var $contenido = $('#historialContenido');
        var visibles = estaciones.filter(function (e) { return filtroActual === 'all' || e.estado === filtroActual; });

        if (visibles.length === 0) {
            $contenido.html('<div class="center-align" style="padding:2rem 0;"><i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">computer</i><p style="color:var(--text-muted);">No hay PCs registradas</p></div>');
            return;
        }

        var html = '<div style="margin-bottom:1.5rem;">'
            + '<p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:0.75rem;"><i class="material-icons left" style="font-size:1.1rem;">info</i>Selecciona una PC para ver su historial de sesiones</p>'
            + '<div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;">';
        visibles.forEach(function (e) {
            var estadoClass = e.estado === 'disponible' ? 'green' : 'orange';
            html += '<button class="btn-small waves-effect waves-light ' + estadoClass + ' btn-ver-historial" data-id="' + e.id + '" data-nombre="' + escHtml(e.marca + ' ' + (e.nombre_tipo || '')) + '" style="border-radius:16px;min-width:60px;text-transform:capitalize;display:inline-flex;align-items:center;gap:0.25rem;"><span style="width:8px;height:8px;border-radius:50%;background:currentColor;display:inline-block;"></span>' + escHtml(e.marca + ' · ' + (e.nombre_tipo || '')) + '</button>';
        });
        html += '</div></div><div id="historialResultados" style="margin-top:1.5rem;"></div>';
        $contenido.html(html);
    }

    // EVENTO: Click en una PC del selector de historial (.btn-ver-historial)
    $(document).on('click', '.btn-ver-historial', function () {
        var activoId = $(this).data('id');
        var nombre = $(this).data('nombre') || 'PC-' + activoId;
        $('.btn-ver-historial').removeClass('active indigo darken-2');
        $(this).addClass('active indigo darken-2');
        cargarHistorialEstacion(activoId, nombre);
    });

    function cargarHistorialEstacion(activoId, nombre) {
        var $resultados = $('#historialResultados');
        $resultados.html('<div class="center-align" style="padding:2rem 0;"><div class="eis-loader-wrapper" style="height:60px;"><div class="eis-tower-loader" style="scale:1.6;"><div class="box box-1"><div class="side-left"></div><div class="side-right"></div><div class="side-top"></div></div><div class="box box-2"><div class="side-left"></div><div class="side-right"></div><div class="side-top"></div></div><div class="box box-3"><div class="side-left"></div><div class="side-right"></div><div class="side-top"></div></div><div class="box box-4"><div class="side-left"></div><div class="side-right"></div><div class="side-top"></div></div></div></div><p style="color:var(--text-muted);margin-top:1.5rem;">Cargando historial de ' + escHtml(nombre) + '...</p></div>');

        EIS.ajax.get(API + 'historial', {
            data: { activo_id: activoId },
            showServerError: false,
            showNetworkError: false,
            onSuccess: function (r) {
                var datos = r.data || [];
                if (datos.length === 0) {
                    $resultados.html('<div class="center-align" style="padding:2rem 0;"><i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.3;">hourglass_empty</i><p style="color:var(--text-muted);font-size:1rem;"><strong>' + escHtml(nombre) + '</strong> no tiene sesiones registradas</p></div>');
                    return;
                }
                var html = '<div style="overflow-x:auto;border-radius:8px;border:1px solid var(--border-light);"><table class="striped responsive-table" id="tabla-historial" style="margin-bottom:0;font-size:0.9rem;"><thead><tr style="background:var(--surface-hover);"><th style="padding:0.6rem 0.8rem;">#</th><th style="padding:0.6rem 0.8rem;">Cliente</th><th style="padding:0.6rem 0.8rem;">Tiempo</th><th style="padding:0.6rem 0.8rem;text-align:right;">Precio</th><th style="padding:0.6rem 0.8rem;">Estado</th></tr></thead><tbody>';
                datos.forEach(function (s, i) {
                    var precioT = (s.precio_tiempo !== null && s.precio_tiempo !== undefined) ? '$' + parseFloat(s.precio_tiempo).toFixed(2) : '-';
                    var estadoBadge = s.estado === 'activa' ? '<span class="new badge orange" style="background:#fb8c00;">Activa</span>' : '<span class="new badge green" style="background:#43a047;">Cerrada</span>';
                    html += '<tr style="border-bottom:1px solid var(--border-light);"><td style="padding:0.5rem 0.8rem;font-weight:600;color:var(--text-muted);">' + (i + 1) + '</td><td style="padding:0.5rem 0.8rem;font-weight:500;">' + escHtml(s.cliente_nombre || 'Anónimo') + '</td><td style="padding:0.5rem 0.8rem;font-size:0.85rem;color:var(--text-muted);">' + escHtml(s.tiempo_uso || '-') + '</td><td style="padding:0.5rem 0.8rem;text-align:right;font-weight:700;color:var(--primary);">' + precioT + '</td><td style="padding:0.5rem 0.8rem;">' + estadoBadge + '</td></tr>';
                });
                html += '</tbody></table></div><div style="margin-top:0.75rem;text-align:right;color:var(--text-muted);font-size:0.8rem;"><i class="material-icons left" style="font-size:0.9rem;">info</i>Mostrando ' + datos.length + ' sesiones de ' + escHtml(nombre) + '</div>';

                if (window.EIS && EIS.datatableDestroy) {
                    EIS.datatableDestroy('#tabla-historial');
                }
                $resultados.html(html);
                if (window.EIS && EIS.datatable) {
                    EIS.datatable('#tabla-historial');
                }
            },
            onError: function (r, xhr) {
                if (xhr) {
                    $resultados.html('<div class="card-panel red lighten-4 red-text text-darken-4" style="border-radius:8px;padding:1rem;"><i class="material-icons left" style="font-size:1.3rem;">error</i>No se pudo conectar con el servidor. Intenta de nuevo.</div>');
                } else {
                    $resultados.html('<div class="card-panel red lighten-4 red-text text-darken-4" style="border-radius:8px;padding:1rem;"><i class="material-icons left" style="font-size:1.3rem;">error</i>' + escHtml((r && r.error) || 'No se pudo cargar el historial. Intenta de nuevo.') + '</div>');
                }
            }
        });
    }

    // ================================================================
    // REFRESCAR
    // ================================================================
    $(document).on('click', '#btnRefrescar', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="material-icons left" style="font-size:1rem;">hourglass_top</i> Actualizando...');
        cargarEstado();
        setTimeout(function () {
            $btn.prop('disabled', false).html('<i class="material-icons left" style="font-size:1rem;">refresh</i>Actualizar');
            EIS.toast('Datos actualizados', 'green', 'refresh');
        }, 500);
    });

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    $('#cyberModal').modal();
    $('#modalPCForm').modal();
    $('#modalConfirmarEliminar').modal();
    $('#modalHistorial').modal();
    cargarEstado();
    cargarClientes();
});
