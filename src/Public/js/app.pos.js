// =====================================================================
// ARCHIVO: app.pos.js
// FUNCIÓN: Maneja la interactividad del módulo POS (Punto de Venta).
//          Implementa un carrito de compras conectado al backend:
//          - Carga el catálogo de productos via AJAX
//          - Agrega/elimina productos con cantidades
//          - Busca el cliente por cédula y precarga sus datos
//          - Procesa la venta enviando los items al servidor
//          - Renderizado seguro de todo contenido dinámico (escHtml)
// =====================================================================

$(function () {

    // URL base de la API del módulo de ventas
    var API = '?pagina=ventas&action=';

    function escHtml(str) {
        return $('<span>').text(str).html();
    }

    // Iconos genéricos para el catálogo (asignados según el producto)
    var iconos = [
        'shopping_bag', 'devices', 'computer', 'keyboard', 'mouse',
        'headphones', 'monitor', 'memory', 'cable', 'print'
    ];

    // ================================================================
    // ESTADO DEL CARRITO
    // ================================================================

    var productos = [];           // Catálogo completo cargado desde el backend
    var posCart = [];             // Carrito: [{id, nombre, precio, cantidad, stock}]
    var posTotal = 0;             // Total acumulado de la venta en números

    // ================================================================
    // FUNCIÓN: cargarProductos()
    // PROPÓSITO: Solicita al backend el catálogo de productos disponibles.
    // ================================================================
    function cargarProductos() {
        $.getJSON(API + 'productos', function (r) {
            if (!r.success) { EIS.toast(r.error || 'Error al cargar productos', 'red', 'error'); return; }
            productos = r.data || [];
            renderProductos();
        }).fail(function () {
            $('#posLoading').html('<i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">cloud_off</i> Error al conectar con el servidor');
            EIS.toast('Error de conexión', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: iconoProducto(p)
    // PROPÓSITO: Asigna un ícono determinístico a cada producto.
    // ================================================================
    function iconoProducto(p) {
        return iconos[(p.id || 0) % iconos.length];
    }

    // ================================================================
    // FUNCIÓN: renderProductos()
    // PROPÓSITO: Renderiza la grilla de productos aplicando el filtro
    //            de búsqueda actual. Todo el contenido es escapado.
    // ================================================================
    function renderProductos() {
        var $grid = $('#posProducts');
        var q = $('#posSearch').val().trim().toLowerCase();

        var filtrados = productos.filter(function (p) {
            if (!q) return true;
            var texto = (p.nombre + ' ' + (p.codigo || '') + ' ' + (p.descripcion || '')).toLowerCase();
            return texto.indexOf(q) !== -1;
        });

        if (filtrados.length === 0) {
            $grid.html('<div class="col s12 center-align" style="color:var(--text-muted);padding:2rem 0;"><i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">search_off</i>No hay productos disponibles</div>');
            return;
        }

        var html = '';
        filtrados.forEach(function (p) {
            var enCarrito = posCart.find(function (i) { return i.id == p.id; });
            var cant = enCarrito ? enCarrito.cantidad : 0;

            html += '<div class="col s6 m4 l3">'
                + '<div class="card-panel pos-product" data-id="' + p.id + '" data-name="' + escHtml(p.nombre) + '" data-price="' + escHtml(p.precio_venta) + '" data-stock="' + p.stock + '" title="' + escHtml(p.nombre) + '">'
                + '<i class="material-icons" style="font-size:2.5rem;color:var(--text-muted);">' + iconoProducto(p) + '</i>'
                + '<h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(p.nombre) + '</h6>'
                + '<span style="color:var(--primary);font-weight:700;font-size:1.1rem;">$' + escHtml(parseFloat(p.precio_venta).toFixed(2)) + '</span>'
                + (cant > 0 ? '<div class="chip green white-text" style="font-size:0.7rem;height:auto;padding:0.1rem 0.5rem;margin-left:0.35rem;">' + cant + '</div>' : '')
                + '<div class="pos-add-btn"><i class="material-icons">add</i></div>'
                + '</div>'
                + '</div>';
        });

        $grid.html(html);
    }

    // ================================================================
    // FUNCIÓN: agregarAlCarrito(id)
    // PROPÓSITO: Incrementa la cantidad de un producto en el carrito.
    //            No permite superar el stock disponible.
    // ================================================================
    function agregarAlCarrito(id) {
        var p = productos.find(function (pr) { return pr.id == id; });
        if (!p) return;

        var item = posCart.find(function (i) { return i.id == id; });
        var cantidadActual = item ? item.cantidad : 0;

        if (cantidadActual >= parseInt(p.stock, 10)) {
            EIS.toast('Stock máximo alcanzado para ' + p.nombre, 'red', 'error');
            return;
        }

        if (item) {
            item.cantidad++;
            item.subtotal = parseFloat(item.precio) * item.cantidad;
        } else {
            posCart.push({
                id: p.id,
                nombre: p.nombre,
                precio: parseFloat(p.precio_venta),
                cantidad: 1,
                stock: parseInt(p.stock, 10),
                subtotal: parseFloat(p.precio_venta)
            });
        }

        posTotal = posCart.reduce(function (acc, i) { return acc + i.subtotal; }, 0);
        actualizarPosUI();
        renderProductos();

        EIS.toast(p.nombre + ' agregado al carrito', 'green', 'add_shopping_cart');
    }

    // ================================================================
    // FUNCIÓN: cambiarCantidad(id, delta)
    // PROPÓSITO: Suma o resta cantidad a un item del carrito.
    // ================================================================
    function cambiarCantidad(id, delta) {
        var item = posCart.find(function (i) { return i.id == id; });
        if (!item) return;

        var nueva = item.cantidad + delta;
        if (nueva < 1) {
            EIS.toast('Usa la X para eliminar el producto', 'orange', 'info');
            return;
        }
        if (nueva > item.stock) {
            EIS.toast('Stock máximo: ' + item.stock, 'red', 'error');
            return;
        }

        item.cantidad = nueva;
        item.subtotal = item.precio * nueva;
        posTotal = posCart.reduce(function (acc, i) { return acc + i.subtotal; }, 0);
        actualizarPosUI();
    }

    // ================================================================
    // FUNCIÓN: eliminarDelCarrito(id)
    // PROPÓSITO: Elimina un producto por completo del carrito.
    // ================================================================
    function eliminarDelCarrito(id) {
        posCart = posCart.filter(function (i) { return i.id != id; });
        posTotal = posCart.reduce(function (acc, i) { return acc + i.subtotal; }, 0);
        actualizarPosUI();
        renderProductos();
        EIS.toast('Producto eliminado del carrito', 'orange', 'remove_shopping_cart');
    }

    // ================================================================
    // FUNCIÓN: actualizarPosUI()
    // PROPÓSITO: Actualiza todos los elementos de la interfaz del POS
    //            que dependen del estado del carrito.
    // ================================================================
    function actualizarPosUI() {
        actualizarMiniTotal();
        actualizarCarritoModal();
    }

    // ================================================================
    // FUNCIÓN: actualizarMiniTotal()
    // PROPÓSITO: Actualiza totales y contadores de la interfaz.
    // ================================================================
    function actualizarMiniTotal() {
        var count = posCart.reduce(function (acc, i) { return acc + i.cantidad; }, 0);
        var totalStr = '$' + posTotal.toFixed(2);

        $('#posMiniTotal').text(totalStr);
        $('#posMiniTotalMobile').text(totalStr);
        $('#posTotal').text(totalStr);
        $('#cartCountBadge').text(count);
        $('#cartCountLabel').text(count + ' ' + (count === 1 ? 'producto' : 'productos'));
    }

    // ================================================================
    // FUNCIÓN: actualizarCarritoModal()
    // PROPÓSITO: Renderiza la lista de items con controles de cantidad.
    // ================================================================
    function actualizarCarritoModal() {
        var $div = $('#posCartItems');

        if (posCart.length === 0) {
            $div.html('<p style="color:var(--text-muted);text-align:center;margin-top:2rem;"><i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.25;">remove_shopping_cart</i> El carrito está vacío<br><small style="color:var(--text-light);">Agrega productos desde el catálogo</small></p>');
            return;
        }

        var html = '';
        posCart.forEach(function (item) {
            html += '<div class="cart-item">'
                + '<div style="display:flex;align-items:center;gap:1rem;flex:1;min-width:0;">'
                + '<span class="chip indigo white-text" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;padding:0;font-size:0.8rem;flex-shrink:0;">' + item.id + '</span>'
                + '<div style="min-width:0;flex:1;"><div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(item.nombre) + '</div>'
                + '<div style="color:var(--text-muted);font-size:0.85rem;">$' + item.precio.toFixed(2) + ' c/u</div></div>'
                + '<div style="display:flex;align-items:center;gap:0.25rem;flex-shrink:0;">'
                + '<button class="btn-floating waves-effect waves-light indigo lighten-2 btn-cantidad-menos" data-id="' + item.id + '" style="width:26px;height:26px;line-height:26px;font-size:1rem;" title="Disminuir"><i class="material-icons" style="font-size:1rem;">remove</i></button>'
                + '<span style="min-width:28px;text-align:center;font-weight:700;">' + item.cantidad + '</span>'
                + '<button class="btn-floating waves-effect waves-light indigo lighten-2 btn-cantidad-mas" data-id="' + item.id + '" style="width:26px;height:26px;line-height:26px;font-size:1rem;" title="Aumentar"><i class="material-icons" style="font-size:1rem;">add</i></button>'
                + '</div>'
                + '<div style="font-weight:700;font-size:0.95rem;color:var(--primary);white-space:nowrap;flex-shrink:0;">$' + item.subtotal.toFixed(2) + '</div>'
                + '</div>'
                + '<span class="cart-item-remove" data-id="' + item.id + '" title="Eliminar"><i class="material-icons" style="font-size:1.2rem;">close</i></span>'
                + '</div>';
        });

        $div.html(html);
    }

    // ================================================================
    // FUNCIÓN: buscarCliente(cedula)
    // PROPÓSITO: Consulta el backend y precarga los datos del cliente
    //            en el formulario si ya está registrado.
    // ================================================================
    function buscarCliente(cedula) {
        if (!cedula) return;
        $.getJSON(API + 'buscarCliente&cedula=' + encodeURIComponent(cedula), function (r) {
            if (!r.success || !r.data) return;
            var c = r.data;
            var nombreCompleto = (c.nombre || '') + ((c.apellido || '') ? ' ' + c.apellido : '');
            if (nombreCompleto) {
                $('#posCiudadano').val(nombreCompleto);
            }
            $('#posDireccion').val(c.direccion || '');
            $('#posTelefono').val(c.telefono || '');
            M.updateTextFields();
            EIS.toast('Cliente encontrado: se precargaron sus datos', 'indigo', 'person');
        }).fail(function () {
            EIS.toast('Error al buscar el cliente', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: vaciarCarrito()
    // PROPÓSITO: Limpia el carrito después de confirmación.
    // ================================================================
    function vaciarCarrito() {
        if (posCart.length === 0) return;
        if (confirm('¿Vaciar todo el carrito?')) {
            posCart = [];
            posTotal = 0;
            actualizarPosUI();
            renderProductos();
            EIS.toast('Carrito vaciado', 'red', 'delete_sweep');
        }
    }

    // ================================================================
    // EVENTO: Click en un producto del catálogo (.pos-product)
    // ================================================================
    $(document).on('click', '.pos-product', function () {
        var id = $(this).data('id');
        agregarAlCarrito(id);

        $(this).addClass('selected');
        setTimeout(function () { $('.pos-product.selected').removeClass('selected'); }, 400);
    });

    // ================================================================
    // EVENTO: Click en botones de cantidad del carrito
    // ================================================================
    $(document).on('click', '.btn-cantidad-mas', function () {
        cambiarCantidad($(this).data('id'), 1);
    });

    $(document).on('click', '.btn-cantidad-menos', function () {
        cambiarCantidad($(this).data('id'), -1);
    });

    // ================================================================
    // EVENTO: Click en botón eliminar producto del carrito (.cart-item-remove)
    // ================================================================
    $(document).on('click', '.cart-item-remove', function () {
        eliminarDelCarrito($(this).data('id'));
    });

    // ================================================================
    // EVENTO: Click en botón "Abrir Carrito" (#openCartBtn)
    // ================================================================
    $(document).on('click', '#openCartBtn', function () {
        var instance = M.Modal.getInstance($('#posCartModal'));
        if (!instance) {
            $('#posCartModal').modal();
            instance = M.Modal.getInstance($('#posCartModal'));
        }
        actualizarCarritoModal();
        instance.open();
    });

    // ================================================================
    // EVENTO: Click en botón "Vaciar Carrito" (#vaciarCarrito)
    // ================================================================
    $(document).on('click', '#vaciarCarrito', function () {
        vaciarCarrito();
    });

    // ================================================================
    // EVENTO: Blur de la cédula → precarga datos del cliente
    // ================================================================
    $(document).on('blur', '#posCedula', function () {
        buscarCliente($(this).val().trim());
    });

    // ================================================================
    // EVENTO: Click en botón "Procesar Venta" (#procesarVenta)
    // Valida el carrito y los datos del cliente, y envía la venta.
    // ================================================================
    $(document).on('click', '#procesarVenta', function () {
        if (posCart.length === 0) {
            EIS.toast('El carrito está vacío', 'red', 'error');
            return;
        }

        var ciudadano = $('#posCiudadano').val().trim();
        var cedula = $('#posCedula').val().trim();
        var telefono = $('#posTelefono').val().trim();
        var direccion = $('#posDireccion').val().trim();

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

        if (!confirm('¿Procesar venta por $' + posTotal.toFixed(2) + '?')) return;

        var items = posCart.map(function (i) {
            return { id: i.id, cantidad: i.cantidad };
        });

        var $btn = $('#procesarVenta');
        $btn.prop('disabled', true);

        $.post(API + 'registrar', {
            ciudadano: ciudadano,
            cedula: cedula,
            direccion: direccion,
            telefono: telefono,
            items: JSON.stringify(items)
        }, function (r) {
            $btn.prop('disabled', false);
            if (r.success) {
                EIS.toast(r.message || '¡Venta registrada!', 'green', 'paid');
                posCart = [];
                posTotal = 0;
                actualizarPosUI();
                renderProductos();
                $('#posClienteForm')[0].reset();
                $('label').removeClass('active');
                M.updateTextFields();
                $('#posCartModal').modal('close');
                cargarProductos();
            } else {
                EIS.toast(r.error || 'Error al registrar la venta', 'red', 'error');
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false);
            EIS.toast('Error de conexión', 'red', 'error');
        });
    });

    // ================================================================
    // EVENTO: Búsqueda en tiempo real de productos (#posSearch)
    // ================================================================
    $(document).on('input', '#posSearch', debounce(function () {
        renderProductos();
    }, 200));

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    $('#posCartModal').modal();
    cargarProductos();
});
