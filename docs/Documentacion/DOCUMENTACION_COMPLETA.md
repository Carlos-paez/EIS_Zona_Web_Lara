# DOCUMENTACION COMPLETA - EIS System (Zona Web Lara)

## Descripcion General

EIS System es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con **Materialize CSS 1.0.0** y **jQuery 3.7.1**. Utiliza un patron Front Controller con enrutador OOP (clase `Router`), arquitectura MVC con namespaces PSR-4, layout maestro y JavaScript modular (10 archivos). Todos los assets son locales (sin CDN) y cuenta con Service Worker para funcionamiento offline.

## Seguridad

### Protecciones Implementadas
- **CSRF Tokens**: `bin2hex(random_bytes(32))` en `Router::__construct()`, inyectado en `window.EIS.csrfToken` y en `<input name="csrf_token">`
- **XSS Sanitizacion**: Helper `escHtml()` en JS, `htmlspecialchars()` en PHP
- **Session Hardening**: `session_regenerate_id(true)` en login/logout
- **Prepared Statements**: PDO con `ATTR_EMULATE_PREPARES => false`
- **Validacion Backend**: Helpers reutilizables en `Model.php` (non-empty, min-length, pattern, positive, FK existence)
- **Validacion Frontend**: HTML5 attributes (`required`, `maxlength`, `pattern`, `title`)
- **Double Escaping Removido**: Controllers solo usan `trim()`, Model maneja sanitizacion

## Arquitectura

### Patron: Front Controller + MVC OOP
- `index.php` es el unico punto de entrada (carga autoloader + instancia Router)
- `Router` clase en `App\Core` gestiona enrutamiento, autenticacion, CSRF tokens, rutas AJAX y vistas
- 5 tipos de peticiones AJAX: ClienteController, InventarioController, RolController, ProveedorController, ProveedorGestionController
- Acciones de auth: AuthController::login() y AuthController::logout() con session_regenerate_id
- Las vistas publicas se cargan directamente; las protegidas dentro del layout maestro

### Flujo de Peticion
```
Navegador -> .htaccess -> index.php -> new Router() -> Router::handle()
    -> session_start() + resolvePage()
    -> CSRF token: bin2hex(random_bytes(32))
    -> leer ?pagina= de la URL + validar regex
    -> ¿Es AJAX? (clientes/inventario/roles/proveedores/proveedores-gestion + action) -> controlador
    -> ¿Es auth? (login_validate/logout) -> AuthController
    -> ¿Es vista? -> renderView():
        -> verificar autenticacion
        -> cargar vista (publica: directa | protegida: renderWithLayout())
```

### Enrutador (router.php - Clase Router)
- Propiedad `$pagina` resuelta por `resolvePage()` con validacion regex
- `handle()`: metodo principal que deriva segun el tipo de peticion
- `isAjaxCliente()`, `isAjaxInventario()`, `isAjaxRoles()`, `isAjaxProveedores()`, `isAjaxProveedorGestion()`: detectan rutas AJAX
- `isAuthAction()`: detecta login_validate y logout
- `requireAuth()`: verifica sesion, retorna JSON error si no autenticado
- `renderWithLayout()`: prepara `$titulos` (13 modulos) + `$extraHeaders` + incluye layout.php

## Estructura de Directorios

