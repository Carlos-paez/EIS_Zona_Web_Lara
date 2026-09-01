# ANÁLISIS TÉCNICO — EIS System (Zona Web Lara)

**Arquitectura:** MVC con POO, Router OOP (Front Controller), Database Singleton, PDO estricto  
**Base de datos:** MySQL 8+ (InnoDB, utf8mb4) — `zona_web_lara`, 21 tablas  
**Frontend:** Materialize CSS 1.0.0 + jQuery 3.7.1 + JS modular  
**Namespace:** `App\Core`, `App\Models`, `App\Controllers` (PSR-4)  
**Seguridad:** CSRF tokens, XSS sanitización, session hardening, validación backend, prepared statements  
**Estado:** Todos los módulos funcionales con BD (MVC + AJAX)

---

## Índice de Módulos

1. [Arquitectura General](#1-arquitectura-general)
2. [Módulo de Autenticación](#2-módulo-de-autenticación)
3. [Módulo Dashboard](#3-módulo-dashboard)
4. [Módulo Inventario](#4-módulo-inventario)
5. [Módulo Ventas (POS)](#5-módulo-ventas-pos)
6. [Módulo Clientes](#6-módulo-clientes)
7. [Módulo Proveedores / Ordenes](#7-módulo-proveedores--ordenes)
8. [Módulo Reportes](#8-módulo-reportes)
9. [Módulo Activos Fijos](#9-módulo-activos-fijos)
10. [Módulo Cybercafé](#10-módulo-cybercafé)
11. [Módulo Asesoría Legal](#11-módulo-asesoría-legal)

---

## 1. Arquitectura General

### 1.1 Estructura de Directorios

```
eis_zona_web_lara/
├── composer.json                        # PSR-4 autoloading ("App\\": "src/app/")
├── src/                                 # Document root
│   ├── .htaccess                        # Apache Rewrite Rules
│   ├── index.php                        # Front Controller (autoloader + Router OOP)
│   ├── manifest.json                    # Manifiesto PWA
│   ├── sw.js                            # Service Worker
│   ├── offline.php                      # Página offline fallback
│   ├── Config/
│   │   └── database.php                 # Conexión PDO (legacy)
│   ├── cli/
│   │   └── create_user.php              # Script CLI para crear usuarios
│   ├── Database/
│   │   ├── estructura.sql               # Esquema completo (21 tablas)
│   │   ├── seed_data.sql                # Datos de prueba
│   │   ├── seed_data_masivo.sql         # Datos masivos de prueba
│   │   └── reportes_ejemplo.sql         # Consultas de ejemplo
│   ├── app/
│   │   ├── core/
│   │   │   ├── Database.php             # Conexión PDO Singleton (moderna)
│   │   │   ├── Model.php                # Clase base abstracta con helpers de validación
│   │   │   ├── router.php               # Enrutador OOP (Front Controller)
│   │   │   ├── Exporter.php             # Exportación CSV/Excel/PDF
│   │   │   └── PdfBuilder.php           # Generador de PDF mínimo
│   │   ├── Controllers/                 # 12 controladores
│   │   │   ├── AuthController.php           # Login/logout
│   │   │   ├── ClienteController.php        # CRUD clientes
│   │   │   ├── InventarioController.php     # CRUD inventario
│   │   │   ├── VentaController.php          # POS
│   │   │   ├── RolController.php            # Roles/permisos
│   │   │   ├── ProveedorController.php      # Ordenes de abastecimiento
│   │   │   ├── ProveedorGestionController.php # Gestión de proveedores
│   │   │   ├── AsesoriaController.php       # Asesoría legal
│   │   │   ├── CiberController.php          # Cybercafé
│   │   │   ├── ActivoController.php         # Activos
│   │   │   ├── DashboardController.php      # KPIs
│   │   │   └── ReporteController.php        # Reportes/exportación
│   │   ├── Models/                     # 13 POO + 2 legacy
│   │   ├── template/
│   │   │   └── layout.php               # Layout maestro (13 módulos)
│   │   └── Views/                       # 15 vistas
│   └── Public/
│       ├── css/                        # styles, login, materialize, material-icons (locales)
│       ├── js/                         # jquery + materialize + 15 módulos app.*.js
│       └── fonts/                      # MaterialIcons-Regular.ttf (local)
├── docs/                               # Documentación detallada
└── vendor/                             # Composer dependencies
```

### 1.2 Patrón MVC

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| **Model** | `src/app/Models/*.php` | Lógica de negocio, acceso a datos con PDO prepared statements, validación con helpers reutilizables |
| **View** | `src/app/Views/*.php` | Presentación HTML, datos del modelo |
| **Controller** | `src/app/Controllers/*.php` (12) | Orquestación: recibe request AJAX, valida, llama a modelos, retorna JSON |
| **Core** | `src/app/core/` (Database, Model, Router, Exporter, PdfBuilder) | Conexión Singleton, clase base, enrutamiento con CSRF, exportación |
| **Config** | `src/Config/database.php` | Conexión PDO legacy |

### 1.3 Principios PDO Estricto

```
PDO::ATTR_ERRMODE            → PDO::ERRMODE_EXCEPTION
PDO::ATTR_DEFAULT_FETCH_MODE → PDO::FETCH_ASSOC
PDO::ATTR_EMULATE_PREPARES   → false  (prepared statements reales)
```

- Toda consulta parametrizada usa `prepare()` + `execute()` / `bindParam()` con placeholders `?`
- Transacciones multi-tabla con `beginTransaction()`, `commit()`, `rollback()` (ventas, asesorías, cyber)
- Excepciones PDO capturadas para manejo centralizado de errores

### 1.4 Flujo de Trabajo Global (Request → Response)

```
Navegador → .htaccess → index.php → new Router() → Router::handle()
    → session_start (si hace falta) + token CSRF (una vez por sesión)
    → resolvePagina()  (regex ^[a-zA-Z0-9_-]+$)
    → control de acceso (páginas privadas requieren $_SESSION['logged_in'])
    → si ?action= en página con controlador → dispatchAction() → handle() → JSON
    → si login_validate POST → AuthController::login()
    → si no → render(): pública directa | protegida con layout.php
```

---

## 2. Módulo de Autenticación

### 2.1 Archivos

| Archivo | Ruta | Función |
|---------|------|---------|
| `login.php` | `src/app/Views/login.php` | Formulario de inicio de sesión |
| `login_validate.php` | `src/app/Views/login_validate.php` | Redirección de seguridad (legacy) |
| `AuthController.php` | `src/app/Controllers/AuthController.php` | Login/logout con CSRF y session hardening |
| `Usuario.php` | `src/app/Models/Usuario.php` | Autenticación, crear/actualizar usuarios |

### 2.2 Seguridad implementada
- `password_hash()`/`password_verify()` con Bcrypt (`PASSWORD_BCRYPT`)
- `session_regenerate_id(true)` en login y logout
- CSRF token: `bin2hex(random_bytes(32))` verificado en mutaciones vía `Router::verifyCsrfToken()`
- `MIN_PASSWORD = 8` en el modelo (validación de longitud en operaciones web)
- Prepared statements con `ATTR_EMULATE_PREPARES => false`
- Credenciales en constantes (pendiente migración a `.env`)

### 2.3 Rutas

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `?pagina=login` | Muestra formulario de login |
| POST | `?pagina=login_validate` | Procesa credenciales (AuthController::login) |
| GET | `?pagina=login&logout=1` | Cierra sesión |

**Páginas públicas:** `login`, `login_validate`  
**Páginas protegidas:** el resto (`dashboard`, `inventario`, `ventas`, etc.)

### 2.4 Crear usuario por consola (CLI)
```bash
php src/cli/create_user.php --username=admin --password=1234 --nombre="Administrador" \
     --apellido="Sistema" --email=admin@ejemplo.com
```
- El script detecta duplicados (username/email) y crea con hash Bcrypt.
- Acepta contraseñas cortas (no aplica `MIN_PASSWORD`).

---

## 3. Módulo Dashboard

### 3.1 Archivos
- `dashboard.php` — vista
- `DashboardController` — endpoint AJAX `kpis`
- `Dashboard` model — consultas agregadas

### 3.2 KPIs reales (desde BD)
| Métrica | Fuente |
|---------|--------|
| Ventas Hoy | `orden_de_venta` (SUM) |
| Ventas últimos 7 días | agregación por día |
| Stock crítico / agotado | `productos` (stock <= mínimo / = 0) |
| Sesiones cyber activas | `sesion_ciber` (finalizada = 0) |
| Solicitudes pendientes | `orden_abastecimiento` |
| Actividad reciente | feed de eventos |

---

## 4. Módulo Inventario

### 4.1 Archivos
- `inventario.php` — vista
- `InventarioController` — CRUD AJAX
- `Inventario` model — POO con getters/setters y validación

### 4.2 Funcionalidad
- CRUD de productos (crear, listar, editar, eliminar)
- KPIs: total, stock crítico/bajo, valor total del inventario
- Búsqueda por término, filtros, paginación
- `app.inventario.js` para las operaciones AJAX

---

## 5. Módulo Ventas (POS)

### 5.1 Archivos
- `ventas.php` — vista
- `VentaController` — `productos`, `clientes`, `buscarCliente`, `registrar`
- `Venta` model — transaccional

### 5.2 Funcionalidad
- Catálogo dinámico desde `?pagina=ventas&action=productos` (productos con stock > 0)
- Carrito con cantidades en `app.pos.js`
- Formulario de cliente (ciudadano, cédula, dirección, teléfono) con prefill al blur de cédula
- `Venta::registrarVenta(items, ciudadano, cedula, ...)` transaccional:
  - Cliente get-or-create
  - `orden_de_venta` + `lineas_venta` (precio tomado de BD)
  - descuento de stock
- CSRF obligatorio en `registrar`

---

## 6. Módulo Clientes

### 6.1 Archivos
- `clientes.php` — vista
- `ClienteController` — CRUD AJAX
- `Cliente` model

### 6.2 Funcionalidad
- CRUD de clientes
- **Get-or-create centralizado**: `Cliente::obtenerOCrearPorCedula(cedula, nombre, apellido,
  direccion, telefono): int` (crea si no existe, actualiza solo campos no vacíos)
- `obtenerClientePorCedula()` para búsqueda por cédula (usado por POS y cyber)
- Validación de unicidad de cédula

---

## 7. Módulo Proveedores / Ordenes

### 7.1 Archivos
- `proveedores.php` (órdenes de abastecimiento) + `ProveedorController` + `Proveedor` model
- `proveedores-gestion.php` (gestión) + `ProveedorGestionController` + `ProveedorGestion` model

### 7.2 Funcionalidad
- CRUD de proveedores (gestión)
- Órdenes de abastecimiento (`orden_abastecimiento`, `lineas_abastecimiento`)
- Búsqueda, filtros, paginación

---

## 8. Módulo Reportes

### 8.1 Archivos
- `reportes.php` — vista
- `ReporteController` — `kpis`, `consultar`, `exportar`
- `Reporte` model — consultas por tipo y rango de fechas
- `Exporter` + `PdfBuilder` (core)

### 8.2 Funcionalidad
- KPIs (ventas del período, productos, horas cyber, solicitudes)
- Consulta por tipo de reporte + rango de fechas `YYYY-MM-DD`
- **Exportación** en **CSV / Excel (HTML) / PDF** (sin librerías externas)
- `exportar` valida CSRF, formato permitido y rango

---

## 9. Módulo Activos Fijos

### 9.1 Archivos
- `activos.php` — vista
- `ActivoController` — `listar`, `detalle`, `crear`, `actualizar`, `estado`, `eliminar`, `kpis`, `tipos`
- `Activo` model

### 9.2 Funcionalidad
- CRUD de activos (marca, descripción, tipo, activa, is_ciber)
- KPIs: total, ciber, ocupados, inactivos, por tipo
- Filtro de FK para errores de eliminación amigables
- Gestión de tipos de activo

---

## 10. Módulo Cybercafé

### 10.1 Archivos
- `ciberControl.php` — vista
- `CiberController` — `estaciones`, `tarifas`, `buscarCliente`, `iniciar`, `finalizar`,
  `estadisticas`, `historial`, `tiposActivo`, `obtenerPC`, `crearPC`, `actualizarPC`,
  `cambiarEstadoPC`, `eliminarPC`
- `CiberControl` model

### 10.2 Funcionalidad
- Listado de estaciones con estado (disponible/ocupada)
- **Iniciar sesión**: cliente get-or-create + `sesion_ciber` (transaccional)
- **Finalizar sesión**: marca `finalizada=1`
- Tarifas, historial por estación, CRUD de PCs (activos cyber)
- Chips de cabecera `#hdrDisponibles` / `#hdrOcupadas`

---

## 11. Módulo Asesoría Legal

### 11.1 Archivos
- `asesorias.php` — vista
- `AsesoriaController` — `listar`, `detalle`, `buscar`, `crear`, `actualizar`, `eliminar`, `kpis`
- `Asesoria` model

### 11.2 Funcionalidad
- CRUD de asesorías
- `Asesoria::crear` transaccional: cliente get-or-create + `cliente_asesoria` + `asesoria`
- KPIs: total, permitidas, derivadas
- Búsqueda por cédula, aviso de "cliente ya registrado" al blur de cédula

---

## Apéndice A: Temas (Claro/Oscuro)

Persistencia vía `localStorage`; toggle en la sidebar; CSS variables con `[data-theme="dark"]`.

## Apéndice B: Exportación de Reportes

`Exporter::csv()/excel()/pdf($titulo, $columnas, $filas)` construye el contenido y encabezados HTTP
de descarga. `PdfBuilder` genera un PDF mínimo y válido (texto + tabla).

---

*Documento de análisis técnico — EIS System (Zona Web Lara)*  
*Última actualización: Agosto 2026 — todos los módulos funcionales con BD (MVC + AJAX + exportación)*  
*Arquitectura: MVC + POO + PDO estricto | Base de datos: MySQL 8+ / InnoDB / utf8mb4*
