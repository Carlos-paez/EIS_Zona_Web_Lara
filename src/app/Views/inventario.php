<?php
// =====================================================================
// VISTA DE INVENTARIO (inventario.php)
// =====================================================================
// Esta es la pagina principal del modulo de inventario.
// Muestra:
//   1. Tarjetas con indicadores (KPIs): Total productos, Stock critico,
//      Stock bajo, Valor total del inventario
//   2. Barra de busqueda y filtro por estado
//   3. Tabla con la lista de productos
//   4. Modal para: crear/editar producto
//
// Los datos se cargan desde PHP en el servidor y el JavaScript
// (app.inventario.js) se encarga de las operaciones dinamicas.
// =====================================================================

// Importo la clase Inventario desde el namespace App\Models
use App\Models\Inventario;

// Instancio el modelo de Inventario para acceder a los datos
$inventarioModel = new Inventario();

// Obtengo la lista completa de productos desde la base de datos
$productos      = $inventarioModel->obtenerProductos();
// Obtengo el total de productos registrados (conteo)
$totalP         = $inventarioModel->totalProductos();
// Obtengo la cantidad de productos con stock critico (stock <= minimo)
$critico        = $inventarioModel->stockCritico();
// Obtengo la cantidad de productos con stock bajo (cerca del minimo)
$bajo           = $inventarioModel->stockBajo();
// Obtengo el valor total del inventario (suma de precio_venta * stock)
$valor          = $inventarioModel->valorTotalInventario();
// Calculo el total de productos contando el array de productos
$totalProductos = count($productos);
// Fin del bloque PHP, paso a la vista HTML
?>

<!-- ================================================================ -->
<!-- TARJETAS DE INDICADORES (KPIs) -->
<!-- Muestran 4 metricas importantes del inventario en tarjetas -->
<!-- ================================================================ -->
<div class="row" style="margin-bottom:1.25rem;">

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 1: TOTAL PRODUCTOS -->
    <!-- Muestra el numero total de productos registrados en el inventario -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <!-- Icono de inventario (caja con productos) -->
            <div class="metric-icon"><i class="material-icons">inventory_2</i></div>
            <div class="metric-label">Total Productos</div>
            <!-- Muestra el numero total de productos, se actualiza via JS con id="kpi-total" -->
            <div class="metric-value" id="kpi-total"><?php echo $totalP; ?></div>
            <!-- Texto secundario indicando que son productos en inventario -->
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">En inventario</div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 2: STOCK CRITICO -->
    <!-- Productos con stock igual o por debajo del minimo (alerta roja) -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <!-- Clase "danger" para fondo rojo -->
        <div class="metric-card danger" style="margin:0;">
            <!-- Icono de advertencia -->
            <div class="metric-icon"><i class="material-icons">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
            <!-- Muestra el numero en color rojo para alertar visualmente -->
            <div class="metric-value" style="color:var(--danger);" id="kpi-critico"><?php echo $critico; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Requiere atención</div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 3: STOCK BAJO -->
    <!-- Productos con stock bajo pero no critico (alerta amarilla) -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <!-- Clase "warning" para fondo amarillo -->
        <div class="metric-card warning" style="margin:0;">
            <!-- Icono de inventario -->
            <div class="metric-icon"><i class="material-icons">inventory</i></div>
            <div class="metric-label">Stock Bajo</div>
            <!-- Valor en color warning (naranja/amarillo) -->
            <div class="metric-value" style="color:var(--warning);" id="kpi-bajo"><?php echo $bajo; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Por debajo del mínimo</div>
        </div>
    </div>

    <!-- ---------------------------------------------------------------- -->
    <!-- TARJETA 4: VALOR TOTAL DEL INVENTARIO -->
    <!-- Suma monetaria de todos los productos (precio_venta * stock) -->
    <!-- ---------------------------------------------------------------- -->
    <div class="col s12 m6 l3">
        <!-- Clase "info" para fondo azul -->
        <div class="metric-card info" style="margin:0;">
            <!-- Icono de pagos/dinero -->
            <div class="metric-icon"><i class="material-icons">payments</i></div>
            <div class="metric-label">Valor Total</div>
            <!-- Muestra el valor con formato de moneda (2 decimales) usando number_format de PHP -->
            <div class="metric-value" style="color:var(--info);" id="kpi-valor">$<?php echo number_format($valor, 2); ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">En productos</div>
        </div>
    </div>

