# DOCUMENTACION COMPLETA - EIS System (Zona Web Lara)

## Descripcion General

EIS System es una aplicacion web de gestion empresarial desarrollada en **PHP 8.x vanilla** con
**Materialize CSS 1.0.0** y **jQuery 3.7.1**. Utiliza un patron **Front Controller** con enrutador
OOP (clase `Router`), arquitectura **MVC** con namespaces PSR-4 (Composer), layout maestro y
JavaScript modular (16 modulos). Todos los assets son locales (sin CDN) y cuenta con Service Worker
para funcionamiento offline (PWA).

Los **13 modulos** estan conectados a la base de datos MySQL (`zona_web_lara`): cada uno dispone de
modelo POO + controlador + vista + JS.

## Seguridad

### Protecciones Implementadas
- **CSRF Tokens**: `bin2hex(random_bytes(32))` generado una vez por sesion en el `Router`, inyectado
  en `window.EIS.csrfToken` (JS) y verificado con `Router::verifyCsrfToken()` en cada mutacion
- **XSS Sanitizacion**: `escHtml()` en JS, `htmlspecialchars()` en PHP
- **Session Hardening**: `session_regenerate_id(true)` en login/logout
- **Prepared Statements**: PDO con `ATTR_EMULATE_PREPARES => false`, uso de `bindParam`
- **Validacion Backend**: helpers reutilizables en `Model.php` + clase final `Validator` (`App\Core\Validator`) con reglas estáticas por campo (cedula, RIF, username, precios, stock, etc.)
- **Operaciones transaccionales**: `beginTransaction()/commit()/rollback()` en ventas, asesorias y sesiones cyber

## Arquitectura

### Patron: Front Controller + MVC OOP
- `index.php` es el unico punto de entrada (carga autoloader + instancia `Router`)
- `Router` clase en `App\Core` gestiona enrutamiento, autenticacion, CSRF tokens, rutas AJAX y vistas
- Despacho AJAX: la tabla `CONTROLLERS` mapea `pagina => controlador`; las peticiones
  `?pagina=X&action=Y` se derivan al metodo `handle()` del controlador correspondiente
- 13 controladores: Auth, Usuario, Cliente, Inventario, Venta, Rol, Proveedor, ProveedorGestion,
  Asesoria, Ciber, Activo, Dashboard, Reporte
- Las vistas publicas (`login`, `login_validate`) se cargan directas; las protegidas dentro del layout
- `handle()` ejecuta `match ($action)` y retorna JSON `{success, data?, error?, message?}`

### Flujo de Peticion
```
Navegador -> .htaccess -> index.php -> new Router() -> Router::handle()
    -> session_start (si hace falta) + token CSRF (una vez por sesion)
    -> resolvePagina() (regex ^[a-zA-Z0-9_-]+$, anti path-traversal)
    -> control de acceso: paginas privadas requieren $_SESSION['logged_in']
    -> si hay ?action= en una pagina con controlador -> dispatchAction()
    -> si es login_validate POST -> AuthController::login()
    -> si no -> render(): publica directa | protegida con layout.php
```

### Enrutador (router.php - Clase Router)
- Propiedad `$pagina` resuelta por `resolvePagina()`
- `handle()`: metodo principal que deriva segun el tipo de peticion
- `dispatchAction()`: instancia el controlador del mapa `CONTROLLERS` y llama a `handle()`
- `PUBLIC_PAGES = ['login', 'login_validate']`
- `verifyCsrfToken()`: compara con `hash_equals()` el token de sesion
- `render()`: prepara `$pageTitle`, `$headerExtra`, `$contentView` e incluye `layout.php`

## Estructura de Directorios

