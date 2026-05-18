<!-- ============================================================
     VISTA: PUNTO DE VENTA (POS)
     ============================================================
     Interfaz tipo catálogo con productos seleccionables que se
     agregan a un carrito de compras modal. Incluye búsqueda
     en tiempo real y total acumulado.

     Renderizada dentro del layout principal por
     VentasController::index().

     NOTA: La lógica del carrito (agregar, quitar, calcular total,
     procesar venta) es manejada completamente por JavaScript
     en app.js. Los productos y precios son estáticos (UI prototype).
     ============================================================ -->

<!-- ========== ENCABEZADO CON INFO DEL POS ========== -->
<div class="row" style="margin-bottom:1.5rem;">

    <!-- Columna izquierda: título del módulo -->
    <div class="col s12 m7">
        <div class="card" style="margin:0;padding:1.25rem;">
            <span style="font-size:1.2rem;font-weight:700;">
                <i class="material-icons left" style="font-size:1.5rem;">point_of_sale</i>Punto de Venta
            </span>
            <span style="color:var(--text-muted);font-size:0.9rem;display:block;margin-top:0.25rem;">Selecciona los productos y procesa la venta</span>
        </div>
    </div>

    <!-- Columna derecha: resumen del carrito -->
    <div class="col s12 m5" style="padding-top:0;">
        <div class="card" style="margin:0;padding:0.75rem 1.25rem;height:100%;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <!-- Etiqueta del total -->
                <span style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;">Total carrito</span>
                <!-- Valor del total (actualizado dinámicamente por JS) -->
                <!-- id="posMiniTotal" es usado por app.js para actualizar el valor -->
                <div style="font-size:1.5rem;font-weight:800;color:var(--primary);" id="posMiniTotal">$0.00</div>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <!-- Botón que abre el modal del carrito -->
                <!-- id="openCartBtn" manejado por JS en app.js -->
                <button id="openCartBtn" class="btn waves-effect waves-light indigo" style="border-radius:20px;height:3rem;">
                    <i class="material-icons left">shopping_cart</i>
                    <span>Carrito</span>
                    <!-- Badge circular con el contador de productos -->
                    <!-- id="cartCountBadge" actualizado por JS -->
                    <span class="new badge white indigo-text" id="cartCountBadge" style="margin-left:0.5rem;border-radius:50%;font-weight:700;">0</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== CATÁLOGO DE PRODUCTOS ========== -->
<div class="card">
    <div class="card-content">

        <!-- Buscador de productos (filtro JS en tiempo real) -->
        <!-- id="posSearch" manejado por app.js -->
        <div class="input-field" style="margin-top:0;">
            <i class="material-icons prefix">search</i>
            <input type="text" id="posSearch" placeholder="Buscar producto por nombre...">
            <label for="posSearch">Buscar producto</label>
        </div>

        <!-- Grid de productos disponibles -->
        <!-- id="posProducts" contenedor para el filtro JS -->
        <div id="posProducts" class="row" style="margin-top:1rem;">

            <!-- Cada producto tiene data-name y data-price para el JS del carrito -->
            <!-- Estos atributos data-* son leídos por app.js al agregar al carrito -->

            <!-- Producto 1: Teclado Mecánico -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Teclado Mecánico" data-price="45.00">
                    <i class="material-icons" style="font-size:2.5rem;color:#546e7a;">keyboard</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Teclado Mecánico</h6>
                    <span style="color:#3949ab;font-weight:700;font-size:1.1rem;">$45.00</span>
                    <!-- Botón "+" para agregar al carrito (manejado por JS) -->
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>

            <!-- Producto 2: Mouse USB -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Mouse USB" data-price="12.50">
                    <i class="material-icons" style="font-size:2.5rem;color:#546e7a;">mouse</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Mouse USB</h6>
                    <span style="color:#3949ab;font-weight:700;font-size:1.1rem;">$12.50</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>

            <!-- Producto 3: Auriculares -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Auriculares" data-price="35.00">
                    <i class="material-icons" style="font-size:2.5rem;color:#546e7a;">headphones</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Auriculares</h6>
                    <span style="color:#3949ab;font-weight:700;font-size:1.1rem;">$35.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>

            <!-- Producto 4: Monitor 24" -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name='Monitor 24"' data-price="189.00">
                    <i class="material-icons" style="font-size:2.5rem;color:#546e7a;">desktop_windows</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Monitor 24"</h6>
                    <span style="color:#3949ab;font-weight:700;font-size:1.1rem;">$189.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>

            <!-- Producto 5: Cable USB-C -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Cable USB-C" data-price="8.00">
                    <i class="material-icons" style="font-size:2.5rem;color:#546e7a;">usb</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Cable USB-C</h6>
                    <span style="color:#3949ab;font-weight:700;font-size:1.1rem;">$8.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========== MODAL DEL CARRITO DE COMPRAS ========== -->
<!-- Ventana emergente de Materialize (modal) -->
<!-- modal-fixed-footer: pie de modal fijo (siempre visible al hacer scroll) -->
<div id="posCartModal" class="modal modal-fixed-footer" style="max-height:90%;">

    <div class="modal-content">
        <!-- Título del modal -->
        <h4 class="modal-title" style="font-weight:700;margin-bottom:1.5rem;">
            <i class="material-icons left">receipt</i>Carrito de Compras
            <!-- Contador de productos (actualizado por JS) -->
            <span class="result-count" style="font-size:0.85rem;color:var(--text-muted);float:right;font-weight:400;margin-top:0.3rem;" id="cartCountLabel">0 productos</span>
        </h4>

        <!-- Contenedor donde JS renderiza los items del carrito -->
        <div id="posCartItems" style="min-height:250px;">
            <!-- Mensaje de carrito vacío (visible inicialmente) -->
            <!-- Se reemplaza dinámicamente por JS cuando hay productos -->
            <p style="color:var(--text-muted);text-align:center;margin-top:4rem;">
                <i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.25;">remove_shopping_cart</i>
                El carrito está vacío<br>
                <small>Agrega productos desde el catálogo</small>
            </p>
        </div>
    </div>

    <!-- Pie del modal: total y botones de acción -->
    <div class="modal-footer" style="padding:1rem 1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">

            <!-- Total a pagar -->
            <div>
                <span style="color:var(--text-muted);font-size:0.85rem;">TOTAL</span>
                <!-- id="posTotal" actualizado por JS -->
                <span style="font-size:2rem;font-weight:800;color:var(--primary);display:block;" id="posTotal">$0.00</span>
            </div>

            <div style="display:flex;gap:0.75rem;">

                <!-- Botón para vaciar el carrito -->
                <!-- modal-close: cierra el modal de Materialize al hacer clic -->
                <button class="btn waves-effect waves-light red lighten-1 modal-close" id="vaciarCarrito" style="border-radius:20px;">
                    <i class="material-icons left">delete_sweep</i>Vaciar
                </button>

                <!-- Botón para procesar la venta -->
                <button class="btn waves-effect waves-light green" id="procesarVenta" style="border-radius:20px;">
                    <i class="material-icons left">paid</i>Procesar Venta
                </button>

            </div>
        </div>
    </div>

</div>
