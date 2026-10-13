// =====================================================================
// ARCHIVO: app.legal.js
// FUNCIÓN: Maneja la interactividad del módulo de Asesoría Legal.
//          Permite validar tipos de documentos legales, registrar
//          asesorías (permitidas o derivadas) creando/actualizando el
//          cliente asociado, editar, eliminar y visualizar el historial
//          filtrable. Toda la persistencia se realiza vía AJAX.
// =====================================================================

$(function () {

    // URL base de la API del módulo de asesorías
    var API = '?pagina=asesorias&action=';

    function escHtml(str) {
        return $('<span>').text(str).html();
    }

    // ================================================================
    // CONFIGURACIÓN INICIAL
    // ================================================================

    // Lista de tipos de documento que la asesoría puede atender directamente
    var allowedDocs = [
        'consulta laboral',
        'consulta civil',
        'consulta familiar',
        'orientación legal general',
        'orientacion legal general',
        'revisión de contrato',
        'revision de contrato',
        'elaboración de documento simple',
        'elaboracion de documento simple',
        'asesoría prevencional',
        'asesoria prevencional'
    ];

    // Arreglo que almacena todas las asesorías cargadas desde el backend
    var asesoriasRegistradas = [];

    // ================================================================
    // FUNCIÓN: normalizarDoc(texto)
    // PROPÓSITO: Normaliza un texto eliminando espacios extra y
    //            convirtiendo a minúsculas para comparación.
    // ================================================================
    function normalizarDoc(texto) {
        return texto.toLowerCase().replace(/\s+/g, ' ').trim();
    }

    // ================================================================
    // FUNCIÓN: documentoPermitido(doc)
    // PROPÓSITO: Verifica si un tipo de documento está dentro de la
    //            lista de permitidos (para feedback visual en tiempo real).
    // ================================================================
    function documentoPermitido(doc) {
        return allowedDocs.indexOf(normalizarDoc(doc)) !== -1;
    }

    // ================================================================
    // FUNCIÓN: refrescarKPI()
    // PROPÓSITO: Carga los indicadores del módulo desde el backend y
    //            actualiza el chip del banner y el badge.
    // ================================================================
    function refrescarKPI() {
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return;
            var k = r.data;
            $('#totalAsesoriasBadge').text(k.total);
            var msg = k.total + ' registradas' + (k.derivadas > 0 ? ' (' + k.derivadas + ' derivadas)' : '');
            $('#asesoriasCountChip span').text(msg);
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: actualizarHistorial()
    // PROPÓSITO: Renderiza la tabla de historial a partir del arreglo
    //            cargado desde el backend y actualiza contadores.
    // ================================================================
    function actualizarHistorial() {
        var $tbody = $('#asesoriasTableBody');
        var $empty = $('#asesoriasEmpty');

        if (asesoriasRegistradas.length === 0) {
            $tbody.html('');
            $empty.show();
            $('#totalAsesoriasBadge').text('0');
            return;
        }

        $empty.hide();
        var html = '';

        asesoriasRegistradas.forEach(function (a, i) {
            // Estado derivado del campo permitido que envía el backend
            var estado = a.permitido == 1 ? 'Permitido' : 'Denegado';
            var estadoClass = estado === 'Permitido' ? 'legal-permitido' : 'legal-denegado';
            var icono = estado === 'Permitido' ? 'check_circle' : 'cancel';

            html += '<tr>'
                + '<td class="hide-on-small-only">' + (i + 1) + '</td>'
                + '<td><strong>' + escHtml(a.ciudadano) + '</strong></td>'
                + '<td class="hide-on-small-only">' + escHtml(a.cedula) + '</td>'
                + '<td>' + escHtml(a.documento) + '</td>'
                + '<td><span class="' + estadoClass + '" style="white-space:nowrap;"><i class="material-icons left" style="font-size:0.85rem;margin:0;">' + icono + '</i>' + estado + '</span></td>'
                + '<td class="hide-on-small-only" style="font-size:0.8rem;color:var(--text-muted);">' + escHtml(a.fecha) + '</td>'
                + '</tr>';
        });

        $tbody.html(html);
        $('#totalAsesoriasBadge').text(asesoriasRegistradas.length);

        EIS.datatableRefresh('#tabla-asesorias');
    }

    // ================================================================
    // FUNCIÓN: cargarAsesorias()
    // PROPÓSITO: Solicita al backend el listado completo de asesorías.
    // ================================================================
    function cargarAsesorias() {
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar', 'red', 'error'); return; }
            asesoriasRegistradas = r.data || [];
            actualizarHistorial();
        }).fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: mostrarValidacion(tipo, mensaje, esPermitido)
    // PROPÓSITO: Muestra el resultado de la validación del documento
    //            en un panel visual debajo del formulario.
    // ================================================================
    function mostrarValidacion(tipo, mensaje, esPermitido) {
        var $div = $('#documentValidationResult');
        var $msg = $('#validationMessage');

        $div.removeClass('success error').addClass(esPermitido ? 'success' : 'error');

        var icono = esPermitido ? 'check_circle' : 'warning';
        var color = esPermitido ? 'green-text' : 'red-text';

        $msg.html('<i class="material-icons left ' + color + '" style="font-size:1.3rem;">' + icono + '</i><strong class="' + color + '">' + tipo + '</strong><br><span style="font-size:0.9rem;">' + mensaje + '</span>');

        $div.slideDown(300);

        if (!esPermitido) {
            M.toast({ html: '<i class="material-icons left" style="font-size:1.2rem;">gavel</i> Caso derivado a oficina oficial', classes: 'red rounded', displayLength: 4000 });
        }
    }

    // ================================================================
    // EVENTO: Submit del formulario de asesoría (#asesoriaForm)
    // Valida los campos, envía la asesoría al backend (que crea o
    // actualiza el cliente según la cédula) y refresca el historial.
    // ================================================================
    $(document).on('submit', '#asesoriaForm', function (e) {
        e.preventDefault();
        var $form = $(this);

        var ciudadano   = $('#ciudadano').val().trim();
        var cedula      = $('#cedula').val().trim();
        var documento   = $('#documento').val().trim();
        var descripcion = $('#descripcion').val().trim();
        var telefono    = $('#telefono').val().trim();
        var direccion   = $('#direccion').val().trim();

        // La validación de campos obligatorios la hace el servidor (PHP):
        // si algún campo obligatorio viene vacío, el controlador devuelve
        // "fieldErrors" y los mostramos de forma persistente en pantalla.

        // Feedback visual previo según el tipo de documento
        var permitido = documentoPermitido(documento);
        if (permitido) {
            mostrarValidacion(
                'DOCUMENTO PERMITIDO',
                'El documento <strong>"' + escHtml(documento) + '"</strong> está dentro de los tipos de asesoría que podemos atender.',
                true
            );
        } else {
            mostrarValidacion(
                'DOCUMENTO NO PERMITIDO',
                'El documento <strong>"' + escHtml(documento) + '"</strong> no corresponde a los tipos de asesoría que podemos atender. <strong>Este caso debe ser derivado a una Oficina de Atención Legal Oficial.</strong>',
                false
            );
        }

        // Se detecta si la cédula ya existe en el historial (cliente existente)
        var existeCliente = asesoriasRegistradas.some(function (a) {
            return a.cedula === cedula;
        });

        $.post(API + 'crear', $form.serialize(), function (r) {
            if (r.success) {
                EIS.toast(
                    existeCliente
                        ? 'Asesoría registrada. Información del cliente actualizada'
                        : 'Asesoría registrada para ' + ciudadano,
                    'green',
                    'how_to_reg'
                );
                EIS.limpiarErroresFormulario($form);
                $form[0].reset();
                $('#btnRegistrar').prop('disabled', true);
                $('#btnRegistrar').removeClass('red').addClass('indigo').html('<i class="material-icons left">verified</i>Validar y Registrar');
                $('label').removeClass('active');
                cargarAsesorias();
                refrescarKPI();
            } else {
                EIS.mostrarErroresFormulario($form, r);
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // ================================================================
    // EVENTO: Entrada de texto en el campo #documento
    // Cambia el color y texto del botón de registro según si el
    // documento es permitido o no.
    // ================================================================
    $(document).on('input', '#documento', function () {
        var val = $(this).val().trim();

        if (val.length > 0) {
            var permitido = documentoPermitido(val);
            $('#btnRegistrar').prop('disabled', false);

            if (permitido) {
                $('#btnRegistrar').removeClass('red').addClass('indigo').html('<i class="material-icons left">verified</i>Validar y Registrar');
            } else {
                $('#btnRegistrar').removeClass('indigo').addClass('red').html('<i class="material-icons left">warning</i>Derivar a Oficina Oficial');
            }
            $('#documentValidationResult').slideUp(200);
        } else {
            $('#btnRegistrar').prop('disabled', true);
            $('#btnRegistrar').removeClass('red').addClass('indigo').html('<i class="material-icons left">verified</i>Validar y Registrar');
        }
    });

    // ================================================================
    // EVENTO: Blur del campo #cedula
    // Si la cédula pertenece a un cliente ya registrado, muestra un
    // aviso de que se actualizará su información de contacto.
    // ================================================================
    $(document).on('blur', '#cedula', function () {
        var cedula = $(this).val().trim();
        if (!cedula) return;

        var previo = asesoriasRegistradas.filter(function (a) {
            return a.cedula === cedula;
        });

        if (previo.length > 0) {
            EIS.toast('Cliente ya registrado: se actualizará su información de contacto', 'indigo', 'person');
        }
    });

    // ================================================================
    // FUNCIÓN: aplicarFiltro()
    // PROPÓSITO: Conecta la búsqueda del historial con la búsqueda
    //            global de DataTables (ya no recorre filas manualmente).
    // ================================================================
    function aplicarFiltro() {
        if (!(window.jQuery && $.fn.DataTable)) return;
        var dt = $('#tabla-asesorias').DataTable();
        if (dt) {
            dt.search($('#searchAsesoria').val() || '');
            dt.draw();
        }
    }

    $(document).on('input', '#searchAsesoria', debounce(function () {
        aplicarFiltro();
    }, 300));

    // ================================================================
    // INIT: Al corregir un campo se ocultan los errores del servidor
    // ================================================================
    $(document).on('input', '#asesoriaForm input, #asesoriaForm textarea', function () {
        EIS.limpiarErroresFormulario($(this).closest('form'));
    });

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    if (window.EIS && EIS.datatable) {
        EIS.datatable('#tabla-asesorias');
        if (EIS.datatableWireSearch) EIS.datatableWireSearch('#tabla-asesorias', '#searchAsesoria');
    }
    cargarAsesorias();
    refrescarKPI();
});
