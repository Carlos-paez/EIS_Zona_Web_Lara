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
//   4. Modales para: crear/editar producto, ver movimientos de stock,
//      y registrar entrada/salida de stock
//
// Los datos se cargan desde PHP en el servidor y el JavaScript
// (app.inventario.js) se encarga de las operaciones dinamicas.
// =====================================================================

use App\Models\inventario;

$inventarioModel = new inventario();

$productos      = $inventarioModel->obtenerProductos();
$totalP         = $inventarioModel->totalProductos();
$critico        = $inventarioModel->stockCritico();
$bajo           = $inventarioModel->stockBajo();
$valor          = $inventarioModel->valorTotalInventario();
$totalProductos = count($productos);
$categorias     = $inventarioModel->obtenerCategorias();
?>

<!-- ================================================================ -->
<!-- TARJETAS DE INDICADORES (KPIs) -->
<!-- Muestran 4 metricas importantes del inventario en tarjetas -->
<!-- ================================================================ -->
<div class="row" style="margin-bottom:1.25rem;">

    <!-- Tarjeta: Total Productos -->
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">inventory_2</i></div>
            <div class="metric-label">Total Productos</div>
            <!-- Muestra el numero total de productos, se actualiza via JS con id="kpi-total" -->
            <div class="metric-value" id="kpi-total"><?php echo $totalP; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">En inventario</div>
        </div>
    </div>

    <!-- Tarjeta: Stock Critico (roja) -->
    <div class="col s12 m6 l3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
            <!-- Muestra el numero en color rojo para alertar -->
            <div class="metric-value" style="color:var(--danger);" id="kpi-critico"><?php echo $critico; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Requiere atención</div>
        </div>
    </div>

    <!-- Tarjeta: Stock Bajo (amarilla) -->
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">inventory</i></div>
            <div class="metric-label">Stock Bajo</div>
            <div class="metric-value" style="color:var(--warning);" id="kpi-bajo"><?php echo $bajo; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Por debajo del mínimo</div>
        </div>
    </div>

    <!-- Tarjeta: Valor Total del Inventario (azul) -->
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">payments</i></div>
            <div class="metric-label">Valor Total</div>
            <!-- Muestra el valor con formato de moneda (2 decimales) -->
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
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">

            <!-- Campo de busqueda por texto -->
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <!-- Cuando el usuario escribe, el JS filtra la tabla -->
                    <input type="text" id="searchProducto" placeholder="Buscar por nombre o código...">
                    <label for="searchProducto">Buscar producto</label>
                </div>
            </div>

            <!-- Selector para filtrar por estado (OK, Critico, Sin stock) -->
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterEstado">
                        <option value="" selected>Todo</option>
                        <option value="ok">Stock OK</option>
                        <option value="crítico">Crítico</option>
                        <option value="sin stock">Sin stock</option>
                    </select>
                    <label>Estado</label>
                </div>
            </div>

            <!-- Boton para abrir modal de nuevo producto -->
            <div class="col s6 m4 l4 right-align" style="padding:0.5rem 0 0;">
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="producto" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <!-- Texto cambia segun el tamaño de pantalla -->
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

        <!-- Encabezado de la tabla -->
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">inventory_2</i> Lista de Productos
            </span>
            <!-- Contador de productos que se actualiza con JS -->
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalProductos; ?> productos</span>
        </div>

        <!-- Contenedor con scroll horizontal para pantallas pequeñas -->
        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped inv-table" id="tabla-productos" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:580px;">
                <!-- Encabezados de columna -->
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Producto</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">ID</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Precio</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Stock</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Estado</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Si no hay productos, muestro un mensaje -->
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">
                                <i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">inventory_2</i>
                                No hay productos registrados
                            </td>
                        </tr>
                        <!-- Si hay productos, los recorro con un foreach -->
                    <?php else: ?>
                        <?php foreach ($productos as $p):
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
                                $badgeClass = 'background:#fce4ec;color:#c62828;'; // Fondo rojo claro, texto rojo oscuro
                                $icon = 'block';       // Icono de "bloqueado/prohibido"
                                $barClass = 'var(--danger)'; // Color rojo para la barra
                                $iconBg = '#fce4ec';
                                $iconColor = 'var(--danger)';
                                $stockColor = 'var(--danger)';
                                $barWidth = 0; // Barra vacia

                                // CASO 2: Stock entre 1 y el minimo -> "Critico" (rojo)
                            } elseif ($p['stock'] <= $p['stock_minimo']) {
                                $estado = 'Crítico';
                                $badgeClass = 'background:#fce4ec;color:#c62828;';
                                $icon = 'warning';     // Icono de advertencia
                                $barClass = 'var(--danger)';
                                $iconBg = '#fce4ec';
                                $iconColor = 'var(--danger)';
                                $stockColor = 'var(--danger)';
                                // La barra muestra el porcentaje respecto al minimo
                                $barWidth = $p['stock_minimo'] > 0 ? max(5, ($p['stock'] / $p['stock_minimo']) * 100) : 50;

                                // CASO 3: Stock mayor al minimo -> "OK" (verde)
                            } else {
                                $estado = 'OK';
                                $badgeClass = 'background:#e8f5e9;color:#2e7d32;'; // Fondo verde claro, texto verde oscuro
                                $icon = 'check_circle';  // Icono de verificado
                                $barClass = 'var(--success)';
                                $iconBg = '#e8f5e9';
                                $iconColor = 'var(--success)';
                                $stockColor = 'var(--success)';
                                // La barra se llena hasta 50% como maximo (para no saturar)
                                $barWidth = $p['stock_minimo'] > 0 ? min(100, ($p['stock'] / $p['stock_minimo']) * 50) : 100;
                            }
                        ?>
                            <!-- Fila del producto con atributos data-* para el JavaScript -->
                            <!-- data-id: ID del producto, data-nombre: nombre para mostrar -->
                            <tr data-id="<?php echo $p['id']; ?>" data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>" style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">

                                <!-- Columna 1: Nombre del producto y categoria -->
                                <td style="padding:0.85rem 1rem;">
                                    <div style="display:flex;align-items:center;gap:0.75rem;">
                                        <!-- Icono circular con el color segun estado -->
                                        <div style="width:38px;height:38px;border-radius:8px;background:<?php echo $iconBg; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="material-icons" style="color:<?php echo $iconColor; ?>;font-size:1.2rem;"><?php echo $icon; ?></i>
                                        </div>
                                        <div>
                                            <!-- Nombre del producto escapado para evitar XSS -->
                                            <div style="font-weight:600;color:var(--text);font-size:0.9rem;"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                            <!-- Categoria del producto (o "Sin categoria" si no tiene) -->
                                            <span style="font-size:0.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($p['categoria'] ?? 'Sin categoría'); ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna 2: Codigo del producto -->
                                <td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#<?php echo htmlspecialchars($p['codigo']); ?></td>

                                <!-- Columna 3: Precio de venta formateado -->
                                <td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$<?php echo number_format($p['precio_venta'], 2); ?></td>

                                <!-- Columna 4: Stock con barra de progreso -->
                                <td style="padding:0.85rem 1rem;">
                                    <div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;">
                                        <!-- Numero de stock y minimo -->
                                        <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                                            <span style="font-weight:700;color:<?php echo $stockColor; ?>;"><?php echo $p['stock']; ?></span>
                                            <span style="color:var(--text-muted);font-size:0.7rem;">mín: <?php echo $p['stock_minimo']; ?></span>
                                        </div>
                                        <!-- Barra de progreso visual del stock -->
                                        <div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                                            <div style="width:<?php echo $barWidth; ?>%;height:100%;background:<?php echo $barClass; ?>;border-radius:4px;"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna 5: Badge con el estado (Sin stock / Critico / OK) -->
                                <td style="padding:0.85rem 1rem;">
                                    <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;<?php echo $badgeClass; ?>">
                                        <i class="material-icons" style="font-size:0.85rem;"><?php echo $icon; ?></i> <?php echo $estado; ?>
                                    </span>
                                </td>

                                <!-- Columna 6: Botones de accion -->
                                <td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">
                                    <!-- Boton: Ver movimientos de stock (gris) -->
                                    <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped btn-movimientos" data-id="<?php echo $p['id']; ?>" data-position="left" data-tooltip="Ver movimientos" style="margin-right:4px;"><i class="material-icons">inventory</i></button>
                                    <!-- Boton: Editar producto (indigo) -->
                                    <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar" data-id="<?php echo $p['id']; ?>" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>
                                    <!-- Boton: Entrada de stock (verde) -->
                                    <button class="btn-floating waves-effect waves-light green tooltipped btn-entrada" data-id="<?php echo $p['id']; ?>" data-position="left" data-tooltip="Entrada" style="margin-right:4px;"><i class="material-icons">add_shopping_cart</i></button>
                                    <!-- Boton: Salida de stock (naranja) -->
                                    <button class="btn-floating waves-effect waves-light orange tooltipped btn-salida" data-id="<?php echo $p['id']; ?>" data-position="left" data-tooltip="Salida" style="margin-right:4px;"><i class="material-icons">remove_shopping_cart</i></button>
                                    <!-- Boton: Eliminar producto (rojo) -->
                                    <button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar" data-id="<?php echo $p['id']; ?>" data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>" data-position="left" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pie de tabla con contador -->
        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo $totalProductos; ?> productos</span>
        </div>

    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL: Crear / Editar Producto -->
