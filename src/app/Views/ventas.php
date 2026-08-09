<!-- ============================================================
     VISTA: PUNTO DE VENTA (POS)
     Interfaz tipo catálogo con productos seleccionables que se
     agregan a un carrito de compras modal. Incluye búsqueda
     en tiempo real, total acumulado y registro de la venta con
     datos del cliente.
     NOTA: Lógica del carrito manejada por JS en app.pos.js.
     Los productos se cargan dinámicamente desde el backend.
     ============================================================ -->

<!-- ================================================================ -->
<!-- ENCABEZADO DEL PUNTO DE VENTA -->
<!-- Muestra el titulo del modulo y el resumen del carrito (total + boton) -->
<!-- ================================================================ -->
<div class="row" style="margin-bottom:1.5rem;">

    <!-- ---------------------------------------------------------------- -->
    <!-- COLUMNA IZQUIERDA: TÍTULO DEL MÓDULO -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m7">
        <div class="card" style="margin:0;padding:1.25rem;">
            <!-- Titulo con icono de punto de venta (POS) -->
            <span style="font-size:1.2rem;font-weight:700;"><i class="material-icons left" style="font-size:1.5rem;">point_of_sale</i>Punto de Venta</span>
            <!-- Subtítulo descriptivo -->
            <span style="color:var(--text-muted);font-size:0.9rem;display:block;margin-top:0.25rem;">Selecciona los productos y procesa la venta</span>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- COLUMNA DERECHA: RESUMEN DEL CARRITO -->
    <!-- Muestra el total actual y el boton para abrir el modal del carrito -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m5" style="padding-top:0;">
        <div class="card" style="margin:0;padding:0.75rem 1.25rem;height:100%;display:flex;align-items:center;justify-content:space-between;">

            <!-- Total del carrito (visible solo en pantallas medianas/grandes) -->
            <div class="hide-on-small-only">
                <span style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;">Total carrito</span>
                <!-- Total actualizado dinamicamente por JavaScript (id="posMiniTotal") -->
                <div style="font-size:1.5rem;font-weight:800;color:var(--primary);" id="posMiniTotal">$0.00</div>
            </div>

            <!-- Total del carrito (visible solo en movil, mas pequeño) -->
            <div class="hide-on-med-and-up" style="display:flex;flex-direction:column;gap:0.1rem;">
                <span style="color:var(--text-muted);font-size:0.65rem;text-transform:uppercase;">Total</span>
                <!-- Versión móvil del total (id="posMiniTotalMobile") -->
                <div style="font-size:1.25rem;font-weight:800;color:var(--primary);line-height:1;" id="posMiniTotalMobile">$0.00</div>
            </div>

            <!-- Botones de accion -->
            <div style="display:flex;gap:0.5rem;">
                <!-- Boton que abre el modal del carrito (id="openCartBtn") -->
                <button id="openCartBtn" class="btn waves-effect waves-light indigo" style="border-radius:20px;height:3rem;display:flex;align-items:center;">
                    <i class="material-icons left" style="margin-right:0.25rem;">shopping_cart</i>
                    <span class="hide-on-small-only">Carrito</span>
                    <!-- Badge circular con el contador de productos (actualizado por JS) -->
                    <span class="new badge white indigo-text" id="cartCountBadge" style="margin-left:0.25rem;border-radius:50%;font-weight:700;min-width:22px;height:22px;line-height:22px;">0</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- CATÁLOGO DE PRODUCTOS -->
<!-- Muestra una grilla de productos disponibles para agregar al carrito -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-content">

        <!-- ---------------------------------------------------------------- -->
        <!-- BUSCADOR DE PRODUCTOS -->
        <!-- Input de busqueda que filtra los productos en tiempo real (JS) -->
        <!-- ---------------------------------------------------------------- -->
        <div class="input-field" style="margin-top:0;">
            <i class="material-icons prefix">search</i>
            <input type="text" id="posSearch" placeholder="Buscar producto por nombre...">
            <label for="posSearch">Buscar producto</label>
        </div>

        <!-- ---------------------------------------------------------------- -->
        <!-- GRID DE PRODUCTOS DISPONIBLES -->
        <!-- Los productos se cargan dinámicamente desde el backend via JS -->
        <!-- ---------------------------------------------------------------- -->
        <div id="posProducts" class="row" style="margin-top:1rem;">
            <!-- Mensaje de carga inicial (reemplazado por JS) -->
            <div class="col s12 center-align" id="posLoading" style="color:var(--text-muted);padding:2rem 0;">
                <i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">inventory_2</i>
                Cargando productos...
            </div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL: CARRITO DE COMPRAS -->
