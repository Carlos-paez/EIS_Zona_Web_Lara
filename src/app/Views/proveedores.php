<!-- ============================================================
     VISTA: SOLICITUDES A PROVEEDORES
     Muestra un listado de solicitudes de compra realizadas a
     proveedores, con filtros por estado, búsqueda y modal de edición.
     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- Tarjetas de resumen -->
<div class="row" style="margin-bottom:1.25rem;">
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">request_quote</i></div>
            <div class="metric-label">Total Solicitudes</div>
            <div class="metric-value">36</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Este mes</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">pending</i></div>
            <div class="metric-label">Pendientes</div>
            <div class="metric-value" style="color:var(--warning);">8</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Por revisar</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Recibidas</div>
            <div class="metric-value" style="color:var(--success);">25</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Completadas</div>
        </div>
    </div>
    <div class="col s12 m6 l3">
        <div class="metric-card" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">people</i></div>
            <div class="metric-label">Proveedores</div>
            <div class="metric-value">12</div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Registrados</div>
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
                    <input type="text" id="searchProveedor" placeholder="Buscar por proveedor o ID...">
                    <label for="searchProveedor">Buscar solicitud</label>
                </div>
            </div>
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <select id="filterEstadoProv">
                        <option value="" selected>Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="recibida">Recibida</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                    <label>Filtrar por estado</label>
                </div>
            </div>
            <div class="col s6 m4 l4 right-align" style="padding:0.5rem 0 0;">
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="solicitud"
                    style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <i class="material-icons left" style="margin:0;">add</i>
                    <span class="hide-on-small-only">Nueva Solicitud</span>
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de solicitudes -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">request_quote</i> Solicitudes de Compra
            </span>
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;">Mostrando 3 de 3 resultados</span>
        </div>

        <div style="overflow-x:auto;margin-top:0.75rem;">
            <table class="striped" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:580px;">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">ID</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Proveedor</th>
                        <th class="hide-on-small-only" style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Rubro</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Fecha</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Estado</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <td style="padding:0.75rem 1rem;"><strong>#SOL-089</strong></td>
                        <td style="padding:0.75rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#42a5f5);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">TS</div>
                                <div>
                                    <div style="font-weight:600;font-size:0.9rem;">TechSupplies S.A.</div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">Electrónica</td>
                        <td style="padding:0.75rem 1rem;font-size:0.85rem;">2024-04-10</td>
                        <td style="padding:0.75rem 1rem;"><span class="new badge orange" data-badge-caption="">Pendiente</span></td>
                        <td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">
                            <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-solicitud" data-index="0" data-position="left" data-tooltip="Editar solicitud"><i class="material-icons">edit</i></button>
                            <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="left" data-tooltip="Ver detalles" style="margin-left:4px;"><i class="material-icons">visibility</i></button>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <td style="padding:0.75rem 1rem;"><strong>#SOL-088</strong></td>
                        <td style="padding:0.75rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2e7d32,#66bb6a);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">GP</div>
                                <div>
                                    <div style="font-weight:600;font-size:0.9rem;">GlobalParts Inc.</div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">Repuestos</td>
                        <td style="padding:0.75rem 1rem;font-size:0.85rem;">2024-04-08</td>
                        <td style="padding:0.75rem 1rem;"><span class="new badge green" data-badge-caption="">Recibida</span></td>
                        <td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">
                            <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-solicitud" data-index="1" data-position="left" data-tooltip="Editar solicitud"><i class="material-icons">edit</i></button>
                            <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="left" data-tooltip="Ver detalles" style="margin-left:4px;"><i class="material-icons">visibility</i></button>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <td style="padding:0.75rem 1rem;"><strong>#SOL-087</strong></td>
                        <td style="padding:0.75rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6a1b9a,#ab47bc);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;">OM</div>
                                <div>
                                    <div style="font-weight:600;font-size:0.9rem;">OfficeMax Corp.</div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">Oficina</td>
                        <td style="padding:0.75rem 1rem;font-size:0.85rem;">2024-04-05</td>
                        <td style="padding:0.75rem 1rem;"><span class="new badge" data-badge-caption="">Cancelada</span></td>
                        <td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">
                            <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-solicitud" data-index="2" data-position="left" data-tooltip="Editar solicitud"><i class="material-icons">edit</i></button>
                            <button class="btn-floating waves-effect waves-light grey tooltipped" data-position="left" data-tooltip="Ver detalles" style="margin-left:4px;"><i class="material-icons">visibility</i></button>
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

