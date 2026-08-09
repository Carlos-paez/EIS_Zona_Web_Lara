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
        if (estacionPreseleccionada && disponibles.some(function (e) { return e.id == estacionPreseleccionada; })) {
            $activo.val(String(estacionPreseleccionada));
        }
        refrescarSelect($activo);

        var $tarifa = $('#cyberTarifa');
        var htmlTarifa = tarifas.length === 0
            ? '<option value="" disabled selected>Sin tarifas registradas</option>'
            : tarifas.map(function (t) {
                return '<option value="' + t.id + '">$' + parseFloat(t.tarifa_hora).toFixed(2) + '/hora — $' + parseFloat(t.precio_tiempo).toFixed(2) + ' (tiempo)</option>';
            }).join('');
        $tarifa.html(htmlTarifa);
        refrescarSelect($tarifa);
    }

    // ================================================================
    // FUNCIÓN: cargarEstado()
    // PROPÓSITO: Carga el estado actual de estaciones y tarifas.
    // ================================================================
    function cargarEstado() {
        $.when(
            $.getJSON(API + 'estaciones'),
            $.getJSON(API + 'tarifas')
        ).done(function (r1, r2) {
            if (r1[0] && r1[0].success) {
                estaciones = r1[0].data || [];
            } else {
                EIS.toast((r1[0] && r1[0].error) || 'Error al cargar estaciones', 'red', 'error');
            }
            if (r2[0] && r2[0].success) {
                tarifas = r2[0].data || [];
            }
            renderGrid();
            actualizarContadores();
        }).fail(function () {
            $('#cyberGrid').html('<div class="row"><div class="col s12 center-align" style="color:var(--text-muted);padding:2rem 0;"><i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">cloud_off</i> Error al conectar con el servidor</div></div>');
            EIS.toast('Error de conexión', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: abrirModalIniciar(estacionId)
    // PROPÓSITO: Prepara y abre el modal para iniciar una sesión.
    // ================================================================
    function abrirModalIniciar(estacionId) {
        $('#cyberForm')[0].reset();
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
        $.getJSON(API + 'buscarCliente&cedula=' + encodeURIComponent(cedula), function (r) {
            if (!r.success || !r.data) return;
            var c = r.data;
            var nombreCompleto = (c.nombre || '') + ((c.apellido || '') ? ' ' + c.apellido : '');
            if (nombreCompleto) {
                $('#cyberCiudadano').val(nombreCompleto);
            }
            $('#cyberDireccion').val(c.direccion || '');
            $('#cyberTelefono').val(c.telefono || '');
            M.updateTextFields();
            EIS.toast('Cliente encontrado: se precargaron sus datos', 'indigo', 'person');
        }).fail(function () {
            EIS.toast('Error al buscar el cliente', 'red', 'error');
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
                $.post(API + 'finalizar', { sesion_id: sesionId }, function (r) {
                    if (r.success) {
                        EIS.toast(r.message, 'green', 'stop_circle');
                        cargarEstado();
                    } else {
                        EIS.toast(r.error || 'Error al finalizar la sesión', 'red', 'error');
                    }
                }, 'json').fail(function () {
                    EIS.toast('Error de conexión', 'red', 'error');
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
            EIS.toast('No hay estaciones disponibles', 'red', 'error');
            return;
        }
        abrirModalIniciar(null);
    });

    // ================================================================
    // EVENTO: Blur de la cédula → precarga datos del cliente
    // ================================================================
    $(document).on('blur', '#cyberCedula', function () {
        buscarCliente($(this).val().trim());
    });

    // ================================================================
    // EVENTO: Submit del formulario de inicio de sesión (#cyberForm)
    // ================================================================
    $(document).on('submit', '#cyberForm', function (e) {
        e.preventDefault();

        var ciudadano = $('#cyberCiudadano').val().trim();
        var cedula = $('#cyberCedula').val().trim();
        var telefono = $('#cyberTelefono').val().trim();
        var direccion = $('#cyberDireccion').val().trim();
        var activoId = $('#cyberActivo').val();
        var tarifaId = $('#cyberTarifa').val();
        var tiempoUso = $('#cyberTiempo').val().trim();

        if (!ciudadano || !cedula) {
            EIS.toast('Nombre y cédula del cliente son obligatorios', 'red', 'error');
            return;
        }
        if (ciudadano.length < 2 || ciudadano.length > 100) {
            EIS.toast('El cliente debe tener entre 2 y 100 caracteres', 'red', 'error');
            return;
        }
        if (cedula.length < 5 || cedula.length > 20) {
            EIS.toast('La cédula debe tener entre 5 y 20 caracteres', 'red', 'error');
            return;
        }
        if (telefono && telefono.length > 20) {
            EIS.toast('El teléfono no puede exceder 20 caracteres', 'red', 'error');
            return;
        }
        if (direccion && direccion.length > 500) {
            EIS.toast('La dirección no puede exceder 500 caracteres', 'red', 'error');
            return;
        }
        if (!activoId) {
            EIS.toast('Selecciona una estación disponible', 'red', 'error');
            return;
        }
        if (!tarifaId) {
            EIS.toast('Selecciona una tarifa', 'red', 'error');
            return;
        }
        if (!tiempoUso) {
            EIS.toast('El tiempo de uso es obligatorio', 'red', 'error');
            return;
        }
        if (!/^\d{1,3}:\d{2}:\d{2}$/.test(tiempoUso)) {
            EIS.toast('El tiempo de uso debe tener formato HH:MM:SS', 'red', 'error');
            return;
        }

        var $btn = $('#btnIniciarSesion');
        $btn.prop('disabled', true);

        $.post(API + 'iniciar', {
            ciudadano: ciudadano,
            cedula: cedula,
            direccion: direccion,
            telefono: telefono,
            activo_id: activoId,
            tarifa_id: tarifaId,
            tiempo_uso: tiempoUso
        }, function (r) {
            $btn.prop('disabled', false);
            if (r.success) {
                EIS.toast(r.message || 'Sesión iniciada', 'green', 'play_circle');
                $('#cyberModal').modal('close');
                cargarEstado();
            } else {
                EIS.toast(r.error || 'Error al iniciar la sesión', 'red', 'error');
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false);
            EIS.toast('Error de conexión', 'red', 'error');
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
    // INICIALIZACIÓN
    // ================================================================
    $('#cyberModal').modal();
    cargarEstado();
});