</div>

<!-- ================================================================ -->
<!-- BARRA DE BUSQUEDA Y FILTROS -->
<!-- Permite buscar productos por texto y filtrar por estado -->
<!-- ================================================================ -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <!-- Fila flex con alineacion vertical centrada y wrap para responsive -->
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">

            <!-- ---------------------------------------------------------------- -->
            <!-- CAMPO DE BUSQUEDA POR TEXTO -->
            <!-- Filtro en tiempo real sobre la tabla de productos -->
            <!-- ---------------------------------------------------------------- -->
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <!-- Icono de lupa como prefijo -->
                    <i class="material-icons prefix">search</i>
                    <!-- Input de texto: cuando el usuario escribe, el JS filtra la tabla -->
                    <input type="text" id="searchProducto" placeholder="Buscar por nombre o código...">
                    <!-- Etiqueta flotante -->
                    <label for="searchProducto">Buscar producto</label>
                </div>
            </div>

            <!-- ---------------------------------------------------------------- -->
            <!-- SELECTOR DE FILTRO POR ESTADO -->
            <!-- Permite filtrar productos por: Todo, Stock OK, Critico, Sin stock -->
            <!-- ---------------------------------------------------------------- -->
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterEstado">
                        <!-- Opcion por defecto: muestra todos -->
                        <option value="" selected>Todo</option>
                        <!-- Filtro: productos con stock normal -->
                        <option value="ok">Stock OK</option>
                        <!-- Filtro: productos con stock critico -->
                        <option value="crítico">Crítico</option>
                        <!-- Filtro: productos agotados -->
                        <option value="sin stock">Sin stock</option>
                    </select>
                    <label>Estado</label>
                </div>
            </div>

            <!-- ---------------------------------------------------------------- -->
            <!-- BOTON "NUEVO PRODUCTO" -->
            <!-- Abre el modal para crear un nuevo producto -->
            <!-- ---------------------------------------------------------------- -->
            <div class="col s6 m4 l4 right-align" style="padding:0.5rem 0 0;">
                <button class="btn waves-effect waves-light grey btn-gestionar-categorias" data-tipo="categoria" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;margin-right:0.5rem;">
                    <i class="material-icons left" style="margin:0;">category</i>
                    <span class="hide-on-small-only">Categorías</span>
                    <span class="hide-on-med-and-up">Cat.</span>
                </button>
                <!-- Boton con forma de pildora (border-radius:24px) -->
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="producto" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <!-- Icono de "+" (agregar) -->
                    <i class="material-icons left" style="margin:0;">add</i>
                    <!-- Texto cambia segun el tamaño de pantalla: "Nuevo Producto" en desktop, "Nuevo" en movil -->
                    <span class="hide-on-small-only">Nuevo Producto</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- TABLA DE PRODUCTOS -->
