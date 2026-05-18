<!-- ============================================================
     VISTA: GESTIÓN DE ACTIVOS
     ============================================================
     Muestra el inventario de activos fijos (equipos, licencias,
     herramientas) con búsqueda, filtro por categoría, tablas
     por tipo de activo y un resumen con totales.

     Renderizada dentro del layout principal por
     ActivosController::index().

     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- ========== BARRA DE HERRAMIENTAS ========== -->
<div class="card">
    <div class="card-content" style="padding:1.25rem 1.5rem;">
        <div class="row" style="margin-bottom:0;">

            <!-- Buscador de activos por nombre o código -->
            <div class="col s12 m6 l5">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchActivo" placeholder="Buscar activo por nombre o código...">
                    <label for="searchActivo">Buscar activo</label>
                </div>
            </div>

            <!-- Filtro desplegable por categoría de activo -->
            <div class="col s6 m3 l3">
                <div class="input-field" style="margin-top:0;margin-bottom:0;">
                    <select>
                        <option value="" selected>Todos los activos</option>
                        <option value="equipos">Equipos</option>
                        <option value="herramientas">Herramientas</option>
                        <option value="licencias">Licencias</option>
                    </select>
                    <label>Categoría</label>
                </div>
            </div>

            <!-- Botón para agregar nuevo activo -->
            <div class="col s6 m3 l4 right-align" style="padding-top:0.75rem;">
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="activo">
                    <i class="material-icons left">add</i>Nuevo Activo
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========== TABLAS DE ACTIVOS AGRUPADOS POR CATEGORÍA ========== -->
<div class="row">

    <!-- ===== SECCIÓN: EQUIPOS ===== -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">print</i>Equipos (3)
                    <!-- Botón "Ver todos" que redirige (placeholder) -->
                    <a class="btn-floating waves-effect waves-light grey right tooltipped" data-position="top" data-tooltip="Ver todos" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">arrow_forward</i></a>
                </span>
                <table class="striped">
                    <thead>
                        <tr>
                            <th>Equipo</th>      <!-- Nombre y serie del equipo -->
                            <th>Estado</th>      <!-- Badge de estado (Activo, Mantenimiento) -->
                            <th class="right-align">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Equipo 1: Impresora Láser HP (Activa) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Impresora Láser HP</div>
                                <small style="color:var(--text-muted);">Serie: HP-2024-001</small> <!-- Número de serie -->
                            </td>
                            <td><span class="new badge green" data-badge-caption="">Activo</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Editar" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">edit</i></button>
                            </td>
                        </tr>
                        <!-- Equipo 2: Proyector Epson (En Mantenimiento) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Proyector Epson</div>
                                <small style="color:var(--text-muted);">Serie: EPS-2023-045</small>
                            </td>
                            <td><span class="new badge orange" data-badge-caption="">Mantenimiento</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Editar" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">edit</i></button>
                            </td>
                        </tr>
                        <!-- Equipo 3: Router Cisco (Activo) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Router Cisco</div>
                                <small style="color:var(--text-muted);">Serie: CSC-2024-012</small>
                            </td>
                            <td><span class="new badge green" data-badge-caption="">Activo</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Editar" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">edit</i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== SECCIÓN: LICENCIAS ===== -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">vpn_key</i>Licencias (2)
                    <a class="btn-floating waves-effect waves-light grey right tooltipped" data-position="top" data-tooltip="Ver todos" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">arrow_forward</i></a>
                </span>
                <table class="striped">
                    <thead>
                        <tr>
                            <th>Licencia</th>    <!-- Nombre y fecha de expiración -->
                            <th>Estado</th>      <!-- Activa, Vencida, etc. -->
                            <th class="right-align">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Licencia 1: Windows 11 Pro (Vencida) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Windows 11 Pro</div>
                                <small style="color:var(--text-muted);">Expira: 2024-12-31</small> <!-- Fecha de expiración -->
                            </td>
                            <td><span class="new badge red" data-badge-caption="">Vencida</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Renovar" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">refresh</i></button>
                            </td>
                        </tr>
                        <!-- Licencia 2: Office 365 (Activa) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Office 365</div>
                                <small style="color:var(--text-muted);">Expira: 2025-06-15</small>
                            </td>
                            <td><span class="new badge green" data-badge-caption="">Activa</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Ver detalles" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">visibility</i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <!-- ===== SECCIÓN: HERRAMIENTAS ===== -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">build</i>Herramientas (4)
                    <a class="btn-floating waves-effect waves-light grey right tooltipped" data-position="top" data-tooltip="Ver todos" style="width:32px;height:32px;"><i class="material-icons" style="font-size:1.1rem;line-height:32px;">arrow_forward</i></a>
                </span>
                <table class="striped">
                    <thead>
                        <tr>
                            <th>Herramienta</th> <!-- Nombre y descripción -->
                            <th>Estado</th>      <!-- Disponible, En uso, etc. -->
                            <th class="right-align">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Herramienta 1: Kit Destornilladores (Disponible) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Kit Destornilladores</div>
                                <small style="color:var(--text-muted);">Completo</small>
                            </td>
                            <td><span class="new badge green" data-badge-caption="">Disponible</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Ver detalles" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">visibility</i></button>
                            </td>
                        </tr>
                        <!-- Herramienta 2: Multímetro Digital (Disponible) -->
                        <tr>
                            <td>
                                <div style="font-weight:600;">Multímetro Digital</div>
                                <small style="color:var(--text-muted);">Precisión ±0.5%</small>
                            </td>
                            <td><span class="new badge green" data-badge-caption="">Disponible</span></td>
                            <td class="right-align">
                                <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="top" data-tooltip="Ver detalles" style="width:30px;height:30px;"><i class="material-icons" style="font-size:1rem;line-height:30px;">visibility</i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== SECCIÓN: RESUMEN DE ACTIVOS ===== -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">assessment</i>Resumen</span>

                <div style="display:flex;flex-direction:column;gap:0.75rem;">

                    <!-- Total de activos (verde) -->
                    <div class="card-panel green lighten-4" style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;margin:0;border-radius:8px;">
                        <span style="font-weight:600;color:#2e7d32;">Activos Totales</span>
                        <span style="font-weight:800;font-size:1.5rem;color:#2e7d32;">9</span>
                    </div>

                    <!-- En mantenimiento (azul) -->
                    <div class="card-panel blue lighten-4" style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;margin:0;border-radius:8px;">
                        <span style="font-weight:600;color:#1565c0;">En Mantenimiento</span>
                        <span style="font-weight:800;font-size:1.5rem;color:#1565c0;">1</span>
                    </div>

                    <!-- Requieren atención (rojo) -->
                    <div class="card-panel red lighten-4" style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;margin:0;border-radius:8px;">
                        <span style="font-weight:600;color:#c62828;">Requieren Atención</span>
                        <span style="font-weight:800;font-size:1.5rem;color:#c62828;">1</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
