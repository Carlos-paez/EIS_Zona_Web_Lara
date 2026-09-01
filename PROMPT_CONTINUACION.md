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

- Rama `Carlos` está **2 commits por delante** de `origin/Carlos` (sin pushear).

### Controladores (12)
`Activo, Asesoria, Auth, Ciber, Cliente, Dashboard, Inventario, Proveedor, ProveedorGestion,
Reporte, Rol, Venta`.

### Modelos (13 POO + 2 legacy)
`Activo, Asesoria, CiberControl, Cliente, Dashboard, Inventario, Proveedor, ProveedorGestion,
Reporte, Rol, Usuario, Venta` + `CiberModel` (legacy) + `crud_users`, `crud_asesorias` (legacy).

### Core (5)
`Database` (Singleton), `Model` (abstracto con helpers de validación), `router` (Front Controller),
`Exporter` (CSV/Excel/PDF), `PdfBuilder` (PDF mínimo propio).

### JS (15 módulos)
`app.core, app.init, app.tables, app.ui, app.pos, app.cyber, app.legal, app.inventario,
app.roles, app.proveedores, app.proveedores-gestion, app.clientes, app.activos, app.reportes`
(+ `app.js` utilidad).

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
- Frontend: jQuery + Materialize local, `EIS.toast(msg, color, icon)`, `debounce()`, `escHtml()`
  (recomendado `$('<span>').text(...).html()`); escapar HTML SIEMPRE al renderizar.
- Rutas de controladores registradas en `src/app/core/router.php` (tabla `CONTROLLERS` +
  despacho en `handle()`).

## Próximos pasos recomendados / pendientes
- [ ] `git push origin Carlos` de los 2 commits locales.
- [ ] Mover credenciales de BD a variables de entorno (`.env`).
- [ ] Unificar modelos legacy (`CiberModel`, `crud_*`) con los POO modernos.
- [ ] Middleware de autenticación/CSRF como capa separada.
- [ ] URLs limpias (`/nombre` en lugar de `?pagina=nombre`).