<!-- Muestra todos los productos con su informacion y botones de accion -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-content" style="padding:0;">

        <!-- ---------------------------------------------------------------- -->
        <!-- ENCABEZADO DE LA TABLA -->
        <!-- Titulo "Lista de Productos" con contador de productos -->
        <!-- ---------------------------------------------------------------- -->
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <!-- Icono de inventario a la izquierda del titulo -->
                <i class="material-icons" style="color:var(--primary);">inventory_2</i> Lista de Productos
            </span>
            <!-- Contador de productos que se actualiza con JS (clase result-count) -->
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalProductos; ?> productos</span>
        </div>

        <!-- ---------------------------------------------------------------- -->
        <!-- CONTENEDOR CON SCROLL HORIZONTAL -->
        <!-- Para que la tabla sea desplazable en pantallas pequeñas -->
        <!-- ---------------------------------------------------------------- -->
        <div style="overflow-x:auto;margin-top:0.75rem;">

            <!-- Tabla con stripeado (filas alternadas) y clase inv-table para estilos personalizados -->
            <table class="striped inv-table" id="tabla-productos" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:580px;">

                <!-- Encabezados de columna (thead) -->
                <thead>
                    <!-- Fila del encabezado con fondo de la variable CSS --surface-hover -->
                    <tr style="background:var(--surface-hover);">
                        <!-- Columna: Nombre del producto -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Producto</th>
                        <!-- Columna: Codigo/ID del producto -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">ID</th>
                        <!-- Columna: Precio de venta -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Precio</th>
                        <!-- Columna: Cantidad en stock -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Stock</th>
                        <!-- Columna: Estado (OK / Critico / Sin stock) -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Estado</th>
                        <!-- Columna: Botones de accion (alineada a la derecha) -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acción</th>
                    </tr>
                </thead>

                <!-- Cuerpo de la tabla (tbody) -->
                <tbody>

                    <!-- ---------------------------------------------------------------- -->
                    <!-- CASO: NO HAY PRODUCTOS -->
                    <!-- Muestra una fila con mensaje y icono grande si no hay registros -->
                    <!-- ---------------------------------------------------------------- -->
                    <?php
                    // Verifico si el array de productos esta vacio
                    if (empty($productos)): ?>
                        <tr>
                            <!-- Fila unica que ocupa las 6 columnas (colspan=6) -->
                            <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">
                                <!-- Icono grande de inventario -->
                                <i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">inventory_2</i>
                                <!-- Mensaje indicando que no hay productos registrados -->
                                No hay productos registrados
                            </td>
                        </tr>
                    <?php
                    // CASO: HAY PRODUCTOS
                    // Los recorro con un foreach generando una fila por cada uno
                    else: ?>
                        <?php
                        // Inicio del bucle que itera sobre cada producto
                        foreach ($productos as $p):
                            // --- LOGICA PARA DETERMINAR EL ESTADO DEL PRODUCTO ---
                            // Segun el stock respecto al minimo, se asignan colores e iconos

                            // Inicializo las variables que usare en la fila
                            $estado = '';      // Texto del estado: "Sin stock", "Critico" u "OK"
                            $badgeClass = '';  // Clase CSS para el color de fondo del badge
                            $icon = '';        // Icono de Material Icons a mostrar
                            $barClass = '';    // Color de la barra de progreso de stock
                            $barWidth = 0;     // Ancho porcentual de la barra de stock
                            $iconBg = '';      // Color de fondo del circulo del icono
                            $stockColor = '';  // Color del texto del stock

                            // CASO 1: Stock en CERO o negativo -> "Sin stock" (rojo)
                            if ($p['stock'] <= 0) {
                                $estado = 'Sin stock';
                                // Fondo rojo claro, texto rojo oscuro
                                $badgeClass = 'background:#fce4ec;color:#c62828;';
                                // Icono de "bloqueado/prohibido"
                                $icon = 'block';
                                // Color rojo para la barra de progreso
                                $barClass = 'var(--danger)';
                                // Fondo del icono rojo claro
                                $iconBg = '#fce4ec';
                                // Color del icono rojo
                                $iconColor = 'var(--danger)';
                                // Color del texto del stock rojo
                                $stockColor = 'var(--danger)';
                                // Barra vacia (0% de ancho)
                                $barWidth = 0;

                            // CASO 2: Stock entre 1 y el minimo -> "Critico" (rojo)
                            } elseif ($p['stock'] <= $p['stock_minimo']) {
                                $estado = 'Crítico';
                                // Mismos colores rojos que sin stock
                                $badgeClass = 'background:#fce4ec;color:#c62828;';
                                $icon = 'warning';     // Icono de advertencia
                                $barClass = 'var(--danger)';
                                $iconBg = '#fce4ec';
                                $iconColor = 'var(--danger)';
                                $stockColor = 'var(--danger)';
                                // La barra muestra el porcentaje respecto al minimo, minimo 5% para que se vea
                                $barWidth = $p['stock_minimo'] > 0 ? max(5, ($p['stock'] / $p['stock_minimo']) * 100) : 50;

                            // CASO 3: Stock mayor al minimo -> "OK" (verde)
                            } else {
                                $estado = 'OK';
                                // Fondo verde claro, texto verde oscuro
                                $badgeClass = 'background:#e8f5e9;color:#2e7d32;';
                                // Icono de verificado (check)
                                $icon = 'check_circle';
                                $barClass = 'var(--success)';
                                $iconBg = '#e8f5e9';
                                $iconColor = 'var(--success)';
                                $stockColor = 'var(--success)';
                                // La barra se llena hasta 50% como maximo (escala comprimida para no saturar)
                                $barWidth = $p['stock_minimo'] > 0 ? min(100, ($p['stock'] / $p['stock_minimo']) * 50) : 100;
                            }
                        ?>
                            <!-- Fila del producto con atributos data-* para el JavaScript -->
                            <!-- data-id: ID del producto, data-nombre: nombre para mostrar en confirmaciones -->
                            <tr data-id="<?php echo $p['id']; ?>" data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>" style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">

                                <!-- ---------------------------------------------------------------- -->
                                <!-- COLUMNA 1: NOMBRE DEL PRODUCTO Y CATEGORIA -->
                                <!-- Muestra icono circular, nombre y categoria -->
                                <!-- ---------------------------------------------------------------- -->
                                <td style="padding:0.85rem 1rem;">
                                    <div style="display:flex;align-items:center;gap:0.75rem;">
                                        <!-- Icono circular con el color de fondo segun el estado del producto -->
                                        <div style="width:38px;height:38px;border-radius:8px;background:<?php echo $iconBg; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <!-- Icono de Material IWords segun estado (block, warning, check_circle) -->
                                            <i class="material-icons" style="color:<?php echo $iconColor; ?>;font-size:1.2rem;"><?php echo $icon; ?></i>
                                        </div>
                                        <div>
                                            <!-- Nombre del producto escapado con htmlspecialchars para evitar XSS -->
                                            <div style="font-weight:600;color:var(--text);font-size:0.9rem;"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                            <!-- Categoria del producto (o "Sin categoria" si no tiene) -->
                                            <span style="font-size:0.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($p['categoria'] ?? 'Sin categoría'); ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- ---------------------------------------------------------------- -->
                                <!-- COLUMNA 2: CODIGO / ID DEL PRODUCTO -->
                                <!-- Muestra el codigo unico con prefijo # -->
                                <!-- ---------------------------------------------------------------- -->
                                <td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#<?php echo htmlspecialchars($p['codigo']); ?></td>

                                <!-- ---------------------------------------------------------------- -->
                                <!-- COLUMNA 3: PRECIO DE VENTA -->
                                <!-- Muestra el precio formateado con 2 decimales y simbolo $ -->
                                <!-- ---------------------------------------------------------------- -->
                                <td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$<?php echo number_format($p['precio_venta'], 2); ?></td>

                                <!-- ---------------------------------------------------------------- -->
                                <!-- COLUMNA 4: STOCK CON BARRA DE PROGRESO -->
                                <!-- Muestra el numero de stock, el minimo y una barra visual -->
                                <!-- ---------------------------------------------------------------- -->
                                <td style="padding:0.85rem 1rem;">
                                    <div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;">
                                        <!-- Fila con el numero de stock y el minimo -->
                                        <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                                            <!-- Stock actual (color segun estado) -->
                                            <span style="font-weight:700;color:<?php echo $stockColor; ?>;"><?php echo $p['stock']; ?></span>
                                            <!-- Stock minimo en gris -->
                                            <span style="color:var(--text-muted);font-size:0.7rem;">mín: <?php echo $p['stock_minimo']; ?></span>
                                        </div>
                                        <!-- Barra de progreso visual del stock -->
                                        <div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                                            <!-- Relleno de la barra con ancho y color segun estado -->
                                            <div style="width:<?php echo $barWidth; ?>%;height:100%;background:<?php echo $barClass; ?>;border-radius:4px;"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- ---------------------------------------------------------------- -->
                                <!-- COLUMNA 5: BADGE DE ESTADO -->
                                <!-- Muestra un badge de color con icono y texto (Sin stock / Critico / OK) -->
                                <!-- ---------------------------------------------------------------- -->
                                <td style="padding:0.85rem 1rem;">
                                    <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;<?php echo $badgeClass; ?>">
                                        <!-- Icono del estado -->
                                        <i class="material-icons" style="font-size:0.85rem;"><?php echo $icon; ?></i> <?php echo $estado; ?>
                                    </span>
                                </td>

                                <!-- ---------------------------------------------------------------- -->
                                <!-- COLUMNA 6: BOTONES DE ACCION -->
                                <!-- Botones de editar (indigo) y eliminar (rojo) con tooltips -->
                                <!-- ---------------------------------------------------------------- -->
                                <td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">
                                    <!-- Boton: Editar producto (indigo, con tooltip "Editar") -->
                                    <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar" data-id="<?php echo $p['id']; ?>" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>
                                    <!-- Boton: Eliminar producto (rojo, con tooltip "Eliminar") -->
                                    <button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar" data-id="<?php echo $p['id']; ?>" data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>" data-position="left" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>
                                </td>

                            </tr>
                        <?php
                        // Fin del bucle foreach
                        endforeach; ?>
                    <?php
                    // Fin del condicional if/else
                    endif; ?>

                </tbody>
            </table>
        </div>

        <!-- ---------------------------------------------------------------- -->
        <!-- PIE DE TABLA -->
        <!-- Muestra el contador de productos nuevamente en el pie -->
        <!-- ---------------------------------------------------------------- -->
        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalProductos; ?> productos</span>
        </div>

    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL: CREAR / EDITAR PRODUCTO -->
