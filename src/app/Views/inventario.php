<!-- ============================================================
     VISTA: GESTIÓN DE INVENTARIO
     Muestra un buscador/filtro de productos y una tabla con el
     listado de productos del inventario, su precio, stock,
     estado y acciones disponibles.
     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- Barra de herramientas: búsqueda, filtro y botón de nuevo producto -->
<div class="card">
    <div class="card-content" style="padding:1.25rem 1.5rem;">
        <div class="row" style="margin-bottom:0;">
            <!-- Campo de búsqueda por nombre o código -->
            <div class="col s12 m6 l5">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchProducto" placeholder="Buscar producto por nombre o código...">
                    <label for="searchProducto">Buscar producto</label>
                </div>
            </div>
            <!-- Filtro desplegable por estado de stock -->
            <div class="col s6 m3 l3">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <select id="filterEstado">
                        <option value="" selected>Todos los estados</option>
                        <option value="ok">Stock OK</option>
                        <option value="critico">Crítico</option>
                        <option value="sin stock">Sin stock</option>
                    </select>
                    <label>Filtrar por estado</label>
                </div>
            </div>
            <!-- Botón para agregar nuevo producto -->
            <div class="col s6 m3 l4 right-align" style="padding-top:0.75rem;">
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="producto"><i class="material-icons left">add</i>Nuevo Producto</button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de listado de productos -->
<div class="card">
    <div class="card-content">
        <span class="card-title">
            <i class="material-icons left">inventory_2</i>Lista de Productos
            <span class="result-count" style="font-size:0.85rem;color:var(--text-muted);float:right;font-weight:400;">Mostrando 3 de 3 resultados</span>
        </span>
        <table class="responsive-table striped"> <!-- striped: filas alternadas de color -->
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th class="right-align">Stock</th>
                    <th class="right-align">Mínimo</th>
                    <th>Estado</th>
                    <th class="right-align">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Producto 1: Mouse Inalámbrico (stock crítico) -->
                <tr>
                    <td><strong>#1042</strong></td>
                    <td>
                        <div style="font-weight:600;">Mouse Inalámbrico</div>
                        <small style="color:var(--text-muted);">Periféricos</small> <!-- Categoría -->
                    </td>
                    <td><strong>$12.50</strong></td>
                    <td class="right-align"><span style="color:var(--danger);font-weight:700;">5</span></td> <!-- Stock en rojo = crítico -->
                    <td class="right-align" style="color:var(--text-muted);">10</td> <!-- Stock mínimo -->
                    <td><span class="new badge red" data-badge-caption="">Crítico</span></td>
                    <td class="right-align">
                        <!-- Botón ver movimientos -->
                        <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="top" data-tooltip="Ver movimientos" style="width:32px;height:32px;margin-right:4px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">inventory</i></button>
                        <!-- Botón editar -->
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
        <!-- Paginación -->
        <div class="row" style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-light);margin-bottom:0;">
            <div class="col s12 m6" style="padding-top:0.5rem;">
                <span class="result-count" style="color:var(--text-muted);font-size:0.9rem;">Mostrando 3 de 3 resultados</span>
            </div>
            <div class="col s12 m6 right-align">
                <ul class="pagination" style="margin:0;">
                    <li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li> <!-- Página anterior (deshabilitada) -->
                    <li class="active indigo"><a href="#!">1</a></li> <!-- Página actual -->
                    <li class="waves-effect"><a href="#!">2</a></li>
                    <li class="waves-effect"><a href="#!">3</a></li>
                    <li class="waves-effect"><a href="#!"><i class="material-icons">chevron_right</i></a></li> <!-- Página siguiente -->
                </ul>
            </div>
        </div>
    </div>
</div>
