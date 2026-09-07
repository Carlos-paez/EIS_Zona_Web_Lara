# PROMPT DE CONTINUACIÓN — Estado actual del proyecto EIS_Zona_Web_Lara

> Copia este archivo como prompt para continuar el trabajo en una sesión nueva de opencode.

## Contexto
Aplicación PHP (`EIS_Zona_Web_Lara`, rama `Carlos`) con arquitectura **Front Controller + MVC OOP**,
Router (App\Core), Database Singleton (PDO), modelos POO con namespace (PSR-4 vía Composer) y
frontend con Materialize CSS + jQuery (assets 100 % locales, PWA offline).

Base de datos: `zona_web_lara` (MySQL, InnoDB, `utf8mb4_spanish_ci`).

## Estado ACTUAL (v4.3 — todo funcional con BD)
Todos los módulos están **conectados a la base de datos** con modelo POO + controlador + vista +
JS modular. Versión actual 4.3.

### Cambios v4.2 ya commiteados (commit ca874de)
- `src/index.php` (+118 líneas): **manejo global de errores** — `display_errors=0`,
  `set_error_handler`, `set_exception_handler`, `register_shutdown_function`, buffer de salida
  (`ob_start`), respuestas JSON genéricas en AJAX y página amigable en HTML; todo se registra vía
  `App\Core\Logger` en `src/logs/errores.md`.
- `src/app/core/Logger.php` (registro de errores en Markdown).
- `src/app/Controllers/UsuarioController.php` + `app.usuarios.js`: el CRUD de **usuarios** ya NO
  depende de `AuthController`/`app.core.js`.
- `Validator.php` (coerción estricta de tipos: texto/entero/decimal/fecha/enum/email/bool).
- Pulido visual y corrección del render de reportes/clientes del POS.

### Cambios SIN commitear (working tree, v4.3)
- `src/Public/js/app.selects.js`: corrección de **dos bugs** en la barra de búsqueda de los selects
  de Materialize (verificada con Puppeteer en el selector de clientes del POS):
  1. **El menú se cerraba al hacer clic en la barra.** Materialize 1.0.0 registra el cierre con
     `document.body.addEventListener("click", handler, true)` en **fase de captura**, por lo que el
     `stopPropagation` en fase de burbuja del `li` no servía. Fix: un bloqueador en fase de captura
     sobre `document` (ancestro de `body`) que hace `e.stopPropagation()` para `click`/`touchend`
     cuando el objetivo está dentro de `.eis-select-search` (app.selects.js, ~líneas 173-180).
  2. **El typeahead robaba el foco al escribir.** El `_handleDropdownKeydown` del dropdown buscaba la
     primera opción con la letra. Fix: handlers `keydown`/`keyup` con `e.stopPropagation()` en el
     input de búsqueda (está dentro del `<ul>`, así el teclado no llega al dropdown, ~líneas 93-95).
  - Además: handler global `focusin/click` en `.select-wrapper input.select-dropdown` que inyecta la
    barra (idempotente) y `restablecerBusqueda()` para limpiar el filtro al abrir el menú.
- `src/sw.js`: `CACHE_NAME` subido a `'eis-cache-v5'` (Stale-While-Revalidate). Mecanismo de
  invalidación: al corregir un JS servido por el SW hay que subir `CACHE_NAME` (v4→v5 ya hecho;
  futuro v6/v7...) para que el navegador reemplace el SW y no sirva el script viejo. Fue la causa de
  que el fix de `app.selects.js` "no funcionara" en el navegador (caché del SW).

### Últimos fixes confirmados (sesiones recientes)
1. **Reportes**: causa raíz de filas vacías antes de exportar — los `rows` llegaban como objetos
   asociativos (`json_encode` de `PDO::FETCH_ASSOC`) y se leían con `fila[i]`; fix en
   `app.reportes.js`: `var valores = Array.isArray(fila) ? fila : Object.values(fila);`.
2. **Ventas (clientes)**: faltaba `use App\Models\Cliente;` en `VentaController.php` →
   `Error: Class "App\Controllers\Cliente" not found` (un `\Error`, no atrapado por `catch (\Exception)`).
   Corregido con import + `catch (\Throwable $e)` en `handle()`.
3. **POS**: el select `#posClienteSelect` se regenera en `refrescarSelect()` (destroy + `formSelect()`)
   y con ello perdía la barra de búsqueda. `app.selects.js` expone ahora
   `EIS.activarBusquedaEnSelect('#posClienteSelect', 'Buscar cliente por nombre o cédula...')`
   (inyectarBusqueda idempotente y respeta un placeholder existente salvo que se pase uno nuevo);
   `app.pos.js` la re-inyecta tras cada refresh y al abrir el modal del carrito. (Los fixes v4.3 de
   `app.selects.js` la hacen funcional: ya no se cierra al hacer clic ni roba el foco al escribir.)
