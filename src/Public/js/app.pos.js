// =====================================================================
// ARCHIVO: app.pos.js
// FUNCIÓN: Maneja la interactividad del módulo POS (Punto de Venta).
//          Implementa un carrito de compras del lado del cliente con:
//          - Agregar productos al carrito
//          - Visualización del total y cantidad de productos
//          - Modal de detalle del carrito
//          - Eliminar productos individuales del carrito
//          - Vaciar carrito completo
//          - Procesar venta (simulación)
//          - Búsqueda en tiempo real de productos
// =====================================================================

// Espero a que el DOM esté listo para ejecutar el código
$(function () {

    // ================================================================
    // ESTADO DEL CARRITO
    // ================================================================

    var posCart = []; // Arreglo de objetos {name, price} que representa el carrito
    var posTotal = 0; // Total acumulado de la venta en números (suma de precios)

    // ================================================================
    // EVENTO: Click en un producto del catálogo (.pos-product)
    // Agrega el producto al carrito, actualiza la UI y muestra un toast
    // ================================================================
    $(document).on('click', '.pos-product', function () {
        var name = $(this).data('name');       // Nombre del producto desde data-name
        var price = parseFloat($(this).data('price')); // Precio desde data-price (convertido a número)

        // Agrego el producto al arreglo del carrito
        posCart.push({ name: name, price: price });
        posTotal += price; // Sumo el precio al total

        actualizarPosUI(); // Actualizo toda la interfaz del carrito

        // Efecto visual: resalto el producto seleccionado brevemente
        $(this).addClass('selected');
        setTimeout(function () { $('.pos-product.selected').removeClass('selected'); }, 400);

        // Muestro toast confirmando la adición al carrito
        EIS.toast(name + ' agregado al carrito', 'green', 'add_shopping_cart');
    });

    // ================================================================
    // FUNCIÓN: actualizarPosUI()
    // PROPÓSITO: Actualiza todos los elementos de la interfaz del POS
    //            que dependen del estado del carrito.
    // ================================================================
    function actualizarPosUI() {
        actualizarMiniTotal();       // Actualizo el total mini (navbar)
        actualizarCarritoModal();    // Actualizo el contenido del modal del carrito
    }

    // ================================================================
    // FUNCIÓN: actualizarMiniTotal()
    // PROPÓSITO: Actualiza los totales mostrados en la barra superior
    //            y en la interfaz principal (total en $, badge contador).
    // ================================================================
    function actualizarMiniTotal() {
        var count = posCart.length;                           // Cantidad de productos
        var totalStr = '$' + posTotal.toFixed(2);            // Total formateado como moneda

        $('#posMiniTotal').text(totalStr);                   // Total en desktop
        $('#posMiniTotalMobile').text(totalStr);              // Total en móvil
        $('#posTotal').text(totalStr);                        // Total en el modal del carrito
        $('#cartCountBadge').text(count);                     // Badge con número de productos
        $('#cartCountLabel').text(count + ' ' + (count === 1 ? 'producto' : 'productos')); // Texto singular/plural
    }

    // ================================================================
    // FUNCIÓN: actualizarCarritoModal()
    // PROPÓSITO: Renderiza la lista de productos dentro del modal
    //            del carrito. Si está vacío, muestra un mensaje.
    // ================================================================
    function actualizarCarritoModal() {
        var $div = $('#posCartItems'); // Contenedor de los items del carrito

        // Si el carrito está vacío, muestro mensaje de "vacío"
        if (posCart.length === 0) {
            $div.html('<p style="color:var(--text-muted);text-align:center;margin-top:4rem;"><i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.25;">remove_shopping_cart</i> El carrito está vacío<br><small style="color:var(--text-light);">Agrega productos desde el catálogo</small></p>');
            return;
        }

        var html = ''; // Acumulador del HTML

        // Recorro cada item del carrito con su índice
        posCart.forEach(function (item, i) {
            // Construyo un item visual con número, nombre, precio y botón eliminar
            html += '<div class="cart-item">'
                + '<div style="display:flex;align-items:center;gap:1rem;flex:1;">'
                // Chip circular con el número de orden
                + '<span class="chip indigo white-text" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;padding:0;font-size:0.8rem;">' + (i + 1) + '</span>'
                // Nombre y precio del producto
                + '<div><div style="font-weight:600;font-size:0.9rem;">' + item.name + '</div>'
                + '<div style="color:var(--text-muted);font-size:0.85rem;">$' + item.price.toFixed(2) + '</div></div></div>'
                // Botón "X" para eliminar el producto
                + '<span class="cart-item-remove" data-index="' + i + '" title="Eliminar"><i class="material-icons" style="font-size:1.2rem;">close</i></span>'
                + '</div>';
        });

        $div.html(html); // Inserto el HTML generado en el contenedor
    }

    // ================================================================
    // EVENTO: Click en botón eliminar producto del carrito (.cart-item-remove)
    // Elimina un producto específico del carrito por su índice
    // ================================================================
    $(document).on('click', '.cart-item-remove', function () {
        var idx = $(this).data('index'); // Índice del producto en el arreglo

        posTotal -= posCart[idx].price; // Resto el precio del producto del total
        posCart.splice(idx, 1);         // Elimino el producto del arreglo por índice

        actualizarPosUI(); // Actualizo la interfaz

        EIS.toast('Producto eliminado del carrito', 'orange', 'remove_shopping_cart');
    });

    // ================================================================
    // EVENTO: Click en botón "Abrir Carrito" (#openCartBtn)
    // Abre el modal del carrito de compras
    // ================================================================
    $(document).on('click', '#openCartBtn', function () {
        // Obtengo la instancia del modal de Materialize
        var instance = M.Modal.getInstance($('#posCartModal'));
        if (!instance) {
            // Si no existe, lo inicializo
            $('#posCartModal').modal();
            instance = M.Modal.getInstance($('#posCartModal'));
        }
        actualizarCarritoModal(); // Actualizo el contenido antes de mostrar
        instance.open(); // Abro el modal
    });

    // ================================================================
    // EVENTO: Click en botón "Procesar Venta" (#procesarVenta)
    // Simula el procesamiento de la venta, limpia el carrito y cierra
    // el modal. Valida que el carrito no esté vacío.
    // ================================================================
    $(document).on('click', '#procesarVenta', function () {
        // Validación: carrito vacío
        if (posCart.length === 0) {
            EIS.toast('El carrito está vacío', 'red', 'error');
            return;
        }

        var totalVenta = posTotal.toFixed(2); // Total formateado
        // Confirmación antes de procesar
        if (confirm('¿Procesar venta por $' + totalVenta + '?')) {
            // Simulación de venta exitosa
            EIS.toast('¡Venta registrada por $' + totalVenta + '!', 'green', 'paid');

            // Limpio el carrito completamente
            posCart = [];
            posTotal = 0;
            actualizarPosUI(); // Actualizo interfaz
            $('#posCartModal').modal('close'); // Cierro el modal
        }
    });

    // ================================================================
    // EVENTO: Click en botón "Vaciar Carrito" (#vaciarCarrito)
    // Elimina todos los productos del carrito después de confirmación
    // ================================================================
    $(document).on('click', '#vaciarCarrito', function () {
        if (posCart.length === 0) return; // Si está vacío, no hago nada

        if (confirm('¿Vaciar todo el carrito?')) {
            // Limpio el carrito
            posCart = [];
            posTotal = 0;
            actualizarPosUI(); // Actualizo interfaz
            EIS.toast('Carrito vaciado', 'red', 'delete_sweep');
        }
    });

    // ================================================================
    // EVENTO: Búsqueda en tiempo real de productos (#posSearch)
    // Filtra los productos del catálogo visible según el texto ingresado
    // ================================================================
    $(document).on('input', '#posSearch', debounce(function () {
        var q = $(this).val().toLowerCase(); // Texto de búsqueda en minúsculas

        // Recorro cada columna de producto dentro del grid
        $('#posProducts .col').each(function () {
            var text = $(this).text().toLowerCase(); // Texto completo de la tarjeta
            $(this).toggle(text.indexOf(q) !== -1); // Muestro/oculto según coincidencia
        });
    }, 200)); // Debounce de 200ms para respuesta rápida

});
