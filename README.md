# EIS_Zona_Web_Lara — Sistema de Gestión Integral

## Descripción

**EIS System** es una aplicación web de gestión empresarial desarrollada en **PHP vanilla** con **Materialize CSS** y **jQuery**. Utiliza una arquitectura **Front Controller** con enrutador OOP (clase `Router`), patrón **Singleton** para conexión PDO, y **MVC** modular con controladores y modelos con namespace.

El sistema administra múltiples aspectos de un negocio: ventas (POS), inventario, proveedores, activos fijos, control de cybercafé y asesoría legal. Todo el frontend funciona sin conexión a Internet gracias a assets locales y un Service Worker.

---

## Estado Actual del Proyecto

### Implementado (Funcional)
- **Front Controller OOP** — `index.php` → `Router::handle()` (clase Router con namespace)
- **Sistema de Login** — Autenticación con sesiones PHP via `AuthController` (login/logout) con `session_regenerate_id`
- **Layout Maestro** — `layout.php` con sidebar, header y footer persistente
- **Tema Oscuro/Claro** — Toggle con persistencia en localStorage
- **Reloj en Tiempo Real** — Clock actualizado vía JavaScript
- **Sistema de Notificaciones** — Toast notifications con Materialize
- **Carrito de Compras (POS)** — Funcionalidad completa en JavaScript con modal
- **Control de Estaciones Cyber** — Toggle de estados con animaciones jQuery
- **Búsqueda en Tablas** — Filtros con debounce en inventario, proveedores, activos y asesorías
- **Filtro por Estado** — Select dinámico para filtrar registros
- **Paginación** — UI de paginación con navegación
- **Animación de Contadores** — Métricas con animación progresiva
- **Validación de Asesoría Legal** — Validación frontend de documentos permitidos
- **Assets 100% Locales** — Sin dependencia de CDNs (Materialize, jQuery, Material Icons)
- **Service Worker** — Caché de assets estáticos para funcionamiento offline
- **Manifiesto PWA** — `manifest.json` para instalación como app
- **Página Offline** — `offline.php` como fallback sin conexión
- **Esquema de Base de Datos** — Completo con 27 tablas, vistas, funciones y procedimientos
- **Core OOP** — Clases `Database` (Singleton PDO), `Model` (base abstracta con helpers de validación), `Router` (Front Controller)
- **Seguridad** — CSRF tokens en todas las peticiones, XSS sanitización, `session_regenerate_id`, prepared statements, validación backend completa
- **Validación Backend** — Helpers reutilizables: `validateNotEmpty`, `validateMinLength`, `validateLength`, `validatePattern`, `validatePositive`, `validateGreaterOrEqual`
- **Modelos con Namespace** — `Cliente`, `Inventario`, `Usuario`, `Proveedor`, `ProveedorGestion`, `Rol`, `Asesoria` + `crud_users` y `crud_asesorias`
- **Controladores AJAX** — `ClienteController`, `InventarioController`, `RolController`, `ProveedorController`, `ProveedorGestionController`, `AuthController`
- **JavaScript Modular** — 10 módulos especializados (app.core, init, tables, ui, pos, cyber, legal, inventario, roles, proveedores)
- **Enrutamiento AJAX** — Router OOP detecta `action` en inventario/roles/proveedores/clientes/proveedores-gestion y deriva al controlador correspondiente

### Implementado (Funcional con BD)
- **Inventario** — Módulo completo con CRUD de productos, KPIs, movimientos de stock (entrada/salida), búsqueda y filtros. Conectado a BD vía `Inventario.php` (modelo POO) + `InventarioController.php` + `app.inventario.js`
- **Usuarios** — CRUD completo con `Usuario.php` (modelo POO) + `AuthController.php` + `app.core.js`
- **Roles y Permisos** — CRUD completo con `Rol.php` (modelo POO) + `RolController.php` + `app.roles.js`
- **Proveedores (Gestión)** — CRUD completo con `ProveedorGestion.php` (modelo POO) + `ProveedorGestionController.php` + `app.proveedores.js`
- **Proveedores (Solicitudes)** — CRUD completo de órdenes de abastecimiento con `Proveedor.php` (modelo POO) + `ProveedorController.php` + `app.proveedores.js`
- **Clientes** — CRUD completo con `Cliente.php` (modelo POO) + `ClienteController.php` + `app.proveedores.js`

