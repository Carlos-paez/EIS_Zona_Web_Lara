// =====================================================================
// ARCHIVO: app.legal.js
// FUNCIÓN: Maneja la interactividad del módulo de Asesoría Legal.
//          Permite validar tipos de documentos legales, registrar
//          asesorías (permitidas o derivadas), visualizar el historial
//          y filtrar/buscar registros.
// =====================================================================

// Espero a que el DOM esté listo para ejecutar el código
$(function () {

    function escHtml(str) {
        return $('<span>').text(str).html();
    }

    // ================================================================
    // CONFIGURACIÓN INICIAL
    // ================================================================

    // Lista de tipos de documento que la asesoría puede atender directamente
    var allowedDocs = [
        'consulta laboral',        // Consultas sobre derecho laboral
        'consulta civil',          // Consultas sobre derecho civil
        'consulta familiar',       // Consultas sobre derecho familiar
        'orientación legal general', // Orientación legal general (con tilde)
        'orientacion legal general', // Orientación legal general (sin tilde)
        'revisión de contrato',    // Revisión de contratos (con tilde)
        'revision de contrato',    // Revisión de contratos (sin tilde)
        'elaboración de documento simple',   // Elaboración de documentos (con tilde)
        'elaboracion de documento simple',   // Elaboración de documentos (sin tilde)
        'asesoría prevencional',   // Asesoría prevencional (con tilde)
        'asesoria prevencional'    // Asesoría prevencional (sin tilde)
    ];

    // Arreglo que almacena todas las asesorías registradas en la sesión actual
    var asesoriasRegistradas = [];

    // ================================================================
    // FUNCIÓN: normalizarDoc(texto)
    // PROPÓSITO: Normaliza un texto eliminando espacios extra y
    //            convirtiendo a minúsculas para comparación.
    // PARÁMETROS:
    //   texto - String a normalizar
    // RETORNA: String normalizado (minúsculas, sin espacios múltiples, trim)
    // ================================================================
    function normalizarDoc(texto) {
        return texto.toLowerCase().replace(/\s+/g, ' ').trim();
    }

    // ================================================================
    // FUNCIÓN: documentoPermitido(doc)
    // PROPÓSITO: Verifica si un tipo de documento está dentro de la
    //            lista de permitidos.
    // PARÁMETROS:
    //   doc - String con el tipo de documento a verificar
    // RETORNA: true si el documento está permitido, false si no
    // ================================================================
    function documentoPermitido(doc) {
        // Busco el documento normalizado en el arreglo allowedDocs
        return allowedDocs.indexOf(normalizarDoc(doc)) !== -1;
    }

    // ================================================================
    // FUNCIÓN: actualizarHistorial()
    // PROPÓSITO: Renderiza la tabla de historial de asesorías registradas
    //            y actualiza los contadores (badge y chip). También
    //            reinicia los tooltips para los nuevos botones.
    // ================================================================
    function actualizarHistorial() {
        var $tbody = $('#asesoriasTableBody'); // <tbody> de la tabla de historial
        var $empty = $('#asesoriasEmpty');     // Mensaje de "sin registros"

        // Si no hay asesorías registradas, muestro el mensaje de vacío y reseteo contadores
        if (asesoriasRegistradas.length === 0) {
            $tbody.html('');           // Limpio el cuerpo de la tabla
            $empty.show();             // Muestro el mensaje de tabla vacía
            $('#totalAsesoriasBadge').text('0'); // Badge en 0
            $('#asesoriasCountChip').text('0 registradas hoy'); // Chip en 0
            return;
        }

        // Oculto el mensaje de vacío porque hay registros
        $empty.hide();
        var html = ''; // Acumulador del HTML de las filas

        // Recorro cada asesoría registrada (unshift = las más recientes primero)
        asesoriasRegistradas.forEach(function (a, i) {
            // Determino la clase CSS según el estado (Permitido o Denegado)
            var estadoClass = a.estado === 'Permitido' ? 'legal-permitido' : 'legal-denegado';
            // Determino el ícono según el estado
            var icono = a.estado === 'Permitido' ? 'check_circle' : 'cancel';

            // Construyo la fila HTML con los datos de la asesoría
            html += '<tr>'
                + '<td class="hide-on-small-only">' + (i + 1) + '</td>'                         // Columna: N° (oculto en móvil)
                + '<td><strong>' + escHtml(a.ciudadano) + '</strong></td>'                                // Columna: Nombre del ciudadano
                + '<td class="hide-on-small-only">' + escHtml(a.cedula) + '</td>'                         // Columna: Cédula (oculto en móvil)
                + '<td>' + escHtml(a.documento) + '</td>'                                                 // Columna: Tipo de documento
                + '<td><span class="' + estadoClass + '" style="white-space:nowrap;"><i class="material-icons left" style="font-size:0.85rem;margin:0;">' + icono + '</i>' + a.estado + '</span></td>' // Columna: Estado con ícono y color
                + '<td class="hide-on-small-only" style="font-size:0.8rem;color:var(--text-muted);">' + a.fecha + '</td>' // Columna: Fecha (oculto en móvil)
                // Botón eliminar para desktop
                + '<td class="right-align hide-on-small-only" style="white-space:nowrap;">'
                + '<button class="btn-floating waves-effect waves-light grey tooltipped btn-eliminar-asesoria" data-index="' + i + '" data-position="top" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>'
                + '</td>'
                // Botón eliminar para móvil (misma acción, otro contenedor responsive)
                + '<td class="right-align hide-on-med-and-up">'
                + '<button class="btn-floating waves-effect waves-light grey tooltipped btn-eliminar-asesoria" data-index="' + i + '" data-position="top" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>'
                + '</td>'
                + '</tr>';
        });

        // Inserto todas las filas construidas en el <tbody>
        $tbody.html(html);
        // Actualizo el badge con el total de asesorías registradas
        $('#totalAsesoriasBadge').text(asesoriasRegistradas.length);

        // Calculo cuántas asesorías fueron denegadas (pendientes de derivación)
        var pendientes = asesoriasRegistradas.filter(function (a) { return a.estado === 'Denegado'; }).length;
        // Actualizo el chip informativo con total y (si hay) derivaciones
        $('#asesoriasCountChip').text(asesoriasRegistradas.length + ' registradas' + (pendientes > 0 ? ' (' + pendientes + ' derivadas)' : ''));

        // Reinicio los tooltips de Materialize para los nuevos botones agregados
        $('.tooltipped').tooltip();
    }

    // ================================================================
    // FUNCIÓN: mostrarValidacion(tipo, mensaje, esPermitido)
    // PROPÓSITO: Muestra el resultado de la validación del documento
    //            en un panel visual debajo del formulario. Si el
    //            documento NO es permitido, muestra un toast adicional
    //            indicando que se deriva a oficina oficial.
    // PARÁMETROS:
    //   tipo        - Título del resultado (ej: "DOCUMENTO PERMITIDO")
    //   mensaje     - Descripción detallada del resultado
    //   esPermitido - true si el documento puede ser atendido, false si no
    // ================================================================
    function mostrarValidacion(tipo, mensaje, esPermitido) {
        var $div = $('#documentValidationResult'); // Contenedor del resultado
        var $msg = $('#validationMessage');        // Elemento del mensaje

        // Remuevo clases previas y agrego la clase correspondiente (success o error)
        $div.removeClass('success error').addClass(esPermitido ? 'success' : 'error');

        // Elijo ícono y color según el resultado
        var icono = esPermitido ? 'check_circle' : 'warning';
        var color = esPermitido ? 'green-text' : 'red-text';

        // Construyo el HTML del mensaje con ícono, título y descripción
        $msg.html('<i class="material-icons left ' + color + '" style="font-size:1.3rem;">' + icono + '</i><strong class="' + color + '">' + tipo + '</strong><br><span style="font-size:0.9rem;">' + mensaje + '</span>');

        // Muestro el panel con una animación slideDown
        $div.slideDown(300);

        // Si el documento NO es permitido, muestro un toast adicional informando la derivación
        if (!esPermitido) {
            M.toast({ html: '<i class="material-icons left" style="font-size:1.2rem;">gavel</i> Caso derivado a oficina oficial', classes: 'red rounded', displayLength: 4000 });
        }
    }

    // ================================================================
    // EVENTO: Submit del formulario de asesoría (#asesoriaForm)
    // Valida los campos obligatorios, verifica si el documento está
    // permitido y registra la asesoría en el arreglo local.
    // ================================================================
    $(document).on('submit', '#asesoriaForm', function (e) {
        e.preventDefault(); // Evito el envío tradicional del formulario

        // Obtengo los valores de los campos del formulario (trim para eliminar espacios)
        var ciudadano = $('#ciudadano').val().trim();   // Nombre del ciudadano
        var cedula = $('#cedula').val().trim();         // Cédula de identidad
        var documento = $('#documento').val().trim();   // Tipo de documento legal
        var descripcion = $('#descripcion').val().trim(); // Descripción del caso

        // Validación: los campos obligatorios no pueden estar vacíos
        if (!ciudadano || !cedula || !documento) {
            EIS.toast('Completa los campos obligatorios', 'red', 'error');
            return; // Detengo el envío
        }

        // Verifico si el tipo de documento está permitido
        var permitido = documentoPermitido(documento);

        if (permitido) {
            // CASO: Documento permitido - se atiende directamente
            mostrarValidacion(
                'DOCUMENTO PERMITIDO',
                'El documento <strong>"' + escHtml(documento) + '"</strong> está dentro de los tipos de asesoría que podemos atender. Se ha registrado el servicio exitosamente.',
                true
            );
            // Agrego la asesoría al inicio del arreglo (más reciente primero)
            asesoriasRegistradas.unshift({
                ciudadano: ciudadano,
                cedula: cedula,
                documento: documento,
                descripcion: descripcion,
                estado: 'Permitido',
                fecha: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            });
            actualizarHistorial(); // Refresco la tabla
            EIS.toast('Asesoría registrada para ' + ciudadano, 'green', 'how_to_reg');
        } else {
            // CASO: Documento NO permitido - se debe derivar a oficina oficial
            mostrarValidacion(
                'DOCUMENTO NO PERMITIDO',
                'El documento <strong>"' + escHtml(documento) + '"</strong> no corresponde a los tipos de asesoría que podemos atender. <strong>Este caso debe ser derivado a una Oficina de Atención Legal Oficial.</strong>',
                false
            );
            // Agrego la asesoría como "Denegado" (derivado)
            asesoriasRegistradas.unshift({
                ciudadano: ciudadano,
                cedula: cedula,
                documento: documento,
                descripcion: descripcion,
                estado: 'Denegado',
                fecha: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            });
            actualizarHistorial(); // Refresco la tabla
        }

        // Limpieza del formulario después del registro
        this.reset();                          // Reseteo los campos del formulario
        $('#btnRegistrar').prop('disabled', true); // Deshabilito el botón de registro
        $('label').removeClass('active');      // Restauro los labels flotantes
        $('#documentValidationResult').delay(5000).slideUp(400); // Oculto el resultado después de 5s
    });

    // ================================================================
    // EVENTO: Entrada de texto en el campo #documento
    // Valida en tiempo real el tipo de documento mientras el usuario
    // escribe. Cambia el color y texto del botón de registro según
    // si el documento es permitido o no.
    // ================================================================
    $(document).on('input', '#documento', function () {
        var val = $(this).val().trim(); // Obtengo el valor actual

        if (val.length > 0) {
            // Si hay texto, verifico si es permitido
            var permitido = documentoPermitido(val);
            $('#btnRegistrar').prop('disabled', false); // Habilito el botón

            if (permitido) {
                // Documento permitido: botón índigo con texto "Validar y Registrar"
                $('#btnRegistrar').removeClass('red').addClass('indigo').html('<i class="material-icons left">verified</i>Validar y Registrar');
            } else {
                // Documento no permitido: botón rojo con texto "Derivar a Oficina Oficial"
                $('#btnRegistrar').removeClass('indigo').addClass('red').html('<i class="material-icons left">warning</i>Derivar a Oficina Oficial');
            }
            // Oculto cualquier resultado de validación anterior
            $('#documentValidationResult').slideUp(200);
        } else {
            // Si el campo está vacío, deshabilito el botón y restauro su aspecto original
            $('#btnRegistrar').prop('disabled', true);
            $('#btnRegistrar').removeClass('red').addClass('indigo').html('<i class="material-icons left">verified</i>Validar y Registrar');
        }
    });

    // ================================================================
    // EVENTO: Click en botón eliminar asesoría (.btn-eliminar-asesoria)
    // Solicita confirmación y elimina la asesoría del arreglo local.
    // ================================================================
    $(document).on('click', '.btn-eliminar-asesoria', function () {
        var idx = $(this).data('index'); // Obtengo el índice del registro
        if (confirm('¿Eliminar esta asesoría del registro?')) {
            asesoriasRegistradas.splice(idx, 1); // Elimino del arreglo por índice
            actualizarHistorial(); // Refresco la tabla
            EIS.toast('Asesoría eliminada', 'orange', 'delete');
        }
    });

    // ================================================================
    // EVENTO: Búsqueda en tiempo real en el historial (#searchAsesoria)
    // Filtra las filas de la tabla de historial según el texto ingresado.
    // ================================================================
    $(document).on('input', '#searchAsesoria', debounce(function () {
        var q = $(this).val().toLowerCase(); // Texto de búsqueda en minúsculas
        var $rows = $('#asesoriasTableBody tr'); // Todas las filas de la tabla
        // Recorro cada fila y verifico si contiene el texto buscado
        $rows.each(function () {
            var text = $(this).text().toLowerCase(); // Texto completo de la fila
            $(this).toggle(text.indexOf(q) !== -1); // Muestro/oculto según coincidencia
        });
    }, 300)); // Debounce de 300ms para no sobrecargar

});