<!-- Ventana emergente (modal) que se abre al hacer clic en -->
<!-- "Nuevo Producto" o en el boton de editar de alguna fila. -->
<!-- Cambia el titulo segun la accion: "Nuevo Producto" o "Editar Producto" -->
<!-- ================================================================ -->
<div id="modal-producto" class="modal modal-fixed-footer">
    <div class="modal-content">

        <!-- El titulo lo cambia el JavaScript segun sea "Nuevo Producto" o "Editar Producto" -->
        <h4 id="modal-producto-title">Nuevo Producto</h4>

        <!-- Formulario de producto. La accion la maneja el JS mediante AJAX -->
        <form id="form-producto">
            <!-- Campo oculto con el ID del producto (vacio si es nuevo, relleno si es edicion) -->
            <input type="hidden" name="id" id="producto-id" value="">

            <div class="row">
                <!-- Campo: Codigo del producto (obligatorio) -->
                <div class="input-field col s12 m6">
                    <input type="text" name="codigo" id="producto-codigo" required>
                    <label for="producto-codigo">Código</label>
                </div>
                <!-- Campo: Nombre del producto (obligatorio) -->
                <div class="input-field col s12 m6">
                    <input type="text" name="nombre" id="producto-nombre" required>
                    <label for="producto-nombre">Nombre del Producto</label>
                </div>
            </div>

            <div class="row">
                <!-- Campo: Descripcion (textarea multilinea) -->
                <div class="input-field col s12">
                    <textarea name="descripcion" id="producto-descripcion" class="materialize-textarea" style="min-height:60px;"></textarea>
                    <label for="producto-descripcion">Descripción</label>
                </div>
            </div>

            <div class="row">
                <!-- Select: Categoria del producto (obligatorio) -->
                <div class="input-field col s12 m6">
                    <select name="categoria_id" id="producto-categoria" required>
                        <option value="" disabled selected>Seleccione</option>
                    </select>
                    <label>Categoría</label>
                </div>
                <!-- Campo: Stock actual (numero, minimo 0, valor por defecto 0) -->
                <div class="input-field col s12 m3">
                    <input type="number" name="stock" id="producto-stock" min="0" value="0">
                    <label for="producto-stock">Stock</label>
                </div>
                <!-- Campo: Stock minimo (numero, minimo 0, valor por defecto 5) -->
                <div class="input-field col s12 m3">
                    <input type="number" name="stock_minimo" id="producto-stock-minimo" min="0" value="5">
                    <label for="producto-stock-minimo">Stock Mínimo</label>
                </div>
            </div>

            <div class="row">
                <!-- Campo: Costo de compra (numero decimal, paso 0.01, por defecto 0) -->
                <div class="input-field col s12 m6">
                    <input type="number" name="costo_compra" id="producto-costo" min="0" step="0.01" value="0">
                    <label for="producto-costo">Costo de Compra ($)</label>
                </div>
                <!-- Campo: Precio de venta (obligatorio, numero decimal) -->
                <div class="input-field col s12 m6">
                    <input type="number" name="precio_venta" id="producto-precio" min="0" step="0.01" value="0" required>
                    <label for="producto-precio">Precio de Venta ($)</label>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer del modal con botones Cancelar y Guardar -->
    <div class="modal-footer">
        <!-- Boton para cerrar el modal sin guardar (modal-close de Materialize) -->
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
        <!-- Boton de tipo submit asociado al formulario via atributo form="form-producto" -->
        <button type="submit" form="form-producto" class="waves-effect waves-green btn indigo" id="btn-guardar-producto">Guardar</button>
    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL: GESTIONAR CATEGORIAS -->
