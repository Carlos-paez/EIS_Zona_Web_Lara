$(function () {

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
        $('#posTotal').text(totalStr);
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

    $(document).on('input', '#posSearch', debounce(function () {
        var q = $(this).val().toLowerCase();
        $('#posProducts .col').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    }, 200));

});