### Parcialmente Implementado (UI Estática o Semi-funcional)
- **Dashboard** — Métricas estáticas (deberían venir de consultas SQL)
- **Ventas (POS)** — Carrito funciona pero no guarda en BD (solo simulación)
- **Cyber Control** — Cambios de estado temporales (no persisten en BD)
- **Activos** — Visualización estática con búsqueda
- **Reportes** — Generador simulado con toasts
- **Asesoría Legal** — Validación frontend sin persistencia en BD

### No Implementado
- **Persistencia en BD** — Dashboard, ventas, cyber, activos, reportes y asesorías aún no persisten en BD

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
│   │   └── database.php              # Configuración BD (PDO + MySQL) — legacy
│   ├── app/
│   │   ├── core/
│   │   │   ├── Database.php          # Conexión PDO Singleton (moderna)
│   │   │   ├── Model.php             # Clase base abstracta para modelos
│   │   │   └── router.php            # Enrutador OOP (clase Router, 385 líneas)
│   │   ├── Controllers/
│   │   │   ├── AuthController.php    # Login/logout con sesiones + CSRF
│   │   │   ├── ClienteController.php # CRUD clientes AJAX
│   │   │   ├── inventarioController.php  # CRUD inventario AJAX (10+ acc.)
│   │   │   ├── ProveedorController.php   # CRUD proveedores AJAX (solicitudes)
│   │   │   ├── ProveedorGestionController.php # CRUD proveedores AJAX (gestión)
│   │   │   └── RolController.php         # CRUD roles/permisos AJAX
│   │   ├── Models/
│   │   │   ├── Cliente.php           # Modelo POO clientes
│   │   │   ├── Inventario.php        # Modelo POO inventario (namespace)
│   │   │   ├── Usuario.php           # Modelo POO usuarios
│   │   │   ├── Proveedor.php         # Modelo POO proveedores (solicitudes)
│   │   │   ├── ProveedorGestion.php  # Modelo POO proveedores (gestión)
│   │   │   ├── Rol.php               # Modelo POO roles y permisos
│   │   │   ├── Asesoria.php          # Modelo POO asesorías
│   │   │   ├── crud_users.php        # CRUD usuarios legacy (8 funciones)
│   │   │   └── crud_asesorias.php    # CRUD asesorías legacy (8 funciones)
│   │   ├── template/
│   │   │   └── layout.php           # Layout maestro (sidebar 12 módulos)
│   │   └── Views/
│   │       ├── login.php             # Formulario de inicio de sesión
│   │       ├── login_validate.php    # Validación de credenciales (legacy)
│   │       ├── menu.php              # Menú de navegación
│   │       ├── dashboard.php         # Panel de control
│   │       ├── inventario.php        # Gestión de inventario
│   │       ├── ventas.php            # Punto de venta (POS)
│   │       ├── proveedores.php       # Solicitudes a proveedores (conectado a BD)
│   │       ├── reportes.php          # Reportes y estadísticas
│   │       ├── activos.php           # Activos fijos
│   │       ├── ciberControl.php      # Control de cybercafé
│   │       ├── asesorias.php         # Asesoría legal
│   │       ├── usuarios.php          # Gestión de usuarios (conectado a BD)
│   │       └── roles.php             # Gestión de roles y permisos (conectado a BD)
│   ├── Database/
│   │   ├── estructura.sql            # Esquema BD v3.0 (27 tablas)
│   │   ├── seed_data.sql             # Datos de prueba
│   │   ├── seed_data_masivo.sql      # Datos masivos de prueba
│   │   └── reportes_ejemplo.sql      # Consultas de ejemplo para reportes
│   └── Public/
│       ├── css/
│       │   ├── styles.css            # Estilos personalizados
│       │   ├── login.css             # Estilos login
│       │   ├── materialize.min.css   # Materialize CSS (local)
│       │   └── material-icons.css    # Material Icons (local)
│       ├── js/
│       │   ├── jquery-3.7.1.min.js   # jQuery (local)
│       │   ├── materialize.min.js    # Materialize JS (local)
│       │   ├── app.core.js           # Utilidades compartidas (EIS, debounce)
│       │   ├── app.init.js           # Inicialización Materialize, reloj, tema
│       │   ├── app.tables.js         # Búsqueda y filtro de tablas
│       │   ├── app.ui.js             # Notificaciones, botones, tooltips
│       │   ├── app.pos.js            # Sistema de carrito POS
│       │   ├── app.cyber.js          # Gestión de estaciones Cyber
│       │   ├── app.legal.js          # Validación de asesoría legal
│       │   ├── app.inventario.js     # CRUD inventario vía AJAX
│       │   ├── app.roles.js          # CRUD roles/permisos vía AJAX
│       │   └── app.proveedores.js    # CRUD proveedores vía AJAX
│       └── fonts/
│           └── MaterialIcons-Regular.ttf  # Material Icons (local)
├── docs/                             # Documentación del proyecto
├── vendor/                           # Autoloader de Composer
└── composer.json                     # PSR-4: "App\\": "src/app/"
```

---

## Tecnologías Utilizadas

### Backend
- **PHP 7.4+** — Lenguaje principal
- **PDO (PHP Data Objects)** — Capa de abstracción de BD con prepared statements
- **MySQL 8.0+ / MariaDB 10.3+** — Sistema de gestión de BD
- **Motor InnoDB** — Soporte para transacciones y claves foráneas
- **Composer** — Autocargador de clases PSR-4

### Frontend
- **Materialize CSS 1.0.0** — Framework de diseño Material Design (local)
- **jQuery 3.7.1** — Manipulación del DOM y eventos (local)
- **Material Icons** — Iconografía (local)
- **HTML5** — Estructura semántica
- **CSS3** — Variables CSS, Flexbox, Grid, Media Queries, tema oscuro/claro
- **Service Worker** — Caché offline de assets estáticos
- **PWA** — Manifest.json para instalación como app

---

## Módulos del Sistema

| Módulo | Vista | JS Asociado | Estado |
|--------|-------|-------------|--------|
| **Login** | `login.php` | `app.core.js` | Funcional |
| **Dashboard** | `dashboard.php` | `app.init.js`, `app.ui.js` | UI Estática |
| **Inventario** | `inventario.php` | `app.inventario.js`, `app.tables.js` | Funcional con BD |
| **Punto de Venta** | `ventas.php` | `app.pos.js` | Semi-funcional* |
| **Cyber Control** | `ciberControl.php` | `app.cyber.js` | Interactivo* |
| **Proveedores** | `proveedores.php` | `app.proveedores.js`, `app.tables.js` | Funcional con BD |
| **Clientes** | `clientes.php` | `app.proveedores.js` | Funcional con BD |
| **Reportes** | `reportes.php` | `app.ui.js` | Simulado |
| **Activos** | `activos.php` | `app.tables.js` | UI Estática |
| **Asesoría Legal** | `asesorias.php` | `app.legal.js` | Semi-funcional* |
| **Menú** | `menu.php` | `app.init.js`, `app.ui.js` | Funcional |
| **Usuarios** | `usuarios.php` | `app.core.js` | Funcional con BD |
| **Roles y Permisos** | `roles.php` | `app.roles.js` | Funcional con BD |

*Funcionalidad del lado del cliente (JavaScript/jQuery) pero sin persistencia en BD.

---

## Instalación y Configuración

### Requisitos
- PHP 7.4 o superior
- MySQL 8.0 o superior / MariaDB 10.3+
- Servidor web (Apache/Nginx/XAMPP/WAMP/Laragon)
- Composer (para el autoloader)

### Pasos

1. **Clonar el repositorio**
   ```bash
   git clone <url-del-repositorio>
   cd eis_zona_web_lara
   ```

2. **Instalar dependencias (autoloader)**
   ```bash
   composer install
   ```

3. **Configurar la base de datos**
   
   Editar `src/Config/database.php` (o la conexión en `src/app/core/Database.php`):
   ```php
   $host = "localhost";
   $db = "zona_web_lara";
   $user = "root";
   $pass = "";  // Cambiar por tu contraseña
   ```

4. **Crear la base de datos**
   ```bash
   mysql -u root -p < src/Database/estructura.sql
   mysql -u root -p < src/Database/seed_data.sql
   ```

5. **Configurar el servidor web**
   
   Asegúrate de que el directorio raíz del virtual host apunte a la carpeta `src/`.

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
Navegador → src/.htaccess → src/index.php → App\Core\Router::handle()
                                                      │
                                               new Router()
                                                      │
                                              session_start()
                                                      │
                                              $this->resolvePage()
                                                      │
                                              preg_match (seguridad)
                                                      │
                                     ┌─────────────────┼────────────────────┐
                                     ▼                 ▼                    ▼
                               ¿Es AJAX?        ¿Es auth?             ¿Es vista?
                              (action param)  (login/logout)         (normal)
                                     │                 │                    │
                                     ▼                 ▼                    ▼
                            require Controller   AuthController       renderView()
                            (Inventario/Rol/     (login/logout)     (layout + vista)
                             Proveedor)                                  │
                                                                   ¿Autenticado?
                                                                         │
                                                              ┌──────────┴──────────┐
                                                              ▼                     ▼
                                                         Página pública         Página privada
                                                         (login)                (dashboard, etc.)
                                                              │                     │
                                                              ▼                     ▼
                                                         Vista directa        require layout.php
                                                                              (incluye $contentView)
```

