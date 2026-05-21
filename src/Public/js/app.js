var EIS = {};

/* ===== Funciones globales ===== */
function debounce(fn, delay) {
    var timer;
    return function () {
        var ctx = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
}

function filtrarTabla(inputSelector, tableSelector, colIndex) {
    var q = $(inputSelector).val().toLowerCase();
    $(tableSelector + ' tbody tr').each(function () {
        var $row = $(this);
        var text = colIndex !== undefined
            ? $row.find('td').eq(colIndex).text().toLowerCase()
            : $row.text().toLowerCase();
        $row.toggle(text.indexOf(q) !== -1);
    });
    var visibles = $(tableSelector + ' tbody tr:visible').length;
    var total = $(tableSelector + ' tbody tr').length;
    $(tableSelector).closest('.card').find('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
}

/* ===== Sistema de notificaciones (Toast) ===== */
EIS.toast = function (msg, color, icon) {
    color = color || 'indigo';
    icon = icon || 'check_circle';
    var html = '<i class="material-icons left" style="font-size:1.2rem;">' + icon + '</i>' + msg;
    M.toast({ html: html, classes: color + ' rounded', displayLength: 3000 });
};

$(function () {

    /* ===== Inicializar componentes Materialize ===== */
    $('.sidenav').sidenav();
    $('select').formSelect();
    $('.tooltipped').tooltip();
    $('.modal').modal();
    $('.dropdown-trigger').dropdown();
    $('.tabs').tabs();
    $('.collapsible').collapsible();
    $('.materialboxed').materialbox();
    $('.parallax').parallax();
    $('.pushpin').pushpin();
    $('.scrollspy').scrollSpy();

    /* ===== Reloj en tiempo real ===== */
    function actualizarReloj() {
        var now = new Date();
        var opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        var timeStr = now.toLocaleTimeString('es-ES', opts);
        var dateStr = now.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
        $('#clock').text(timeStr + ' - ' + dateStr);
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    /* ===== Tema oscuro/claro ===== */
    function updateThemeUI(theme) {
        var isDark = theme === 'dark';
        $('#themeIcon').text(isDark ? 'light_mode' : 'dark_mode');
        $('#themeLabel').text(isDark ? 'Modo Claro' : 'Modo Oscuro');
    }

    var currentTheme = localStorage.getItem('theme') || 'light';
    $('html').attr('data-theme', currentTheme);
    updateThemeUI(currentTheme);

    $(document).on('click', '#themeToggle', function () {
        var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
        $('html').attr('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateThemeUI(theme);
        EIS.toast('Tema cambiado a ' + (theme === 'dark' ? 'oscuro' : 'claro'), 'indigo', 'palette');
    });

    /* ===== Transición de página con fadeIn ===== */
    $('main').hide().fadeIn(400);
    $('.container').hide().fadeIn(500);

    /* ===== Animación de contadores en métricas ===== */
    function animarContadores() {
        $('.metric-value').each(function () {
            var $el = $(this);
            var text = $el.text();
            var num = parseFloat(text.replace(/[^0-9.,-]/g, '').replace(',', ''));
            if (isNaN(num)) return;
            var prefix = text.replace(num.toString().replace(',', '.'), '').replace(/[0-9]/g, '').trim();
            var isCurrency = text.indexOf('$') !== -1;
            $el.text(prefix + '0');
            $({ val: 0 }).animate({ val: num }, {
                duration: 1200,
                easing: 'swing',
                step: function () {
                    var v = isCurrency ? '$' + this.val.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') : prefix + Math.round(this.val);
                    $el.text(v);
                },
                complete: function () {
                    $el.text(text);
                }
            });
        });
    }
    animarContadores();

    /* ===== Búsqueda en tablas con debounce ===== */
    $(document).on('input', '#searchProducto', debounce(function () {
        filtrarTabla('#searchProducto', '.responsive-table', 1);
    }, 300));

    $(document).on('input', '#searchProveedor', debounce(function () {
        filtrarTabla('#searchProveedor', '.responsive-table', 1);
    }, 300));

    $(document).on('input', '#searchActivo', debounce(function () {
        filtrarTabla('#searchActivo', '.striped', 0);
    }, 300));

    $(document).on('input', '#posSearch', debounce(function () {
        var q = $(this).val().toLowerCase();
        $('#posProducts .col').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    }, 200));

    /* ===== Filtro por estado (select) en inventario y proveedores ===== */
    $(document).on('change', '#filterEstado, #filterEstadoProv', function () {
        var val = $(this).val().toLowerCase();
        var table = $(this).closest('.card').next('.card').find('table');
        if (!table.length) table = $(this).closest('.row').siblings('.card').find('table');
        table.find('tbody tr').each(function () {
            var badge = $(this).find('.new-badge, .new badge, .badge').text().trim().toLowerCase();
            if (!val || badge.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        var visibles = table.find('tbody tr:visible').length;
        var total = table.find('tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    });

    /* ===== SISTEMA POS con carrito modal ===== */
    var posCart = [];
    var posTotal = 0;

    $(document).on('click', '.pos-product', function () {
        var name = $(this).data('name');
        var price = parseFloat($(this).data('price'));
        posCart.push({ name: name, price: price });
        posTotal += price;
        actualizarPosUI();
        $(this).addClass('selected');
        setTimeout(function () { $('.pos-product.selected').removeClass('selected'); }, 400);
        EIS.toast(name + ' agregado al carrito', 'green', 'add_shopping_cart');
    });

    function actualizarPosUI() {
        actualizarMiniTotal();
        actualizarCarritoModal();
    }

    function actualizarMiniTotal() {
        var count = posCart.length;
        var totalStr = '$' + posTotal.toFixed(2);
        $('#posMiniTotal').text(totalStr);
        $('#posMiniTotalMobile').text(totalStr);
        $('#cartCountBadge').text(count);
        $('#cartCountLabel').text(count + ' ' + (count === 1 ? 'producto' : 'productos'));
    }

    function actualizarCarritoModal() {
        var $div = $('#posCartItems');
        if (posCart.length === 0) {
            $div.html('<p style="color:var(--text-muted);text-align:center;margin-top:4rem;"><i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.25;">remove_shopping_cart</i> El carrito está vacío<br><small style="color:var(--text-light);">Agrega productos desde el catálogo</small></p>');
            return;
        }
        var html = '';
        posCart.forEach(function (item, i) {
            html += '<div class="cart-item">'
                + '<div style="display:flex;align-items:center;gap:1rem;flex:1;">'
                + '<span class="chip indigo white-text" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;padding:0;font-size:0.8rem;">' + (i + 1) + '</span>'
                + '<div><div style="font-weight:600;font-size:0.9rem;">' + item.name + '</div>'
                + '<div style="color:var(--text-muted);font-size:0.85rem;">$' + item.price.toFixed(2) + '</div></div></div>'
                + '<span class="cart-item-remove" data-index="' + i + '" title="Eliminar"><i class="material-icons" style="font-size:1.2rem;">close</i></span>'
                + '</div>';
        });
        $div.html(html);
    }

    $(document).on('click', '.cart-item-remove', function () {
        var idx = $(this).data('index');
        posTotal -= posCart[idx].price;
        posCart.splice(idx, 1);
        actualizarPosUI();
        EIS.toast('Producto eliminado del carrito', 'orange', 'remove_shopping_cart');
    });

    $(document).on('click', '#openCartBtn', function () {
        var instance = M.Modal.getInstance($('#posCartModal'));
        if (!instance) {
            $('#posCartModal').modal();
            instance = M.Modal.getInstance($('#posCartModal'));
        }
        actualizarCarritoModal();
        instance.open();
    });

    $(document).on('click', '#procesarVenta', function () {
        if (posCart.length === 0) {
            EIS.toast('El carrito está vacío', 'red', 'error');
            return;
        }
        var totalVenta = posTotal.toFixed(2);
        if (confirm('¿Procesar venta por $' + totalVenta + '?')) {
            EIS.toast('¡Venta registrada por $' + totalVenta + '!', 'green', 'paid');
            posCart = [];
            posTotal = 0;
            actualizarPosUI();
            $('#posCartModal').modal('close');
        }
    });

    $(document).on('click', '#vaciarCarrito', function () {
        if (posCart.length === 0) return;
        if (confirm('¿Vaciar todo el carrito?')) {
            posCart = [];
            posTotal = 0;
            actualizarPosUI();
            EIS.toast('Carrito vaciado', 'red', 'delete_sweep');
        }
    });

    /* ===== Cyber: toggle estaciones con jQuery ===== */
    function actualizarCyberContadores() {
        var total = $('.station-card').length;
        var disp = $('.station-card.disponible').length;
        var ocup = $('.station-card.ocupada').length;
        var mant = $('.station-card.mantenimiento').length;
        $('#countDisponibles').text(disp);
        $('#countOcupadas').text(ocup);
        $('#countMantenimiento').text(mant);
    }

    $(document).on('click', '.station-card', function () {
        var $card = $(this);
        var status = $card.data('status');
        var num = $card.find('.station-badge').text();

        if (status === 'disponible') {
            if (confirm('¿Iniciar sesión en estación ' + num + '?')) {
                $card.removeClass('disponible').addClass('ocupada').data('status', 'ocupada');
                $card.find('.station-icon .material-icons').text('timelapse');
                $card.find('.station-status').text('Ocupada');
                $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 }).animate({ transform: 'scale(1)', opacity: 1 }, 300);
                actualizarCyberContadores();
                EIS.toast('Sesión iniciada en ' + num, 'green', 'play_circle');
            }
        } else if (status === 'ocupada') {
            if (confirm('¿Finalizar sesión en estación ' + num + '?')) {
                $card.removeClass('ocupada').addClass('disponible').data('status', 'disponible');
                $card.find('.station-icon .material-icons').text('check_circle');
                $card.find('.station-status').text('Disponible');
                $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 }).animate({ transform: 'scale(1)', opacity: 1 }, 300);
                actualizarCyberContadores();
                EIS.toast('Sesión finalizada en ' + num, 'orange', 'stop_circle');
            }
        } else {
            EIS.toast('Estación ' + num + ' en mantenimiento', 'red', 'build');
        }
    });

    /* ===== Cyber: filtrar estaciones con animación ===== */
    $(document).on('click', '.filter-btn', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');
        $('.station-card').each(function () {
            var $col = $(this).closest('.col');
            if (filter === 'all') {
                $col.slideDown(200);
            } else {
                var match = $(this).data('status') === filter;
                if (match) { $col.slideDown(200); } else { $col.hide(); }
            }
        });
        var label = $(this).text().trim();
        EIS.toast('Mostrando: ' + label, 'indigo', 'filter_alt');
    });

    /* ===== Reportes: generador con jQuery ===== */
    $(document).on('submit', '#formReporte', function (e) {
        e.preventDefault();
        var tipo = $(this).find('select').val() || 'Ventas por fecha';
        var formato = $(this).find('input[name="format"]:checked').val();
        EIS.toast('Generando reporte ' + tipo + ' en formato ' + formato.toUpperCase() + '...', 'indigo', 'download');
        setTimeout(function () {
            EIS.toast('Reporte generado exitosamente', 'green', 'check_circle');
        }, 1200);
    });

    /* ===== Botones de acción ===== */
    $(document).on('click', '[data-confirm]', function () {
        var msg = $(this).data('confirm');
        if (!confirm(msg)) return false;
        EIS.toast('Acción completada', 'green', 'done');
    });

    $(document).on('click', '.btn-nuevo', function () {
        var tipo = $(this).data('tipo') || 'elemento';
        EIS.toast('Formulario para nuevo ' + tipo + ' abierto (demo)', 'indigo', 'add_circle');
    });

    /* ===== Paginación con jQuery ===== */
    $(document).on('click', '.pagination li:not(.disabled):not(.active) a', function (e) {
        e.preventDefault();
        var $li = $(this).closest('li');
        var $ul = $li.closest('.pagination');
        $ul.find('li.active').removeClass('active indigo');
        $li.addClass('active indigo');
        var page = $(this).text().trim();
        EIS.toast('Navegando a página ' + page, 'indigo', 'chevron_right');
    });

    /* ===== Enlaces de descarga con feedback ===== */
    $(document).on('click', '.btn-download', function () {
        EIS.toast('Descargando archivo...', 'green', 'file_download');
    });

    /* ===== Tooltips mejorados ===== */
    $(document).on('mouseenter', '.btn-floating, .tooltip-me', function () {
        var title = $(this).attr('title') || $(this).data('tooltip');
        if (title) $(this).attr('title', title);
    });

    /* ===== Asesoría Legal: validación de documentos ===== */
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

    /* ===== Notificaciones de demo (campana) ===== */
    $(document).on('click', '#notifBell', function () {
        var msgs = [
            'Stock crítico: Mouse Inalámbrico',
            'Sesión Cyber #2 finalizada',
            'Nueva solicitud de proveedor'
        ];
        msgs.forEach(function (m) { EIS.toast(m, 'orange', 'notifications'); });
        $('#notifBadge').hide();
    });

    /* ===== Botón volver arriba ===== */
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 400) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });

    $(document).on('click', '#backToTop', function () {
        $('html, body').animate({ scrollTop: 0 }, 400);
    });

});
