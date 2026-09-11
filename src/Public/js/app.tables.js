// =====================================================================
// ARCHIVO: app.tables.js
// FUNCIÓN: La búsqueda, los filtros y la paginación de todas las tablas
//          principales de la aplicación ahora los gestiona jQuery
//          DataTables (ver app.core.js y los módulos específicos:
//          app.inventario.js, app.activos.js, app.roles.js,
//          app.clientes.js, app.proveedores.js, app.proveedores-gestion.js,
//          app.legal.js, app.reportes.js, app.cyber.js).
//
//          Este archivo se conserva únicamente como punto de extensión
//          genérico. No registra handlers de filtrado/paginación manuales
//          porque entrarían en conflicto con DataTables.
// =====================================================================

$(function () {
    // Sin handlers globales: cada módulo conecta su propia búsqueda y
    // filtros a la instancia de DataTables correspondiente.
});