```
src/
├── index.php                    # Front Controller (autoloader + Router OOP)
├── .htaccess                    # Reglas rewrite Apache (URLs limpias parciales)
├── manifest.json                # Manifiesto PWA
├── sw.js                        # Service Worker
├── offline.php                  # Pagina offline fallback
├── Config/
│   └── database.php             # Conexion PDO MySQL (legacy)
├── app/
│   ├── core/
│   │   ├── Database.php         # Conexion PDO Singleton (moderna)
│   │   ├── Model.php            # Clase base abstracta para modelos
│   │   └── router.php           # Enrutador OOP (clase Router, 385 lineas)
│   ├── Controllers/
│   │   ├── AuthController.php       # Login/logout con sesiones + CSRF + session_regenerate_id
│   │   ├── ClienteController.php    # CRUD clientes AJAX
│   │   ├── inventarioController.php # Controlador AJAX inventario
│   │   ├── RolController.php        # Controlador AJAX roles/permisos
│   │   ├── ProveedorController.php  # Controlador AJAX proveedores (solicitudes)
│   │   └── ProveedorGestionController.php # Controlador AJAX proveedores (gestion)
│   ├── Models/
│   │   ├── Cliente.php              # Modelo POO clientes
│   │   ├── Inventario.php           # Modelo POO inventario (namespace)
│   │   ├── Usuario.php              # Modelo POO usuarios
│   │   ├── Proveedor.php            # Modelo POO proveedores (solicitudes)
│   │   ├── ProveedorGestion.php     # Modelo POO proveedores (gestion)
│   │   ├── Rol.php                  # Modelo POO roles/permisos
│   │   ├── Asesoria.php             # Modelo POO asesorias
│   │   ├── crud_users.php           # CRUD usuarios legacy (8 funciones)
│   │   └── crud_asesorias.php       # CRUD asesorias legacy (8 funciones)
│   ├── template/
│   │   └── layout.php           # Layout maestro (12 modulos, 6 JS condicionales)
│   └── Views/
│       ├── login.php            # Login
│       ├── login_validate.php   # Validacion credenciales (legacy)
│       ├── dashboard.php        # Panel de control
│       ├── inventario.php       # Inventario (conectado a BD)
│       ├── ventas.php           # POS
│       ├── proveedores.php      # Solicitudes a proveedores (conectado a BD)
│       ├── clientes.php         # Gestion de clientes (conectado a BD)
│       ├── reportes.php         # Reportes
│       ├── activos.php          # Activos fijos
│       ├── ciberControl.php     # Cybercafe
│       ├── asesorias.php        # Asesoria legal
│       ├── menu.php             # Menu navegacion
│       ├── usuarios.php         # Gestion usuarios (conectado a BD)
│       └── roles.php            # Roles y permisos (conectado a BD)
├── Database/
│   ├── estructura.sql           # Esquema BD v3.0 (27 tablas)
│   ├── seed_data.sql            # Datos de prueba
│   └── seed_data_masivo.sql     # Datos masivos de prueba
└── Public/
    ├── css/                     # Estilos (locales, 4 archivos)
    ├── js/                      # JavaScript modular (10 archivos + 2 librerias)
    └── fonts/                   # Material Icons TTF (local)
```

## Layout Maestro (layout.php)

### Head
- Meta tags: charset, viewport, theme-color, apple-mobile-web-app
- Manifest: `<link rel="manifest" href="manifest.json">`
- CSS: material-icons.css, materialize.min.css, styles.css
- JS: jquery-3.7.1.min.js (en head para disponibilidad inmediata)

### Sidebar (Materialize Sidenav)
- 13 modulos: Dashboard, Inventario, Ventas (POS), Solicitudes, Clientes, Cyber, Reportes, Activos, Asesoria Legal, Usuarios, Roles y Permisos
- Theme toggle (oscuro/claro)
- Cerrar sesion
- Clase `active` en el item correspondiente segun `$pagina`

### Header
- Nav bar con titulo dinamico (`$pageTitle`)
- Reloj digital (actualizado por JS)
- Notificaciones con badge
- Header extra opcional (`$headerExtra`) - ej: chips de estado en ciberControl
- Nombre de usuario (Admin)

### Scripts Cargados
- Siempre: materialize.min.js, app.core.js, app.init.js, app.tables.js, app.ui.js
- Condicional: app.pos.js (ventas), app.cyber.js (ciberControl), app.legal.js (asesorias), app.inventario.js (inventario), app.roles.js (roles), app.proveedores.js (proveedores/clientes)
- Service Worker: navigator.serviceWorker.register('sw.js')

## JavaScript Modular

### app.core.js
- Namespace: `var EIS = {}`
- `function debounce(fn, delay)` - Limitador de frecuencia
- `function filtrarTabla(inputSelector, tableSelector, colIndex)` - Filtro de tablas
- `EIS.toast(msg, color, icon)` - Notificaciones Materialize

### app.init.js
- Inicializacion de componentes Materialize (sidenav, select, tooltips, modal, tabs, collapsible, etc.)
- Reloj digital con `setInterval(actualizarReloj, 1000)`
- Tema oscuro/claro con localStorage
- Animacion de transicion de pagina (fadeIn)
- Animacion de contadores numericos (`.metric-value`)
- Boton volver arriba (scroll event + click)

### app.tables.js
- Busqueda en tabla de inventario (`#searchProducto`)
- Busqueda en tabla de proveedores (`#searchProveedor`)
- Busqueda en tabla de activos (`#searchActivo`)
- Filtro por estado (`#filterEstado`, `#filterEstadoProv`)
- Paginacion (`.pagination li a`)

### app.ui.js
- Notificaciones demo (`#notifBell`)
- Generador de reportes simulado (`#formReporte`)
- Botones con confirmacion (`[data-confirm]`)
- Boton nuevo elemento (`.btn-nuevo`)
- Descarga simulada (`.btn-download`)
- Tooltips mejorados (`.btn-floating`, `.tooltip-me`)