4. Antes de esto: 7 bugs corregidos en v4.1 (DataTables, `app.ui.js` sin handlers demo,
   `direccion`/`telefono` opcionales, `isset()` en checkbox `activa`, `asignarRolAUsuario()`
   → `rol_usuarios.id`, `descripcion`/`created_at` en roles, INSERT de `cliente_asesoria`).

## Arquitectura
### Controladores (13 = 12 AJAX + Auth)
Mapa `CONTROLLERS` del Router (`src/app/core/router.php`): `clientes, inventario, ventas, roles,
proveedores, proveedores-gestion, asesorias, ciberControl, activos, dashboard, reportes, usuarios`.
Además `AuthController` (login/logout, no AJAX).
Rutas `?pagina=` privadas (12): dashboard, inventario, ventas, clientes, proveedores,
proveedores-gestion, ciberControl, reportes, activos, asesorias, usuarios, roles.
Públicas: `login`, `login_validate`.

### Modelos (15 = 12 POO + 3 legacy)
POO: `Activo, Asesoria, CiberControl, Cliente, Dashboard, Inventario, Proveedor, ProveedorGestion,
Reporte, Rol, Usuario, Venta`.
Legacy: `CiberModel`, `crud_users`, `crud_asesorias`.

### Core (7)
`Database` (Singleton), `Model` (abstracto con helpers de validación), `router` (Front Controller),
`Validator` (coerción estricta), `Logger` (errores en `src/logs/errores.md`),
`Exporter` (CSV/Excel/PDF), `PdfBuilder` (PDF mínimo propio).

### JS (17 módulos + 4 librerías)
`app.core, app.init, app.selects, app.tables, app.ui, app.pos, app.cyber, app.legal, app.inventario,
app.roles, app.proveedores, app.proveedores-gestion, app.clientes, app.activos, app.reportes,
app.usuarios` (+ `app.js` utilidad). Librerías locales: jquery-3.7.1, materialize.min, jquery.dataTables.min,
dataTables.materialize.
Las tablas principales usan **jQuery DataTables** (local) vía `EIS.datatable*` de `app.core.js`.

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
- Validación backend: `App\Core\Validator` (tipos) + helpers en setters del modelo
  (`sanitizeString`, `validateLength`, `validateNotEmpty` de `App\Core\Model`).
- Cliente **get-or-create** centralizado: `Cliente::obtenerOCrearPorCedula(cedula, nombre,
  apellido, direccion, telefono): int`.
- Operaciones transaccionales: `Venta::registrarVenta`, `Asesoria::crear`,
  `CiberControl::iniciarSesion` (cliente get-or-create + entidades relacionadas).
- Exportación: `Exporter::csv|excel|pdf($titulo, $columnas, $filas)`; `ReporteController::exportar`
  valida CSRF + rango de fechas + formato permitido. (Los `rows` pueden ser asociativos:
  normalizar con `Object.values` al renderizar.)
- Frontend: jQuery + Materialize + DataTables local, `EIS.toast(msg, color, icon)`, `debounce()`,
  `escHtml()`; escapar HTML SIEMPRE al renderizar. Barra de búsqueda en selects:
  `EIS.habilitarBusquedaEnSelects()` y `EIS.activarBusquedaEnSelect(selector, placeholder)`.
- Errores: nunca se muestran al usuario; `Logger` escribe en `src/logs/errores.md`.
- Rutas de controladores registradas en `src/app/core/router.php` (tabla `CONTROLLERS` +
  despacho en `handle()`).

## Próximos pasos recomendados / pendientes
- [ ] Commitear el working tree actual (v4.3: fix de barras de búsqueda en `app.selects.js` + `CACHE_NAME` v5 en `sw.js` + actualización de toda la documentación .md a v4.3; el rename de `EXPLICACION_LINEA_POR_LINEA.md` → `docs/` ya está staged).
- [ ] Aplicar el `ALTER TABLE roles` (descripcion/created_at) a BD existentes si no se ha hecho.
- [ ] Mover credenciales de BD a variables de entorno (`.env`) — hoy están duplicadas en `src/Config/database.php` y `src/app/core/Database.php`.
- [ ] Unificar modelos legacy (`CiberModel`, `crud_*`) con los POO modernos (columnas antiguas `id_activo`, `id_sesion`, `id_tarifa` no existen en el esquema actual).
- [ ] Middleware de autenticación/CSRF como capa separada.
- [ ] URLs limpias (`/nombre` en lugar de `?pagina=nombre`).

## Datos de prueba
- Credenciales dev (`src/Database/usuario dev.txt`): `cpaez` / `3006`.
- Seed: `admin` / `1234`.
- Servidor de pruebas: `php -S 127.0.0.1:8099 -t src` (document root debe ser `src/`).
- User agent param en PowerShell 5.1: sin `-SkipHttpErrorCheck`; con `-MaximumRedirection 0` las
  redirecciones 302 lanzan error no terminante pero la sesión persiste.