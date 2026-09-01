# EIS_Zona_Web_Lara — Sistema de Gestión Integral

## Descripción

**EIS System** es una aplicación web de gestión empresarial desarrollada en **PHP vanilla (8.x)** con **Materialize CSS** y **jQuery**. Utiliza una arquitectura **Front Controller** con enrutador OOP (clase `Router`), patrón **Singleton** para conexión PDO, y **MVC** modular con controladores y modelos con namespace (PSR-4 vía Composer).

El sistema administra múltiples aspectos de un negocio: ventas (POS), inventario, proveedores, activos fijos y control de cybercafé, asesoría legal, usuarios, roles y permisos, dashboard con KPIs reales y reportes exportables. Cada módulo cuenta con **CRUD completo persistente en base de datos** vía AJAX (JSON) y validación de seguridad (CSRF, XSS, prepared statements). El frontend funciona sin conexión a Internet gracias a assets locales y un Service Worker (PWA).

---

## Estado Actual del Proyecto

> **Estado general: Funcional.** Todos los módulos están conectados a la base de datos MySQL (`zona_web_lara`) con modelo POO + controlador + vista dinámica + JS modular.

### Módulos Completos (con BD)
| Módulo | Vista | Modelo | Controlador | JS | Descripción |
|--------|-------|--------|-------------|----|-------------|
| **Login/Auth** | `login.php` | `Usuario` | `AuthController` | `app.core.js` | Login/logout, session hardening, CSRF |
| **Dashboard** | `dashboard.php` | `Dashboard` | `DashboardController` | `app.init.js`, `app.ui.js` | KPIs reales (ventas, stock crítico, sesiones cyber, solicitudes) |
| **Inventario** | `inventario.php` | `Inventario` | `InventarioController` | `app.inventario.js` | CRUD productos, KPIs, stock, búsqueda/filtros |
| **Ventas (POS)** | `ventas.php` | `Venta` | `VentaController` | `app.pos.js` | Carrito, catálogo, registro de venta transaccional con cliente get-or-create y descuento de stock |
| **Clientes** | `clientes.php` | `Cliente` | `ClienteController` | `app.clientes.js` | CRUD clientes, get-or-create por cédula |
| **Proveedores (Órdenes)** | `proveedores.php` | `Proveedor` | `ProveedorController` | `app.proveedores.js` | Órdenes de abastecimiento |
| **Proveedores (Gestión)** | `proveedores-gestion.php` | `ProveedorGestion` | `ProveedorGestionController` | `app.proveedores-gestion.js` | CRUD proveedores |
| **Cyber Control** | `ciberControl.php` | `CiberControl` | `CiberController` | `app.cyber.js` | Estaciones, iniciar/finalizar sesiones, tarifas, CRUD de PCs |
| **Activos Fijos** | `activos.php` | `Activo` | `ActivoController` | `app.activos.js` | CRUD activos, tipos, KPIs, estado ciber |
| **Asesoría Legal** | `asesorias.php` | `Asesoria` | `AsesoriaController` | `app.legal.js` | CRUD asesorías, cliente get-or-create, KPIs |
| **Usuarios** | `usuarios.php` | `Usuario` | `AuthController` | `app.core.js` | CRUD usuarios |
| **Roles y Permisos** | `roles.php` | `Rol` | `RolController` | `app.roles.js` | CRUD roles/permisos |
| **Reportes** | `reportes.php` | `Reporte` | `ReporteController` | `app.reportes.js` | KPIs, consultas por rango y **exportación CSV/Excel/PDF** |

### Características transversales
- Autenticación con `session_regenerate_id` y `password_hash()`/`password_verify()` (Bcrypt)
- Token **CSRF** único por sesión (`bin2hex(random_bytes(32))`) verificado en todas las mutaciones (`Router::verifyCsrfToken`)
- Sanitización XSS (escapado en backend y en el render del frontend)
- **Prepared statements** con PDO (sin emulación) y **bindParam**
- Validación backend reutilizable (helpers en `App\Core\Model`)
- Operaciones **transaccionales** (ventas, asesorías, sesiones cyber) con get-or-create centralizado de clientes
- Exportadores sin dependencias: **CSV**, **Excel (HTML)** y **PDF** (generador propio, `Exporter` + `PdfBuilder`)
- Assets 100% locales, **Service Worker** (caché offline), **Manifest PWA** y página `offline.php`
- Tema oscuro/claro con persistencia en localStorage, reloj en tiempo real, notificaciones toast

