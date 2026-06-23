// =====================================================================
// ARCHIVO: app.roles.js
// FUNCIÓN: Maneja toda la interactividad del módulo de Roles y Permisos.
//          Se comunica con el controlador PHP mediante AJAX y permite:
//          - CRUD de roles (Crear, Leer, Actualizar, Eliminar)
//          - Asignación/desasignación de permisos a roles
//          - Asignación de roles a usuarios
//          - Filtros y búsqueda en tabla de roles
// =====================================================================

// Espero a que el DOM esté listo para ejecutar el código
$(function () {
    // URL base de la API del módulo de roles
    // Se concatena con cada acción (listar, crear, actualizar, eliminar, etc.)
    var API = '?pagina=roles&action=';

    // ================================================================
    // FUNCIÓN: refrescarKPI()
    // PROPÓSITO: Actualiza los indicadores (KPIs) de la cabecera:
    //            total de roles, total de usuarios, roles con usuarios
    //            y total de permisos. Hace dos llamadas AJAX paralelas.
    // ================================================================
    function refrescarKPI() {
        // Primera llamada: obtengo la lista de roles para calcular KPIs
        $.getJSON(API + 'listar', function (r) {
            if (!r.success || !r.data) return; // Si hay error, salgo
            var totalRoles = r.data.length; // Cantidad total de roles
            var totalUsuarios = 0; // Acumulador de usuarios
            var conRol = 0; // Contador de roles que tienen al menos 1 usuario

            // Recorro cada rol y sumo sus usuarios
            $.each(r.data, function (i, rol) {
                totalUsuarios += parseInt(rol.total_usuarios) || 0;
                if (parseInt(rol.total_usuarios) > 0) conRol++;
            });

            // Actualizo las tarjetas KPI
            $('#kpi-roles').text(totalRoles);       // Total de roles
            $('#kpi-usuarios').text(totalUsuarios); // Total de usuarios con rol
            $('#kpi-con-rol').text(conRol);         // Roles con al menos 1 usuario
        }).fail(function () {
            EIS.toast('Error al cargar indicadores', 'red', 'error');
        });

        // Segunda llamada: obtengo la lista de permisos para el KPI de permisos
        $.getJSON(API + 'permisos', function (r) {
            if (r.success && r.data) {
                $('#kpi-permisos').text(r.data.length); // Total de permisos registrados
            }
        }).fail(function () {
            EIS.toast('Error al cargar permisos', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: refrescarTabla()
    // PROPÓSITO: Recarga la tabla de roles desde el servidor vía AJAX.
    //            Renderiza cada fila con avatar circular, nombre,
    //            descripción, usuarios asociados y botones de acción.
    // ================================================================
    function refrescarTabla() {
        // GET request para obtener todos los roles
        $.getJSON(API + 'listar', function (r) {
            if (!r.success) return; // Si hay error, salgo

            var tbody = $('#tabla-roles-body'); // <tbody> de la tabla de roles
            tbody.empty(); // Limpio el contenido actual

            // Si no hay roles, muestro mensaje de tabla vacía
            if (!r.data || r.data.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="material-icons" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;">admin_panel_settings</i>No hay roles registrados</td></tr>');
                $('.result-count').text('0 roles');
                return;
            }

            // Recorro cada rol recibido del servidor
            $.each(r.data, function (i, rol) {
                // Verifico si el rol tiene usuarios asignados
                var tieneUsuarios = parseInt(rol.total_usuarios) > 0;
                var usuariosBadge = tieneUsuarios
                    ? '<span style="font-weight:600;color:var(--success);">' + rol.total_usuarios + '</span>'  // Verde si tiene
                    : '<span style="color:var(--text-muted);">0</span>'; // Gris si no tiene

                // Paleta de colores para los avatares circulares (se repite cíclicamente)
                var colors = ['#3949ab', '#43a047', '#fb8c00', '#e53935', '#00acc1', '#8e24aa', '#6d4c41', '#546e7a'];
                var color = colors[i % colors.length]; // Asigno color según índice
                var inicial = (rol.nombre || 'R').charAt(0).toUpperCase(); // Primera letra del nombre

                // --- CONSTRUCCIÓN DE LA FILA ---
                var row = '<tr data-id="' + rol.id + '" data-nombre="' + $('<span>').text(rol.nombre).html() + '">';

                // Columna 1: Avatar circular + Nombre del rol
                row += '<td style="padding:0.75rem 1rem;"><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,' + color + ',' + color + 'cc);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:0.9rem;">' + inicial + '</div><div><div style="font-weight:600;font-size:0.9rem;">' + $('<span>').text(rol.nombre).html() + '</div></div></div></td>';

                // Columna 2: Descripción del rol (oculto en móvil)
                row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.85rem;">' + $('<span>').text(rol.descripcion || 'Sin descripci&oacute;n').html() + '</td>';

                // Columna 3: Badge con cantidad de usuarios asignados
                row += '<td style="padding:0.75rem 1rem;">' + usuariosBadge + '</td>';

                // Columna 4: Fecha de creación del rol (oculto en móvil)
                row += '<td class="hide-on-small-only" style="padding:0.75rem 1rem;color:var(--text-muted);font-size:0.8rem;">' + (rol.created_at || '-') + '</td>';

                // Columna 5: Botones de acción (Editar, Permisos, Eliminar)
                row += '<td style="padding:0.75rem 1rem;text-align:right;white-space:nowrap;">';
                row += '<button class="btn-floating waves-effect waves-light indigo tooltipped btn-editar-rol" data-id="' + rol.id + '" data-position="left" data-tooltip="Editar"><i class="material-icons">edit</i></button>';
                row += '<button class="btn-floating waves-effect waves-light grey tooltipped btn-permisos-rol" data-id="' + rol.id + '" data-position="left" data-tooltip="Permisos" style="margin-left:4px;"><i class="material-icons">security</i></button>';
                row += '<button class="btn-floating waves-effect waves-light red tooltipped btn-eliminar-rol" data-id="' + rol.id + '" data-nombre="' + $('<span>').text(rol.nombre).html() + '" data-position="left" data-tooltip="Eliminar" style="margin-left:4px;"><i class="material-icons">delete</i></button>';
                row += '</td></tr>';

                tbody.append(row); // Agrego la fila al tbody
            });

            // Actualizo el contador de resultados
            $('.result-count').text(r.data.length + ' roles');
            // Reinicio los tooltips para los nuevos botones
            $('.tooltipped').tooltip();
            // Aplico los filtros activos (búsqueda y estado)
            aplicarFiltro();
        }).fail(function () {
            EIS.toast('Error al cargar la tabla', 'red', 'error');
        });
    }

    // ================================================================
    // FUNCIÓN: aplicarFiltro()
    // PROPÓSITO: Filtra las filas de la tabla de roles según el texto
    //            de búsqueda y el filtro de estado (con usuarios,
    //            sin usuarios, todos).
    // ================================================================
    function aplicarFiltro() {
        var q = $('#searchRol').val().toLowerCase();        // Texto de búsqueda
        var filtro = $('#filterRolEstado').val();           // Filtro de estado

        // Recorro cada fila de la tabla de roles
        $('#tabla-roles-body tr').each(function () {
            var mostrar = true; // Por defecto se muestra
            var $row = $(this);
            var nombre = $row.data('nombre') || ''; // Nombre del rol desde data-nombre

            // --- Filtro por texto de búsqueda ---
            if (q && nombre.toLowerCase().indexOf(q) === -1) mostrar = false;

            // --- Filtro por estado (con/sin usuarios) ---
            if (filtro === 'con-usuarios') {
                var users = parseInt($row.find('td').eq(2).text().trim()) || 0;
                if (users === 0) mostrar = false; // Oculto si no tiene usuarios
            } else if (filtro === 'sin-usuarios') {
                var users = parseInt($row.find('td').eq(2).text().trim()) || 0;
                if (users > 0) mostrar = false; // Oculto si tiene usuarios
            }

            $row.toggle(mostrar); // Muestro/oculto según las condiciones
        });

        // Actualizo el contador de resultados visibles vs totales
        var visibles = $('#tabla-roles-body tr:visible').length;
        var total = $('#tabla-roles-body tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    }

    // ================================================================
    // FUNCIÓN: abrirModalRol(titulo, datos)
    // PROPÓSITO: Abre el modal de creación/edición de rol.
    // PARÁMETROS:
    //   titulo - String con el título del modal
    //   datos  - (Opcional) Objeto con datos del rol para editar
    // ================================================================
    function abrirModalRol(titulo, datos) {
        $('#modal-rol-title').text(titulo);       // Título del modal
        $('#modal-rol-icon').text(titulo === 'Nuevo Rol' ? 'add' : 'edit'); // Ícono según acción
        $('#formRol')[0].reset();                 // Reseteo el formulario
        $('#rol-id').val('');                     // Limpio el ID (nuevo rol)

        if (datos) {
            // Si hay datos, cargo los campos para edición
            $('#rol-id').val(datos.id);               // ID del rol
            $('#rol-nombre').val(datos.nombre);       // Nombre
            $('#rol-descripcion').val(datos.descripcion); // Descripción
        }

        $('#modalRol').modal('open'); // Abro el modal de Materialize
        M.updateTextFields();        // Actualizo los labels flotantes
    }

    // ================================================================
    // FUNCIÓN: abrirModalPermisos(rol_id, rol_nombre)
    // PROPÓSITO: Abre el modal de gestión de permisos para un rol.
    //            Carga todos los permisos disponibles y marca los que
    //            ya están asignados al rol actual.
    // PARÁMETROS:
    //   rol_id     - ID del rol
    //   rol_nombre - Nombre del rol (para mostrar en el título)
    // ================================================================
    function abrirModalPermisos(rol_id, rol_nombre) {
        $('#permisoRolNombre').text(rol_nombre);     // Muestro el nombre del rol
        $('#permisos-rol-id').val(rol_id);           // Guardo el ID del rol
        $('#permisos-container').html('<p style="color:var(--text-muted);text-align:center;">Cargando...</p>'); // Mensaje de carga
        $('#modalPermisos').modal('open');           // Abro el modal

        // Primera llamada: obtengo todos los permisos disponibles
        $.getJSON(API + 'permisos', function (rPermisos) {
            if (!rPermisos.success || !rPermisos.data) {
                $('#permisos-container').html('<p style="color:var(--danger);text-align:center;">Error al cargar permisos</p>');
                return;
            }

            // Segunda llamada: obtengo los permisos ya asignados a este rol
            $.getJSON(API + 'permisosRol&rol_id=' + rol_id, function (rAsignados) {
                var asignados = rAsignados.success ? (rAsignados.data || []) : []; // IDs de permisos asignados
                var html = ''; // Acumulador HTML

                // Recorro cada permiso disponible
                $.each(rPermisos.data, function (i, p) {
                    var checked = asignados.indexOf(p.id) !== -1; // Verifico si ya está asignado
                    var icono = p.icono || 'lock_open'; // Ícono del permiso (o default)

                    // Construyo un item con nombre, ícono y toggle switch
                    html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1rem;background:var(--surface-hover);border-radius:8px;">';
                    html += '<span style="font-weight:500;font-size:0.9rem;display:flex;align-items:center;gap:0.5rem;"><i class="material-icons" style="font-size:1.1rem;color:var(--primary);">' + icono + '</i> ' + $('<span>').text(p.nombre).html() + '</span>';
                    // Switch de Materialize: checked si está asignado
                    html += '<div class="switch"><label><input type="checkbox" class="permiso-checkbox" value="' + p.id + '"' + (checked ? ' checked' : '') + '><span class="lever"></span></label></div>';
                    html += '</div>';
                });

                // Inserto los items de permisos en el contenedor
                $('#permisos-container').html(html);
            }).fail(function () {
                $('#permisos-container').html('<p style="color:var(--danger);text-align:center;">Error al cargar permisos del rol</p>');
            });
        }).fail(function () {
            $('#permisos-container').html('<p style="color:var(--danger);text-align:center;">Error al cargar permisos</p>');
        });
    }

    // ================================================================
    // FUNCIÓN: abrirModalAsignar()
    // PROPÓSITO: Abre el modal para asignar un rol a un usuario.
    //            Carga la lista de usuarios y roles desde el servidor
    //            y los muestra en selects.
    // ================================================================
    function abrirModalAsignar() {
        // Limpio y preparo los selects con opción placeholder
        $('#asignar-usuario').empty().append('<option value="" disabled selected>Seleccionar usuario</option>');
        $('#asignar-rol').empty().append('<option value="" disabled selected>Seleccionar rol</option>');

        // Obtengo usuarios y roles del servidor
        $.getJSON(API + 'usuarios', function (r) {
            if (!r.success || !r.data) return;

            // Lleno el select de usuarios
            $.each(r.data.usuarios, function (i, u) {
                var estado = u.activo ? '' : ' (Inactivo)'; // Indico si el usuario está inactivo
                $('#asignar-usuario').append('<option value="' + u.id + '">' + $('<span>').text(u.nombre).html() + ' (' + $('<span>').text(u.username).html() + ') - ' + $('<span>').text(u.rol || 'Sin rol').html() + estado + '</option>');
            });

            // Lleno el select de roles
            $.each(r.data.roles, function (i, rol) {
                $('#asignar-rol').append('<option value="' + rol.id + '">' + $('<span>').text(rol.nombre).html() + '</option>');
            });

            // Inicializo los selects de Materialize y abro el modal
            $('#asignar-usuario').formSelect();
            $('#asignar-rol').formSelect();
            $('#modalAsignar').modal('open');
        }).fail(function () {
            EIS.toast('Error al cargar usuarios', 'red', 'error');
        });
    }

    // ================================================================
    // EVENTOS DE BOTONES
    // ================================================================

    // Botón "Nuevo Rol" en la cabecera
    $(document).on('click', '#btnNuevoRol', function () {
        abrirModalRol('Nuevo Rol', null); // Abre modal vacío para crear
    });

    // Botón "Editar" en cada fila de la tabla
    $(document).on('click', '.btn-editar-rol', function () {
        var id = $(this).data('id'); // ID del rol
        // Cargo los datos del rol vía AJAX
        $.getJSON(API + 'detalle&id=' + id, function (r) {
            if (r.success) {
                abrirModalRol('Editar Rol', r.data); // Abro modal con datos
            } else {
                EIS.toast(r.error || 'Error al cargar rol', 'red', 'error');
            }
        }).fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    // Botón "Eliminar" en cada fila de la tabla
    $(document).on('click', '.btn-eliminar-rol', function () {
        var id = $(this).data('id');       // ID del rol
        var nombre = $(this).data('nombre'); // Nombre del rol
        // Confirmación antes de eliminar
        if (confirm('¿Est&aacute; seguro de eliminar el rol "' + nombre + '"?')) {
            // POST request para eliminar el rol
            $.post(API + 'eliminar', { id: id }, function (r) {
                if (r.success) {
                    EIS.toast(r.message, 'green', 'check_circle');
                    refrescarTabla(); // Recargo tabla
                    refrescarKPI();   // Actualizo KPIs
                } else {
                    EIS.toast(r.error || 'Error al eliminar', 'red', 'error');
                }
            }, 'json').fail(function () {
                EIS.toast('Error de conexi&oacute;n', 'red', 'error');
            });
        }
    });

    // Botón "Permisos" en cada fila de la tabla
    $(document).on('click', '.btn-permisos-rol', function () {
        var id = $(this).data('id'); // ID del rol
        var nombre = $(this).closest('tr').data('nombre') || ''; // Nombre desde la fila
        abrirModalPermisos(id, nombre); // Abro modal de permisos
    });

    // ================================================================
    // EVENTOS DE BOTONES GUARDAR EN MODALES
    // ================================================================

    // Botón "Guardar" en modal de rol
    $('#btnGuardarRol').on('click', function () {
        var id = $('#rol-id').val(); // ID del rol (vacío si es nuevo)
        var accion = id ? 'actualizar' : 'crear'; // Determino la acción
        var data = {
            id: id,
            nombre: $('#rol-nombre').val(),
            descripcion: $('#rol-descripcion').val()
        };

        // Validación: el nombre del rol es obligatorio
        if (!data.nombre.trim()) {
            EIS.toast('El nombre del rol es obligatorio', 'red', 'error');
            return;
        }

        // POST request para crear o actualizar el rol
        $.post(API + accion, data, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modalRol').modal('close'); // Cierro modal
                refrescarTabla(); // Recargo tabla
                refrescarKPI();   // Actualizo KPIs
            } else {
                EIS.toast(r.error || 'Error al guardar', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    // Botón "Guardar" en modal de permisos
    $('#btnGuardarPermisos').on('click', function () {
        var rol_id = $('#permisos-rol-id').val(); // ID del rol
        var permisos = []; // Arreglo de IDs de permisos seleccionados
        // Recojo todos los checkboxes marcados
        $('.permiso-checkbox:checked').each(function () {
            permisos.push($(this).val());
        });

        // POST request para guardar los permisos del rol
        $.post(API + 'guardarPermisos', { rol_id: rol_id, permisos: permisos }, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modalPermisos').modal('close'); // Cierro modal
            } else {
                EIS.toast(r.error || 'Error al guardar permisos', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    // Botón "Asignar Rol" en modal de asignación
    $('#btnAsignarRol').on('click', function () {
        var usuario_id = $('#asignar-usuario').val(); // ID del usuario seleccionado
        var rol_id = $('#asignar-rol').val();          // ID del rol seleccionado

        // Validación: ambos campos deben estar seleccionados
        if (!usuario_id || !rol_id) {
            EIS.toast('Seleccione un usuario y un rol', 'red', 'error');
            return;
        }

        // POST request para asignar el rol al usuario
        $.post(API + 'asignarRol', { usuario_id: usuario_id, rol_id: rol_id }, function (r) {
            if (r.success) {
                EIS.toast(r.message, 'green', 'check_circle');
                $('#modalAsignar').modal('close'); // Cierro modal
                refrescarTabla(); // Recargo tabla
                refrescarKPI();   // Actualizo KPIs
            } else {
                EIS.toast(r.error || 'Error al asignar rol', 'red', 'error');
            }
        }, 'json').fail(function () {
            EIS.toast('Error de conexi&oacute;n', 'red', 'error');
        });
    });

    // Botón "Asignar Rol" en la cabecera (abre el modal de asignación)
    $(document).on('click', '#btnAsignarRolHeader', function () {
        abrirModalAsignar();
    });

    // ================================================================
    // EVENTOS DE FILTROS Y BÚSQUEDA
    // ================================================================

    // Búsqueda en tiempo real en campo #searchRol
    $('#searchRol').on('keyup', debounce(function () {
        aplicarFiltro();
    }, 300));

    // Cambio en el filtro de estado de roles
    $('#filterRolEstado').on('change', function () {
        aplicarFiltro();
    });

    // ================================================================
    // INICIALIZACIÓN DE COMPONENTES MATERIALIZE
    // ================================================================

    // Activo tooltips, selects y modales de Materialize
    $('.tooltipped').tooltip();
    $('select').formSelect();
    $('.modal').modal();

    // ================================================================
    // CARGA INICIAL DE DATOS
    // ================================================================

    refrescarKPI();   // Cargo los indicadores al iniciar
    refrescarTabla(); // Cargo la tabla de roles al iniciar
});
