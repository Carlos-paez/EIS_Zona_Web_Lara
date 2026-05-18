<!-- ============================================================
     VISTA: SOLICITUDES A PROVEEDORES
     ============================================================
     Muestra un listado de solicitudes de compra realizadas a
     proveedores, con filtros por estado, búsqueda y botón
     para crear nuevas solicitudes.

     Renderizada dentro del layout principal por
     ProveedoresController::index().

     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- ========== BARRA DE HERRAMIENTAS ========== -->
<div class="card">
    <div class="card-content" style="padding:1.25rem 1.5rem;">
        <div class="row" style="margin-bottom:0;">

            <!-- Campo de búsqueda por proveedor o ID de solicitud -->
            <div class="col s12 m6 l5">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <i class="material-icons prefix">search</i>
                    <!-- id="searchProveedor" usado por JS para filtrar la tabla -->
                    <input type="text" id="searchProveedor" placeholder="Buscar por proveedor o ID...">
                    <label for="searchProveedor">Buscar solicitud</label>
                </div>
            </div>

            <!-- Filtro desplegable por estado de la solicitud -->
            <div class="col s6 m3 l3">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <select id="filterEstadoProv">
                        <option value="" selected>Todos los estados</option>
                        <option value="pendiente">Pendiente</option>  <!-- Solicitud en espera de respuesta -->
                        <option value="recibida">Recibida</option>     <!-- Productos/servicios recibidos -->
                        <option value="cancelada">Cancelada</option>  <!-- Solicitud anulada -->
                    </select>
                    <label>Filtrar por estado</label>
                </div>
            </div>

            <!-- Botón para crear nueva solicitud -->
            <div class="col s6 m3 l4 right-align" style="padding-top:0.75rem;">
                <!-- data-tipo="solicitud" usado por JS genérico btn-nuevo -->
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="solicitud">
                    <i class="material-icons left">add</i>Nueva Solicitud
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========== TABLA DE LISTADO DE SOLICITUDES ========== -->
<div class="card">
    <div class="card-content">

        <span class="card-title">
            <i class="material-icons left">request_quote</i>Lista de Solicitudes
            <span class="result-count" style="font-size:0.85rem;color:var(--text-muted);float:right;font-weight:400;">Mostrando 3 de 3 resultados</span>
        </span>

        <!-- Tabla responsiva con filas alternadas -->
        <table class="responsive-table striped">
            <thead>
                <tr>
                    <th>ID</th>                    <!-- Código único de la solicitud -->
                    <th>Proveedor</th>             <!-- Nombre del proveedor y rubro -->
                    <th>Fecha</th>                 <!-- Fecha de creación de la solicitud -->
                    <th>Estado</th>                <!-- Badge de estado (Pendiente, Recibida, Cancelada) -->
                    <th class="right-align">Acciones</th> <!-- Botones de acción -->
                </tr>
            </thead>
            <tbody>

                <!-- Solicitud 1: Pendiente (naranja) -->
                <tr>
                    <td><strong>#SOL-089</strong></td>
                    <td>
                        <div style="font-weight:600;">TechSupplies S.A.</div>
                        <small style="color:var(--text-muted);">Electrónica</small> <!-- Rubro del proveedor -->
                    </td>
                    <td>2024-04-10</td> <!-- Fecha en formato ISO -->
                    <!-- Badge naranja indicando "Pendiente" -->
                    <td><span class="new badge orange" data-badge-caption="">Pendiente</span></td>
                    <td class="right-align">
                        <!-- Botón para ver detalles de la solicitud -->
                        <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Ver detalles" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">visibility</i></button>
                    </td>
                </tr>

                <!-- Solicitud 2: Recibida (verde) -->
                <tr>
                    <td><strong>#SOL-088</strong></td>
                    <td>
                        <div style="font-weight:600;">GlobalParts Inc.</div>
                        <small style="color:var(--text-muted);">Repuestos</small>
                    </td>
                    <td>2024-04-08</td>
                    <td><span class="new badge green" data-badge-caption="">Recibida</span></td>
                    <td class="right-align">
                        <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Ver detalles" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">visibility</i></button>
                    </td>
                </tr>

                <!-- Solicitud 3: Cancelada (gris) -->
                <tr>
                    <td><strong>#SOL-087</strong></td>
                    <td>
                        <div style="font-weight:600;">OfficeMax Corp.</div>
                        <small style="color:var(--text-muted);">Oficina</small>
                    </td>
                    <td>2024-04-05</td>
                    <!-- Badge gris (sin clase de color) = Cancelada -->
                    <td><span class="new badge" data-badge-caption="">Cancelada</span></td>
                    <td class="right-align">
                        <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Ver detalles" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">visibility</i></button>
                    </td>
                </tr>

            </tbody>
        </table>

        <!-- ========== PAGINACIÓN ========== -->
        <div class="row" style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-light);margin-bottom:0;">
            <div class="col s12 m6" style="padding-top:0.5rem;">
                <span class="result-count" style="color:var(--text-muted);font-size:0.9rem;">Mostrando 3 de 3 resultados</span>
            </div>
            <div class="col s12 m6 right-align">
                <ul class="pagination" style="margin:0;">
                    <li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li>
                    <li class="active indigo"><a href="#!">1</a></li>
                    <li class="waves-effect"><a href="#!">2</a></li>
                    <li class="waves-effect"><a href="#!">3</a></li>
                    <li class="waves-effect"><a href="#!"><i class="material-icons">chevron_right</i></a></li>
                </ul>
            </div>
        </div>

    </div>
</div>