---

## Estructura del Proyecto

```
eis_zona_web_lara/
├── src/                              # Raíz de la aplicación
│   ├── index.php                     # Front Controller (punto de entrada)
│   ├── .htaccess                     # Reglas de reescritura Apache (URLs limpias)
│   ├── manifest.json                 # Manifiesto PWA
│   ├── sw.js                         # Service Worker (caché offline)
│   ├── offline.php                   # Página de fallo offline
│   ├── Config/
│   │   └── database.php              # Conexión PDO (legacy, usada por crud_*.php)
│   ├── cli/
│   │   └── create_user.php           # Script CLI para crear usuarios
│   ├── app/
│   │   ├── core/
│   │   │   ├── Database.php          # Conexión PDO Singleton (moderna)
│   │   │   ├── Model.php             # Clase base abstracta con helpers de validación
│   │   │   ├── router.php            # Enrutador OOP (Front Controller)
│   │   │   ├── Exporter.php          # Exportación CSV / Excel / PDF
│   │   │   └── PdfBuilder.php        # Generador de PDF mínimo
│   │   ├── Controllers/              # 12 controladores (app/json + render)
│   │   │   ├── AuthController.php            # Login/logout
│   │   │   ├── ClienteController.php         # CRUD clientes
│   │   │   ├── InventarioController.php      # CRUD inventario
│   │   │   ├── VentaController.php           # POS: productos, clientes, registrar venta
│   │   │   ├── RolController.php             # Roles y permisos
│   │   │   ├── ProveedorController.php       # Órdenes de abastecimiento
│   │   │   ├── ProveedorGestionController.php # Gestión de proveedores
│   │   │   ├── AsesoriaController.php        # Asesoría legal
│   │   │   ├── CiberController.php           # Control de cybercafé
│   │   │   ├── ActivoController.php          # Activos fijos
│   │   │   ├── DashboardController.php       # KPIs del panel
│   │   │   └── ReporteController.php         # Reportes y exportación
│   │   ├── Models/
│   │   │   ├── Usuario.php, Cliente.php, Inventario.php, Venta.php
│   │   │   ├── Proveedor.php, ProveedorGestion.php, Rol.php
│   │   │   ├── Asesoria.php, Activo.php, CiberControl.php
│   │   │   ├── Dashboard.php, Reporte.php
│   │   │   ├── CiberModel.php       # Modelo legacy de cybercafé
│   │   │   ├── crud_users.php       # CRUD legacy procedural
│   │   │   └── crud_asesorias.php   # CRUD legacy procedural
│   │   ├── template/
│   │   │   └── layout.php           # Layout maestro (sidebar 13 módulos)
│   │   └── Views/                    # 15 vistas
│   │       ├── login.php, login_validate.php, menu.php
│   │       ├── dashboard.php, inventario.php, ventas.php
│   │       ├── clientes.php, proveedores.php, proveedores-gestion.php
│   │       ├── ciberControl.php, reportes.php, activos.php
│   │       ├── asesorias.php, usuarios.php, roles.php
│   ├── Database/
│   │   ├── estructura.sql            # Esquema BD (21 tablas)
│   │   ├── seed_data.sql             # Datos de prueba
│   │   ├── seed_data_masivo.sql      # Datos masivos de prueba
│   │   ├── reportes_ejemplo.sql      # Consultas de ejemplo
│   │   └── usuario dev.txt           # Credenciales de usuario dev
│   └── Public/
│       ├── css/                      # styles, login, materialize, material-icons (local)
│       ├── js/                       # jQuery, Materialize + 15 módulos app.*.js
│       └── fonts/                    # MaterialIcons-Regular.ttf (local)
├── docs/                             # Documentación del proyecto
├── vendor/                           # Autoloader de Composer
└── composer.json                     # PSR-4: "App\\": "src/app/"
```

---

## Tecnologías Utilizadas

