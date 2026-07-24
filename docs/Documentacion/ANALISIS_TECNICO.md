# ANÁLISIS TÉCNICO — EIS System (Zona Web Lara)

**Arquitectura:** MVC con POO, Router OOP, Database Singleton, PDO estricto  
**Base de datos:** MySQL 8+ (InnoDB, utf8mb4_spanish_ci)  
**Frontend:** Materialize CSS 1.0.0 + jQuery 3.7.1 + JS modular  
**Namespace:** `App\Core`, `App\Models`, `App\Controllers` (PSR-4)  
**Seguridad:** CSRF tokens, XSS sanitización, session hardening, validación backend completa

---

## Índice de Módulos

1. [Arquitectura General](#1-arquitectura-general)
2. [Módulo de Autenticación](#2-módulo-de-autenticación)
3. [Módulo Dashboard](#3-módulo-dashboard)
4. [Módulo Inventario](#4-módulo-inventario)
5. [Módulo Ventas (POS)](#5-módulo-ventas-pos)
6. [Módulo Proveedores / Solicitudes](#6-módulo-proveedores--solicitudes)
7. [Módulo Clientes](#7-módulo-clientes)
8. [Módulo Reportes](#8-módulo-reportes)
9. [Módulo Activos Fijos](#9-módulo-activos-fijos)
10. [Módulo Cybercafé](#10-módulo-cybercafé)

---

## 1. Arquitectura General

### 1.1 Estructura de Directorios

```
eis_zona_web_lara/
├── composer.json                        # PSR-4 autoloading ("App\\": "src/app/")
├── src/                                 # Document root
│   ├── .htaccess                        # Apache Rewrite Rules (URLs limpias)
│   ├── index.php                        # Front Controller (autoloader + Router OOP)
│   ├── manifest.json                    # Manifiesto PWA
│   ├── sw.js                            # Service Worker
│   ├── offline.php                      # Página offline fallback
│   ├── Config/
│   │   └── database.php                 # Conexión PDO (legacy)
│   ├── Database/
│   │   ├── estructura.sql               # Esquema completo (27 tablas, v3.0)
│   │   ├── seed_data.sql                # Datos de prueba
│   │   └── seed_data_masivo.sql         # Datos masivos de prueba
│   ├── app/
│   │   ├── core/
│   │   │   ├── Database.php             # Conexión PDO Singleton (moderna)
│   │   │   ├── Model.php                # Clase base abstracta para modelos
│   │   │   └── router.php               # Enrutador OOP (clase Router, 385 líneas)
│   │   ├── Controllers/
│   │   │   ├── AuthController.php       # Login/logout con sesiones + CSRF + session_regenerate_id
│   │   │   ├── ClienteController.php    # CRUD clientes AJAX
│   │   │   ├── inventarioController.php # CRUD inventario AJAX
│   │   │   ├── RolController.php        # CRUD roles/permisos AJAX
│   │   │   ├── ProveedorController.php  # CRUD proveedores AJAX (solicitudes)
│   │   │   └── ProveedorGestionController.php # CRUD proveedores AJAX (gestión)
│   │   ├── Models/
│   │   │   ├── Cliente.php              # Modelo POO clientes
│   │   │   ├── Inventario.php           # Modelo POO inventario (namespace)
│   │   │   ├── Usuario.php              # Modelo POO usuarios
│   │   │   ├── Proveedor.php            # Modelo POO proveedores (solicitudes)
│   │   │   ├── ProveedorGestion.php     # Modelo POO proveedores (gestión)
│   │   │   ├── Rol.php                  # Modelo POO roles/permisos
│   │   │   ├── Asesoria.php             # Modelo POO asesorías
│   │   │   ├── crud_users.php           # CRUD usuarios legacy
│   │   │   └── crud_asesorias.php       # CRUD asesorías legacy
│   │   ├── template/
│   │   │   └── layout.php               # Layout maestro (12 módulos, 10 JS)
│   │   └── Views/
│   │       ├── login.php                # Autenticación
│   │       ├── login_validate.php       # Validación de credenciales (legacy)
│   │       ├── dashboard.php            # Panel principal
│   │       ├── menu.php                 # Menú de navegación
│   │       ├── inventario.php           # Gestión de inventario (conectado a BD)
│   │       ├── ventas.php               # Punto de venta (POS)
│   │       ├── proveedores.php          # Solicitudes a proveedores (conectado a BD)
│   │       ├── clientes.php             # Gestión de clientes (conectado a BD)
│   │       ├── reportes.php             # Reportes y estadísticas
│   │       ├── activos.php              # Activos fijos
│   │       ├── ciberControl.php         # Control de cybercafé
│   │       ├── asesorias.php            # Asesoría legal
│   │       ├── usuarios.php             # Gestión de usuarios (conectado a BD)
│   │       └── roles.php                # Roles y permisos (conectado a BD)
│   └── Public/
│       ├── css/
│       │   ├── styles.css               # Estilos generales
│       │   ├── login.css                # Estilos de login
│       │   ├── materialize.min.css      # Materialize CSS (local)
│       │   └── material-icons.css       # Material Icons (local)
│       ├── js/
│       │   ├── jquery-3.7.1.min.js      # jQuery (local)
│       │   ├── materialize.min.js       # Materialize JS (local)
│       │   ├── app.core.js              # Utilidades compartidas
│       │   ├── app.init.js              # Inicialización Materialize
│       │   ├── app.tables.js            # Búsqueda y filtros
│       │   ├── app.ui.js                # UI notificaciones
│       │   ├── app.pos.js               # Sistema POS
│       │   ├── app.cyber.js             # Estaciones cyber
│       │   ├── app.legal.js             # Asesoría legal
│       │   ├── app.inventario.js        # CRUD inventario AJAX
│       │   ├── app.roles.js             # CRUD roles AJAX
│       │   └── app.proveedores.js       # CRUD proveedores AJAX
│       └── fonts/
│           └── MaterialIcons-Regular.ttf # Material Icons (local)
├── docs/
│   └── Documentacion/                   # Documentación detallada
└── vendor/                              # Composer dependencies
```

### 1.2 Patrón MVC

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| **Model** | `src/app/Models/*.php` (7 POO con namespace + 2 legacy) | Lógica de negocio, acceso a datos con PDO prepared statements, validación con helpers reutilizables |
| **View** | `src/app/Views/*.php` | Presentación HTML, datos del modelo |
| **Controller** | `src/app/Controllers/*.php` (6 clases) | Orquestación: recibe request AJAX, valida datos, llama a modelos, retorna JSON |
| **Core** | `src/app/core/` (Database.php, Model.php, router.php) | Conexión Singleton, clase base con helpers de validación, enrutamiento OOP con CSRF |
| **Config** | `src/Config/database.php` | Conexión PDO legacy |

### 1.3 Principios PDO Estricto

```
PDO::ATTR_ERRMODE            → PDO::ERRMODE_EXCEPTION
PDO::ATTR_DEFAULT_FETCH_MODE → PDO::FETCH_ASSOC
PDO::ATTR_EMULATE_PREPARES   → false  (prepared statements reales)
```

- Toda consulta parametrizada usa `prepare()` + `execute()` con placeholders `?`
- Las transacciones multi-tabla usan `beginTransaction()`, `commit()`, `rollback()`
- Las excepciones PDO se capturan para manejo centralizado de errores

### 1.4 Flujo de Trabajo Global (Request → Response)

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐     ┌──────────────┐
│  Navegador  │ ──→ │  .htaccess   │ ──→ │  index.php  │ ──→ │  router.php  │
└─────────────┘     └──────────────┘     └─────────────┘     └──────────────┘
                                                                    │
                                                                    ▼
                                                           ┌─────────────────┐
                                                           │  ¿Requiere      │
                                                           │  autenticación? │
                                                           └───────┬─────────┘
                                                              Sí  │   No
                                                              ▼   ▼
                                                    ┌─────────────────────┐
                                                    │  Validar sesión     │
                                                    │  (redirect si no)   │
                                                    └──────────┬──────────┘
                                                               ▼
                                                     ┌───────────────────┐
                                                     │  Cargar Vista     │
                                                     │  require_once     │
                                                     └───────────────────┘
                                                               │
                                                               ▼
                                                     ┌───────────────────┐
                                                     │  Renderizar HTML  │
                                                     │  → Navegador      │
                                                     └───────────────────┘
```

---

## 2. Módulo de Autenticación

### 2.1 Archivos

| Archivo | Ruta | Función |
|---------|------|---------|
| `login.php` | `src/app/Views/login.php` | Formulario de inicio de sesión |
| `login_validate.php` | `src/app/Views/login_validate.php` | Procesa credenciales y gestiona sesión (legacy) |
| `AuthController.php` | `src/app/Controllers/AuthController.php` | Login/logout con sesiones + CSRF + session_regenerate_id |

### 2.2 Diseño y Estructura

**Controlador:** `AuthController.php` — maneja login/logout con CSRF y session hardening

**Modelos relacionados:**
- `Usuario.php` → `validarUsuario()` — validación de credenciales con prepared statements
- `crud_users.php` → funciones legacy `crearUsuario()`, `obtenerUsuarios()`, etc.

**Vistas:**
- `login.php`: standalone (sin sidebar), con glassmorphism, tema oscuro/claro, campo CSRF oculto
- `login_validate.php`: lógica de validación POST, gestión de sesión (legacy)

**Seguridad implementada:**
- CSRF token: `bin2hex(random_bytes(32))` en `Router::__construct()`, inyectado en `window.EIS.csrfToken` y en `<input name="csrf_token">`
- `session_regenerate_id(true)` en login exitoso
- `session_regenerate_id(true)` en logout
- Prepared statements con `PDO::ATTR_EMULATE_PREPARES => false`
- Sin `echo` de debug en respuestas JSON
- Credenciales en constantes (pendiente migración a .env)

### 2.3 Flujo de Trabajo

```
┌──────────────────────────────────────────────────────────────────┐
│                        FLUJO DE LOGIN                           │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. Usuario visita ?pagina=login                                 │
│     → router.php detecta página pública                          │
│     → Carga views/login.php                                      │
│     → Renderiza formulario con:                                  │
│       • Campo username + password                                │
│       • Tema oscuro/claro (localStorage)                         │
│       • Botones sociales (Google/GitHub)                         │
│       • Mensaje de error si ?error=1                             │
│                                                                  │
│  2. Usuario completa formulario y hace POST                      │
│     → router.php carga login_validate.php                        │
│     → Extrae $_POST["username"] y $_POST["password"]             │
│     → Valida contra credenciales del sistema                     │
│     → Si éxito:                                                  │
│       • $_SESSION['logged_in'] = true                            │
│       • $_SESSION['username'] = $username                        │
│       • Redirige a ?pagina=dashboard                             │
│     → Si falla:                                                  │
│       • Redirige a ?pagina=login&error=1                         │
│                                                                  │
│  3. Cierre de sesión                                             │
│     → Enlace "Cerrar Sesión" en sidebar                          │
│     → Redirige a ?pagina=login                                   │
│     → session sin destruir (pendiente de implementar)            │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 2.4 Elementos de la UI

```
┌──────────────────────────────────────┐
│            ⚡ EIS SYSTEM              │
│   Ingresa tus credenciales           │
│                                      │
│   ┌──────────────────────────────┐   │
│   │ Usuario                      │   │
│   │ [_________________________]  │   │
│   └──────────────────────────────┘   │
│                                      │
│   ┌──────────────────────────────┐   │
│   │ Contraseña                   │   │
│   │ [_________________________]  │   │
│   └──────────────────────────────┘   │
│                                      │
│   ¿Olvidaste tu contraseña?          │
│                                      │
│   ┌──────────────────────────────┐   │
│   │  🚀 Iniciar Sesión           │   │
│   └──────────────────────────────┘   │
│                                      │
│   ─── O continúa con ───            │
│   [G] [GitHub]                       │
│                                      │
│   ¿No tienes una cuenta? Regístrate  │
└──────────────────────────────────────┘
```

### 2.5 Rutas

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `?pagina=login` | Muestra formulario de login |
| POST | `?pagina=login_validate` | Procesa credenciales |

### 2.6 Consultas PDO (Modelo crud.php)

```php
// Crear usuario
$sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nombre, $email]);

// Obtener todos
$stmt = $pdo->query("SELECT * FROM usuarios");

// Obtener por ID
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);

// Actualizar
$sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nombre, $email, $id]);

// Eliminar
$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
```

### 2.7 Sesión

| Variable | Propósito |
|----------|-----------|
| `$_SESSION['logged_in']` | Indica si el usuario está autenticado |
| `$_SESSION['username']` | Nombre del usuario para mostrar en UI |

**Páginas públicas** (no requieren autenticación):
- `login`
- `login_validate`

**Páginas protegidas** (requieren `$_SESSION['logged_in']`):
- `dashboard`, `inventario`, `ventas`, `proveedores`, `reportes`, `activos`, `ciberControl`, `menu`

---

## 3. Módulo Dashboard

### 3.1 Archivo

| Archivo | Ruta |
|---------|------|
| `dashboard.php` | `src/app/Views/dashboard.php` |

### 3.2 Diseño y Estructura

**Layout:** Sidebar (izquierda) + Main Content (derecha)

**Secciones:**
1. **Banner de bienvenida** — gradiente primary → secondary
2. **Métricas (grid-4)** — 4 tarjetas con indicadores clave
3. **Tablas (grid-2)** — Horas pico + Productos sin stock
4. **Actividad reciente** — Feed de eventos

**Modelos involucrados:**
- `crud.php` → `obtenerUsuarios()` para obtener datos del usuario actual
- Queries PDO para métricas (ventas, stock, sesiones cyber, solicitudes)

### 3.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Panel de Control                      🌙  👤 Admin      │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │  ¡Bienvenido de nuevo!                                 │  │
│  │  Gestiona tu negocio de manera eficiente con EIS System │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ 💰       │  │ ⚠️       │  │ 🖥️       │  │ 📋       │    │
│  │ Ventas   │  │ Stock    │  │ Sesiones │  │ Solic.   │    │
│  │ Hoy      │  │ Crítico  │  │ Cyber    │  │ Pend.    │    │
│  │ $1,245.50│  │ 4        │  │ 7        │  │ 3        │    │
│  │ ↗23 trans│  │ Bajo mín │  │ 45min prom│  │ Ctas pag │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│                                                               │
│  ┌────────────────────┐  ┌────────────────────┐              │
│  │ 🕒 Horas Pico      │  │ 📦 Sin Stock       │              │
│  │────────────────────│  │────────────────────│              │
│  │ 10-11am │ 42  ↑12% │  │ Resma A4   │ 0    │              │
│  │ 2-3pm   │ 38  ↑8%  │  │ Tóner Negro│ 0    │              │
│  │ 6-7pm   │ 31  ↓5%  │  │ Cable USB-C│ 0    │              │
│  └────────────────────┘  └────────────────────┘              │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 📋 Actividad Reciente                                  │  │
│  │ 🛒 Venta #V-00142 procesada        — hace 5 min       │  │
│  │ 📦 Stock: Mouse Inalámbrico        — hace 15 min      │  │
│  │ 🖥️ Nueva sesión Cyber #5          — hace 30 min      │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

### 3.4 Flujo de Trabajo

```
1. Usuario autenticado visita ?pagina=dashboard
2. router.php verifica $_SESSION['logged_in']
3. Carga views/dashboard.php
4. La vista obtiene datos (actualmente estáticos) que provendrán de:
   → Consulta PDO: SELECT COUNT(*), SUM(total) FROM ventas WHERE DATE(fecha)=CURDATE()
   → Consulta PDO: SELECT COUNT(*) FROM productos WHERE stock <= stock_minimo
   → Consulta PDO: SELECT COUNT(*) FROM sesiones_cyber WHERE hora_fin IS NULL
   → Consulta PDO: SELECT COUNT(*) FROM solicitudes WHERE estado='Pendiente'
   → Consulta PDO: horas pico con GROUP BY HOUR(fecha)
   → Consulta PDO: productos con stock = 0
5. Renderiza el dashboard completo
```

### 3.5 Datasheet de Métricas

| Métrica | Descripción | Tipo | Color |
|---------|-------------|------|-------|
| Ventas Hoy | Total $ y # transacciones del día | primary | Azul índigo |
| Stock Crítico | Productos por debajo del mínimo | danger | Rojo |
| Sesiones Cyber | Estaciones ocupadas actualmente | warning | Ámbar |
| Solicitudes Pend. | Solicitudes a proveedores sin resolver | info | Azul |

---

## 4. Módulo Inventario

### 4.1 Archivo

| Archivo | Ruta |
|---------|------|
| `inventario.php` | `src/app/Views/inventario.php` |

### 4.2 Diseño y Estructura

**Layout:** Sidebar + Main Content

**Secciones:**
1. **Barra de filtros** — búsqueda por texto + selector de estado + botón "Nuevo Producto"
2. **Tabla de productos** — columnas: ID, Producto, Precio, Stock, Mínimo, Estado, Acciones
3. **Paginación** — controles Anterior/1/2/3/Siguiente

**Modelo involucrado:** `crud.php` (funciones de lectura/escritura con PDO)

### 4.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Gestión de Inventario                  🌙  👤 Admin      │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 🔍 [Buscar producto...       ] [Todos los estados ▼]  │  │
│  │                                    [+ Nuevo Producto]  │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 📦 Lista de Productos           Mostrando 2 de 45     │  │
│  ├──────┬──────────────┬───────┬──────┬──────┬──────┬───┤  │
│  │ ID   │ Producto     │ Precio│ Stock│ Mín  │ Est. │ 🔧│  │
│  ├──────┼──────────────┼───────┼──────┼──────┼──────┼───┤  │
│  │#1042 │ Mouse Inalámb│ $12.50│   5  │  10  │Críti.│ 📦✏️│  │
│  │#1043 │ Monitor 24"  │ $189  │  24  │   5  │ OK   │ 📦✏️│  │
│  │#1044 │ Teclado Mec. │ $45.00│   8  │  10  │ Bajo │ 📦✏️│  │
│  └──────┴──────────────┴───────┴──────┴──────┴──────┴───┘  │
│                                                               │
│   Mostrando 1-3 de 45    [← Ant] [1] [2] [3] [Sig →]        │
└──────────────────────────────────────────────────────────────┘
```

### 4.4 Flujo de Trabajo

```
1. Usuario visita ?pagina=inventario
2. router.php verifica autenticación
3. Carga views/inventario.php
4. Filtros disponibles:
   → Búsqueda por nombre o código
   → Filtro por estado: Todos / Stock OK / Crítico / Sin stock
5. Acciones por producto:
   → 📦 (Ver movimientos): abre historial de movimientos de stock
   → ✏️ (Editar): abre formulario de edición del producto
6. Paginación:
   → Navegación entre páginas de resultados
   → Cada página muestra un lote de productos desde DB
7. Botón "+ Nuevo Producto": abre formulario de creación

Consultas PDO asociadas:
   SELECT * FROM productos 
   WHERE (nombre LIKE ? OR codigo LIKE ?) AND estado = ?
   ORDER BY created_at DESC LIMIT ? OFFSET ?
```

### 4.5 Tabla de Estados de Producto

| Estado | Badge | Condición |
|--------|-------|-----------|
| OK | `badge-success` (verde) | `stock >= stock_minimo` |
| Bajo | `badge-warning` (ámbar) | `stock > 0 AND stock < stock_minimo` |
| Crítico | `badge-danger` (rojo) | `stock <= stock_minimo * 0.5` (ejemplo) |
| Sin stock | `badge-danger` (rojo) | `stock = 0` |

---

## 5. Módulo Ventas (POS)

### 5.1 Archivo

| Archivo | Ruta |
|---------|------|
| `ventas.php` | `src/app/Views/ventas.php` |

### 5.2 Diseño y Estructura

**Layout:** Sidebar + Main Content con layout de 2 columnas (POS)

**Secciones:**
1. **Catálogo de productos** (izquierda) — grid de tarjetas de producto con precio
2. **Carrito de compras** (derecha) — sticky, lista de items + total + botón procesar

**JavaScript funcional:**
- `cart[]` — array de objetos `{name, price}`
- `posAddItem(name, price)` — agrega al carrito
- `updateCart()` — renderiza carrito y actualiza total
- `removeItem(index)` — elimina del carrito

### 5.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Punto de Venta (POS)                   🌙  👤 Admin      │
├──────────────────────────────────────────────────────────────┤
│  ┌───────────────────────────┐  ┌─────────────────────────┐  │
│  │ 🛍️ Catálogo [Buscar...]  │  │ 🧾 Carrito Actual       │  │
│  │───────────────────────────│  │─────────────────────────│  │
│  │ ┌─────┐ ┌─────┐ ┌─────┐  │  │ Teclado Mec.    ×$45.00│  │
│  │ │⌨️   │ │🖱️   │ │🎧   │  │  │ Mouse USB       ×$12.50│  │
│  │ │Tecla.│ │Mouse │ │Auri.│  │  │                         │  │
│  │ │$45   │ │$12.5│ │$35  │  │  │                         │  │
│  │ └─────┘ └─────┘ └─────┘  │  │─────────────────────────│  │
│  │ ┌─────┐ ┌─────┐          │  │ Total:          $57.50   │  │
│  │ │🖥️   │ │🔌   │          │  │                         │  │
│  │ │Monitor│ │Cable │          │  │ [💵 Procesar Venta]   │  │
│  │ │$189  │ │$8   │          │  │                         │  │
│  │ └─────┘ └─────┘          │  └─────────────────────────┘  │
│  └───────────────────────────┘                               │
└──────────────────────────────────────────────────────────────┘
```

### 5.4 Flujo de Trabajo

```
1. Usuario visita ?pagina=ventas
2. router.php verifica autenticación
3. Carga views/ventas.php
4. Interacción del usuario:

   ┌─────────────────────────────────────────────────────────┐
   │  a. Usuario hace clic en producto del catálogo          │
   │     → posAddItem("Mouse USB", 12.50)                   │
   │     → cart.push({name:"Mouse USB", price:12.50})       │
   │     → updateCart()                                     │
   │                                                         │
   │  b. Producto aparece en carrito                         │
   │     → Se renderiza con nombre y precio                  │
   │     → Botón × para eliminar                             │
   │     → Total se actualiza: total += price                │
   │                                                         │
   │  c. Usuario hace clic en "Procesar Venta"               │
   │     → Valida que hay items en carrito                   │
   │     → Envía datos al backend vía POST/fetch             │
   │     → Backend procesa con transacción PDO:              │
   │        • INSERT INTO ventas                             │
   │        • INSERT INTO detalle_ventas (por cada item)     │
   │        • UPDATE productos SET stock = stock - cantidad  │
   │        • INSERT INTO movimientos_stock                  │
   │        • commit() / rollback()                          │
   │     → Responde con éxito o error                        │
   │     → Si éxito: limpia carrito, muestra confirmación    │
   │                                                         │
   └─────────────────────────────────────────────────────────┘
```

### 5.5 Estructura de Datos del Carrito

```javascript
// Cada item en el carrito
{
    name: "Mouse USB",          // Nombre del producto
    price: 12.50                // Precio unitario
}

// Estado del carrito
let cart = [item1, item2, ...]; // Array de items
let total = 0;                   // Suma de precios
```

### 5.6 Productos del Catálogo

| Producto | Precio | Emoji |
|----------|--------|-------|
| Teclado Mecánico | $45.00 | ⌨️ |
| Mouse USB | $12.50 | 🖱️ |
| Auriculares | $35.00 | 🎧 |
| Monitor 24" | $189.00 | 🖥️ |
| Cable USB-C | $8.00 | 🔌 |

---

## 6. Módulo Proveedores / Solicitudes

### 6.1 Archivo

| Archivo | Ruta |
|---------|------|
| `proveedores.php` | `src/app/Views/proveedores.php` |

### 6.2 Diseño y Estructura

**Layout:** Sidebar + Main Content

**Secciones:**
1. **Barra de filtros** — búsqueda por proveedor/ID + selector de estado + botón "Nueva Solicitud"
2. **Tabla de solicitudes** — columnas: ID, Proveedor, Fecha, Estado, Acciones
3. **Paginación** — controles de navegación

### 6.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Solicitudes a Proveedores               🌙  👤 Admin     │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 🔍 [Buscar por proveedor o ID...]  [Todos los est. ▼] │  │
│  │                                    [+ Nueva Solicitud] │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 📝 Lista de Solicitudes          Mostrando 3 de 28    │  │
│  ├──────────┬─────────────────┬──────────┬────────┬──────┤  │
│  │ ID       │ Proveedor       │ Fecha    │ Estado │ Acc. │  │
│  ├──────────┼─────────────────┼──────────┼────────┼──────┤  │
│  │#SOL-089  │ TechSupplies S.A│2024-04-10│Pend.   │ 👁️  │  │
│  │#SOL-088  │ GlobalParts Inc.│2024-04-08│Recib.  │ 👁️  │  │
│  │#SOL-087  │ OfficeMax Corp. │2024-04-05│Cancel. │ 👁️  │  │
│  └──────────┴─────────────────┴──────────┴────────┴──────┘  │
│                                                               │
│   Mostrando 1-3 de 28    [← Ant] [1] [2] [3] [Sig →]        │
└──────────────────────────────────────────────────────────────┘
```

### 6.4 Flujo de Trabajo

```
1. Usuario visita ?pagina=proveedores
2. router.php verifica autenticación
3. Carga views/proveedores.php
4. Filtros disponibles:
   → Búsqueda por nombre de proveedor o ID de solicitud
   → Filtro por estado: Todos / Pendiente / Recibida / Cancelada
5. Acciones:
   → 👁️ (Ver): abre detalle completo de la solicitud
   → "+ Nueva Solicitud": abre formulario de creación
6. Paginación entre resultados

Consultas PDO asociadas:
   SELECT s.*, p.nombre as proveedor_nombre 
   FROM solicitudes s 
   JOIN proveedores p ON s.proveedor_id = p.id
   WHERE s.estado = ? 
   ORDER BY s.fecha DESC 
   LIMIT ? OFFSET ?
```

### 6.5 Estados de Solicitud

| Estado | Badge | Significado |
|--------|-------|-------------|
| Pendiente | `badge-warning` (ámbar) | Solicitud creada, no recibida |
| Recibida | `badge-success` (verde) | Productos recibidos |
| Cancelada | `badge-gray` (gris) | Solicitud anulada |

---

## 7. Módulo Clientes

### 7.1 Archivos

| Archivo | Ruta | Función |
|---------|------|---------|
| `clientes.php` | `src/app/Views/clientes.php` | Vista de gestión de clientes |
| `ClienteController.php` | `src/app/Controllers/ClienteController.php` | Controlador AJAX CRUD |
| `Cliente.php` | `src/app/Models/Cliente.php` | Modelo POO con validación |

### 7.2 Diseño y Estructura

**Layout:** Sidebar + Main Content

**Secciones:**
1. **Barra de filtros** — búsqueda por nombre/cédula + botón "Nuevo Cliente"
2. **Tabla de clientes** — columnas: ID, Cédula, Nombre, Apellido, Dirección, Teléfono, Acciones
3. **Paginación** — controles de navegación

### 7.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Gestión de Clientes                    🌙  👤 Admin      │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 🔍 [Buscar por nombre o cédula...]  [+ Nuevo Cliente] │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 👤 Lista de Clientes              Mostrando 3 de 45   │  │
│  ├──────┬──────────┬────────┬─────────┬──────────┬────────┤  │
│  │ ID   │ Cédula   │ Nombre │ Apellido│ Direcc.  │ Acc.   │  │
│  ├──────┼──────────┼────────┼─────────┼──────────┼────────┤  │
│  │ #1   │ V-12345  │ Carlos │ García  │ Calle 5  │ ✏️ 🗑️ │  │
│  │ #2   │ V-67890  │ María  │ López   │ Av. 8    │ ✏️ 🗑️ │  │
│  │ #3   │ V-11111  │ Pedro  │ Martínez│ Urb. 3   │ ✏️ 🗑️ │  │
│  └──────┴──────────┴────────┴─────────┴──────────┴────────┘  │
│                                                               │
│   Mostrando 1-3 de 45    [← Ant] [1] [2] [3] [Sig →]        │
└──────────────────────────────────────────────────────────────┘
```

### 7.4 Validación Backend

| Campo | Regla | Mensaje |
|-------|-------|---------|
| cedula | No vacío, mín. 5 caracteres, formato V-XXXXX o E-XXXXX | "La cédula debe tener al menos 5 caracteres" |
| nombre | No vacío, mín. 2 caracteres | "El nombre debe tener al menos 2 caracteres" |
| apellido | No vacío, mín. 2 caracteres | "El apellido debe tener al menos 2 caracteres" |
| direccion | No vacío | "La dirección es obligatoria" |
| telefono | No vacío | "El teléfono es obligatorio" |

**Verificaciones adicionales:**
- `existeCedula(excludeId)` — verifica unicidad de cédula excluyendo registro actual en edición

### 7.5 Flujo de Trabajo

```
1. Usuario visita ?pagina=clientes
2. router.php verifica autenticación
3. Carga views/clientes.php
4. JS: app.proveedores.js maneja eventos del módulo
5. AJAX: CRUD completo vía ClienteController
6. Backend: Cliente.php valida todos los campos antes de DB
7. Respuesta: JSON con éxito/error, frontend muestra toast
```

### 7.6 Modelo de Datos

```
┌─────────────────────────┐
│        clientes         │
├─────────────────────────┤
│ id (PK)                 │
│ cedula (VARCHAR, UNQ)   │
│ nombre (VARCHAR)        │
│ apellido (VARCHAR)      │
│ direccion (VARCHAR)     │
│ telefono (VARCHAR)      │
│ created_at              │
│ updated_at              │
└─────────────────────────┘
```

**Relaciones:**
- `cliente_asesoria.cliente_id` → `clientes.id`
- `orden_de_venta.cliente_id` → `clientes.id`
- `sesion_ciber.cliente_id` → `clientes.id`

---

## 8. Módulo Reportes

### 7.1 Archivo

| Archivo | Ruta |
|---------|------|
| `reportes.php` | `src/app/Views/reportes.php` |

### 7.2 Diseño y Estructura

**Layout:** Sidebar + Main Content

**Secciones:**
1. **Métricas rápidas (grid-4)** — Ventas del mes, Productos activos, Horas Cyber, Solicitudes
2. **Generador de reportes** — formulario con tipo, fechas y formato de salida
3. **Reportes recientes** — lista de reportes generados con botón de descarga

### 7.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Reportes y Estadísticas                🌙  👤 Admin      │
├──────────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ 💰       │  │ 📦       │  │ 🖥️       │  │ 📋       │    │
│  │ Ventas   │  │ Prod.    │  │ Horas    │  │ Solic.   │    │
│  │ del Mes  │  │ Activos  │  │ Cyber    │  │          │    │
│  │ $34,580  │  │ 245      │  │ 1,240    │  │ 28       │    │
│  │ ↗12%     │  │ Inv.     │  │ Este mes │  │ Proc.    │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│                                                               │
│  ┌────────────────────────┐  ┌────────────────────────┐     │
│  │ 📈 Generador Reportes  │  │ 📊 Reportes Recientes  │     │
│  │                        │  │                        │     │
│  │ Tipo: [Ventas por fecha│  │ 📈 Ventas - Abril 2024 │     │
│  │       ▼               │  │    Hoy 10:30AM    [⬇️]  │     │
│  │                        │  │                        │     │
│  │ Fecha Inicio: [____]   │  │ 📦 Inventario Actual  │     │
│  │ Fecha Fin:    [____]   │  │    Ayer 3:15PM   [⬇️]  │     │
│  │                        │  │                        │     │
│  │ Formato:               │  │ 🖥️ Horas Cyber - Marzo │     │
│  │ ○ PDF  ○ Excel  ○ CSV  │  │    Hace 2 días   [⬇️]  │     │
│  │                        │  │                        │     │
│  │ [🔍 Generar Reporte]   │  │ 📋 Solicitudes Q1     │     │
│  └────────────────────────┘  │    Hace 5 días   [⬇️]  │     │
│                              └────────────────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

### 7.4 Flujo de Trabajo

```
1. Usuario visita ?pagina=reportes
2. router.php verifica autenticación
3. Carga views/reportes.php
4. Las métricas se cargan con consultas agregadas:
   → Ventas del mes: SUM(total) FROM ventas WHERE MONTH(fecha)=MONTH(NOW())
   → Productos activos: COUNT(*) FROM productos WHERE estado_venta='Activo'
   → Horas cyber: SUM(duracion_minutos) FROM sesiones_cyber WHERE MONTH(...)
   → Solicitudes: COUNT(*) FROM solicitudes WHERE MONTH(...)

5. Generación de reporte:
   a. Usuario selecciona:
      • Tipo de reporte (Ventas, Inventario, Movimientos, Solicitudes, Cyber)
      • Rango de fechas (inicio - fin)
      • Formato de salida (PDF, Excel, CSV)
   b. Envía formulario
   c. Backend procesa:
      • Consulta DB con filtros de fechas
      • Genera archivo en formato seleccionado
      • Ofrece descarga al usuario
```

### 7.5 Tipos de Reporte

| Tipo | Descripción | Tabla(s) involucrada(s) |
|------|-------------|------------------------|
| Ventas por fecha | Transacciones en rango | `ventas`, `detalle_ventas` |
| Estado de inventario | Productos con stock | `productos` |
| Movimientos de stock | Auditoría de movimientos | `movimientos_stock` |
| Solicitudes a proveedores | Compras realizadas | `solicitudes`, `proveedores` |
| Horas Cybercafé | Sesiones y costos | `sesiones_cyber`, `estaciones_cyber` |

---

## 9. Módulo Activos Fijos

### 9.1 Archivo

| Archivo | Ruta |
|---------|------|
| `activos.php` | `src/app/Views/activos.php` |

### 9.2 Diseño y Estructura

**Layout:** Sidebar + Main Content

**Secciones:**
1. **Barra de filtros** — búsqueda + filtro por categoría + botón "Nuevo Activo"
2. **Grid de tarjetas por tipo (grid-2):**
   - Equipos (3 items)
   - Licencias (2 items)
   - Herramientas (2 items)
   - Resumen (totales)
3. Cada tarjeta contiene tabla con items y botón de acción

### 9.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Gestión de Activos                      🌙  👤 Admin     │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 🔍 [Buscar activo...            ] [Todos ▼]           │  │
│  │                                    [+ Nuevo Activo]   │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌──────────────────────────┐  ┌──────────────────────────┐  │
│  │ 🖨️ Equipos (3)          │  │ 🔑 Licencias (2)        │  │
│  │ [Ver todos]              │  │ [Ver todos]              │  │
│  │──────────────────────────│  │──────────────────────────│  │
│  │ Impresora HP   │ Activo  │  │ Windows 11 Pro│ Vencida │  │
│  │ Proyector Epson│ Mant.   │  │ Office 365    │ Activa  │  │
│  │ Router Cisco   │ Activo  │  │              │         │  │
│  └──────────────────────────┘  └──────────────────────────┘  │
│                                                               │
│  ┌──────────────────────────┐  ┌──────────────────────────┐  │
│  │ 🔧 Herramientas (2)     │  │ 📊 Resumen               │  │
│  │ [Ver todos]              │  │                          │  │
│  │──────────────────────────│  │ 🟢 Activos Totales: 9   │  │
│  │ Kit Destornill. │ Dispon.│  │ 🔵 En Mantenimiento: 1  │  │
│  │ Multímetro      │ Dispon.│  │ 🔴 Requieren Atenc.: 1  │  │
│  └──────────────────────────┘  └──────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

### 9.4 Flujo de Trabajo

```
1. Usuario visita ?pagina=activos
2. router.php verifica autenticación
3. Carga views/activos.php
4. Los activos se agrupan por tipo (Equipos, Herramientas, Licencias)
5. Filtros:
   → Búsqueda por nombre
   → Filtro por tipo de activo
6. Acciones por activo:
   → ✏️ (Editar): modificar datos del activo
   → 🔄 (Renovar): para licencias próximas a vencer
   → 👁️ (Ver detalles): información completa
7. Resumen muestra totales calculados:
   → Conteo por estado (Activo, Mantenimiento, Vencida)

Consultas PDO asociadas:
   SELECT * FROM activos WHERE tipo = ? ORDER BY nombre
   SELECT COUNT(*), 
          SUM(estado = 'Activo') as activos,
          SUM(estado = 'Mantenimiento') as mantenimiento,
          SUM(estado = 'Vencida') as vencidas
   FROM activos
```

### 9.5 Categorías de Activos

| Tipo | Ejemplos | Estados posibles |
|------|----------|------------------|
| Equipos | Impresoras, proyectores, routers | Activo, Mantenimiento |
| Herramientas | Kits, multímetros | Disponible (Activo) |
| Licencias | Windows, Office | Activa, Vencida |

---

## 10. Módulo Cybercafé

### 10.1 Archivo

| Archivo | Ruta |
|---------|------|
| `ciberControl.php` | `src/app/Views/ciberControl.php` |

### 10.2 Diseño y Estructura

**Layout:** Sidebar + Main Content

**Secciones:**
1. **Barra de acciones** — botones "Nueva Estación", "Historial Sesiones", filtros de estado
2. **Grid de estaciones (grid-cyber)** — 10 tarjetas con estado visual

**JavaScript interactivo:**
- `toggleStation(element)` — cambia estado entre Disponible/Ocupada con confirmación
- `filterStations(filter)` — muestra/oculta estaciones por data-status

### 10.3 Esquema de la Interfaz

```
┌──────────────────────────────────────────────────────────────┐
│  ☰ Control de Cybercafé     [7🟢] [3🟡]   🌙  👤 Admin    │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [+ Nueva Estación] [📜 Historial]                     │  │
│  │ [🟢Disponibles] [🟡Ocupadas] [🔴Manten.] [◻️Todas]  │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐         │
│  │ 🟢   │  │ 🟡   │  │ 🟢   │  │ 🔴   │  │ 🟡   │         │
│  │  #1  │  │  #2  │  │  #3  │  │  #4  │  │  #5  │         │
│  │ Disp.│  │ Ocup.│  │ Disp.│  │ Mant.│  │ Ocup.│         │
│  │Gaming│  │45min │  │Estánd│  │Tecl. │  │1h20m │         │
│  │      │  │$2.50 │  │      │  │dañado│  │$4.50 │         │
│  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘         │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐         │
│  │ 🟢   │  │ 🟡   │  │ 🟢   │  │ 🟢   │  │ 🟡   │         │
│  │  #6  │  │  #7  │  │  #8  │  │  #9  │  │ #10  │         │
│  │ Disp.│  │ Ocup.│  │ Disp.│  │ Disp.│  │ Ocup.│         │
│  │Gaming│  │30min │  │Estánd│  │Gaming│  │ 2h   │         │
│  │      │  │$1.50 │  │      │  │      │  │ $6.00│         │
│  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘         │
│                                                               │
│  💡 Haz clic en una estación para cambiar su estado          │
└──────────────────────────────────────────────────────────────┘
```

### 10.4 Flujo de Trabajo

```
1. Usuario visita ?pagina=ciberControl
2. router.php verifica autenticación
3. Carga views/ciberControl.php
4. Las estaciones se renderizan desde DB con su estado actual

5. Interacción del usuario:

   ┌─────────────────────────────────────────────────────────┐
   │  a. Hacer clic en estación DISPONIBLE                   │
   │     → confirm("¿Iniciar sesión en estación #N?")       │
   │     → Si acepta:                                        │
   │       • Cliente: cambia UI a Ocupada                    │
   │       • Backend: INSERT sesion_cyber, UPDATE estacion   │
   │                                                         │
   │  b. Hacer clic en estación OCUPADA                      │
   │     → confirm("¿Finalizar sesión en estación #N?")     │
   │     → Si acepta:                                        │
   │       • Cliente: cambia UI a Disponible                 │
   │       • Backend: UPDATE sesion (hora_fin, duracion,     │
   │         costo), UPDATE estacion a Disponible            │
   │                                                         │
   │  c. Hacer clic en estación MANTENIMIENTO                │
   │     → alert("Estación #N en mantenimiento")             │
   │     → No se permite acción                              │
   │                                                         │
   │  d. Filtros:                                            │
   │     → Disponibles / Ocupadas / Mantenimiento / Todas    │
   │     → filterStations() muestra/oculta por data-status   │
   └─────────────────────────────────────────────────────────┘
```

### 10.5 Estados de Estación

```
┌─────────────────────────────────────────────────────────────┐
│                      CICLO DE VIDA                          │
│                                                             │
│        ┌─────────────────────────────────────┐              │
│        │                                     │              │
│        ▼                                     │              │
│  ┌──────────┐       ┌──────────┐             │              │
│  │DISPONIBLE│ ────→ │ OCUPADA  │ ────────────┘              │
│  └──────────┘       └──────────┘                            │
│        │                                                    │
│        │                                                    │
│        ▼                                                    │
│  ┌──────────┐                                               │
│  │MANTENIM. │                                               │
│  └──────────┘                                               │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

| Estado | Color | Acción al hacer clic |
|--------|-------|----------------------|
| Disponible | 🟢 Verde (`--success`) | Iniciar sesión |
| Ocupada | 🟡 Ámbar (`--warning`) | Finalizar sesión |
| Mantenimiento | 🔴 Rojo (`--danger`) | Mostrar alerta informativa |

### 10.6 Consultas PDO Asociadas

```php
// Listar estaciones con sesión activa
$sql = "SELECT ec.*, sc.id as sesion_id, sc.hora_inicio,
               TIMESTAMPDIFF(MINUTE, sc.hora_inicio, NOW()) as minutos_transcurridos
        FROM estaciones_cyber ec
        LEFT JOIN sesiones_cyber sc 
            ON ec.id = sc.estacion_id AND sc.hora_fin IS NULL
        ORDER BY ec.nombre";

// Iniciar sesión (transacción)
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO sesiones_cyber (estacion_id, usuario_id, hora_inicio) VALUES (?, ?, NOW())");
$stmt->execute([$estacionId, $usuarioId]);
$stmt = $pdo->prepare("UPDATE estaciones_cyber SET estado = 'Ocupada' WHERE id = ?");
$stmt->execute([$estacionId]);
$pdo->commit();

// Finalizar sesión (transacción)
$pdo->beginTransaction();
$stmt = $pdo->prepare("UPDATE sesiones_cyber SET hora_fin = NOW(), duracion_minutos = TIMESTAMPDIFF(MINUTE, hora_inicio, NOW()), costo = ROUND(TIMESTAMPDIFF(MINUTE, hora_inicio, NOW()) * 0.10, 2) WHERE id = ? AND hora_fin IS NULL");
$stmt->execute([$sesionId]);
$stmt = $pdo->prepare("UPDATE estaciones_cyber SET estado = 'Disponible' WHERE id = (SELECT estacion_id FROM sesiones_cyber WHERE id = ?)");
$stmt->execute([$sesionId]);
$pdo->commit();
```

### 10.7 Modelo de Datos: Estaciones + Sesiones

```
┌─────────────────────────┐       ┌─────────────────────────┐
│    estaciones_cyber     │       │     sesiones_cyber      │
├─────────────────────────┤       ├─────────────────────────┤
│ id (PK)                 │◄──────│ estacion_id (FK)        │
│ nombre (UNIQUE)         │       │ usuario_id (FK)         │
│ estado (ENUM)           │       │ hora_inicio             │
│ tipo (VARCHAR)          │       │ hora_fin (NULL=activa)  │
│ created_at              │       │ duracion_minutos        │
│ updated_at              │       │ costo                   │
└─────────────────────────┘       └─────────────────────────┘
```

---

## Apéndice A: Base de Datos — Diagrama Relacional

```
┌───────────┐       ┌──────────────┐       ┌────────────────┐
│  usuarios │       │   ventas     │       │ detalle_ventas  │
├───────────┤       ├──────────────┤       ├────────────────┤
│ id (PK)   │──┐    │ id (PK)      │──┐    │ id (PK)        │
│ nombre    │  │    │ fecha        │  │    │ venta_id (FK)───┘
│ email     │  │    │ total        │  │    │ producto_id (FK)─┐
│ password  │  │    │ usuario_id(FK)┘  │    │ cantidad        │
│ created_at│  │    │ estado       │    │    │ precio_unitario │
│ updated_at│  │    └──────────────┘    │    │ subtotal        │
└───────────┘  │                       │    └────────────────┘
               │                       │
┌──────────────┐│  ┌────────────────┐  │    ┌────────────────┐
│  solicitudes ││  │ movimientos    │  │    │   productos    │
├──────────────┤│  │ _stock        │  │    ├────────────────┤
│ id (PK)      ││  ├────────────────┤  │    │ id (PK)        │──┘
│ codigo (UNQ) ││  │ id (PK)        │  │    │ codigo (UNIQUE)│
│ proveedor_id ││  │ producto_id(FK)┘  │    │ nombre         │
│ fecha        ││  │ tipo           │  │    │ categoria      │
│ estado       ││  │ cantidad       │  │    │ stock          │
│ usuario_id(FK)┘  │ stock_anterior │  │    │ precio_venta   │
└──────────────┘   │ stock_nuevo    │  │    └────────────────┘
                   │ usuario_id(FK)─┘
┌───────────┐      │ fecha          │       ┌────────────────┐
│proveedores│      │ motivo         │       │estaciones_cyber│
├───────────┤      └────────────────┘       ├────────────────┤
│ id (PK)   │──┐                             │ id (PK)        │
│ nombre    │  │  ┌────────────────┐         │ nombre (UNIQUE)│
│ contacto  │  └──│sesiones_cyber  │         │ estado         │
│ email     │     ├────────────────┤         │ tipo           │
│ telefono  │     │ id (PK)        │         └───────┬────────┘
└───────────┘     │ estacion_id(FK)│───────────────┘
                  │ usuario_id(FK)─┘
┌───────────┐     │ hora_inicio    │
│  activos  │     │ hora_fin       │
├───────────┤     │ duracion_minutos│
│ id (PK)   │     │ costo          │
│ nombre    │     └────────────────┘
│ tipo      │
│ estado    │
└───────────┘
```

## Apéndice B: Sistema de Rutas

```
public/                   → .htaccess redirige a index.php
index.php                 → require_once router.php
router.php                → session_start + dispatch

Queries GET:
  ?pagina=login           → views/login.php           (pública)
  ?pagina=login_validate  → views/login_validate.php  (pública, POST)
  ?pagina=dashboard       → views/dashboard.php       (protegida)
  ?pagina=inventario      → views/inventario.php      (protegida)
  ?pagina=ventas          → views/ventas.php          (protegida)
  ?pagina=proveedores     → views/proveedores.php     (protegida)
  ?pagina=clientes        → views/clientes.php        (protegida)
  ?pagina=reportes        → views/reportes.php        (protegida)
  ?pagina=activos         → views/activos.php         (protegida)
  ?pagina=ciberControl    → views/ciberControl.php    (protegida)
  ?pagina=menu            → views/menu.php            (protegida)
  cualquier otra          → 404 Error                 (protegida)

Reescritura Apache (.htaccess):
  /dashboard  →  ?pagina=dashboard
  /inventario →  ?pagina=inventario
  (etc.)
```

## Apéndice C: Temas (Claro/Oscuro)

El sistema implementa un sistema de temas persistente vía `localStorage`:

```
1. Al cargar la página:
   → Lee localStorage.getItem('theme') || 'light'
   → Aplica data-theme="dark|light" en <html>
   → Ajusta ícono del toggle (🌙/☀️)

2. Al hacer clic en toggle:
   → Intercambia data-theme
   → Guarda en localStorage
   → Cambia ícono

3. CSS variables cambian con [data-theme="dark"]:
   → --bg: #f1f5f9  →  #0f172a
   → --surface: #ffffff → #1e293b
   → --text: #1e293b → #f1f5f9
   → (y todas las demás variables de color)
```

---

*Documento de análisis técnico generado el 7 de mayo de 2026 — EIS System (Zona Web Lara)*  
*Última actualización: julio 2026 — Módulo Clientes, ProveedorGestion, seguridad completa*  
*Arquitectura: MVC + POO + PDO estricto | Base de datos: MySQL 8+ / InnoDB / utf8mb4*