<!-- Muestra los productos agregados con cantidades, el total acumulado -->
<!-- y el formulario de datos del cliente para procesar la venta -->
<!-- ================================================================ -->
<div id="posCartModal" class="modal modal-fixed-footer" style="max-height:92%;">
    <div class="modal-content">

        <!-- Título del modal con icono de recibo -->
        <h4 class="modal-title" style="font-weight:700;margin-bottom:1rem;">
            <i class="material-icons left">receipt</i>Carrito de Compras
            <!-- Contador de productos en el carrito (actualizado por JS) -->
            <span class="result-count" style="font-size:0.85rem;color:var(--text-muted);float:right;font-weight:400;margin-top:0.3rem;" id="cartCountLabel">0 productos</span>
        </h4>

        <!-- Contenedor donde JavaScript renderiza dinamicamente los items del carrito -->
        <div id="posCartItems" style="min-height:150px;">
            <!-- Mensaje de carrito vacío (se muestra por defecto, JS lo reemplaza cuando hay items) -->
            <p style="color:var(--text-muted);text-align:center;margin-top:2rem;">
                <i class="material-icons" style="font-size:3.5rem;display:block;margin-bottom:0.5rem;opacity:0.25;">remove_shopping_cart</i>
                El carrito está vacío<br>
                <small>Agrega productos desde el catálogo</small>
            </p>
        </div>

        <!-- Formulario de datos del cliente -->
        <div class="divider" style="margin:1rem 0;"></div>
        <span style="font-weight:600;font-size:0.95rem;display:flex;align-items:center;gap:0.35rem;margin-bottom:0.25rem;">
            <i class="material-icons" style="font-size:1.2rem;color:var(--primary);">person</i> Datos del Cliente
        </span>
        <form id="posClienteForm">
            <div class="row" style="margin-bottom:0;">
                <!-- Nombre completo del cliente -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" id="posCiudadano" name="ciudadano" maxlength="100" placeholder="Nombre y apellido">
                    <label for="posCiudadano">Cliente *</label>
                </div>
                <!-- Cédula de identidad -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person_pin</i>
                    <input type="text" id="posCedula" name="cedula" maxlength="20" placeholder="Ej: 12345678">
                    <label for="posCedula">Cédula *</label>
                </div>
                <!-- Dirección del cliente -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">location_on</i>
                    <input type="text" id="posDireccion" name="direccion" maxlength="500" placeholder="Opcional">
                    <label for="posDireccion">Dirección</label>
                </div>
                <!-- Teléfono del cliente -->
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">phone</i>
                    <input type="text" id="posTelefono" name="telefono" maxlength="20" placeholder="Opcional">
                    <label for="posTelefono">Teléfono</label>
                </div>
            </div>
        </form>
    </div>

    <!-- Pie del modal: total acumulado y botones de accion -->
    <div class="modal-footer" style="padding:1rem 1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">

            <!-- ---------------------------------------------------------------- -->
            <!-- TOTAL DEL CARRITO -->
            <!-- Muestra el monto total en formato grande -->
            <!-- ---------------------------------------------------------------- -->
            <div>
                <span style="color:var(--text-muted);font-size:0.85rem;">TOTAL</span>
                <span style="font-size:2rem;font-weight:800;color:var(--primary);display:block;" id="posTotal">$0.00</span>
            </div>

            <!-- ---------------------------------------------------------------- -->
            <!-- BOTONES DE ACCION -->
            <!-- Vaciar carrito (rojo) y Procesar venta (verde) -->
            <!-- ---------------------------------------------------------------- -->
            <div style="display:flex;gap:0.75rem;">

                <button class="btn waves-effect waves-light red lighten-1 modal-close" id="vaciarCarrito" style="border-radius:20px;display:inline-flex;align-items:center;">
                    <i class="material-icons left" style="margin-right:0.35rem;">delete_sweep</i>Vaciar
                </button>

                <button class="btn waves-effect waves-light green" id="procesarVenta" style="border-radius:20px;white-space:normal;line-height:1.2;height:auto;min-height:2.75rem;padding:0.5rem 0.75rem;display:inline-flex;align-items:center;">
                    <i class="material-icons left" style="margin-right:0.35rem;">paid</i><span>Procesar</span>
                </button>

            </div>
        </div>
    </div>
</div>
