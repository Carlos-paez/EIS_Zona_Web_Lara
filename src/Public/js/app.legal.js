$(function () {

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

    var asesoriasRegistradas = [];

    function normalizarDoc(texto) {
        return texto.toLowerCase().replace(/\s+/g, ' ').trim();
    }

    function documentoPermitido(doc) {
        return allowedDocs.indexOf(normalizarDoc(doc)) !== -1;
    }

    function actualizarHistorial() {
        var $tbody = $('#asesoriasTableBody');
        var $empty = $('#asesoriasEmpty');

        if (asesoriasRegistradas.length === 0) {
            $tbody.html('');
            $empty.show();
            $('#totalAsesoriasBadge').text('0');
            $('#asesoriasCountChip').text('0 registradas hoy');
            return;
        }

        $empty.hide();
        var html = '';
        asesoriasRegistradas.forEach(function (a, i) {
            var estadoClass = a.estado === 'Permitido' ? 'legal-permitido' : 'legal-denegado';
            var icono = a.estado === 'Permitido' ? 'check_circle' : 'cancel';
            html += '<tr>'
                + '<td class="hide-on-small-only">' + (i + 1) + '</td>'
                + '<td><strong>' + a.ciudadano + '</strong></td>'
                + '<td class="hide-on-small-only">' + a.cedula + '</td>'
                + '<td>' + a.documento + '</td>'
                + '<td><span class="' + estadoClass + '" style="white-space:nowrap;"><i class="material-icons left" style="font-size:0.85rem;margin:0;">' + icono + '</i>' + a.estado + '</span></td>'
                + '<td class="hide-on-small-only" style="font-size:0.8rem;color:var(--text-muted);">' + a.fecha + '</td>'
                + '<td class="right-align hide-on-small-only" style="white-space:nowrap;">'
                + '<button class="btn-floating waves-effect waves-light grey tooltipped btn-eliminar-asesoria" data-index="' + i + '" data-position="top" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>'
                + '</td>'
                + '<td class="right-align hide-on-med-and-up">'
                + '<button class="btn-floating waves-effect waves-light grey tooltipped btn-eliminar-asesoria" data-index="' + i + '" data-position="top" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>'
                + '</td>'
                + '</tr>';
        });
        $tbody.html(html);
        $('#totalAsesoriasBadge').text(asesoriasRegistradas.length);
        var pendientes = asesoriasRegistradas.filter(function (a) { return a.estado === 'Denegado'; }).length;
        $('#asesoriasCountChip').text(asesoriasRegistradas.length + ' registradas' + (pendientes > 0 ? ' (' + pendientes + ' derivadas)' : ''));
        $('.tooltipped').tooltip();
    }

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

    $(document).on('submit', '#asesoriaForm', function (e) {
        e.preventDefault();
        var ciudadano = $('#ciudadano').val().trim();
        var cedula = $('#cedula').val().trim();
        var documento = $('#documento').val().trim();
        var descripcion = $('#descripcion').val().trim();

        if (!ciudadano || !cedula || !documento) {
            EIS.toast('Completa los campos obligatorios', 'red', 'error');
            return;
        }

        var permitido = documentoPermitido(documento);

        if (permitido) {
            mostrarValidacion(
                'DOCUMENTO PERMITIDO',
                'El documento <strong>"' + documento + '"</strong> está dentro de los tipos de asesoría que podemos atender. Se ha registrado el servicio exitosamente.',
                true
            );
            asesoriasRegistradas.unshift({
                ciudadano: ciudadano,
                cedula: cedula,
                documento: documento,
                descripcion: descripcion,
                estado: 'Permitido',
                fecha: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            });
            actualizarHistorial();
            EIS.toast('Asesoría registrada para ' + ciudadano, 'green', 'how_to_reg');
        } else {
            mostrarValidacion(
                'DOCUMENTO NO PERMITIDO',
                'El documento <strong>"' + documento + '"</strong> no corresponde a los tipos de asesoría que podemos atender. <strong>Este caso debe ser derivado a una Oficina de Atención Legal Oficial.</strong>',
                false
            );
            asesoriasRegistradas.unshift({
                ciudadano: ciudadano,
                cedula: cedula,
                documento: documento,
                descripcion: descripcion,
                estado: 'Denegado',
                fecha: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            });
            actualizarHistorial();
        }

        this.reset();
        $('#btnRegistrar').prop('disabled', true);
        $('label').removeClass('active');
        $('#documentValidationResult').delay(5000).slideUp(400);
    });

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

    $(document).on('click', '.btn-eliminar-asesoria', function () {
        var idx = $(this).data('index');
        if (confirm('¿Eliminar esta asesoría del registro?')) {
            asesoriasRegistradas.splice(idx, 1);
            actualizarHistorial();
            EIS.toast('Asesoría eliminada', 'orange', 'delete');
        }
    });

    $(document).on('input', '#searchAsesoria', debounce(function () {
        var q = $(this).val().toLowerCase();
        var $rows = $('#asesoriasTableBody tr');
        $rows.each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    }, 300));

});