### Backend
- **PHP 8.x** — Lenguaje principal (tipado estricto, `match`, arrow functions, `readonly`)
- **PDO (PHP Data Objects)** — Abstracción de BD con prepared statements reales
- **MySQL / MariaDB** — Sistema de gestión de BD (motor InnoDB)
- **Composer** — Autocargador de clases PSR-4

### Frontend
- **Materialize CSS 1.0.0** — Framework Material Design (local)
- **jQuery 3.7.1** — Manipulación DOM y AJAX (local)
- **Material Icons** — Iconografía (local)
- **HTML5 / CSS3** — Variables CSS, Flexbox, Grid, tema oscuro/claro
- **Service Worker + PWA** — Caché offline, `manifest.json`, `offline.php`

---

## Módulos del Sistema

| Módulo | Vista | Estado |
|--------|-------|--------|
| Login / Autenticación | `login.php` | ✅ Funcional con BD |
| Dashboard | `dashboard.php` | ✅ Funcional con BD (KPIs reales) |
| Inventario | `inventario.php` | ✅ Funcional con BD |
| Punto de Venta (POS) | `ventas.php` | ✅ Funcional con BD (ventas transaccionales) |
| Clientes | `clientes.php` | ✅ Funcional con BD |
| Proveedores (Órdenes) | `proveedores.php` | ✅ Funcional con BD |
| Proveedores (Gestión) | `proveedores-gestion.php` | ✅ Funcional con BD |
| Control de Cybercafé | `ciberControl.php` | ✅ Funcional con BD (sesiones) |
| Activos Fijos | `activos.php` | ✅ Funcional con BD |
| Asesoría Legal | `asesorias.php` | ✅ Funcional con BD |
| Usuarios | `usuarios.php` | ✅ Funcional con BD |
| Roles y Permisos | `roles.php` | ✅ Funcional con BD |
| Reportes | `reportes.php` | ✅ Funcional con BD + exportación |

---

## Instalación y Configuración

### Requisitos
- PHP 8.0 o superior (probado en 8.3)
- MySQL 8.0+ / MariaDB 10.3+
- Servidor web (Apache/Nginx/XAMPP/WAMP/Laragon)
- Composer (para el autoloader)

### Pasos

1. **Instalar dependencias (autoloader)**
   ```bash
   composer install
   ```

2. **Configurar la base de datos**

   Editar las credenciales en dos lugares (`src/Config/database.php` y `src/app/core/Database.php`):
   ```php
   $host = "localhost";
   $db   = "zona_web_lara";
   $user = "root";
   $pass = "";   // Cambiar por tu contraseña
   ```

3. **Crear la base de datos**
   ```bash
   mysql -u root -p < src/Database/estructura.sql
   mysql -u root -p < src/Database/seed_data.sql
   ```

4. **Crear un usuario administrador (CLI)** — este script crea un usuario con contraseña cifrada en Bcrypt y comprueba duplicados:
   ```bash
   # Usuario admin con contraseña 1234
   php src/cli/create_user.php --username=admin --password=1234 --nombre="Administrador" --apellido="Sistema" --email=admin@ejemplo.com
   ```
   > El script ignora la longitud mínima de contraseña (a diferencia del login web), por lo que `1234` funciona.

5. **Configurar el servidor web** — el documento raíz del virtual host debe apuntar a `src/`.

6. **Acceder a la aplicación**
   ```
   URL: http://localhost/eis_zona_web_lara/src/
   Usuario: admin
   Contraseña: 1234
   ```

---

## Arquitectura

### Flujo de una petición

```
Navegador → src/.htaccess → src/index.php → App\Core\Router->handle()
                                                    │
                                       session_start (si hace falta)
                                       genera token CSRF (una vez por sesión)
                                                    │
                                       resolvePagina()  (regex ant path-traversal)
                                                    │
                          ┌─────────────────────────┼───────────────────────────┐
                          ▼                         ▼                           ▼
                   Control de acceso           Despacho AJAX                Render vista
              ¿logged_in? ¿página pública?   (?pagina=X&action=Y)     publica (login) o
                          │                         │                 protegida (layout)
                          ▼                         ▼                         │
                    redirect(login)      match(action) → método        require layout.php
                     o continúa          del controlador handle()      (incluye $contentView)
```