<!-- Se abre cuando el usuario hace clic en "Nuevo Producto" o en el -->
<!-- boton de editar de alguna fila. Cambia el titulo segun la accion. -->
<!-- ================================================================ -->
<div id="modal-producto" class="modal modal-fixed-footer">
    <div class="modal-content">
        <!-- El titulo lo cambia el JavaScript segun sea "Nuevo Producto" o "Editar Producto" -->
        <h4 id="modal-producto-title">Nuevo Producto</h4>

        <!-- Formulario de producto. El action lo maneja el JS via AJAX -->
        <form id="form-producto">
            <!-- Campo oculto con el ID del producto (vacio si es nuevo) -->
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
                <!-- Select: Categoria (obligatorio) -->
                <div class="input-field col s12 m6">
                    <select name="categoria_id" id="producto-categoria" required>
                        <option value="" disabled selected>Seleccione</option>
                        <!-- Recorre las categorias desde PHP -->
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Categoría</label>
                </div>
                <!-- Campo: Stock actual -->
                <div class="input-field col s12 m3">
                    <input type="number" name="stock" id="producto-stock" min="0" value="0">
                    <label for="producto-stock">Stock</label>
                </div>
                <!-- Campo: Stock minimo (por defecto 5) -->
                <div class="input-field col s12 m3">
                    <input type="number" name="stock_minimo" id="producto-stock-minimo" min="0" value="5">
                    <label for="producto-stock-minimo">Stock Mínimo</label>
                </div>
            </div>

            <div class="row">
                <!-- Campo: Costo de compra -->
                <div class="input-field col s12 m6">
                    <input type="number" name="costo_compra" id="producto-costo" min="0" step="0.01" value="0">
                    <label for="producto-costo">Costo de Compra ($)</label>
                </div>
                <!-- Campo: Precio de venta (obligatorio) -->
                <div class="input-field col s12 m6">
                    <input type="number" name="precio_venta" id="producto-precio" min="0" step="0.01" value="0" required>
                    <label for="producto-precio">Precio de Venta ($)</label>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer del modal con botones Cancelar y Guardar -->
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
        <button type="submit" form="form-producto" class="waves-effect waves-green btn indigo" id="btn-guardar-producto">Guardar</button>
    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL: Historial de Movimientos de Stock -->
