# PROMPT DE CONTINUACIÓN — Estado actual del proyecto EIS_Zona_Web_Lara

> Copia este archivo como prompt para continuar el trabajo en una sesión nueva de opencode.

## Contexto
Aplicación PHP (`EIS_Zona_Web_Lara`, rama `Carlos`) con arquitectura **Front Controller + MVC OOP**,
Router (App\Core), Database Singleton (PDO), modelos POO con namespace (PSR-4 vía Composer) y
frontend con Materialize CSS + jQuery (assets 100 % locales, PWA offline).

Base de datos: `zona_web_lara`.

## Estado ACTUAL (todo funcional con BD)
Todos los módulos están **conectados a la base de datos** con modelo POO + controlador + vista +
JS modular. Los commits recientes (`8a7d5e3`, `c6d4ba7`, `6d5437b`, `0076d6e`, `01719e5`, `70e4c38`)
implementaron: registro seguro de clientes en Asesoría/Ventas/CiberControl, POS transaccional,
CiberControl con sesiones iniciar/finalizar, CRUD de Activos, Dashboard con KPIs reales y
Reportes con exportación (CSV/Excel/PDF).

Además se corrigieron **7 bugs de funcionalidad** (ya confirmados y pusheados en el commit v4.1):
1. `app.ui.js`: se eliminaron los handlers demo de `#formReporte` (que hacía `preventDefault()` y leía
   `name="format"` inexistente) y de `.btn-nuevo` (toast "(demo)") que interferían con los módulos reales.
2. `ClienteController.php` + `Cliente.php`: `direccion`/`telefono` ahora son **opcionales**.
3. `ActivoController.php`: checkbox `activa` usa `isset($_POST['activa'])` (un desmarcado ya no se guarda como activo).
4. `Rol.php`: `asignarRolAUsuario()` resuelve `rol_usuarios.id` buscando por `fk_rol` antes de escribir `fk_rol_usuario`.
5. `roles`: se añadió soporte completo a `descripcion` y `created_at` (columnas nuevas en `estructura.sql`,
   setters en `Rol.php`, persistencia en `crearRol()`/`actualizarRol()`, y paso de descripción en `RolController.php`).
6. `seed_data.sql`: el INSERT de `cliente_asesoria` usa ahora las columnas reales `(fk_cliente, email, rif, tipo)`
   con los ID de clientes correctos (antes usaba columnas inexistentes y fallaba).
7. `app.roles.js`: la columna "Creado" formatea `created_at` correctamente en vez de mostrar la fecha cruda.

> **Importante:** el cambio en la tabla `roles` requiere aplicar a BD existentes:
> ```sql
> ALTER TABLE roles ADD COLUMN descripcion VARCHAR(500) DEFAULT NULL AFTER nombre_rol,
>   ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER descripcion;
> ```

- Rama `Carlos` está **2 commits por delante** de `origin/Carlos` (sin pushear), y además tiene
  **9 archivos modificados sin commitear** (los 7 fixes).

### Controladores (12)
`Activo, Asesoria, Auth, Ciber, Cliente, Dashboard, Inventario, Proveedor, ProveedorGestion,
Reporte, Rol, Venta`.

### Modelos (13 POO + 2 legacy)
`Activo, Asesoria, CiberControl, Cliente, Dashboard, Inventario, Proveedor, ProveedorGestion,
Reporte, Rol, Usuario, Venta` + `CiberModel` (legacy) + `crud_users`, `crud_asesorias` (legacy).

### Core (5)
`Database` (Singleton), `Model` (abstracto con helpers de validación), `router` (Front Controller),
`Exporter` (CSV/Excel/PDF), `PdfBuilder` (PDF mínimo propio).

### JS (15 módulos + DataTables)
`app.core, app.init, app.selects, app.tables, app.ui, app.pos, app.cyber, app.legal, app.inventario,
app.roles, app.proveedores, app.proveedores-gestion, app.clientes, app.activos, app.reportes`
(+ `app.js` utilidad). Las tablas principales usan **jQuery DataTables** (local) vía los helpers
`EIS.datatable*` de `app.core.js`; la busqueda/filtro/paginacion de cada modulo se conecta a su instancia.

### Vistas (15)
`login, login_validate, menu, dashboard, inventario, ventas, clientes, proveedores,
proveedores-gestion, ciberControl, reportes, activos, asesorias, usuarios, roles`.

## Patrones clave / contratos
- BD: `zona_web_lara`. Tablas (21): `roles, permisos, categoria, clientes, cliente_asesoria,
  proveedores, status_seguimiento, tipo_asesoria, tarifas, tipo_activo, rol_usuarios, usuarios,
  permisos_rol, productos, orden_de_venta, lineas_venta, orden_abastecimiento,
  lineas_abastecimiento, asesoria, activos, sesion_ciber`.
- Respuestas JSON: `{success:bool, data?:..., error?:string, message?:string}`.
- Usuario logueado: `$_SESSION['user_id']`.
- CSRF: el layout inyecta `window.EIS.csrfToken` y `$.ajaxSetup` lo agrega a cada POST;
  el backend valida con `Router::verifyCsrfToken($_POST['csrf_token'] ?? null)`.
- Validación backend en setters del modelo (`sanitizeString`, `validateLength`,
  `validateNotEmpty` de `App\Core\Model`).
- Cliente **get-or-create** centralizado: `Cliente::obtenerOCrearPorCedula(cedula, nombre,
  apellido, direccion, telefono): int`.
- Operaciones transaccionales: `Venta::registrarVenta`, `Asesoria::crear`,
  `CiberControl::iniciarSesion` (cliente get-or-create + entidades relacionadas).
- Exportación: `Exporter::csv|excel|pdf($titulo, $columnas, $filas)`; `ReporteController::exportar`
  valida CSRF + rango de fechas + formato permitido.
- Frontend: jQuery + Materialize + DataTables local, `EIS.toast(msg, color, icon)`, `debounce()`, `escHtml()`
  (recomendado `$('<span>').text(...).html()`); escapar HTML SIEMPRE al renderizar. Tablas con DataTables
  vía `EIS.datatable()` / `EIS.datatableRefresh()` / `EIS.datatableWireSearch()` / etc.
- Rutas de controladores registradas en `src/app/core/router.php` (tabla `CONTROLLERS` +
  despacho en `handle()`).

## Próximos pasos recomendados / pendientes
- [x] Integrar jQuery DataTables en todas las tablas principales y pushear rama `Carlos`.
- [x] Commitear los **7 fixes de bugs** y pushear rama `Carlos`.
- [ ] Aplicar el `ALTER TABLE roles` a la BD si ya existe.
- [ ] Mover credenciales de BD a variables de entorno (`.env`).
- [ ] Unificar modelos legacy (`CiberModel`, `crud_*`) con los POO modernos.
- [ ] Middleware de autenticación/CSRF como capa separada.
- [ ] URLs limpias (`/nombre` en lugar de `?pagina=nombre`).