### Enrutador (`src/app/core/router.php` — clase OOP)
- Clase `Router` en namespace `App\Core`, método `handle()`
- Inicia la sesión y genera/reutiliza un token **CSRF** por sesión
- `resolvePagina()` valida el nombre de la página con regex `^[a-zA-Z0-9_-]+$` (anti path-traversal)
- Tabla `CONTROLLERS` mapea `pagina => controlador`; las peticiones con `?action=` se despachan al `handle()` de cada controlador
- `PUBLIC_PAGES = ['login', 'login_validate']`; el resto requiere `logged_in`
- `Router::verifyCsrfToken()` compara el token recibido con el de sesión usando `hash_equals`
- Manejo 404 con `http_response_code(404)`

### Controladores (`App\Controllers`)
- Cada módulo expone un endpoint JSON vía `handle()` que despacha `match ($action)` a métodos privados
- Las mutaciones (crear/actualizar/eliminar/estado/finalizar/iniciar/registrar) verifican **CSRF** y método `POST`
- Capturan `\PDOException`, `\InvalidArgumentException` y `\Exception` devolviendo JSON `{success, data?, error?, message?}`
- Filtro de error de FK en `ActivoController` para mensajes amigables

### Modelos (`App\Models`)
- Heredan de `App\Core\Model` (conexión PDO por Singleton en `$this->db`)
- Helpers de validación: `sanitizeString`, `sanitizeInt`, `sanitizeFloat`, `validateNotEmpty`, `validateMinLength`, `validatePattern`, `validatePositive`, `validateGreaterOrEqual`, `validateEmail`, `existeEnTabla`
- Uso de `bindParam` en todas las consultas (seguro)
- Operaciones transaccionales: `Venta::registrarVenta`, `Asesoria::crear`, `CiberControl::iniciarSesion`
- Cliente **get-or-create** centralizado en `Cliente::obtenerOCrearPorCedula()`

### Exportación de Reportes (`App\Core\Exporter` + `PdfBuilder`)
- `Exporter::csv()`, `excel()` y `pdf()` convierten `{columnas, filas}` en descargas
- `PdfBuilder` genera un PDF mínimo y válido (texto + tabla) sin librerías externas
- `ReporteController::exportar` valida CSRF, rango de fechas y formato permitido

### JavaScript Modular
- **`app.core.js`** — namespace `EIS`, `debounce`, `EIS.toast`, `escHtml` (XSS)
- **`app.init.js`** — inicia Materialize, reloj, tema oscuro/claro
- **`app.tables.js`** — búsqueda con debounce, filtro por estado, paginación
- **`app.ui.js`** — notificaciones, botones, tooltips
- **`app.inventario.js`**, **`app.roles.js`**, **`app.clientes.js`**, **`app.proveedores.js`**, **`app.proveedores-gestion.js`**, **`app.activos.js`**, **`app.legal.js`**, **`app.pos.js`**, **`app.cyber.js`**, **`app.reportes.js`** — CRUD/acciones AJAX por módulo
- **CSRF automático** — el layout inyecta `window.EIS.csrfToken` y `$.ajaxSetup` lo agrega a cada POST

### Offline / PWA
- Assets locales (sin CDNs)
- Service Worker (`sw.js`) con estrategia Cache First
- Página offline (`offline.php`) y manifiesto (`manifest.json`)

---

## Base de Datos

Esquema en `src/Database/estructura.sql` con **21 tablas** InnoDB:

`roles, permisos, categoria, clientes, cliente_asesoria, proveedores, status_seguimiento, tipo_asesoria, tarifas, tipo_activo, rol_usuarios, usuarios, permisos_rol, productos, orden_de_venta, lineas_venta, orden_abastecimiento, lineas_abastecimiento, asesoria, activos, sesion_ciber`

---

## Documentación Disponible