```
src/
├── index.php                    # Front Controller (autoloader + Router OOP)
├── .htaccess                    # Reglas rewrite Apache
├── manifest.json                # Manifiesto PWA
├── sw.js                        # Service Worker
├── offline.php                  # Pagina offline fallback
├── Config/
│   └── database.php             # Conexion PDO MySQL (legacy)
├── cli/
│   └── create_user.php          # Script CLI para crear usuarios
├── app/
│   ├── core/
│   │   ├── Database.php         # Conexion PDO Singleton (moderna)
│   │   ├── Model.php            # Clase base abstracta con helpers de validacion
│   │   ├── Validator.php        # Clase final con reglas estáticas de validacion por campo
│   │   ├── router.php           # Enrutador OOP (Front Controller)
│   │   ├── Exporter.php         # Exportacion CSV/Excel/PDF
│   │   └── PdfBuilder.php       # Generador de PDF minimo
│   ├── Controllers/             # 13 controladores (auth, json AJAX + render)
│   │   ├── AuthController.php           # Login/logout
│   │   ├── UsuarioController.php        # CRUD usuarios, estados y password
│   │   ├── ClienteController.php        # CRUD clientes
│   │   ├── InventarioController.php     # CRUD inventario
│   │   ├── VentaController.php          # POS (productos, clientes, registrar venta)
│   │   ├── RolController.php            # CRUD roles/permisos
│   │   ├── ProveedorController.php      # Ordenes de abastecimiento
│   │   ├── ProveedorGestionController.php # Gestion de proveedores
│   │   ├── AsesoriaController.php       # Asesoria legal
│   │   ├── CiberController.php          # Control de cybercafe
│   │   ├── ActivoController.php         # Activos fijos
│   │   ├── DashboardController.php      # KPIs del panel
│   │   └── ReporteController.php        # Reportes y exportacion
│   ├── Models/                  # 13 POO + 2 legacy procedurales
│   │   ├── Usuario.php, Cliente.php, Inventario.php, Venta.php
│   │   ├── Proveedor.php, ProveedorGestion.php, Rol.php
│   │   ├── Asesoria.php, Activo.php, CiberControl.php
│   │   ├── Dashboard.php, Reporte.php
│   │   ├── CiberModel.php       # legacy cybercafe
│   │   ├── crud_users.php       # legacy procedural
│   │   └── crud_asesorias.php   # legacy procedural
│   ├── template/
│   │   └── layout.php           # Layout maestro (sidebar 12 modulos)
│   └── Views/                   # 15 vistas
│       ├── login.php, login_validate.php, menu.php
│       ├── dashboard.php, inventario.php, ventas.php
│       ├── clientes.php, proveedores.php, proveedores-gestion.php
│       ├── ciberControl.php, reportes.php, activos.php
│       ├── asesorias.php, usuarios.php, roles.php
├── Database/
│   ├── estructura.sql           # Esquema BD (21 tablas)
│   ├── seed_data.sql            # Datos de prueba
│   ├── seed_data_masivo.sql     # Datos masivos de prueba
│   ├── reportes_ejemplo.sql     # Consultas de ejemplo
│   └── usuario dev.txt          # Credenciales dev
└── Public/
    ├── css/                     # styles, login, materialize, material-icons (locales)
    ├── js/                      # jquery + materialize + 15 modulos app.*.js
    └── fonts/                   # MaterialIcons-Regular.ttf (local)
```

## Layout Maestro (layout.php)

### Head
- Meta: charset, viewport, theme-color, apple-mobile-web-app
- Manifest: `<link rel="manifest" href="manifest.json">`
- CSS: material-icons.css, materialize.min.css, styles.css
- JS: jquery-3.7.1.min.js (en head)

### Sidebar (Materialize Sidenav)
- 12 modulos: Dashboard, Inventario, Ventas (POS), Clientes, Proveedores (Ordenes/Proveedores),
  Cyber, Reportes, Activos, Asesoria Legal, Usuarios, Roles y Permisos, Cerrar Sesion
- Theme toggle (oscuro/claro con localStorage)
- Clase `active` en el item correspondiente segun `$pagina`

### Header
- Nav bar con titulo dinamico (`$pageTitle`)
- Reloj digital (JS)
- Notificaciones con badge
- Header extra opcional (`$headerExtra`) - ej: chips de estado en ciberControl
- Nombre de usuario

### Scripts Cargados y CSRF
- Siempre: materialize.min.js, jquery.dataTables.min.js, dataTables.materialize.js, app.core.js, app.init.js, app.selects.js, app.tables.js, app.ui.js
- Condicional por modulo: app.pos.js, app.cyber.js, app.legal.js, app.inventario.js, app.roles.js,
  app.proveedores.js, app.proveedores-gestion.js, app.clientes.js, app.activos.js, app.reportes.js,
  app.usuarios.js
- Inyecta `window.EIS.csrfToken` y `$.ajaxSetup` agrega el token a cada POST AJAX
- Service Worker: `navigator.serviceWorker.register('sw.js')`

## JavaScript Modular

Nombres: `app.core, app.init, app.selects, app.tables, app.ui, app.pos, app.cyber, app.legal, app.inventario,
app.roles, app.proveedores, app.proveedores-gestion, app.clientes, app.activos, app.reportes, app.usuarios`
(+ `app.js` legacy) y el motor de tables `jquery.dataTables.min.js` + `dataTables.materialize.js`.

