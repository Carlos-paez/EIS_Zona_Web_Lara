// =====================================================================
// JAVASCRIPT DEL MODULO DE INVENTARIO (app.inventario.js)
// =====================================================================
// Este archivo maneja toda la interactividad del modulo de inventario
// del lado del cliente. Se comunica con el controlador PHP mediante
// peticiones AJAX (GET y POST) y actualiza la pagina sin recargarla.
//
// Funcionalidades:
//   1. Carga y actualizacion de KPIs (tarjetas indicadoras)
//   2. Carga y actualizacion de la tabla de productos
//   3. Filtro de productos por busqueda de texto y por estado
//   4. CRUD de productos (Crear, Leer, Actualizar, Eliminar)
//   5. Movimientos de stock (entrada, salida, historial)
//   6. Modales con formularios para las operaciones anteriores
// =====================================================================

// Cuando el DOM esta listo (la pagina termino de cargar), ejecuto todo
// El "$(function () { ... })" es la forma corta de jQuery para
// "$(document).ready(function () { ... })"
$(function () {

    // ================================================================
    // CONFIGURACION INICIAL
    // ================================================================

    // URL base de la API del modulo de inventario
    // "?pagina=inventario&action=" se concatena con cada accion
    // Ejemplo: ?pagina=inventario&action=listar
    var API = '?pagina=inventario&action=';

    // ================================================================
    // FUNCION: refrescarKPI()
    // Actualiza los valores de las 4 tarjetas de indicadores (KPIs)
    // llamando al servidor via AJAX. Se usa despues de cada operacion
    // que modifica el stock (crear, editar, eliminar, entrada, salida).
    // ================================================================
    function refrescarKPI() {
        // GET request a ?pagina=inventario&action=kpis
        // Espera una respuesta JSON con total, critico, bajo, valor
        $.getJSON(API + 'kpis', function (r) {
            // Si el servidor devolvio success = false, no hago nada
            if (!r.success) return;
            // Actualizo el texto de cada tarjeta con los nuevos valores
            $('#kpi-total').text(r.data.total);            // Total de productos
            $('#kpi-critico').text(r.data.critico);         // Productos con stock critico
            $('#kpi-bajo').text(r.data.bajo);               // Productos con stock bajo
            // Para el valor total, lo formateo con 2 decimales y signo $
            $('#kpi-valor').text('$' + parseFloat(r.data.valor).toFixed(2));
        });
    }

    // ================================================================
    // FUNCION: refrescarTabla()
    // Vuelve a cargar toda la tabla de productos desde el servidor.
    // Se llama despues de cualquier cambio (crear, editar, eliminar,
    // entrada, salida) para reflejar los cambios visualmente.
    // ================================================================
    function refrescarTabla() {
        // GET request a ?pagina=inventario&action=listar
        // Devuelve un arreglo con todos los productos activos
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return;

            // Referencia al <tbody> de la tabla de productos
            var tbody = $('#tabla-productos tbody');
            // Limpio el contenido actual del tbody
            tbody.empty();

            // Si no hay productos, muestro un mensaje de tabla vacia
            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">inventory_2</i>No hay productos registrados</td></tr>');
                $('.result-count').text('0 productos');
                return; // Termino porque no hay productos que mostrar
            }

            // Recorro cada producto del arreglo usando jQuery.each
            $.each(r.data, function (i, p) {
                // --- LOGICA DE ESTADO (igual que en la vista PHP) ---
                // Determino colores, iconos y barra segun el stock
                var estado, badgeClass, icon, iconBg, iconColor, stockColor, barClass, barWidth;

                // CASO 1: Sin stock (stock <= 0) -> color rojo
                if (p.stock <= 0) {
                    estado = 'Sin stock'; badgeClass = 'background:#fce4ec;color:#c62828;';
                    icon = 'block'; iconBg = '#fce4ec'; iconColor = 'var(--danger)';
                    stockColor = 'var(--danger)'; barClass = 'var(--danger)'; barWidth = 0;

                // CASO 2: Stock bajo (entre 1 y minimo) -> color rojo
                } else if (p.stock <= p.stock_minimo) {
                    estado = 'Crítico'; badgeClass = 'background:#fce4ec;color:#c62828;';
                    icon = 'warning'; iconBg = '#fce4ec'; iconColor = 'var(--danger)';
                    stockColor = 'var(--danger)'; barClass = 'var(--danger)';
                    // Calculo el porcentaje de la barra respecto al minimo
                    barWidth = p.stock_minimo > 0 ? Math.max(5, (p.stock / p.stock_minimo) * 100) : 50;

                // CASO 3: Stock OK (mayor al minimo) -> color verde
                } else {
                    estado = 'OK'; badgeClass = 'background:#e8f5e9;color:#2e7d32;';
                    icon = 'check_circle'; iconBg = '#e8f5e9'; iconColor = 'var(--success)';
                    stockColor = 'var(--success)'; barClass = 'var(--success)';
                    // La barra no pasa de 50% para mantener proporcion
                    barWidth = p.stock_minimo > 0 ? Math.min(100, (p.stock / p.stock_minimo) * 50) : 100;
                }

                // --- CONSTRUCCION DE LA FILA HTML ---
                // Creo cada celda manualmente concatenando strings
                // Uso $('<span>').text(...).html() para escapar caracteres
                // especiales y evitar inyeccion XSS

                // Columna 1: Icono + Nombre + Categoria
                var row = '<tr data-id="' + p.id + '" data-nombre="' + $('<span>').text(p.nombre).html() + '">';
                row += '<td style="padding:0.85rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:38px;height:38px;border-radius:8px;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="material-icons" style="color:' + iconColor + ';font-size:1.2rem;">' + icon + '</i></div><div><div style="font-weight:600;color:var(--text);font-size:0.9rem;">' + $('<span>').text(p.nombre).html() + '</div><span style="font-size:0.75rem;color:var(--text-muted);">' + $('<span>').text(p.categoria || 'Sin categoría').html() + '</span></div></div></td>';

                // Columna 2: Codigo
                row += '<td style="padding:0.85rem 1rem;color:var(--text-muted);font-size:0.85rem;">#' + $('<span>').text(p.codigo).html() + '</td>';

                // Columna 3: Precio formateado
                row += '<td style="padding:0.85rem 1rem;font-weight:600;font-size:0.9rem;">$' + parseFloat(p.precio_venta).toFixed(2) + '</td>';

                // Columna 4: Stock + Barra de progreso
                row += '<td style="padding:0.85rem 1rem;"><div style="display:flex;flex-direction:column;gap:0.25rem;min-width:80px;"><div style="display:flex;justify-content:space-between;font-size:0.85rem;"><span style="font-weight:700;color:' + stockColor + ';">' + p.stock + '</span><span style="color:var(--text-muted);font-size:0.7rem;">mín: ' + p.stock_minimo + '</span></div><div style="width:100%;height:5px;background:var(--border-light);border-radius:4px;overflow:hidden;"><div style="width:' + barWidth + '%;height:100%;background:' + barClass + ';border-radius:4px;"></div></div></div></td>';

                // Columna 5: Badge de estado
                row += '<td style="padding:0.85rem 1rem;"><span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;' + badgeClass + '"><i class="material-icons" style="font-size:0.85rem;">' + icon + '</i> ' + estado + '</span></td>';

                // Columna 6: Botones de accion
                row += '<td style="padding:0.85rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light grey lighten-1 tooltipped btn-movimientos" data-id="' + p.id + '" data-position="left" data-tooltip="Ver movimientos" style="margin-right:4px;"><i class="material-icons">inventory</i></button>';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar" data-id="' + p.id + '" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light green tooltipped btn-entrada" data-id="' + p.id + '" data-position="left" data-tooltip="Entrada" style="margin-right:4px;"><i class="material-icons">add_shopping_cart</i></button>';
                row += '<button class="btn-floating waves-effect waves-light orange tooltipped btn-salida" data-id="' + p.id + '" data-position="left" data-tooltip="Salida" style="margin-right:4px;"><i class="material-icons">remove_shopping_cart</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar" data-id="' + p.id + '" data-nombre="' + $('<span>').text(p.nombre).html() + '" data-position="left" data-tooltip="Eliminar"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                // Agrego la fila al tbody
                tbody.append(row);
            });

            // Actualizo el contador de resultados
            $('.result-count').text(r.data.length + ' productos');

            // Reinicio los tooltips de los botones (Materialize los necesita)
            $('.tooltipped').tooltip();

            // Aplico el filtro actual para mantener consistencia
            aplicarFiltro();
        });
    }

    // ================================================================
    // FUNCION: aplicarFiltro()
    // Filtra las filas de la tabla en el lado del cliente (sin recargar)
    // segun el texto de busqueda y el estado seleccionado.
    // Se ejecuta cada vez que el usuario escribe o cambia el filtro.
    // ================================================================
    function aplicarFiltro() {
        // Obtengo el texto de busqueda (en minusculas para comparar)
        var q = $('#searchProducto').val().toLowerCase();
        // Obtengo el filtro de estado seleccionado
        var estadoFiltro = $('#filterEstado').val();

        // Recorro cada fila de la tabla
        $('#tabla-productos tbody tr').each(function () {
            var mostrar = true; // Por defecto, la fila se muestra
            var $row = $(this);

            // --- Filtro por texto ---
            // Obtengo el nombre (data-nombre) y el codigo (columna 2) de la fila
            var nombre = $row.data('nombre') || '';
            var codigo = $row.find('td').eq(1).text().toLowerCase();
            var texto = nombre.toLowerCase() + ' ' + codigo;
            // Si hay texto de busqueda y no coincide, oculto la fila
            if (q && texto.indexOf(q) === -1) mostrar = false;

            // --- Filtro por estado ---
            if (estadoFiltro) {
                // El estado esta en la columna 5 (indice 4) como texto del badge
                var badge = $row.find('td').eq(4).text().trim().toLowerCase();
                // Si el texto del badge no contiene el filtro, oculto
                if (badge.indexOf(estadoFiltro) === -1) mostrar = false;
            }

            // Muestro u oculto la fila segun las condiciones
            $row.toggle(mostrar);
        });

        // Actualizo el contador de resultados visibles vs totales
        var visibles = $('#tabla-productos tbody tr:visible').length;
        var total = $('#tabla-productos tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    }

    // ================================================================
    // FUNCION: abrirModalProducto(titulo, datos)
    // Abre el modal de creacion/edicion de producto.
    // Si recibe "datos" (un objeto con datos del producto), carga los
    // campos con esos valores para editar. Si no, deja el formulario
    // vacio para crear un nuevo producto.
    // ================================================================
    function abrirModalProducto(titulo, datos) {
        // Cambio el titulo del modal segun la accion
        $('#modal-producto-title').text(titulo);
        // Reseteo el formulario a sus valores iniciales
        $('#form-producto')[0].reset();
        // Limpio el ID oculto (para nuevo producto)
        $('#producto-id').val('');

        // Si me pasaron datos (edicion), lleno los campos
        if (datos) {
            $('#producto-id').val(datos.id);             // ID del producto
            $('#producto-codigo').val(datos.codigo);     // Codigo
            $('#producto-nombre').val(datos.nombre);     // Nombre
            $('#producto-categoria').val(datos.categoria_id); // Categoria
            $('#producto-stock').val(datos.stock);       // Stock actual
            $('#producto-stock-minimo').val(datos.stock_minimo); // Stock minimo
            $('#producto-costo').val(datos.costo_compra); // Costo de compra
            $('#producto-precio').val(datos.precio_venta); // Precio de venta
        }

        // Abro el modal de Materialize
        $('#modal-producto').modal('open');
        // Actualizo los labels flotantes de Materialize (para que no
        // se superpongan con los valores cargados)
        M.updateTextFields();
        // Inicializo el select de categoria (Materialize lo necesita)
        $('#producto-categoria').formSelect();
    }

    // ================================================================
    // FUNCION: abrirModalMovimientos(id, nombre)
    // Abre el modal que muestra el historial de movimientos de stock
    // de un producto. Los datos se cargan via AJAX desde el servidor.
    // ================================================================
    function abrirModalMovimientos(id, nombre) {
        // Pongo el titulo y el nombre del producto
        $('#modal-movimientos-title').text('Movimientos de Stock');
        $('#modal-movimientos-producto').text('Producto: ' + nombre);
        // Mientras carga, muestro "Cargando..."
        $('#tabla-movimientos tbody').html('<tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Cargando...</td></tr>');
        // Abro el modal para que el usuario vea el estado de carga
        $('#modal-movimientos').modal('open');

        // Peticion AJAX para obtener los movimientos del producto
        $.getJSON(API + 'movimientos&id=' + id, function (r) {
            var tbody = $('#tabla-movimientos tbody');
            tbody.empty(); // Limpio el contenido anterior

            // Si no hay movimientos, muestro mensaje
            if (!r.success || !r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Sin movimientos registrados</td></tr>');
                return;
            }

            // Recorro cada movimiento y creo una fila en la tabla
            $.each(r.data, function (i, m) {
                // Color del texto segun el tipo: verde para entrada, rojo para salida
                var tipoClase = m.tipo === 'entrada' ? 'green-text' : m.tipo === 'salida' ? 'red-text' : 'orange-text';

                var fila = '<tr>';
                fila += '<td style="padding:0.5rem 0.75rem;">' + m.fecha + '</td>';           // Fecha del movimiento
                fila += '<td style="padding:0.5rem 0.75rem;"><span class="' + tipoClase + '" style="font-weight:600;text-transform:capitalize;">' + m.tipo + '</span></td>'; // Tipo (entrada/salida) con color
                fila += '<td style="padding:0.5rem 0.75rem;">' + m.cantidad + '</td>';         // Cantidad movida
                fila += '<td style="padding:0.5rem 0.75rem;">' + m.stock_anterior + '</td>';   // Stock antes del movimiento
                fila += '<td style="padding:0.5rem 0.75rem;">' + m.stock_nuevo + '</td>';      // Stock despues del movimiento
                fila += '<td style="padding:0.5rem 0.75rem;">' + (m.usuario || '-') + '</td>'; // Quien hizo el movimiento
                fila += '<td style="padding:0.5rem 0.75rem;">' + (m.motivo || '-') + '</td>';  // Motivo del movimiento
                fila += '</tr>';

                tbody.append(fila);
            });
        });
    }

    // ================================================================
    // FUNCION: abrirModalStock(tipo, id, nombre)
    // Abre el modal para registrar una entrada o salida de stock.
    // El parametro "tipo" es "entrada" o "salida" y cambia tanto
    // el titulo como el texto del boton de guardar.
    // ================================================================
    function abrirModalStock(tipo, id, nombre) {
        // true si es entrada, false si es salida
        var esEntrada = tipo === 'entrada';

        // Cambio titulo y descripcion segun el tipo
        $('#modal-stock-title').text(esEntrada ? 'Entrada de Stock' : 'Salida de Stock');
        $('#modal-stock-producto').text('Producto: ' + nombre);

        // Guardo el ID del producto y el tipo en campos ocultos
        $('#stock-producto-id').val(id);
        $('#stock-tipo').val(tipo);

        // Reseteo el formulario y pongo valores por defecto
        $('#form-stock')[0].reset();
        $('#stock-cantidad').val(1); // Cantidad por defecto: 1
        $('#stock-motivo').val(esEntrada ? 'Reposición de inventario' : 'Venta o uso interno');

        // Cambio el texto del boton de guardar
        $('#btn-guardar-stock').text(esEntrada ? 'Registrar Entrada' : 'Registrar Salida');

        // Abro el modal y actualizo los labels flotantes
        $('#modal-stock').modal('open');
        M.updateTextFields();
    }

    // ================================================================
    // EVENTOS DE LOS BOTONES DE LA TABLA
    // ================================================================

    // Evento: Boton "Nuevo Producto" (en la barra superior)
    // Abre el modal de producto vacio para crear uno nuevo
    $(document).on('click', '.btn-nuevo', function () {
        abrirModalProducto('Nuevo Producto', null); // null = sin datos = nuevo
    });

    // Evento: Boton "Editar" (en cada fila)
    // Primero carga los datos del producto via AJAX, luego abre el modal
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).data('id'); // Obtengo el ID del producto del boton
        // Pido los datos completos del producto al servidor
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (r.success) {
                // Si encontro el producto, abro el modal con sus datos
                abrirModalProducto('Editar Producto', r.data);
            } else {
                // Si hubo error, muestro un toast de Materialize
                EIS.toast(r.error || 'Error al cargar producto', 'red', 'error');
            }
        });
    });

    // Evento: Boton "Eliminar" (en cada fila)
    // Pide confirmacion y luego elimina el producto via AJAX
    $(document).on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        // Confirmacion nativa del navegador
        if (confirm('¿Está seguro de eliminar el producto "' + nombre + '"?')) {
            // POST request para eliminar el producto
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle'); // Mensaje de exito
                    refrescarTabla();  // Actualizo la tabla
                    refrescarKPI();    // Actualizo los KPIs
                } else {
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json');
        }
    });

    // Evento: Boton "Movimientos" (en cada fila)
    // Abre el modal con el historial de movimientos de stock
    $(document).on('click', '.btn-movimientos', function () {
        var id = $(this).data('id');
        // Obtengo el nombre desde el atributo data-nombre de la fila <tr>
        var nombre = $(this).closest('tr').data('nombre') || '';
        abrirModalMovimientos(id, nombre);
    });

    // Evento: Boton "Entrada de Stock" (en cada fila)
    // Abre el modal de entrada de stock
    $(document).on('click', '.btn-entrada', function () {
        var id = $(this).data('id');
        var nombre = $(this).closest('tr').data('nombre') || '';
        abrirModalStock('entrada', id, nombre);
    });

    // Evento: Boton "Salida de Stock" (en cada fila)
    // Abre el modal de salida de stock
    $(document).on('click', '.btn-salida', function () {
        var id = $(this).data('id');
        var nombre = $(this).closest('tr').data('nombre') || '';
        abrirModalStock('salida', id, nombre);
    });

    // ================================================================
    // EVENTOS DE LOS FORMULARIOS
    // ================================================================

    // Evento: Submit del formulario de producto (crear o editar)
    // Detecta automaticamente si es creacion o edicion segun si hay ID
    $('#form-producto').on('submit', function (e) {
        e.preventDefault(); // Evito que el formulario se envie de forma tradicional

        var id = $('#producto-id').val();       // Si tiene ID, es edicion; si no, es nuevo
        var accion = id ? 'actualizar' : 'crear'; // Elijo la accion segun corresponda
        var data = $(this).serialize();         // Convierto el formulario a string (clave=valor&...)

        // POST request para crear o actualizar
        $.post(API + accion, data, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-producto').modal('close'); // Cierro el modal
                refrescarTabla();  // Actualizo la tabla
                refrescarKPI();    // Actualizo los KPIs
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json');
    });

    // Evento: Submit del formulario de movimiento de stock (entrada/salida)
    // El tipo (entrada o salida) se lee del campo oculto #stock-tipo
    $('#form-stock').on('submit', function (e) {
        e.preventDefault();

        var tipo = $('#stock-tipo').val(); // "entrada" o "salida"
        var data = $(this).serialize();

        // POST request a ?pagina=inventario&action=entrada (o salida)
        $.post(API + tipo, data, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modal-stock').modal('close'); // Cierro el modal
                refrescarTabla();  // Actualizo la tabla (el stock cambio)
                refrescarKPI();    // Actualizo los KPIs
            } else {
                EIS.toast(r.error || 'Error al registrar movimiento', 'red', 'error');
            }
        }, 'json');
    });

    // ================================================================
    // EVENTOS DE FILTROS Y BUSQUEDA
    // ================================================================

    // Evento: Tecla levantada en el campo de busqueda
    // Uso debounce para no ejecutar el filtro en cada tecla, sino
    // esperar 300ms despues de que el usuario deje de escribir
    $('#searchProducto').on('keyup', debounce(function () {
        aplicarFiltro();
    }, 300));

    // Evento: Cambio en el selector de estado
    $('#filterEstado').on('change', function () {
        aplicarFiltro();
    });

    // ================================================================
    // INICIALIZACION DE COMPONENTES MATERIALIZE
    // ================================================================

    // Activo los tooltips (los globitos de ayuda al pasar el mouse)
    $('.tooltipped').tooltip();
    // Inicializo el select de categorias (transforma <select> en algo bonito)
    $('#producto-categoria').formSelect();
    // Inicializo todos los modales de la pagina para que Materialice los maneje
    $('.modal').modal();

});