<!-- Muestra todos los movimientos (entradas y salidas) de un producto -->
<!-- Los datos se cargan via AJAX cuando se abre el modal. -->
<!-- ================================================================ -->
<div id="modal-movimientos" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4 id="modal-movimientos-title">Movimientos de Stock</h4>
        <!-- Nombre del producto (lo pone el JS) -->
        <p id="modal-movimientos-producto" style="color:var(--text-muted);"></p>

        <div style="overflow-x:auto;">
            <table class="striped" id="tabla-movimientos">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Stock Anterior</th>
                        <th>Stock Nuevo</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mientras carga, muestra "Cargando..." -->
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--text-muted);">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL: Registrar Entrada o Salida de Stock -->
<!-- Formulario para ingresar o sacar stock de un producto. -->
<!-- El titulo y el texto del boton cambian segun sea entrada o salida. -->
<!-- ================================================================ -->
<div id="modal-stock" class="modal">
    <div class="modal-content">
        <h4 id="modal-stock-title">Movimiento de Stock</h4>
        <!-- Nombre del producto (lo pone el JS) -->
        <p id="modal-stock-producto" style="color:var(--text-muted);"></p>

        <form id="form-stock">
            <!-- Campo oculto: ID del producto -->
            <input type="hidden" name="producto_id" id="stock-producto-id" value="">
            <!-- Campo oculto: Tipo de movimiento ("entrada" o "salida") -->
            <input type="hidden" name="tipo" id="stock-tipo" value="">

            <div class="row">
                <!-- Campo: Cantidad a mover -->
                <div class="input-field col s12 m6">
                    <input type="number" name="cantidad" id="stock-cantidad" min="1" value="1" required>
                    <label for="stock-cantidad">Cantidad</label>
                </div>
                <!-- Campo: Motivo del movimiento -->
                <div class="input-field col s12 m6">
                    <input type="text" name="motivo" id="stock-motivo" placeholder="Ej: Reposición, Venta, Ajuste">
                    <label for="stock-motivo">Motivo</label>
                </div>
            </div>
        </form>
    </div>

    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
        <button type="submit" form="form-stock" class="waves-effect waves-green btn indigo" id="btn-guardar-stock">Realizar Movimiento</button>
    </div>
</div>

<!-- ================================================================ -->
<!-- ESTILOS CSS ADICIONALES -->
<!-- Pequeños ajustes de estilo para la tabla de inventario -->
<!-- ================================================================ -->
<style>
    /* Efecto hover en las filas de la tabla */
    .inv-table tbody tr:hover {
        background: var(--surface-hover);
    }

    /* Alineacion vertical centrada en las celdas */
    .inv-table td {
        vertical-align: middle;
    }

    /* Contenedor de la barra de stock */
    .inv-table .stock-bar {
        height: 5px;
        background: var(--border-light);
        border-radius: 4px;
        overflow: hidden;
    }

    /* Relleno de la barra de stock (animado) */
    .inv-table .stock-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.4s ease;
    }

    /* Ajustes responsive para pantallas pequeñas */
    @media only screen and (max-width: 600px) {

        .inv-table td,
        .inv-table th {
            padding: 0.55rem 0.5rem !important;
        }

        .inv-table td>div[style*="flex"] {
            gap: 0.5rem !important;
        }

        .inv-table td .product-icon {
            width: 32px !important;
            height: 32px !important;
        }

        .inv-table td .product-icon i {
            font-size: 1rem !important;
        }
    }
</style>