### Enrutador (`router.php` - Clase OOP)
- Clase `Router` en namespace `App\Core` con método `handle()`
- Constructor llama a `session_start()` y `resolvePage()` (valida con regex)
- Genera token CSRF (`bin2hex(random_bytes(32))`) y lo inyecta en `window.EIS.csrfToken`
- Detecta peticiones AJAX por `?action=` y deriva a `ClienteController`, `InventarioController`, `RolController`, `ProveedorController` o `ProveedorGestionController`
- Acciones de autenticación (`login_validate`, `logout`) se derivan a `AuthController`
- `renderView()` carga vistas públicas (sin layout) o protegidas (con `layout.php`)
- Manejo de errores 404 con `http_response_code(404)`

### Layout (`layout.php`)
- Sidebar con Materialize Sidenav (10 módulos + theme toggle + cerrar sesión)
- Header con nav, reloj digital, notificaciones, header extra
- Contenedor `<main>` que incluye la vista específica
- Botón "volver arriba"
- Carga condicional de JS por página (app.pos.js, app.cyber.js, app.legal.js)
- Service Worker registration

### JavaScript Modular
- **`app.core.js`** — Namespace `EIS`, `debounce()`, `filtrarTabla()`, `EIS.toast()`, helper `escHtml()` para XSS
- **`app.init.js`** — Inicialización Materialize, reloj, tema, animaciones
- **`app.tables.js`** — Búsqueda en tablas, filtro por estado, paginación
- **`app.ui.js`** — Notificaciones, botones, reportes, tooltips
- **`app.pos.js`** — Sistema de carrito POS (solo en ventas)
- **`app.cyber.js`** — Gestión de estaciones Cyber (solo en ciberControl)
- **`app.legal.js`** — Validación de documentos legales (solo en asesorias)
- **`app.inventario.js`** — CRUD completo de inventario vía AJAX (solo en inventario)
- **`app.roles.js`** — CRUD completo de roles y permisos vía AJAX (solo en roles)
- **`app.proveedores.js`** — CRUD completo de proveedores, solicitudes y clientes vía AJAX
- **CSRF automático** — `$.ajaxSetup` inyecta token CSRF en todas las peticiones AJAX desde `window.EIS.csrfToken`

