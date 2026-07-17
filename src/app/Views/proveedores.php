<?php
// =============================================================================
// VISTA: SOLICITUDES DE COMPRA A PROVEEDORES
// =============================================================================
// Muestra tarjetas KPI, tabla de órdenes de compra, búsqueda y filtrado,
// y modales para crear/editar órdenes y agregar líneas de productos.
// =============================================================================

// Importa el modelo Proveedor desde la carpeta App\Models
use App\Models\Proveedor;

// Instancia una nueva conexión al modelo de Proveedor
$provModel = new Proveedor();
// Obtiene todas las órdenes de compra registradas
$ordenes   = $provModel->obtenerOrdenes();
// Obtiene el conteo de órdenes agrupadas por estado
$kpis      = $provModel->contarPorEstado();
// Obtiene el total de solicitudes (todas las órdenes)
$total     = $provModel->totalSolicitudes();
// Obtiene la lista de proveedores registrados
$proveedores = $provModel->obtenerProveedores();
// Obtiene todos los estados/status disponibles
$statuses    = $provModel->obtenerStatuses();
// Obtiene todos los productos registrados
$productos   = $provModel->obtenerProductos();

// Inicializa los contadores de órdenes pendientes y recibidas
$pendientes = 0; $recibidas = 0;
// Recorre el arreglo de KPIs para clasificar cada estado
foreach ($kpis as $row) {
    // Convierte el nombre del estado a minúsculas
    $est = strtolower($row['estado']);
    // Si el estado contiene "pend", suma al contador de pendientes
    if (str_contains($est, 'pend')) $pendientes = (int)$row['total'];
    // Si el estado contiene "recib", suma al contador de recibidas
    elseif (str_contains($est, 'recib')) $recibidas = (int)$row['total'];
}
?>
<!-- ===== TARJETAS KPI (MÉTRICAS CLAVE) ===== -->
<!-- Fila contenedora con margen inferior -->
<div class="row" style="margin-bottom:1.25rem;">
    <!-- Columna: Total de solicitudes -->
    <div class="col s12 m6 l3">
        <!-- Tarjeta de métrica sin margen -->
        <div class="metric-card" style="margin:0;">
            <!-- Icono de la tarjeta (solicitud) -->
            <div class="metric-icon"><i class="material-icons">request_quote</i></div>
            <!-- Etiqueta de la métrica -->
            <div class="metric-label">Total Solicitudes</div>
            <!-- Valor numérico de la métrica, con ID para actualización dinámica -->
            <div class="metric-value" id="kpi-total"><?php echo $total; ?></div>
            <!-- Texto secundario explicativo -->
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Registradas</div>
        </div>
    </div>
    <!-- Columna: Solicitudes pendientes -->
    <div class="col s12 m6 l3">
        <!-- Tarjeta con estilo de advertencia (warning) -->
        <div class="metric-card warning" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">pending</i></div>
            <div class="metric-label">Pendientes</div>
            <!-- Valor numérico en color de advertencia -->
            <div class="metric-value" style="color:var(--warning);" id="kpi-pendientes"><?php echo $pendientes; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Por revisar</div>
        </div>
    </div>
    <!-- Columna: Solicitudes recibidas -->
    <div class="col s12 m6 l3">
        <!-- Tarjeta con estilo de éxito (success) -->
        <div class="metric-card success" style="margin:0;">
            <div class="metric-icon"><i class="material-icons">check_circle</i></div>
            <div class="metric-label">Recibidas</div>
            <!-- Valor numérico en color de éxito -->
            <div class="metric-value" style="color:var(--success);" id="kpi-recibidas"><?php echo $recibidas; ?></div>
            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:0.25rem;">Completadas</div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE HERRAMIENTAS (BÚSQUEDA, FILTRO Y BOTÓN NUEVO) ===== -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-content" style="padding:1rem 1.25rem;">
        <!-- Fila con alineación vertical centrada y wrap -->
        <div class="row valign-wrapper" style="margin-bottom:0;flex-wrap:wrap;">
            <!-- Campo de búsqueda de solicitudes -->
            <div class="col s12 m12 l5" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <!-- Icono de búsqueda como prefijo -->
                    <i class="material-icons prefix">search</i>
                    <!-- Input de texto para buscar por proveedor o ID -->
                    <input type="text" id="searchProveedor" placeholder="Buscar por proveedor o ID...">
                    <!-- Etiqueta flotante del campo -->
                    <label for="searchProveedor">Buscar solicitud</label>
                </div>
            </div>
            <!-- Selector de filtro por estado -->
            <div class="col s6 m4 l3" style="margin-bottom:0;">
                <div class="input-field" style="margin:0;">
                    <!-- Select para elegir un estado específico -->
                    <select id="filterEstadoProv">
                        <!-- Opción por defecto: todos los estados -->
                        <option value="" selected>Todos los estados</option>
                        <!-- Itera sobre los status disponibles y los muestra como opciones -->
                        <?php foreach ($statuses as $s): ?>
                        <!-- Cada opción tiene el valor en minúscula y el texto escapado -->
                        <option value="<?php echo strtolower($s['status']); ?>"><?php echo htmlspecialchars($s['status']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Filtrar por estado</label>
                </div>
            </div>
            <!-- Botón para crear nueva solicitud -->
            <div class="col s6 m4 l4 right-align" style="padding:0.5rem 0 0;display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                <!-- Botón con estilo indigo, bordes redondeados, data-tipo para JS -->
                <button class="btn waves-effect waves-light indigo btn-nuevo" data-tipo="solicitud"
                    style="border-radius:24px;display:inline-flex;align-items:center;gap:0.35rem;padding:0 1.25rem;">
                    <!-- Icono de añadir -->
                    <i class="material-icons left" style="margin:0;">add</i>
                    <!-- Texto visible en pantallas grandes -->
                    <span class="hide-on-small-only">Nueva Solicitud</span>
                    <!-- Texto corto para pantallas pequeñas -->
                    <span class="hide-on-med-and-up">Nuevo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA DE SOLICITUDES DE COMPRA ===== -->
<div class="card">
    <div class="card-content" style="padding:0;">
        <!-- Encabezado de la tabla con título y contador de resultados -->
        <div style="padding:1.25rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
            <!-- Título de la sección -->
            <span style="font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                <i class="material-icons" style="color:var(--primary);">request_quote</i> Solicitudes de Compra
            </span>
            <!-- Cantidad de resultados encontrados -->
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo count($ordenes); ?> resultados</span>
        </div>

        <!-- Contenedor con scroll horizontal para la tabla -->
        <div style="overflow-x:auto;margin-top:0.75rem;">
            <!-- Tabla de órdenes con clase striped de Materialize -->
            <table class="striped" id="tabla-ordenes" style="margin-bottom:0;border-collapse:collapse;width:100%;min-width:580px;">
                <!-- Cabecera de la tabla -->
                <thead>
                    <!-- Fila de cabecera con fondo de hover -->
                    <tr style="background:var(--surface-hover);">
                        <!-- Cada columna con padding, mayúsculas, tracking y color muted -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Solicitud</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Proveedor</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">RIF</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Fecha</th>
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);">Estado</th>
                        <!-- Columna de acciones alineada a la derecha -->
                        <th style="padding:0.75rem 1rem;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:700;border-bottom:2px solid var(--border);text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Si no hay órdenes, muestra un mensaje de tabla vacía -->
                    <?php if (empty($ordenes)): ?>
                    <tr>
                        <!-- Celda única que ocupa las 6 columnas -->
                        <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">
                            <!-- Icono grande indicando que no hay datos -->
                            <i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">request_quote</i>
                            No hay solicitudes registradas
                        </td>
                    </tr>
                    <?php else: ?>
                    <!-- Itera sobre cada orden de compra -->
                    <?php foreach ($ordenes as $o):
                        // Color por defecto del badge de estado
                        $color = 'grey';
                        // Toma el estado o asigna "Sin estado" si está vacío
                        $estado = $o['estado'] ?? 'Sin estado';
                        // Convierte el estado a minúsculas para comparar
                        $e = strtolower($estado);
                        // Asigna color naranja si contiene "pend" (pendiente)
                        if (str_contains($e, 'pend')) $color = 'orange';
                        // Asigna color verde si contiene "recib" (recibida)
                        elseif (str_contains($e, 'recib')) $color = 'green';
                        // Asigna color rojo si contiene "cancel" (cancelada)
                        elseif (str_contains($e, 'cancel')) $color = 'red';
                    ?>
                    <!-- Fila con data-atributos para identificación, borde inferior sutil -->
                    <tr data-id="<?php echo $o['id']; ?>" data-numero="<?php echo htmlspecialchars($o['numero_de_orden']); ?>" style="border-bottom:1px solid var(--border-light);transition:background 0.15s;">
                        <!-- Número de orden en negrita -->
                        <td style="padding:0.75rem 1rem;"><strong>#<?php echo htmlspecialchars($o['numero_de_orden']); ?></strong></td>
                        <!-- Columna del proveedor con avatar circular -->
                        <td style="padding:0.75rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <!-- Avatar con las iniciales del proveedor (gradiente azul) -->
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#42a5f5);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.8rem;"><?php echo strtoupper(substr($o['proveedor_nombre'] ?? '?', 0, 2)); ?></div>
                                <div>
                                    <!-- Nombre del proveedor -->
                                    <div style="font-weight:600;font-size:0.9rem;"><?php echo htmlspecialchars($o['proveedor_nombre'] ?? 'Sin proveedor'); ?></div>
                                </div>
                            </div>
                        </td>
                        <!-- RIF del proveedor -->
                        <td style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;"><?php echo htmlspecialchars($o['rif'] ?? '-'); ?></td>
                        <!-- Fecha de la orden -->
                        <td style="padding:0.75rem 1rem;font-size:0.85rem;"><?php echo htmlspecialchars($o['fecha']); ?></td>
                        <!-- Badge de estado con color dinámico -->
                        <td style="padding:0.75rem 1rem;"><span class="new badge <?php echo $color; ?>" data-badge-caption=""><?php echo htmlspecialchars($estado); ?></span></td>
                        <!-- Botones de acción: editar, ver detalle, eliminar -->
                        <td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">
                            <!-- Botón flotante para editar la orden -->
                            <button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-orden" data-id="<?php echo $o['id']; ?>" data-position="left" data-tooltip="Editar solicitud"><i class="material-icons">edit</i></button>
                            <!-- Botón flotante para ver detalles de la orden -->
                            <button class="btn-floating waves-effect waves-light grey tooltipped btn-detalle-orden" data-id="<?php echo $o['id']; ?>" data-position="left" data-tooltip="Ver detalles" style="margin-left:4px;"><i class="material-icons">visibility</i></button>
                            <!-- Botón flotante para eliminar la orden -->
                            <button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-orden" data-id="<?php echo $o['id']; ?>" data-numero="<?php echo htmlspecialchars($o['numero_de_orden']); ?>" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pie de la tabla con contador de resultados -->
        <div style="padding:0.85rem 1.25rem;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <span class="result-count" style="color:var(--text-muted);font-size:0.85rem;"><?php echo count($ordenes); ?> resultados</span>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVA/EDITAR SOLICITUD DE COMPRA ===== -->