### app.pos.js
- Array `posCart` y variable `posTotal`
- Click en `.pos-product` agrega al carrito
- Funciones: `actualizarPosUI()`, `actualizarMiniTotal()`, `actualizarCarritoModal()`
- Eliminar item (`.cart-item-remove`)
- Abrir modal carrito (`#openCartBtn`)
- Procesar venta simulado (`#procesarVenta`)
- Vaciar carrito (`#vaciarCarrito`)
- Busqueda de productos (`#posSearch` con debounce 200ms)

### app.cyber.js
- Funcion `actualizarCyberContadores()` - Actualiza contadores de estaciones
- Click en `.station-card` - Toggle entre disponible/ocupada con confirmacion
- Click en `.filter-btn` - Filtro visual por estado (todas/disponible/ocupada/mantenimiento)

### app.legal.js
- Array `allowedDocs` con 11 tipos de documentos permitidos
- Array `asesoriasRegistradas` con historial de la sesion
- Funciones: `normalizarDoc()`, `documentoPermitido()`, `actualizarHistorial()`, `mostrarValidacion()`
- Submit de `#asesoriaForm` - Validacion y registro
- Input `#documento` - Validacion en tiempo real
- Click en `.btn-eliminar-asesoria` - Eliminar registro
- Input `#searchAsesoria` - Busqueda en historial con debounce

## Offline / PWA

### Assets Locales
| Recurso | Localizacion |
|---------|-------------|
| Materialize CSS | `Public/css/materialize.min.css` |
| Materialize JS | `Public/js/materialize.min.js` |
| jQuery 3.7.1 | `Public/js/jquery-3.7.1.min.js` |
| Material Icons CSS | `Public/css/material-icons.css` |
| Material Icons Font | `Public/fonts/MaterialIcons-Regular.ttf` |

### Service Worker (sw.js)
- Cachea 17 assets estaticos en la instalacion
- Estrategia Cache First para CSS, JS, fuentes y manifest
- Estrategia Network First con fallback a `offline.php` para navegacion PHP
- Versionado mediante `CACHE_NAME`

### Manifest (manifest.json)
- name: "EIS System"
- short_name: "EIS"
- display: standalone
- theme_color: #1a237e

### Offline Page (offline.php)
- Icono cloud_off
- Mensaje "Sin Conexion"
- Boton "Reintentar" con location.reload()

## Base de Datos

- **Nombre**: zona_web_lara
- **Motor**: InnoDB
- **Charset**: utf8mb4
- **Collation**: utf8mb4_spanish_ci
- **Tablas**: 27 (21 CREATE TABLE + vistas/relaciones)
- **Indices**: Claves foraneas en todas las relaciones; UNIQUE en cedula, user_name, codigo, rif
- **Vistas**: No hay vistas definidas en el esquema actual (los calculos se hacen via consultas SQL directas en los modelos)
- **Objetos**: No hay SP, funciones, triggers ni eventos definidos actualmente (toda la logica transaccional se maneja desde PHP con PDO)

### Tablas
roles, categorias, marcas, tipos_activo, tarifas_cyber, tipos_pago, usuarios, productos, proveedores, producto_proveedor, ventas, detalle_ventas, solicitudes, detalle_solicitudes, activos, estaciones_cyber, sesiones_cyber, movimientos_stock, asesorias

## Vistas y Modulos

| Pagina | Vista | JS | Tipo |
|--------|-------|-----|------|
| login | login.php | core | Publica |
| login_validate | login_validate.php | - | Publica |
| dashboard | dashboard.php | init, ui | Protegida |
| inventario | inventario.php | tables | Protegida |
| ventas | ventas.php | pos | Protegida |
| proveedores | proveedores.php | tables | Protegida |
| clientes | clientes.php | tables | Protegida |
| ciberControl | ciberControl.php | cyber | Protegida |
| reportes | reportes.php | ui | Protegida |
| activos | activos.php | tables | Protegida |
| asesorias | asesorias.php | legal | Protegida |
| menu | menu.php | init, ui | Protegida |
| usuarios | usuarios.php | - | Protegida |
| roles | roles.php | - | Protegida |

## Tecnologias

### Backend
- PHP 7.4+ (vanilla, MVC OOP con namespaces PSR-4)
- PDO MySQL (prepared statements, utf8mb4)
- Composer (autoloading PSR-4)
- Model.php: Base abstracta con helpers de validacion (non-empty, min-length, pattern, FK existence, duplicates)
- Database.php: Singleton PDO con constantes de configuracion

### Frontend
- Materialize CSS 1.0.0 (local)
- jQuery 3.7.1 (local)
- Material Icons (local)
- CSS3 Custom Properties (tema claro/oscuro)
- Service Worker API

### Servidor
- Apache (mod_rewrite)
- .htaccess para URLs amigables

---

**Version**: 3.0
**Julio 2026**