<!-- Ventana emergente para crear, editar y eliminar categorias        -->
<!-- ================================================================ -->
<div id="modal-categorias" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons" style="vertical-align:middle;margin-right:0.25rem;">category</i> Gestionar Categorías</h4>

        <div class="card-panel grey lighten-4" style="padding:1rem;">
            <form id="form-categoria" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:flex-end;">
                <input type="hidden" name="id" id="categoria-id" value="">
                <div class="input-field" style="flex:2;min-width:150px;margin:0;">
                    <input type="text" name="nombre" id="categoria-nombre" required>
                    <label for="categoria-nombre">Nombre de la categoría</label>
                </div>
                <button type="submit" class="btn waves-effect waves-light green" style="height:44px;border-radius:24px;padding:0 1rem;">
                    <i class="material-icons">save</i>
                </button>
                <button type="button" class="btn waves-effect waves-light grey btn-cancelar-cat" style="height:44px;border-radius:24px;padding:0 1rem;">
                    <i class="material-icons">cancel</i>
                </button>
            </form>
        </div>

        <table class="striped" id="tabla-categorias" style="margin-top:1rem;">
            <thead>
                <tr style="background:var(--surface-hover);">
                    <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;">Categoría</th>
                    <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;text-align:right;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2" style="text-align:center;padding:2rem;color:var(--text-muted);">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cerrar</a>
    </div>