<!-- Modal de Materialize con fixed footer -->
<div id="modal-orden" class="modal modal-fixed-footer">
    <div class="modal-content">
        <!-- Título dinámico del modal (lo cambia JS entre Nuevo/Editar) -->
        <h4 id="modal-orden-title">Nueva Solicitud</h4>
        <!-- Formulario de la orden -->
        <form id="form-orden">
            <!-- Campo oculto para almacenar el ID de la orden (vacío en creación) -->
            <input type="hidden" name="id" id="orden-id" value="">
            <!-- Primera fila: número de orden y fecha -->
            <div class="row">
                <div class="input-field col s12 m6">
                    <!-- Número de orden (requerido) -->
                    <input type="text" name="numero" id="orden-numero" required>
                    <label for="orden-numero">Número de Orden</label>
                </div>
                <div class="input-field col s12 m6">
                    <!-- Fecha de la orden (requerido, tipo date) -->
                    <input type="date" name="fecha" id="orden-fecha" required>
                    <!-- Etiqueta activa por defecto porque el campo type=date tiene valor -->
                    <label for="orden-fecha" class="active">Fecha</label>
                </div>
            </div>
            <!-- Segunda fila: proveedor y estado -->
            <div class="row">
                <div class="input-field col s12 m6">
                    <!-- Select de proveedores (requerido) -->
                    <select name="fk_proveedor" id="orden-proveedor" required>
                        <option value="" disabled selected>Seleccione</option>
                        <!-- Itera sobre la lista de proveedores -->
                        <?php foreach ($proveedores as $p): ?>
                        <!-- Cada opción muestra nombre y RIF del proveedor -->
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre'] . ' (' . $p['rif'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Proveedor</label>
                </div>
                <div class="input-field col s12 m6">
                    <!-- Select de estados (requerido) -->
                    <select name="fk_status" id="orden-status" required>
                        <option value="" disabled selected>Seleccione</option>
                        <!-- Itera sobre los status disponibles -->
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['status']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Estado</label>
                </div>
            </div>
        </form>
    </div>
    <!-- Footer del modal con botones de acción -->
    <div class="modal-footer">
        <!-- Botón para cancelar y cerrar el modal -->
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
        <!-- Botón de submit asociado al formulario vía atributo form -->
        <button type="submit" form="form-orden" class="waves-effect waves-green btn indigo" id="btn-guardar-orden">Guardar</button>
    </div>
</div>

<!-- ===== MODAL: DETALLE DE SOLICITUD CON LÍNEAS DE PRODUCTOS ===== -->
<div id="modal-detalle" class="modal modal-fixed-footer">
    <div class="modal-content">
        <!-- Título dinámico del modal -->
        <h4 id="modal-detalle-title">Detalle de Solicitud</h4>
        <!-- Párrafo para información adicional de la orden -->
        <p id="modal-detalle-info" style="color:var(--text-muted);"></p>

        <!-- Tabla de líneas (productos) de la orden -->
        <div style="overflow-x:auto;">
            <table class="striped" id="tabla-lineas">
                <thead>
                    <tr style="background:var(--surface-hover);">
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                        <th class="right-align">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mensaje de carga mientras se obtienen los datos vía AJAX -->
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Cargando...</td></tr>
                </tbody>
                <tfoot>
                    <!-- Fila de total general -->
                    <tr style="font-weight:700;">
                        <td colspan="3" style="text-align:right;padding:0.75rem 1rem;">Total:</td>
                        <!-- Celda donde se muestra el total calculado -->
                        <td id="detalle-total" style="padding:0.75rem 1rem;">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Formulario para agregar un producto a la orden -->
        <div class="card-panel grey lighten-4" style="margin-top:1rem;padding:1rem;">
            <span style="font-weight:600;font-size:0.9rem;"><i class="material-icons left" style="font-size:1.1rem;">add_circle</i> Agregar Producto</span>
            <form id="form-linea" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.5rem;">
                <!-- ID de la orden asociada a esta línea -->
                <input type="hidden" name="orden_id" id="linea-orden-id" value="">
                <!-- Select de productos -->
                <div class="input-field" style="flex:2;min-width:120px;margin:0;">
                    <select name="producto_id" id="linea-producto" required>
                        <option value="" disabled selected>Producto</option>
                        <!-- Itera sobre productos, incluye data-precio para auto-completar -->
                        <?php foreach ($productos as $pr): ?>
                        <option value="<?php echo $pr['id']; ?>" data-precio="<?php echo $pr['precio_compra']; ?>"><?php echo htmlspecialchars($pr['nombre'] . ' (' . $pr['codigo'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Input de cantidad -->
                <div class="input-field" style="flex:1;min-width:60px;margin:0;">
                    <input type="number" name="cantidad" id="linea-cantidad" min="1" value="1" required>
                </div>
                <!-- Input de precio unitario -->
                <div class="input-field" style="flex:1;min-width:80px;margin:0;">
                    <input type="number" name="precio" id="linea-precio" min="0" step="0.01" required>
                </div>
                <!-- Botón para agregar la línea -->
                <button type="submit" class="btn waves-effect waves-light green" style="height:44px;line-height:44px;padding:0 1rem;border-radius:24px;"><i class="material-icons">add</i></button>
            </form>
        </div>
    </div>
    <!-- Footer del modal de detalle -->
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>

<!-- ===== ESTILOS CSS EMBEBIDOS ===== -->
<style>
/* Ajuste de padding para tablas dentro del modal de detalle */
#modal-detalle td, #modal-detalle th { padding: 0.5rem 0.75rem; }
/* Altura fija para los inputs del formulario de líneas */
#form-linea .input-field input { margin: 0; height: 2.5rem; }
/* Elimina el margen superior de los contenedores de inputs dentro del formulario de líneas */
#form-linea .input-field { margin-top: 0; }
/* Media query para pantallas pequeñas (máx. 600px): reduce el padding de los modales */
@media only screen and (max-width: 600px) {
    #modal-orden .modal-content, #modal-detalle .modal-content { padding: 1.25rem !important; }
}
</style>
