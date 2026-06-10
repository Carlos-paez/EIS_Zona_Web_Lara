# DOCUMENTACION COMPLETA - EIS System (Zona Web Lara)

## Descripcion General

EIS System es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con **Materialize CSS 1.0.0** y **jQuery 3.7.1**. Utiliza un patron Front Controller con enrutador procedural, layout maestro y JavaScript modular. Todos los assets son locales (sin CDN) y cuenta con Service Worker para funcionamiento offline.

## Arquitectura

### Patron: Front Controller
- `index.php` es el unico punto de entrada
- `router.php` gestiona el enrutamiento, autenticacion y carga de vistas
- Las vistas publicas se cargan directamente
- Las vistas protegidas se cargan dentro del layout maestro

### Flujo de Peticion
```
Navegador -> .htaccess -> index.php -> router.php
    -> session_start()
    -> leer ?pagina= de la URL
    -> validar parametro (regex seguridad)
    -> verificar autenticacion
    -> cargar vista (publica: directa | protegida: dentro de layout)
```

### Enrutador (router.php)
- Variable `$pagina` del parametro GET `?pagina=`
- Validacion con `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)`
- Paginas publicas: `login`, `login_validate`
- Array `$titulos` con titulos para cada pagina
- Array `$extraHeaders` con HTML extra para el header
- Layout incluido via `require __DIR__ . '/../template/layout.php'`
- Error 404 si la vista no existe

## Estructura de Directorios

```
src/
├── index.php                    # Front Controller
├── .htaccess                    # Reglas rewrite Apache
├── manifest.json                # Manifiesto PWA
├── sw.js                        # Service Worker
├── offline.php                  # Pagina offline fallback
├── Config/
│   └── database.php             # Conexion PDO MySQL
├── app/
│   ├── core/
│   │   └── router.php           # Enrutador procedural
│   ├── Controllers/
│   │   └── inventarioController.php # Controlador AJAX inventario (10 acc.)
│   ├── Models/
│   │   ├── crud_inventario.php  # CRUD inventario (15+ funciones)
│   │   ├── crud_users.php       # CRUD usuarios (8 funciones)
│   │   └── crud_asesorias.php   # CRUD asesorias (8 funciones)
│   ├── template/
│   │   └── layout.php           # Layout maestro
│   └── Views/
│       ├── login.php            # Login
│       ├── login_validate.php   # Validacion credenciales
│       ├── dashboard.php        # Panel de control
│       ├── inventario.php       # Inventario
│       ├── ventas.php           # POS
│       ├── proveedores.php      # Solicitudes
│       ├── reportes.php         # Reportes
│       ├── activos.php          # Activos fijos
│       ├── ciberControl.php     # Cybercafe
│       ├── asesorias.php        # Asesoria legal
│       ├── menu.php             # Menu navegacion
│       └── usuarios.php         # Gestion usuarios
├── Database/
│   ├── estructura.sql           # Esquema BD (19 tablas)
│   └── datos_prueba.sql         # Datos de prueba
└── Public/
    ├── css/                     # Estilos (locales)
    ├── js/                      # JavaScript modular (8 archivos)
    └── fonts/                   # Material Icons TTF (local)
```

## Layout Maestro (layout.php)

### Head
- Meta tags: charset, viewport, theme-color, apple-mobile-web-app
- Manifest: `<link rel="manifest" href="manifest.json">`
- CSS: material-icons.css, materialize.min.css, styles.css
- JS: jquery-3.7.1.min.js (en head para disponibilidad inmediata)

### Sidebar (Materialize Sidenav)
- 10 modulos: Dashboard, Inventario, Ventas (POS), Solicitudes, Cyber, Reportes, Activos, Asesoria Legal, Usuarios, Menu
- Theme toggle (oscuro/claro)
- Cerrar sesion
- Clase `active` en el item correspondiente segun `$pagina`

### Header
- Nav bar con titulo dinamico (`$pageTitle`)
- Reloj digital (actualizado por JS)
- Notificaciones con badge
- Header extra opcional (`$headerExtra`)
- Nombre de usuario (Admin)

### Scripts Cargados
- Siempre: materialize.min.js, app.core.js, app.init.js, app.tables.js, app.ui.js
- Condicional: app.pos.js (ventas), app.cyber.js (ciberControl), app.legal.js (asesorias)
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

- **Nombre**: zwl
- **Motor**: InnoDB
- **Charset**: utf8mb4
- **Tablas**: 19
- **Indices**: 26
- **Vistas**: 3 (v_productos_stock, v_ventas_diarias, v_sesiones_activas)
- **Funciones**: 1 (fn_estado_stock)
- **Procedimientos**: 2 (sp_registrar_movimiento_stock, sp_cerrar_sesion_cyber)
- **Triggers**: 2 (trg_actualizar_totales_venta, trg_auditar_precio_producto)
- **Eventos**: 1 (ev_vencer_licencias)

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
| ciberControl | ciberControl.php | cyber | Protegida |
| reportes | reportes.php | ui | Protegida |
| activos | activos.php | tables | Protegida |
| asesorias | asesorias.php | legal | Protegida |
| menu | menu.php | init, ui | Protegida |
| usuarios | usuarios.php | - | Protegida |

## Tecnologias

### Backend
- PHP 7.4+ (vanilla, procedural)
- PDO MySQL (prepared statements, utf8mb4)
- Composer (autoloading PSR-4)

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

**Version**: 2.2
**Junio 2026**

