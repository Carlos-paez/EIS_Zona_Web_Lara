# ANALISIS TECNICO — EIS System (Zona Web Lara)

**Arquitectura:** Front Controller procedural con layout maestro  
**Base de datos:** MySQL 8+ (InnoDB, utf8mb4, 19 tablas)  
**Frontend:** jQuery 3.7.1 + Materialize CSS 1.0.0 (assets locales)  
**Offline:** Service Worker + PWA Manifest + pagina offline

---

## Indice de Modulos

1. [Arquitectura General](#1-arquitectura-general)
2. [Modulo de Autenticacion](#2-modulo-de-autenticacion)
3. [Modulo Dashboard](#3-modulo-dashboard)
4. [Modulo Inventario](#4-modulo-inventario)
5. [Modulo Ventas (POS)](#5-modulo-ventas-pos)
6. [Modulo Proveedores / Solicitudes](#6-modulo-proveedores--solicitudes)
7. [Modulo Reportes](#7-modulo-reportes)
8. [Modulo Activos Fijos](#8-modulo-activos-fijos)
9. [Modulo Cybercafe](#9-modulo-cybercafe)
10. [Modulo Asesoria Legal](#10-modulo-asesoria-legal)
11. [Offline y PWA](#11-offline-y-pwa)

---

## 1. Arquitectura General

### 1.1 Estructura de Directorios

```
eis_zona_web_lara/
├── composer.json                        # PSR-4 autoloading
├── src/                                 # Raiz de la aplicacion
│   ├── .htaccess                        # Apache Rewrite Rules
│   ├── index.php                        # Front Controller
│   ├── manifest.json                    # Manifiesto PWA
│   ├── sw.js                            # Service Worker
│   ├── offline.php                      # Pagina offline
│   ├── Config/
│   │   └── database.php                 # Conexion PDO MySQL
│   ├── Database/
│   │   ├── estructura.sql               # Esquema completo (19 tablas)
│   │   └── datos_prueba.sql             # Datos de prueba
│   ├── app/
│   │   ├── core/
│   │   │   └── router.php               # Enrutador procedural
│   │   ├── template/
│   │   │   └── layout.php               # Layout maestro
│   │   ├── Models/
│   │   │   ├── crud_users.php           # CRUD usuarios
│   │   │   └── crud_asesorias.php       # CRUD asesorias
│   │   └── Views/
│   │       ├── login.php                # Autenticacion
│   │       ├── login_validate.php       # Validacion credenciales
│   │       ├── dashboard.php            # Panel principal
│   │       ├── menu.php                 # Menu navegacion
│   │       ├── inventario.php           # Gestion inventario
│   │       ├── ventas.php               # Punto de venta (POS)
│   │       ├── proveedores.php          # Solicitudes a proveedores
│   │       ├── reportes.php             # Reportes y estadisticas
│   │       ├── activos.php              # Activos fijos
│   │       ├── ciberControl.php         # Control de cybercafe
│   │       ├── asesorias.php            # Asesoria legal
│   │       └── usuarios.php             # Gestion usuarios
│   └── Public/
│       ├── css/
│       │   ├── styles.css               # Estilos generales (1105 lineas)
│       │   ├── login.css                # Estilos login (138 lineas)
│       │   ├── materialize.min.css      # Materialize CSS (local)
│       │   └── material-icons.css       # Material Icons (local)
│       ├── js/
│       │   ├── jquery-3.7.1.min.js      # jQuery (local)
│       │   ├── materialize.min.js       # Materialize JS (local)
│       │   ├── app.core.js              # Funciones compartidas
│       │   ├── app.init.js              # Inicializacion
│       │   ├── app.tables.js            # Busqueda en tablas
│       │   ├── app.ui.js                # UI notificaciones
│       │   ├── app.pos.js               # Sistema POS
│       │   ├── app.cyber.js             # Estaciones cyber
│       │   └── app.legal.js             # Asesoria legal
│       └── fonts/
│           └── MaterialIcons-Regular.ttf # Material Icons (local)
├── docs/                                # Documentacion
└── vendor/                              # Composer dependencies
```

### 1.2 Flujo de Trabajo Global (Request → Response)

```
Navegador → /.htaccess → src/.htaccess → index.php → router.php
                                                         │
                                                session_start()
                                                         │
                                                $pagina = $_GET["pagina"]
                                                         │
                                                preg_match (seguridad)
                                                         │
                                               ┌──────────┴──────────┐
                                               ▼                     ▼
                                          ¿Requiere              Pagina no
                                          auth?                  existe
                                               │                     │
                                     ┌─────────┴─────────┐          ▼
                                     ▼                   ▼      Error 404
                                  ¿Sesion            Pagina
                                  activa?            publica
                                     │                   │
                            ┌────────┴────────┐          │
                            ▼                 ▼          │
                         Redirige         Cargar         │
                         a login          layout        │
                                           + vista       │
                                            │            │
                                            ▼            │
                                        Respuesta       │
                                        HTML             │
                                                          ▼
                                                     Vista directa
                                                     (standalone)
```

---

## 2. Modulo de Autenticacion

### 2.1 Archivos

| Archivo | Ruta | Funcion |
|---------|------|---------|
| `login.php` | `src/app/Views/login.php` | Formulario de inicio de sesion (134 lineas) |
| `login_validate.php` | `src/app/Views/login_validate.php` | Procesa credenciales (30 lineas) |

### 2.2 Diseño y Estructura

**Enrutador:** `router.php` determina si la pagina es publica y carga la vista correspondiente.

**Vistas:**
- `login.php`: standalone (sin layout), con Material Design, tema oscuro/claro
- `login_validate.php`: solo logica PHP, no produce HTML visible

### 2.3 Flujo de Trabajo

```
1. Usuario visita ?pagina=login
   → router.php: pagina publica, carga views/login.php
   → Renderiza formulario con Material Design

2. Usuario completa formulario y hace POST
   → router.php: pagina publica, carga login_validate.php
   → Extrae $_POST["username"] y $_POST["password"]
   → Valida contra credenciales hardcodeadas (admin/1234)
   → Si exito:
     • $_SESSION['logged_in'] = true
     • $_SESSION['username'] = 'admin'
     • Redirige a ?pagina=dashboard
   → Si falla:
     • Redirige a ?pagina=login&error=1

3. Cierre de sesion
   → Enlace "Cerrar Sesion" en sidebar
   → Redirige a ?pagina=login
```

### 2.4 Sesion

| Variable | Proposito |
|----------|-----------|
| `$_SESSION['logged_in']` | Indica si el usuario esta autenticado |
| `$_SESSION['username']` | Nombre del usuario para mostrar en UI |

**Paginas publicas** (no requieren autenticacion): `login`, `login_validate`

**Paginas protegidas**: `dashboard`, `inventario`, `ventas`, `proveedores`, `reportes`, `activos`, `ciberControl`, `asesorias`, `menu`, `usuarios`

---

## 3. Modulo Dashboard

| Archivo | Ruta |
|---------|------|
| `dashboard.php` | `src/app/Views/dashboard.php` (130 lineas) |
| JS asociado | `app.init.js`, `app.ui.js` |

**Secciones:**
1. Banner de bienvenida con gradiente
2. 4 metricas: Ventas Hoy, Stock Critico, Sesiones Cyber, Solicitudes Pend.
3. Tablas: Horas Pico y Productos Sin Stock
4. Actividad Reciente

**Estado**: UI Estatica (datos de ejemplo, sin conexion a BD).

---

## 4. Modulo Inventario

| Archivo | Ruta |
|---------|------|
| `inventario.php` | `src/app/Views/inventario.php` (129 lineas) |
| JS asociado | `app.tables.js` |

**Secciones:**
1. Barra de filtros: busqueda por texto + selector de estado + boton "Nuevo Producto"
2. Tabla de productos con columnas: ID, Producto, Precio, Stock, Minimo, Estado, Acciones
3. Paginacion

**JS**: `app.tables.js` proporciona busqueda con debounce (300ms), filtro por estado y paginacion.

**Estado**: UI Estatica con filtros funcionales (frontend).

---

## 5. Modulo Ventas (POS)

| Archivo | Ruta |
|---------|------|
| `ventas.php` | `src/app/Views/ventas.php` (130 lineas) |
| JS asociado | `app.pos.js` |

**Secciones:**
1. Catalogo de productos (grid de 5 productos)
2. Carrito de compras con modal Materialize

**JS**: `app.pos.js` implementa:
- Array `posCart` con objetos `{name, price}`
- Agregar producto al carrito
- Modal de carrito con total, eliminar y procesar
- Busqueda de productos con debounce 200ms

**Estado**: Semi-funcional (carrito frontend funcional, sin persistencia en BD).

---

## 6. Modulo Proveedores / Solicitudes

| Archivo | Ruta |
|---------|------|
| `proveedores.php` | `src/app/Views/proveedores.php` (115 lineas) |
| JS asociado | `app.tables.js` |

**Secciones:**
1. Barra de filtros: busqueda + selector de estado + boton "Nueva Solicitud"
2. Tabla de solicitudes con paginacion

**Estado**: UI Estatica.

---

## 7. Modulo Reportes

| Archivo | Ruta |
|---------|------|
| `reportes.php` | `src/app/Views/reportes.php` (139 lineas) |
| JS asociado | `app.ui.js` |

**Secciones:**
1. 4 metricas rapidas (Ventas del mes, Productos activos, Horas Cyber, Solicitudes)
2. Generador de reportes con formulario (tipo, fechas, formato)
3. Reportes recientes

**Estado**: Simulado (submit del formulario muestra toast, sin generacion real).

---

## 8. Modulo Activos Fijos

| Archivo | Ruta |
|---------|------|
| `activos.php` | `src/app/Views/activos.php` (207 lineas) |
| JS asociado | `app.tables.js` |

**Secciones:**
1. Barra de filtros con busqueda
2. Grid de tarjetas por tipo: Equipos (3), Licencias (2), Herramientas (4)
3. Resumen con totales

**Estado**: UI Estatica.

---

## 9. Modulo Cybercafe

| Archivo | Ruta |
|---------|------|
| `ciberControl.php` | `src/app/Views/ciberControl.php` (133 lineas) |
| JS asociado | `app.cyber.js` |

**Particularidad**: Los datos de estaciones se generan desde PHP con un array `$zonas` que define 3 zonas (Gamer, Estandar, VIP) con estaciones especificas. Los contadores se calculan con PHP nativo (`array_filter`, `array_merge`).

**JS**: `app.cyber.js` implementa:
- Toggle de estado entre disponible/ocupada con confirmacion
- Animacion de transicion con jQuery `.animate()`
- Filtro visual por estado (todas/disponible/ocupada/mantenimiento)

**Estado**: Interactivo (cambios temporales en frontend, sin persistencia en BD).

---

## 10. Modulo Asesoria Legal

| Archivo | Ruta |
|---------|------|
| `asesorias.php` | `src/app/Views/asesorias.php` (128 lineas) |
| JS asociado | `app.legal.js` |

**JS**: `app.legal.js` implementa:
- Catalogo de 11 tipos de documentos permitidos
- Validacion en tiempo real: boton cambia de color (indigo = permitido, rojo = derivar)
- Historial de asesorias registradas en la sesion (array en memoria)
- Busqueda en historial con debounce 300ms
- Eliminacion de registros

**Estado**: Semi-funcional (validacion frontend completa, sin persistencia en BD).

---

## 11. Offline y PWA

### Service Worker (`src/sw.js`)

```javascript
var CACHE_NAME = 'eis-cache-v1';
var STATIC_ASSETS = [
  'Public/css/material-icons.css',
  'Public/css/materialize.min.css',
  'Public/css/styles.css',
  'Public/css/login.css',
  'Public/js/jquery-3.7.1.min.js',
  'Public/js/materialize.min.js',
  'Public/js/app.core.js',
  'Public/js/app.init.js',
  'Public/js/app.tables.js',
  'Public/js/app.ui.js',
  'Public/js/app.pos.js',
  'Public/js/app.cyber.js',
  'Public/js/app.legal.js',
  'Public/fonts/MaterialIcons-Regular.ttf',
  'manifest.json',
  'offline.php'
];
```

**Estrategias de cache:**
- **Cache First** para assets estaticos (CSS, JS, fuentes, manifest)
- **Network First** con fallback a `offline.php` para navegacion PHP

### Assets Locales

Todos los recursos que antes se cargaban desde CDN ahora son locales:
- Materialize CSS/JS → `Public/css/` y `Public/js/`
- jQuery 3.7.1 → `Public/js/jquery-3.7.1.min.js`
- Material Icons → `Public/css/material-icons.css` + `Public/fonts/MaterialIcons-Regular.ttf`

### JavaScript Modular

El monolito original `app.js` se dividio en 7 archivos especializados:

| Archivo | Funcion | Carga |
|---------|---------|-------|
| `app.core.js` | EIS, debounce, filtrarTabla, toast | Siempre |
| `app.init.js` | Materialize init, reloj, tema, animaciones | Siempre |
| `app.tables.js` | Busqueda en tablas, filtros, paginacion | Siempre |
| `app.ui.js` | Notificaciones, botones, reportes, tooltips | Siempre |
| `app.pos.js` | Sistema POS (carrito) | Solo ventas |
| `app.cyber.js` | Estaciones cyber | Solo ciberControl |
| `app.legal.js` | Asesoria legal | Solo asesorias |

---

**Documentacion**: Junio 2026  
**Version**: 2.1