- **app.core.js**: namespace `EIS`, `debounce()`, `EIS.toast()`, `escHtml()` y los helpers de DataTables `EIS.datatable*`
- **app.init.js**: Materialize init, reloj, tema oscuro/claro, animaciones
- **app.selects.js**: barra de busqueda en los selects (dropdowns) de Materialize
- **app.tables.js**: punto de extension generico; la busqueda, filtro por estado y paginacion las gestiona DataTables
- **app.ui.js**: notificaciones, botones, tooltips
- **DataTables**: las tablas principales de cada modulo se inicializan con `EIS.datatable()` (ordenamiento, paginacion, busqueda); los re-render por AJAX se sincronizan con `EIS.datatableRefresh()` y las barras de busqueda/filtros existentes se conectan con `EIS.datatableWireSearch()`/`EIS.datatableWireColumnFilter()`. Tema adaptado en `dataTables.materialize.css`
- **Modulos CRUD**: cada modulo implementa su CRUD/acciones vía AJAX
  (`?pagina=X&action=Y`) con render seguro usando `escHtml()`
- **app.reportes.js**: consultas por rango y descarga CSV/Excel/PDF (tabla dinamica `#tablaReporte` con DataTables)

## Offline / PWA

### Assets Locales
| Recurso | Localizacion |
|---------|-------------|
| Materialize CSS | `Public/css/materialize.min.css` |
| Materialize JS | `Public/js/materialize.min.js` |
| jQuery 3.7.1 | `Public/js/jquery-3.7.1.min.js` |
| jQuery DataTables | `Public/js/jquery.dataTables.min.js` |
| Tema DataTables | `Public/css/dataTables.materialize.css` + `Public/js/dataTables.materialize.js` |
| Material Icons CSS | `Public/css/material-icons.css` |
| Material Icons Font | `Public/fonts/MaterialIcons-Regular.ttf` |

### Service Worker (sw.js)
- Cachea assets estaticos en la instalacion
- Estrategia Cache First para CSS/JS/fuentes/manifest
- Network First con fallback a `offline.php` para navegacion

### Manifest / Offline
- `name: "EIS System"`, `display: standalone`, `theme_color: #1a237e`
- Página offline con boton "Reintentar" (`location.reload()`)

## Base de Datos

- **Nombre**: zona_web_lara
- **Motor**: InnoDB
- **Charset**: utf8mb4
- **Tablas (21)**: roles, permisos, categoria, clientes, cliente_asesoria, proveedores,
  status_seguimiento, tipo_asesoria, tarifas, tipo_activo, rol_usuarios, usuarios, permisos_rol,
  productos, orden_de_venta, lineas_venta, orden_abastecimiento, lineas_abastecimiento, asesoria,
  activos, sesion_ciber
- **Indices**: FK en todas las relaciones; UNIQUE en cedula, user_name, codigo, rif
- **Objetos**: logica transaccional manejada desde PHP con PDO (sin SP/triggers)

## Vistas y Modulos

| Pagina | Vista | JS principal | Controlador | Tipo |
|--------|-------|--------------|-------------|------|
| login | login.php | core | Auth | Publica |
| login_validate | login_validate.php | - | Auth | Publica |
| dashboard | dashboard.php | init, ui | Dashboard | Protegida |
| inventario | inventario.php | inventario, tables | Inventario | Protegida |
| ventas | ventas.php | pos | Venta | Protegida |
| clientes | clientes.php | clientes, tables | Cliente | Protegida |
| proveedores | proveedores.php | proveedores, tables | Proveedor | Protegida |
| proveedores-gestion | proveedores-gestion.php | proveedores-gestion, tables | ProveedorGestion | Protegida |
| ciberControl | ciberControl.php | cyber | Ciber | Protegida |
| reportes | reportes.php | reportes | Reporte | Protegida |
| activos | activos.php | activos, tables | Activo | Protegida |
| asesorias | asesorias.php | legal | Asesoria | Protegida |
| menu | menu.php | init, ui | - | Protegida |
| usuarios | usuarios.php | usuarios, tables | Usuario | Protegida |
| roles | roles.php | roles | Rol | Protegida |

## Tecnologias

### Backend
- PHP 8.x (vanilla, MVC OOP con namespaces PSR-4)
- PDO MySQL (prepared statements reales, utf8mb4)
- Composer (autoloading PSR-4)
- `Model.php`: base abstracta con helpers de validacion
- `Validator.php`: clase final con reglas de validacion estaticas
- `Database.php`: Singleton PDO
- `Exporter.php` + `PdfBuilder.php`: exportacion CSV/Excel/PDF sin dependencias

### Frontend
- Materialize CSS 1.0.0 (local)
- jQuery 3.7.1 (local)
- Material Icons (local)
- CSS3 Custom Properties (tema claro/oscuro)
- Service Worker API (PWA)

### Servidor
- Apache (mod_rewrite) / Nginx
- `.htaccess` para URLs amigables

---

**Version**: 4.2
**Septiembre 2026**