</div>

<!-- ================================================================ -->
<!-- ESTILOS CSS ADICIONALES -->
<!-- Pequeños ajustes de estilo especificos para la tabla de inventario -->
<!-- ================================================================ -->
<style>
    /* Efecto hover en las filas de la tabla: cambia el fondo al pasar el raton */
    .inv-table tbody tr:hover {
        background: var(--surface-hover);
    }

    /* Alineacion vertical centrada en todas las celdas de la tabla */
    .inv-table td {
        vertical-align: middle;
    }

    /* Contenedor de la barra de progreso de stock (fondo gris claro) */
    .inv-table .stock-bar {
        height: 5px;
        background: var(--border-light);
        border-radius: 4px;
        overflow: hidden;
    }

    /* Relleno de la barra de stock con transicion suave al cambiar de ancho */
    .inv-table .stock-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.4s ease;
    }

    /* Ajustes responsive para pantallas muy pequeñas (max 600px de ancho) */
    @media only screen and (max-width: 600px) {

        /* Reduzco el padding de las celdas en movil */
        .inv-table td,
        .inv-table th {
            padding: 0.55rem 0.5rem !important;
        }

        /* Reduzco el gap de los contenedores flex dentro de las celdas */
        .inv-table td>div[style*="flex"] {
            gap: 0.5rem !important;
        }

        /* Reduzco el tamaño del icono del producto en movil */
        .inv-table td .product-icon {
            width: 32px !important;
            height: 32px !important;
        }

        /* Reduzco el tamaño del icono de Material Icons dentro del icono del producto */
        .inv-table td .product-icon i {
            font-size: 1rem !important;
        }
    }
</style>
