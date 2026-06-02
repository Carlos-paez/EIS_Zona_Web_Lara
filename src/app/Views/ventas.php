<!-- ============================================================
     VISTA: PUNTO DE VENTA (POS)
     Interfaz tipo catálogo con productos seleccionables que se
     agregan a un carrito de compras modal. Incluye búsqueda
     en tiempo real y total acumulado.
     NOTA: Lógica del carrito manejada por JS en app.pos.js.
     Los productos y precios son estáticos (UI prototype).
     ============================================================ -->

<!-- Encabezado con info del POS y resumen del carrito -->
<div class="row" style="margin-bottom:1.5rem;">
    <!-- Título del módulo -->
    <div class="col s12 m7">
        <div class="card" style="margin:0;padding:1.25rem;">
            <span style="font-size:1.2rem;font-weight:700;"><i class="material-icons left" style="font-size:1.5rem;">point_of_sale</i>Punto de Venta</span>
            <span style="color:var(--text-muted);font-size:0.9rem;display:block;margin-top:0.25rem;">Selecciona los productos y procesa la venta</span>
        </div>
    </div>
    <!-- Resumen del total del carrito y botón para abrir el modal -->
    <div class="col s12 m5" style="padding-top:0;">
        <div class="card" style="margin:0;padding:0.75rem 1.25rem;height:100%;display:flex;align-items:center;justify-content:space-between;">
            <div class="hide-on-small-only">
                <span style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;">Total carrito</span>
                <div style="font-size:1.5rem;font-weight:800;color:var(--primary);" id="posMiniTotal">$0.00</div> <!-- Actualizado por JS -->
            </div>
            <div class="hide-on-med-and-up" style="display:flex;flex-direction:column;gap:0.1rem;">
                <span style="color:var(--text-muted);font-size:0.65rem;text-transform:uppercase;">Total</span>
                <div style="font-size:1.25rem;font-weight:800;color:var(--primary);line-height:1;" id="posMiniTotalMobile">$0.00</div>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <!-- Botón que abre el modal del carrito -->
                <button id="openCartBtn" class="btn waves-effect waves-light indigo" style="border-radius:20px;height:3rem;display:flex;align-items:center;">
                    <i class="material-icons left" style="margin-right:0.25rem;">shopping_cart</i>
                    <span class="hide-on-small-only">Carrito</span>
                    <span class="new badge white indigo-text" id="cartCountBadge" style="margin-left:0.25rem;border-radius:50%;font-weight:700;min-width:22px;height:22px;line-height:22px;">0</span> <!-- Contador de productos -->
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Catálogo de productos -->
<div class="card">
    <div class="card-content">
        <!-- Buscador de productos (filtro JS) -->
        <div class="input-field" style="margin-top:0;">
            <i class="material-icons prefix">search</i>
            <input type="text" id="posSearch" placeholder="Buscar producto por nombre...">
            <label for="posSearch">Buscar producto</label>
        </div>
        <!-- Grid de productos disponibles -->
        <div id="posProducts" class="row" style="margin-top:1rem;">
            <!-- Cada producto tiene data-name y data-price para el JS del carrito -->
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Teclado Mecánico" data-price="45.00">
                    <i class="material-icons" style="font-size:2.5rem;color:var(--text-muted);">keyboard</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Teclado Mecánico</h6>
                    <span style="color:var(--primary);font-weight:700;font-size:1.1rem;">$45.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div> <!-- Botón "+" para agregar al carrito -->
                </div>
            </div>
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Mouse USB" data-price="12.50">
                    <i class="material-icons" style="font-size:2.5rem;color:var(--text-muted);">mouse</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Mouse USB</h6>
                    <span style="color:var(--primary);font-weight:700;font-size:1.1rem;">$12.50</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Auriculares" data-price="35.00">
                    <i class="material-icons" style="font-size:2.5rem;color:var(--text-muted);">headphones</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Auriculares</h6>
                    <span style="color:var(--primary);font-weight:700;font-size:1.1rem;">$35.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Monitor 24" data-price="189.00">
                    <i class="material-icons" style="font-size:2.5rem;color:var(--text-muted);">desktop_windows</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Monitor 24"</h6>
                    <span style="color:var(--primary);font-weight:700;font-size:1.1rem;">$189.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>
            <div class="col s6 m4 l3">
                <div class="card-panel pos-product" data-name="Cable USB-C" data-price="8.00">
                    <i class="material-icons" style="font-size:2.5rem;color:var(--text-muted);">usb</i>
                    <h6 style="font-size:0.9rem;margin:0.5rem 0 0.25rem;">Cable USB-C</h6>
                    <span style="color:var(--primary);font-weight:700;font-size:1.1rem;">$8.00</span>
                    <div class="pos-add-btn"><i class="material-icons">add</i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal del carrito de compras (ventana emergente) -->
<div id="posCartModal" class="modal modal-fixed-footer" style="max-height:90%;">
    <div class="modal-content">
        <h4 class="modal-title" style="font-weight:700;margin-bottom:1.5rem;">
            <i class="material-icons left">receipt</i>Carrito de Compras
            <span class="result-count" style="font-size:0.85rem;color:var(--text-muted);float:right;font-weight:400;margin-top:0.3rem;" id="cartCountLabel">0 productos</span>
        </h4>
        <!-- Contenedor donde JS renderiza los items del carrito -->
        <div id="posCartItems" style="min-height:250px;">
            <!-- Mensaje de carrito vacío (se reemplaza dinámicamente por JS) -->
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
            <div>
                <span style="color:var(--text-muted);font-size:0.85rem;">TOTAL</span>
                <span style="font-size:2rem;font-weight:800;color:var(--primary);display:block;" id="posTotal">$0.00</span>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <!-- Botón para vaciar el carrito -->
                <button class="btn waves-effect waves-light red lighten-1 modal-close" id="vaciarCarrito" style="border-radius:20px;display:inline-flex;align-items:center;">
                    <i class="material-icons left" style="margin-right:0.35rem;">delete_sweep</i>Vaciar
                </button>
                <!-- Botón para procesar la venta -->
                <button class="btn waves-effect waves-light green" id="procesarVenta" style="border-radius:20px;white-space:normal;line-height:1.2;height:auto;min-height:2.75rem;padding:0.5rem 0.75rem;display:inline-flex;align-items:center;">
                    <i class="material-icons left" style="margin-right:0.35rem;">paid</i><span>Procesar</span>
                </button>
            </div>
        </div>
    </div>
</div>
