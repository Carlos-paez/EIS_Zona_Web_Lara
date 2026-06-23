// =====================================================================
// ARCHIVO: app.inventario.js
// FUNCIÓN: Maneja toda la interactividad del módulo de inventario
//          del lado del cliente. Se comunica con el controlador PHP
//          mediante peticiones AJAX (GET y POST) y actualiza la página
//          sin recargarla.
//
// Funcionalidades:
//   1. Carga y actualización de KPIs (tarjetas indicadoras)
//   2. Carga y actualización de la tabla de productos
//   3. Filtro de productos por búsqueda de texto y por estado
//   4. CRUD de productos (Crear, Leer, Actualizar, Eliminar)
//   5. Modales con formularios para las operaciones anteriores
// =====================================================================

// Cuando el DOM está listo (la página terminó de cargar), ejecuto todo
// El "$(function () { ... })" es la forma corta de jQuery para
// "$(document).ready(function () { ... })"
$(function () {

    // ================================================================
    // CONFIGURACIÓN INICIAL
    // ================================================================

    // URL base de la API del módulo de inventario
    // "?pagina=inventario&action=" se concatena con cada acción
    // Ejemplo: ?pagina=inventario&action=listar
    var API = '?pagina=inventario&action=';

    // ================================================================
    // FUNCIÓN: refrescarKPI()
    // PROPÓSITO: Actualiza los valores de las 4 tarjetas de indicadores
    //            (KPIs) llamando al servidor vía AJAX. Se usa después
    //            de cada operación que modifica el stock (crear, editar,
    //            eliminar, entrada, salida).
    // ================================================================
    function refrescarKPI() {
        // GET request a ?pagina=inventario&action=kpis
        // El servidor responde con un JSON que contiene total, crítico, bajo y valor
        $.getJSON(API + 'kpis', function (r) {
            if (!r.success) return; // Si la respuesta no es exitosa, no hago nada
            // Actualizo cada tarjeta KPI con los valores recibidos
            $('#kpi-total').text(r.data.total);       // Total de productos
            $('#kpi-critico').text(r.data.critico);   // Productos con stock crítico
            $('#kpi-bajo').text(r.data.bajo);         // Productos con stock bajo
            $('#kpi-valor').text('$' + parseFloat(r.data.valor).toFixed(2)); // Valor total del inventario en $
        }).fail(function () {
            // Si hay un error de conexión, muestro un toast de error
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: refrescarTabla()
    // PROPÓSITO: Vuelve a cargar toda la tabla de productos desde el
    //            servidor. Se llama después de cualquier cambio (crear,
    //            editar, eliminar, entrada, salida) para reflejar los
    //            cambios visualmente.
    // ================================================================
    function refrescarTabla() {
        // GET request a ?pagina=inventario&action=listar
        // El servidor devuelve un arreglo con todos los productos activos
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return; // Si la respuesta no es exitosa, salgo

            // Referencia al <tbody> de la tabla de productos
            var tbody = $('#tabla-productos tbody');
            // Limpio el contenido actual del tbody para regenerarlo
            tbody.empty();

            // Si no hay productos, muestro un mensaje de tabla vacía
            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">inventory_2</i>No hay productos registrados</td></tr>');
                $('.result-count').text('0 productos'); // Actualizo el contador
                return; // Termino porque no hay productos que mostrar
            }

            // Recorro cada producto del arreglo usando jQuery.each
            $.each(r.data, function (i, p) {
                // --- LÓGICA DE ESTADO ---
                // Determino colores, íconos y barra de progreso según el nivel de stock
                var estado, badgeClass, icon, iconBg, iconColor, stockColor, barClass, barWidth;

                // CASO 1: Sin stock (stock <= 0) -> color rojo y sin barra
                if (p.stock <= 0) {
                    estado = 'Sin stock'; badgeClass = 'background:#fce4ec;color:#c62828;';
                    icon = 'block'; iconBg = '#fce4ec'; iconColor = 'var(--danger)';
                    stockColor = 'var(--danger)'; barClass = 'var(--danger)'; barWidth = 0;

                // CASO 2: Stock bajo (entre 1 y el mínimo) -> color rojo/ámbar
                } else if (p.stock <= p.stock_minimo) {
                    estado = 'Crítico'; badgeClass = 'background:#fce4ec;color:#c62828;';
                    icon = 'warning'; iconBg = '#fce4ec'; iconColor = 'var(--danger)';
                    stockColor = 'var(--danger)'; barClass = 'var(--danger)';
                    // Calculo el porcentaje de la barra respecto al mínimo
                    barWidth = p.stock_minimo > 0 ? Math.max(5, (p.stock / p.stock_minimo) * 100) : 50;

                // CASO 3: Stock OK (mayor al mínimo) -> color verde
                } else {
                    estado = 'OK'; badgeClass = 'background:#e8f5e9;color:#2e7d32;';
                    icon = 'check_circle'; iconBg = '#e8f5e9'; iconColor = 'var(--success)';
                    stockColor = 'var(--success)'; barClass = 'var(--success)';
                    // La barra no pasa de 50% para mantener proporción visual
                    barWidth = p.stock_minimo > 0 ? Math.min(100, (p.stock / p.stock_minimo) * 50) : 100;
                }

                // --- CONSTRUCCIÓN DE LA FILA HTML ---
                // Creo cada celda manualmente concatenando strings
                // Uso $('<span>').text(...).html() para escapar caracteres
                // especiales y evitar inyección XSS

                // Columna 1: Ícono + Nombre + Categoría
                var row = '<tr data-id="' + p.id + '" data-nombre="' + $('<span>').text(p.nombre).html() + '">';
                row += '<td style="padding:0.85rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:38px;height:38px;border-radius:8px;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="material-icons" style="color:' + iconColor + ';font-size:1.2rem;">' + icon + '</i></div><div><div style="font-weight:600;color:var(--text);font-size:0.9rem;">' + $('<span>').text(p.nombre).html() + '</div><span style="font-size:0.75rem;color:var(--text-muted);">' + $('<span>').text(p.categoria || 'Sin categoría').html() + '</span></div></div></td>';

                // Columna 2: Código del producto (#codigo)
                row += '<td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#' + $('<span>').text(p.codigo).html() + '</td>';

                // Columna 3: Precio de venta formateado con 2 decimales
                row += '<td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$' + parseFloat(p.precio_venta).toFixed(2) + '</td>';

                // Columna 4: Stock actual + Barra de progreso + Mínimo
                row += '<td style="padding:0.85rem 1rem;"><div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;"><div style="display:flex;justify-content:space-between;font-size:0.85rem;"><span style="font-weight:700;color:' + stockColor + ';">' + p.stock + '</span><span style="color:var(--text-muted);font-size:0.7rem;">mín: ' + p.stock_minimo + '</span></div><div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;"><div style="width:' + barWidth + '%;height:100%;background:' + barClass + ';border-radius:4px;"></div></div></div></td>';

                // Columna 5: Badge de estado (Sin stock / Crítico / OK)
                row += '<td style="padding:0.85rem 1rem;"><span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;' + badgeClass + '"><i class="material-icons" style="font-size:0.85rem;">' + icon + '</i> ' + estado + '</span></td>';

                // Columna 6: Botones de acción (Editar, Eliminar)
                row += '<td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar" data-id="' + p.id + '" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar" data-id="' + p.id + '" data-nombre="' + $('<span>').text(p.nombre).html() + '" data-position="left" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                // Agrego la fila construida al tbody
                tbody.append(row);
            });

            // Actualizo el contador de resultados con la cantidad de productos
            $('.result-count').text(r.data.length + ' productos');

            // Reinicio los tooltips de los botones (Materialize los necesita después de agregar HTML nuevo)
            $('.tooltipped').tooltip();

            // Aplico el filtro activo (si hay búsqueda o filtro de estado) sobre la tabla recién cargada
                aplicarFiltro();
        }).fail(function () {
            // Si hay error de conexión, muestro toast de error
            EIS.toast('Error al cargar la tabla', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: aplicarFiltro()
    // PROPÓSITO: Filtra las filas de la tabla en el lado del cliente
    //            (sin recargar) según el texto de búsqueda y el estado
    //            seleccionado. Se ejecuta cada vez que el usuario
    //            escribe o cambia el filtro.
    // ================================================================
    function aplicarFiltro() {
        // Obtengo el texto de búsqueda en minúsculas para comparación
        var q = $('#searchProducto').val().toLowerCase();
        // Obtengo el filtro de estado seleccionado (vacío = todos)
        var estadoFiltro = $('#filterEstado').val();

        // Recorro cada fila de la tabla de productos
        $('#tabla-productos tbody tr').each(function () {
            var mostrar = true; // Por defecto, la fila se muestra
            var $row = $(this);

            // --- Filtro por texto de búsqueda ---
            // Obtengo el nombre desde data-nombre (escapado) y el código de la columna 1
            var nombre = $row.data('nombre') || '';
            var codigo = $row.find('td').eq(1).text().toLowerCase();
            var texto = nombre.toLowerCase() + ' ' + codigo;
            // Si hay texto de búsqueda y no coincide, oculto la fila
            if (q && texto.indexOf(q) === -1) mostrar = false;

            // --- Filtro por estado ---
            if (estadoFiltro) {
                // El estado está en la columna 5 (índice 4) como texto del badge
                var badge = $row.find('td').eq(4).text().trim().toLowerCase();
                // Si el texto del badge no contiene el filtro seleccionado, oculto
                if (badge.indexOf(estadoFiltro) === -1) mostrar = false;
            }

            // Muestro u oculto la fila según las condiciones evaluadas
            $row.toggle(mostrar);
        });

        // Actualizo el contador de resultados visibles vs totales
        var visibles = $('#tabla-productos tbody tr:visible').length;
        var total = $('#tabla-productos tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    }

    // ================================================================
    // FUNCIÓN: abrirModalProducto(titulo, datos)
    // PROPÓSITO: Abre el modal de creación/edición de producto.
    // PARÁMETROS:
    //   titulo - Título del modal (ej: "Nuevo Producto", "Editar Producto")
    //   datos  - (Opcional) Objeto con datos del producto para editar.
    //            Si es null, el formulario se muestra vacío para crear.
    // ================================================================
    function abrirModalProducto(titulo, datos) {
        // Cambio el título del modal según la acción (crear o editar)
        $('#modal-producto-title').text(titulo);
        // Reseteo el formulario a sus valores iniciales
        $('#form-producto')[0].reset();
        // Limpio el campo oculto del ID (para indicar que es un nuevo producto)
        $('#producto-id').val('');

        // Si me pasaron datos (edición), lleno los campos del formulario
        if (datos) {
            $('#producto-id').val(datos.id);                 // ID único del producto
            $('#producto-codigo').val(datos.codigo);         // Código interno
            $('#producto-nombre').val(datos.nombre);         // Nombre del producto
            $('#producto-descripcion').val(datos.descripcion || ''); // Descripción (opcional)
            $('#producto-categoria').val(datos.categoria_id); // Categoría (FK)
            $('#producto-stock').val(datos.stock);           // Stock actual
            $('#producto-stock-minimo').val(datos.stock_minimo); // Stock mínimo
            $('#producto-costo').val(datos.costo_compra);    // Costo de compra
            $('#producto-precio').val(datos.precio_venta);   // Precio de venta
        }

        // Abro el modal de Materialize usando su API
        $('#modal-producto').modal('open');
        // Actualizo los labels flotantes de Materialize para que no se
        // superpongan con los valores cargados
        M.updateTextFields();
        // Inicializo el select de categoría (Materialize lo requiere)
        $('#producto-categoria').formSelect();
    }

    // ================================================================
    // EVENTOS DE LOS BOTONES DE LA TABLA
    // ================================================================

    // ================================================================
    // EVENTO: Click en botón "Nuevo Producto" (en la barra superior)
    // Abre el modal de producto vacío para crear uno nuevo.
    // ================================================================
    $(document).on('click', '.btn-nuevo', function () {
        abrirModalProducto('Nuevo Producto', null); // null = sin datos = nuevo
    });

    // ================================================================
    // EVENTO: Click en botón "Editar" (en cada fila de la tabla)
    // Primero carga los datos completos del producto vía AJAX,
    // luego abre el modal con los campos pre-cargados.
    // ================================================================
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).data('id'); // Obtengo el ID del producto desde data-id
        // Pido los datos completos del producto al servidor
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (r.success) {
                // Si la carga es exitosa, abro el modal en modo edición
                abrirModalProducto('Editar Producto', r.data);
            } else {
                // Si hay error del servidor, muestro el mensaje de error
                EIS.toast(r.error || 'Error al cargar producto', 'red', 'error');
            }
        }).fail(function () {
            // Si hay error de conexión, muestro toast de error
            EIS.toast('Error de conexión al cargar producto', 'red', 'error');
        });
    });

    // ================================================================
    // EVENTO: Click en botón "Eliminar" (en cada fila de la tabla)
    // Pide confirmación al usuario y luego elimina el producto vía AJAX.
    // ================================================================
    $(document).on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');           // ID del producto a eliminar
        var nombre = $(this).data('nombre');   // Nombre del producto (para confirmación)
        // Confirmación nativa del navegador antes de eliminar
        if (confirm('¿Está seguro de eliminar el producto "' + nombre + '"?')) {
            // POST request para eliminar el producto
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    // Si la eliminación es exitosa, muestro toast y refresco
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarTabla(); // Recargo la tabla
                    refrescarKPI();   // Actualizo los indicadores
                } else {
                    // Si hay error, muestro el mensaje del servidor
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json').fail(function () {
                // Si hay error de conexión, muestro toast de error
                EIS.toast('Error de conexión al eliminar', 'red', 'error');
            });
        }
    });

    // ================================================================
    // EVENTOS DE LOS FORMULARIOS
    // ================================================================

    // ================================================================
    // EVENTO: Submit del formulario de producto (#form-producto)
    // Detecta automáticamente si es creación o edición según si el
    // campo oculto #producto-id tiene un valor.
    // ================================================================
    $('#form-producto').on('submit', function (e) {
        e.preventDefault(); // Evito que el formulario se envíe de forma tradicional

        var id = $('#producto-id').val();       // Si tiene ID, es edición; si no, es nuevo
        var accion = id ? 'actualizar' : 'crear'; // Elijo la acción según corresponda
        var data = $(this).serialize();         // Convierto el formulario a string clave=valor&...

        // POST request para crear o actualizar el producto
        $.post(API + accion, data, function (r) {
            if (r.success) {
                // Si la operación es exitosa, muestro toast, cierro modal y refresco
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-producto').modal('close'); // Cierro el modal
                refrescarTabla(); // Recargo la tabla
                refrescarKPI();   // Actualizo los indicadores
            } else {
                // Si hay error, muestro el mensaje del servidor
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            // Si hay error de conexión, muestro toast de error
            EIS.toast('Error de conexión al guardar', 'red', 'error');
        });
    });

    // ================================================================
    // EVENTOS DE FILTROS Y BÚSQUEDA
    // ================================================================

    // ================================================================
    // EVENTO: Tecla levantada en el campo de búsqueda (#searchProducto)
    // Uso debounce para no ejecutar el filtro en cada tecla, sino
    // esperar 300ms después de que el usuario deje de escribir.
    // ================================================================
    $('#searchProducto').on('keyup', debounce(function () {
        aplicarFiltro();
    }, 300));

    // ================================================================
    // EVENTO: Cambio en el selector de estado (#filterEstado)
    // Cuando el usuario selecciona un estado, se aplica el filtro
    // inmediatamente.
    // ================================================================
    $('#filterEstado').on('change', function () {
        aplicarFiltro();
    });

    // ================================================================
    // INICIALIZACIÓN DE COMPONENTES MATERIALIZE
    // ================================================================

    // Activo los tooltips (globitos de ayuda al pasar el mouse)
    $('.tooltipped').tooltip();
    // Inicializo el select de categorías (transforma <select> nativo en un selector visual)
    $('#producto-categoria').formSelect();
    // Inicializo todos los modales de la página para que Materialize los maneje
    $('.modal').modal();

});