### Offline / PWA
- Assets locales (sin CDNs)
- Service Worker (`sw.js`) con estrategia Cache First para assets
- Página offline (`offline.php`) como fallback
- Manifest (`manifest.json`) para instalación

---

## Documentación Disponible

### `docs/Documentacion/DOCUMENTACION.md`
Documentación técnica línea por línea de todo el código fuente.

### `docs/Documentacion/DOCUMENTACION_JQUERY.md`
Documentación específica de la integración de jQuery 3.7.1 y Materialize CSS.

### `docs/Documentacion/DOCUMENTACION_COMPLETA.md`
Documentación completa para NotebookLM.

### `docs/Documentacion/routing-system.md`
Documentación del sistema de enrutamiento.

### `docs/Fases del diseño de la base de datos/`
Documentación completa de la base de datos (v2.0):
- **Conceptual**: Diagramas ER, entidades, relaciones, reglas de negocio
- **Lógico**: Esquemas SQL, tipos de datos, índices, normalización
- **Físico**: Almacenamiento InnoDB, particionamiento, configuración MySQL

---

## Problemas Conocidos

### Arquitectura
1. **Datos Estáticos** — Dashboard, ventas, cyber, activos, reportes y asesorías aún no persisten en BD
2. **Sin .env** — Configuración no flexible (credenciales en Database.php como constantes)