### `docs/Documentacion/`
- **`DOCUMENTACION_COMPLETA.md`** — Documentación completa para NotebookLM
- **`DOCUMENTACION.md`** — Documentación técnica detallada del código fuente
- **`DOCUMENTACION_JQUERY.md`** — Integración de jQuery + Materialize
- **`routing-system.md`**, **`Enrutado del proyecto.md`**, **`Mapa navegacional.md`** — Enrutamiento y navegación
- **`Modulo de Inventario.md`**, **`MANUAL_INVENTARIO.md`**, **`DOCUMENTACION_MODELO_INVENTARIO.md`** — Inventario y modelos
- **`ANALISIS_TECNICO.md`**, **`Base de datos.md`**, **`Documentación App para estudiar.md`**

### `docs/Fases del diseño de la base de datos/`
Diseño conceptual, lógico y físico de la base de datos, ER y diagrama de clases.

### Otros
- **`EXPLICACION_LINEA_POR_LINEA.md`** — Explicación línea por línea del núcleo (index, Database, Model, Router, Auth, CLI, layout, login, offline)
- **`PROMPT_CONTINUACION.md`** — Prompt de continuación con el estado actual y pendientes
- **`docs/VARIABLES_GLOBALES.md`**, planes de formación, planificación (RUP/Gantt, diseño-desarrollo, pruebas-instalación)

---

## Problemas Conocidos

1. **Configuración sin `.env`** — Las credenciales de BD están como constantes en `Database.php` y `Config/database.php` (duplicadas en dos archivos)
2. **Modelos legacy** — `CiberModel.php` y los `crud_*.php` coexisten con los modernos (`CiberControl.php`, modelos POO); se mantienen por compatibilidad
3. **Rama de desarrollo** — El trabajo activo está en la rama `Carlos` (puede estar **2 commits por delante** de `origin/Carlos` sin pushear)

---

## Próximos Pasos Recomendados

- [x] Conectar Dashboard a consultas SQL reales
- [x] Persistir ventas del POS en BD (transaccional con descuento de stock)
- [x] Persistir sesiones del cybercafé en BD (iniciar/finalizar)
- [x] CRUD de activos fijos con BD
- [x] Reportes reales con exportación CSV/Excel/PDF
- [ ] Mover credenciales de BD a variables de entorno (`.env`)
- [ ] Unificar modelos legacy (`CiberModel`, `crud_*`) con los POO modernos
- [ ] Middleware de autenticación/CSRF como capa separada
- [ ] URLs limpias (`/nombre` en lugar de `?pagina=nombre`)

---

## Estadísticas del Proyecto

| Métrica | Valor |
|---------|-------|
| Controladores | 12 |
| Modelos | 15 (13 POO + 2 legacy procedurales) |
| Vistas | 15 |
| Core OOP | 5 (Database, Model, Router, Exporter, PdfBuilder) |
| Archivos JS | 17 (2 librerías + 15 módulos) |
| Archivos CSS | 4 |
| Archivos SQL | 4 (estructura + 3 seed/ejemplos) |
| Tablas en BD | 21 |
| Módulos del sistema | 13 |
| Dependencias CDN | 0 (100% local) |
| Exportadores | CSV, Excel, PDF (sin librerías) |

---

## Autor

**Carlos Páez Guerra**
Email: carlospaezguerra@gmail.com

---

## Historial de Versiones

| Versión | Fecha | Descripción |
|---------|-------|-------------|
| 4.x | Ago 2026 | Dashboard y Reportes conectados a datos reales con exportación (CSV/Excel/PDF vía `Exporter`/`PdfBuilder`); registro de clientes en POS; CRUD de Activos; CiberControl con sesiones iniciar/finalizar y CRUD de PCs |
| 3.3 | Jul 2026 | Validación backend completa: helpers reutilizables, existence checks, coherencia de datos |
| 3.2 | Jul 2026 | Seguridad: CSRF, XSS, session hardening, módulos Clientes y ProveedorGestion |
| 3.1 | 2026 | Migración a OOP: Router, Database Singleton, Model abstracto; módulos Usuarios, Roles, Proveedores |
| 3.0 | 2026 | Módulo de Inventario completo con MVC+AJAX |
| 2.x | 2026 | JS modular, assets locales, Service Worker, PWA, offline |
| 1.x | 2024 | Versión inicial — UI Prototype procedural |

---

**Última actualización**: Agosto 2026
**Estado**: En desarrollo activo (rama `Carlos`). Todos los módulos funcionales con MVC + AJAX + BD.
