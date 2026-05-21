<!-- ============================================================
     VISTA: GESTIÓN DE INVENTARIO
     Diseño moderno con tarjetas de resumen, buscador/filtro
     mejorados y tabla con indicadores visuales de stock.
     ============================================================ -->

<!-- Tarjetas de resumen (KPIs) -->
<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">inventory_2</i></div>
            <div class="metric-label">Total Productos</div>
            <div class="metric-value">3</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">En inventario</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card danger" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">warning</i></div>
            <div class="metric-label">Stock Crítico</div>
            <div class="metric-value" style="color:var(--danger);">1</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Requiere atención</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">inventory</i></div>
            <div class="metric-label">Stock Bajo</div>
            <div class="metric-value" style="color:var(--warning);">1</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Por debajo del mínimo</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card info" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">payments</i></div>
            <div class="metric-label">Valor Total</div>
            <div class="metric-value" style="color:var(--info);">$246.50</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">En productos</div>
        </div>
    </div>
</div>

<!-- Barra de herramientas -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="searchProducto" placeholder="Buscar por nombre o código...">
                    <label for="searchProducto">Buscar producto</label>
                </div>
            </div>
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterEstado">
                        <option value="" selected>Todo</option>
                        <option value="ok">Stock OK</option>
                        <option value="critico">Crítico</option>
                        <option value="sin stock">Sin stock</option>
                    </select>
                    <label>Estado</label>
                </div>
            </div>
            <div class="col s6 m4 l4 right-align" style="padding:0.5rem 0 0;">
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="producto" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nuevo Producto</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">inventory_2</i> Lista de Productos
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;">3 productos</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped inv-table" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:580px;">
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
                    <!-- Mouse Inalámbrico (stock crítico) -->
                    <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <td style="padding:0.85rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:38px;height:38px;border-radius:8px;background:#fce4ec;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="material-icons" style="color:var(--danger);font-size:1.2rem;">mouse</i>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text);font-size:0.9rem;">Mouse Inalámbrico</div>
                                    <span style="font-size:0.75rem;color:var(--text-muted);">Periféricos</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#1042</td>
                        <td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$12.50</td>
                        <td style="padding:0.85rem 1rem;">
                            <div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;">
                                <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                                    <span style="font-weight:700;color:var(--danger);">5</span>
                                    <span style="color:var(--text-muted);font-size:0.7rem;">mín: 10</span>
                                </div>
                                <div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                                    <div style="width:25%;height:100%;background:var(--danger);border-radius:4px;"></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;background:#fce4ec;color:#c62828;">
                                <i class="material-icons" style="font-size:0.85rem;">warning</i> Crítico
                            </span>
                        </td>
                        <td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">
                            <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="left" data-tooltip="Ver movimientos" style="margin-right:4px;"><i class="material-icons">inventory</i></button>
                            <button class="btn-floating waves-effect waves-light indigo tooltipped" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>
                        </td>
                    </tr>
                    <!-- Monitor 24" IPS (stock OK) -->
                    <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <td style="padding:0.85rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:38px;height:38px;border-radius:8px;background:#e8f5e9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="material-icons" style="color:var(--success);font-size:1.2rem;">desktop_windows</i>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text);font-size:0.9rem;">Monitor 24" IPS</div>
                                    <span style="font-size:0.75rem;color:var(--text-muted);">Pantallas</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#1043</td>
                        <td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$189.00</td>
                        <td style="padding:0.85rem 1rem;">
                            <div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;">
                                <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                                    <span style="font-weight:700;color:var(--success);">24</span>
                                    <span style="color:var(--text-muted);font-size:0.7rem;">mín: 5</span>
                                </div>
                                <div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                                    <div style="width:100%;height:100%;background:var(--success);border-radius:4px;"></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;background:#e8f5e9;color:#2e7d32;">
                                <i class="material-icons" style="font-size:0.85rem;">check_circle</i> OK
                            </span>
                        </td>
                        <td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">
                            <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="left" data-tooltip="Ver movimientos" style="margin-right:4px;"><i class="material-icons">inventory</i></button>
                            <button class="btn-floating waves-effect waves-light indigo tooltipped" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>
                        </td>
                    </tr>
                    <!-- Teclado Mecánico RGB (stock bajo) -->
                    <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <td style="padding:0.85rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:38px;height:38px;border-radius:8px;background:#fff3e0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="material-icons" style="color:var(--warning);font-size:1.2rem;">keyboard</i>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text);font-size:0.9rem;">Teclado Mecánico RGB</div>
                                    <span style="font-size:0.75rem;color:var(--text-muted);">Periféricos</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#1044</td>
                        <td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$45.00</td>
                        <td style="padding:0.85rem 1rem;">
                            <div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;">
                                <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                                    <span style="font-weight:700;color:var(--warning);">8</span>
                                    <span style="color:var(--text-muted);font-size:0.7rem;">mín: 10</span>
                                </div>
                                <div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                                    <div style="width:40%;height:100%;background:var(--warning);border-radius:4px;"></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;background:#fff3e0;color:#e65100;">
                                <i class="material-icons" style="font-size:0.85rem;">inventory</i> Bajo
                            </span>
                        </td>
                        <td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">
                            <button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped" data-position="left" data-tooltip="Ver movimientos" style="margin-right:4px;"><i class="material-icons">inventory</i></button>
                            <button class="btn-floating waves-effect waves-light indigo tooltipped" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span style="color:var(--text-muted);font-size:0.85rem;">Mostrando 3 de 3 resultados</span>
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

<style>
.inv-table tbody tr:hover { background: var(--surface-hover); }
.inv-table td { vertical-align: middle; }
.inv-table .stock-bar { height:5px; background:var(--border-light); border-radius:4px; overflow:hidden; }
.inv-table .stock-fill { height:100%; border-radius:4px; transition: width 0.4s ease; }
@media only screen and (max-width: 600px) {
    .inv-table td,
    .inv-table th { padding: 0.55rem 0.5rem !important; }
    .inv-table td > div[style*="flex"] { gap: 0.5rem !important; }
    .inv-table td .product-icon { width: 32px !important; height: 32px !important; }
    .inv-table td .product-icon i { font-size: 1rem !important; }
}
</style>
