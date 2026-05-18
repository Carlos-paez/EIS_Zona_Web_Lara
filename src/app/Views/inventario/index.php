<!-- ============================================================
     VISTA: GESTIÓN DE INVENTARIO
     ============================================================
     Muestra un buscador/filtro de productos y una tabla con el
     listado de productos del inventario, su precio, stock,
     estado y acciones disponibles.

     Renderizada dentro del layout principal por
     InventarioController::index().

     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- ========== BARRA DE HERRAMIENTAS ========== -->
<!-- Card que contiene búsqueda, filtro y botón de nuevo producto -->
<div class="card">
    <div class="card-content" style="padding:1.25rem 1.5rem;">
        <div class="row" style="margin-bottom:0;">

            <!-- Campo de búsqueda por nombre o código de producto -->
            <!-- s12 m6 l5: 12 cols en móvil, 6 en tablet, 5 en desktop -->
            <div class="col s12 m6 l5">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <!-- Icono de búsqueda dentro del campo -->
                    <i class="material-icons prefix">search</i>
                    <!-- Input de texto con id para el JS de búsqueda -->
                    <!-- placeholder: texto de ayuda dentro del campo -->
                    <input type="text" id="searchProducto" placeholder="Buscar producto por nombre o código...">
                    <label for="searchProducto">Buscar producto</label>
                </div>
            </div>

            <!-- Filtro desplegable por estado de stock -->
            <div class="col s6 m3 l3">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <!-- Select que Materialize convierte en menú desplegable -->
                    <select id="filterEstado">
                        <!-- value="" seleccionado por defecto (todos los estados) -->
                        <option value="" selected>Todos los estados</option>
                        <option value="ok">Stock OK</option>       <!-- Stock suficiente -->
                        <option value="critico">Crítico</option>    <!-- Stock por debajo del mínimo -->
                        <option value="sin stock">Sin stock</option> <!-- Stock en cero -->
                    </select>
                    <label>Filtrar por estado</label>
                </div>
            </div>

            <!-- Botón para agregar nuevo producto -->
            <div class="col s6 m3 l4 right-align" style="padding-top:0.75rem;">
                <!-- data-tipo="producto" usado por JS para identificar el tipo de recurso -->
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="producto">
                    <i class="material-icons left">add</i>Nuevo Producto
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========== TABLA DE LISTADO DE PRODUCTOS ========== -->
<div class="card">
    <div class="card-content">

        <!-- Título y contador de resultados -->
        <span class="card-title">
            <i class="material-icons left">inventory_2</i>Lista de Productos
            <!-- Indicador de cuántos resultados se muestran -->
            <span class="result-count" style="font-size:0.85rem;color:var(--text-muted);float:right;font-weight:400;">Mostrando 3 de 3 resultados</span>
        </span>

        <!-- Tabla responsiva con filas alternadas (striped) -->
        <table class="responsive-table striped">
            <thead>
                <tr>
                    <th>ID</th>                    <!-- Código identificador del producto -->
                    <th>Producto</th>               <!-- Nombre y categoría -->
                    <th>Precio</th>                 <!-- Precio unitario -->
                    <th class="right-align">Stock</th>  <!-- Cantidad en inventario -->
                    <th class="right-align">Mínimo</th> <!-- Stock mínimo permitido -->
                    <th>Estado</th>                 <!-- Badge de estado (OK, Crítico, etc.) -->
                    <th class="right-align">Acciones</th> <!-- Botones de acción -->
                </tr>
            </thead>
            <tbody>

                <!-- Producto 1: Mouse Inalámbrico (stock crítico) -->
                <tr>
                    <td><strong>#1042</strong></td>
                    <td>
                        <div style="font-weight:600;">Mouse Inalámbrico</div>
                        <small style="color:var(--text-muted);">Periféricos</small> <!-- Categoría del producto -->
                    </td>
                    <td><strong>$12.50</strong></td>
                    <!-- Stock en rojo (5) porque está por debajo del mínimo (10) -->
                    <td class="right-align"><span style="color:var(--danger);font-weight:700;">5</span></td>
                    <td class="right-align" style="color:var(--text-muted);">10</td> <!-- Stock mínimo -->
                    <!-- Badge rojo "Crítico" -->
                    <td><span class="new badge red" data-badge-caption="">Crítico</span></td>
                    <td class="right-align">
                        <!-- Botón para ver movimientos del producto -->
                        <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="top" data-tooltip="Ver movimientos" style="width:32px;height:32px;margin-right:4px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">inventory</i></button>
                        <!-- Botón para editar el producto -->
                        <button class="btn-floating waves-effect waves-light indigo tooltipped" data-position="top" data-tooltip="Editar" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">edit</i></button>
                    </td>
                </tr>

                <!-- Producto 2: Monitor 24" IPS (stock OK) -->
                <tr>
                    <td><strong>#1043</strong></td>
                    <td>
                        <div style="font-weight:600;">Monitor 24" IPS</div>
                        <small style="color:var(--text-muted);">Pantallas</small>
                    </td>
                    <td><strong>$189.00</strong></td>
                    <!-- Stock en verde (24) porque está sobre el mínimo (5) -->
                    <td class="right-align"><span style="color:var(--success);font-weight:700;">24</span></td>
                    <td class="right-align" style="color:var(--text-muted);">5</td>
                    <td><span class="new badge green" data-badge-caption="">OK</span></td>
                    <td class="right-align">
                        <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="top" data-tooltip="Ver movimientos" style="width:32px;height:32px;margin-right:4px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">inventory</i></button>
                        <button class="btn-floating waves-effect waves-light indigo tooltipped" data-position="top" data-tooltip="Editar" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">edit</i></button>
                    </td>
                </tr>

                <!-- Producto 3: Teclado Mecánico RGB (stock bajo) -->
                <tr>
                    <td><strong>#1044</strong></td>
                    <td>
                        <div style="font-weight:600;">Teclado Mecánico RGB</div>
                        <small style="color:var(--text-muted);">Periféricos</small>
                    </td>
                    <td><strong>$45.00</strong></td>
                    <!-- Stock en naranja (8) porque está cerca del mínimo (10) -->
                    <td class="right-align"><span style="color:var(--warning);font-weight:700;">8</span></td>
                    <td class="right-align" style="color:var(--text-muted);">10</td>
                    <td><span class="new badge orange" data-badge-caption="">Bajo</span></td>
                    <td class="right-align">
                        <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="top" data-tooltip="Ver movimientos" style="width:32px;height:32px;margin-right:4px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">inventory</i></button>
                        <button class="btn-floating waves-effect waves-light indigo tooltipped" data-position="top" data-tooltip="Editar" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">edit</i></button>
                    </td>
                </tr>

            </tbody>
        </table>

        <!-- ========== PAGINACIÓN ========== -->
        <div class="row" style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-light);margin-bottom:0;">
            <div class="col s12 m6" style="padding-top:0.5rem;">
                <!-- Texto informativo de resultados -->
                <span class="result-count" style="color:var(--text-muted);font-size:0.9rem;">Mostrando 3 de 3 resultados</span>
            </div>
            <div class="col s12 m6 right-align">
                <!-- Paginación de Materialize -->
                <ul class="pagination" style="margin:0;">
                    <li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li> <!-- Anterior (deshabilitado = primera página) -->
                    <li class="active indigo"><a href="#!">1</a></li> <!-- Página actual (resaltada) -->
                    <li class="waves-effect"><a href="#!">2</a></li>
                    <li class="waves-effect"><a href="#!">3</a></li>
                    <li class="waves-effect"><a href="#!"><i class="material-icons">chevron_right</i></a></li> <!-- Siguiente -->
                </ul>
            </div>
        </div>

    </div>
</div>