<!-- ===== MODAL EDITAR SOLICITUD ===== -->
<div id="modalEditarSolicitud" class="modal" style="max-width:600px;">
    <div class="modal-content" style="padding:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h4 style="font-weight:700;margin:0;font-size:1.3rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">edit</i> Editar Solicitud
            </h4>
            <a href="#!" class="modal-close btn-flat" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="material-icons">close</i></a>
        </div>
        <form id="formEditarSolicitud">
            <div class="row" style="margin-bottom:0;">
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">qr_code</i>
                    <input type="text" id="editIdSolicitud" value="#SOL-089" readonly style="color:var(--text-muted);">
                    <label for="editIdSolicitud" class="active">ID Solicitud</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">store</i>
                    <input type="text" id="editProveedor" value="TechSupplies S.A.">
                    <label for="editProveedor" class="active">Proveedor</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">category</i>
                    <input type="text" id="editRubro" value="Electrónica">
                    <label for="editRubro" class="active">Rubro</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">calendar_today</i>
                    <input type="date" id="editFecha" value="2024-04-10">
                    <label for="editFecha" class="active">Fecha</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">description</i>
                    <input type="text" id="editProducto" value="Lotes de componentes electrónicos">
                    <label for="editProducto" class="active">Producto/Servicio</label>
                </div>
                <div class="col s12 m6 input-field">
                    <i class="material-icons prefix">attach_money</i>
                    <input type="number" id="editMonto" value="12500" min="0" step="0.01">
                    <label for="editMonto" class="active">Monto ($)</label>
                </div>
                <div class="col s12 input-field">
                    <i class="material-icons prefix">flag</i>
                    <select id="editEstadoSolicitud">
                        <option value="pendiente" selected>Pendiente</option>
                        <option value="recibida">Recibida</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                    <label>Estado</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="padding:1rem 2rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--border-light);">
        <button class="btn waves-effect waves-light grey lighten-1 modal-close" style="border-radius:24px;">Cancelar</button>
        <button class="btn waves-effect waves-light indigo" id="btnGuardarSolicitud" style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;"><i class="material-icons left" style="margin:0;">save</i> Guardar Cambios</button>
    </div>
</div>

<style>
#modalEditarSolicitud td, #modalEditarSolicitud th { padding: 0.75rem 1rem; }
@media only screen and (max-width: 600px) {
    #modalEditarSolicitud .modal-content { padding: 1.25rem !important; }
}
</style>

<script>
$(function () {
    $('#searchProveedor').on('input', function () {
        var q = $(this).val().toLowerCase();
        $('table tbody tr').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    });

    $(document).on('click', '.btn-editar-solicitud', function () {
        var instance = M.Modal.getInstance($('#modalEditarSolicitud'));
        if (!instance) { $('#modalEditarSolicitud').modal(); instance = M.Modal.getInstance($('#modalEditarSolicitud')); }
        instance.open();
    });

    $('.btn-nuevo').on('click', function () {
        $('#editIdSolicitud').val('#SOL-NUEVA');
        $('#editProveedor').val(''); $('#editRubro').val(''); $('#editProducto').val(''); $('#editMonto').val('');
        $('#editFecha').val(new Date().toISOString().slice(0,10));
        $('#editEstadoSolicitud').val('pendiente');
        M.updateTextFields();
        $('select').formSelect();
        var instance = M.Modal.getInstance($('#modalEditarSolicitud'));
        if (!instance) { $('#modalEditarSolicitud').modal(); instance = M.Modal.getInstance($('#modalEditarSolicitud')); }
        instance.open();
    });

    $('#btnGuardarSolicitud').on('click', function () {
        EIS.toast('Solicitud guardada correctamente', 'green', 'check_circle');
        $('#modalEditarSolicitud').modal('close');
    });
});
</script>