---

## Próximos Pasos Recomendados

### Fase 1: Conexión a Base de Datos
- [x] Módulo de Inventario completo con CRUD + AJAX + BD
- [x] Módulo de Usuarios completo con CRUD + AJAX + BD
- [x] Módulo de Roles y Permisos completo con CRUD + AJAX + BD
- [x] Módulo de Proveedores completo con CRUD + AJAX + BD
- [x] Módulo de Clientes completo con CRUD + AJAX + BD
- [ ] Conectar Dashboard con consultas SQL reales
- [ ] Hacer que el carrito POS persista ventas en BD
- [ ] Persistir cambios de estado en cybercafé

### Fase 2: Mejoras de Arquitectura
- [x] Convertir a MVC con clases y namespaces (Router, Database, Model, Controllers)
- [ ] Implementar Request como clase encapsuladora
- [ ] Agregar sistema de middleware (auth, CSRF)
- [ ] Implementar URLs limpias (/nombre en lugar de ?pagina=nombre)

### Fase 3: Seguridad
- [x] Implementar `password_hash()` para contraseñas (Bcrypt)
- [x] Agregar CSRF tokens en todas las peticiones
- [x] Sanitizar entrada de datos (validación backend + XSS sanitization)
- [x] Prepared statements con PDO (sin emulación)
- [x] Session hardening (`session_regenerate_id` en login)
- [ ] Usar variables de entorno (.env) para credenciales

### Fase 4: Funcionalidad
- [ ] Persistencia de ventas en BD
- [ ] Cálculo real de tiempos en cybercafé
- [ ] Generación real de reportes (PDF/Excel)
- [ ] Conexión de dashboard a BD real

---

## Estadísticas del Proyecto

| Métrica | Valor |
|---------|-------|
| Archivos PHP | 30+ |
| Vistas | 14 |
| Modelos funcionales | 9 (7 POO con namespace + 2 legacy procedurales) |
| Controladores | 6 (Cliente, Inventario, Auth, Rol, Proveedor, ProveedorGestion) |
| Archivos Core OOP | 3 (Database, Model, Router) |
| Archivos CSS | 4 |
| Archivos JS | 12 (2 librerías + 10 módulos) |
| Archivos SQL | 4 (estructura + 3 seed) |
| Tablas en BD | 27 |
| Módulos del sistema | 13 |
| Dependencias CDN | 0 (100% local) |

---

## Autor

**Carlos Páez Guerra**
Email: carlospaezguerra@gmail.com

---

## Historial de Versiones

| Versión | Fecha | Descripción |
|---------|-------|-------------|
| 3.3 | Jul 2026 | Validación backend completa: helpers reutilizables (Model), non-empty/min-length en setters, FK existence checks, duplicados, coherencia de datos. Eliminación de double escaping. |
| 3.2 | Jul 2026 | Seguridad: CSRF tokens, XSS sanitización, session hardening, validación frontend+backend, módulo Clientes, módulo ProveedorGestion, 6 controladores, 7 modelos POO |
| 3.1 | 2026 | Migración a OOP: Router clase, Database Singleton, Model abstracto. Nuevos módulos: Usuarios, Roles/Permisos, Proveedores con CRUD AJAX+BD. 27 tablas en BD. |
| 3.0 | 2026 | Módulo de Inventario completo con MVC+AJAX, controlador PHP, CRUD con BD, `app.inventario.js` |
| 2.1 | 2026 | JS modular (8 archivos), assets locales, Service Worker, PWA, offline |
| 2.0 | 2026 | Refactorización con Materialize CSS + jQuery + Layout maestro |
| 1.1 | 2026 | Agregado módulo de Asesoría Legal, actualización de BD a v2.0 (19 tablas) |
| 1.0 | 2024 | Versión inicial — UI Prototype procedural |

---

**Última actualización**: Julio 2026
**Estado**: En desarrollo (Inventario, Usuarios, Roles, Proveedores funcionales con MVC+AJAX+BD; resto UI prototipo)

