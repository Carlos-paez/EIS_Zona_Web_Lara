# DOCUMENTACION TECNICA DETALLADA - EIS_Zona_Web_Lara

## Indice
1. [Descripcion General](#descripcion-general)
2. [Flujo de la Aplicacion](#flujo-de-la-aplicacion)
3. [Analisis de Codigo Fuente](#analisis-de-codigo-fuente)
4. [Explicacion Detallada por Archivo](#explicacion-detallada-por-archivo)
5. [Layout Maestro](#layout-maestro)
6. [JavaScript Modular](#javascript-modular)
7. [Base de Datos](#base-de-datos)
8. [Offline y PWA](#offline-y-pwa)
9. [CSS y Estilos](#css-y-estilos)

---

## Descripcion General

**EIS System** es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con interfaz **Material Design** (Materialize CSS 1.0.0) y **jQuery 3.7.1**. Utiliza un patron **Front Controller** con enrutador OOP (clase `Router`) y layout maestro, siguiendo una arquitectura **MVC** con namespaces PSR-4.

El proyecto administra un negocio que incluye: cybercafe, ventas POS, inventario, usuarios, roles/permisos, proveedores, activos, asesoria legal, reportes.

**Caracteristicas tecnicas destacadas**:
- Assets 100% locales (sin dependencia de CDN)
- JavaScript modular en 15 archivos especializados mas el motor de jQuery DataTables
- Service Worker para funcionamiento offline
- PWA con manifest.json
- Tema oscuro/claro con persistencia en localStorage
- Motor de busqueda con debounce en tablas
- Namespace `App\Core`, `App\Models`, `App\Controllers` con autoloading PSR-4
- Patron Singleton para conexion PDO (clase `Database`)
- Clase base abstracta `Model` con helpers de validacion reutilizables
- 12 controladores con namespace para CRUD via AJAX
- Seguridad: CSRF tokens, XSS sanitizacion, session hardening, validacion backend completa

---

## Flujo de la Aplicacion

```
1. Usuario accede a index.php
   |
2. index.php: require autoload.php + uso de namespace App\Core\Router
   |
3. new Router() -> constructor: session_start() + resolvePage() + CSRF token
   |
4. resolvePage(): Obtiene parametro "pagina" de la URL (?pagina=X)
   |
5. resolvePage(): Valida que el nombre sea alfanumerico (seguridad regex)
   |
6. Router::handle():
   |
7. ¿Existe pagina y action en el mapa CONTROLLERS? -> dispatchAction()
   |
8. dispatchAction(): Instancia el controlador segun self::CONTROLLERS[$pagina] y ejecuta handle()
   |
9. ¿Es accion de auth? (login_validate/logout) -> AuthController
   |
10. Si no es ninguna accion especial -> render()
   |
11. render(): Verifica si el usuario esta logueado
   |
12. Si no esta logueado Y la pagina no es publica -> Redirige a login
   |
13. Si la pagina es publica (login) -> Carga directa
   |
14. Si es pagina autenticada -> render(): Carga layout.php que incluye:
    |  - CSRF token en window.EIS.csrfToken y <input name="csrf_token">
    |  - Sidebar con Materialize Sidenav (13 modulos)
    |  - Header con nav, reloj y notificaciones
    |  - Contenido especifico de la vista
    |  - Scripts: Materialize JS + 14 modulos JS segun pagina
    |  - Service Worker registration
    |
15. Si el archivo no existe -> Muestra error 404
```

---

## Analisis de Codigo Fuente

### Estadisticas del Proyecto

| Archivo | Lineas | Proposito |
|---------|--------|----------|
| src/index.php | 21 | Front Controller (autoloader + Router OOP) |
| src/Config/database.php | 46 | Configuracion BD (legacy) |
| src/app/core/Database.php | 81 | Conexion PDO Singleton (moderna) |
| src/app/core/Model.php | 200+ | Clase base abstracta con helpers de validacion |
| src/app/core/router.php | — | Enrutador OOP (clase Router, CSRF tokens, mapa CONTROLLERS, dispatchAction, auth, vistas) |
| src/app/template/layout.php | 201 | Layout maestro con CSRF token + JS condicional (14 modulos JS) |
| src/app/Controllers/AuthController.php | — | Controlador login/logout con session_regenerate_id |
| src/app/Controllers/ClienteController.php | — | Controlador AJAX clientes |
| src/app/Controllers/inventarioController.php | — | Controlador AJAX inventario |
| src/app/Controllers/ProveedorController.php | — | Controlador AJAX proveedores (solicitudes) |
| src/app/Controllers/ProveedorGestionController.php | — | Controlador AJAX proveedores (gestion) |
| src/app/Controllers/RolController.php | — | Controlador AJAX roles/permisos |
| src/app/Controllers/ActivoController.php | — | Controlador AJAX activos |
| src/app/Controllers/AsesoriaController.php | — | Controlador AJAX asesorias |
| src/app/Controllers/CiberController.php | — | Controlador AJAX cybercafe |
| src/app/Controllers/DashboardController.php | — | Controlador AJAX dashboard |
| src/app/Controllers/ReporteController.php | — | Controlador AJAX reportes |
| src/app/Controllers/VentaController.php | — | Controlador AJAX ventas (POS) |
| src/app/Models/Cliente.php | — | Modelo POO clientes |
| src/app/Models/Inventario.php | — | Modelo POO inventario (namespace) |
| src/app/Models/Usuario.php | — | Modelo POO usuarios |
| src/app/Models/Proveedor.php | — | Modelo POO proveedores (solicitudes) |
| src/app/Models/ProveedorGestion.php | — | Modelo POO proveedores (gestion) |
| src/app/Models/Rol.php | — | Modelo POO roles/permisos |
| src/app/Models/Asesoria.php | — | Modelo POO asesorias |
| src/app/Models/Activo.php | — | Modelo POO activos fijos |
| src/app/Models/Venta.php | — | Modelo POO ventas |
| src/app/Models/Reporte.php | — | Modelo POO reportes |
| src/app/Models/Dashboard.php | — | Modelo POO dashboard |
| src/app/Models/CiberControl.php | — | Modelo POO control cyber |
| src/app/Models/CiberModel.php | — | Modelo POO sesiones cyber |
| src/app/Models/crud_users.php | 54 | CRUD usuarios legacy (8 funciones) |
| src/app/Models/crud_asesorias.php | 49 | CRUD asesorias legacy (8 funciones) |
| src/app/Views/login.php | — | Pagina login |
| src/app/Views/login_validate.php | — | Validacion login (legacy) |
| src/app/Views/dashboard.php | — | Panel principal |
| src/app/Views/menu.php | — | Menu navegacion |
| src/app/Views/inventario.php | — | Gestion inventario (conectado a BD) |
| src/app/Views/ventas.php | — | Punto de venta |
| src/app/Views/proveedores.php | — | Solicitudes a proveedores (conectado a BD) |
| src/app/Views/clientes.php | — | Gestion de clientes (conectado a BD) |
| src/app/Views/reportes.php | — | Reportes |
| src/app/Views/activos.php | — | Activos fijos |
| src/app/Views/ciberControl.php | — | Control cyber |
| src/app/Views/asesorias.php | — | Asesoria legal |
| src/app/Views/usuarios.php | — | Gestion usuarios (conectado a BD) |
| src/app/Views/roles.php | — | Roles y permisos (conectado a BD) |
| src/Public/css/styles.css | — | Estilos personalizados |
| src/Public/css/login.css | — | Estilos login |
| src/Public/css/material-icons.css | — | Estilos Material Icons |
| src/Public/css/materialize.min.css | — | Materialize CSS (local) |
| src/Public/css/dataTables.materialize.css | — | Tema de DataTables adaptado a Materialize y tema oscuro/claro |
| src/Public/js/app.core.js | — | Funciones compartidas (incluye helpers `EIS.datatable*`) |
| src/Public/js/app.init.js | — | Inicializacion Materialize |
| src/Public/js/app.selects.js | — | Barra de busqueda en selects de Materialize |
| src/Public/js/jquery.dataTables.min.js | — | Motor jQuery DataTables 1.13.8 (local, sin CDN) |
| src/Public/js/dataTables.materialize.js | — | Integracion de DataTables con Materialize y config por defecto (es/i18n) |
| src/Public/js/app.tables.js | — | Punto de extension generico (la busqueda/filtro/paginacion la gestiona DataTables) |
| src/Public/js/app.ui.js | — | UI notificaciones |
| src/Public/js/app.pos.js | — | Sistema POS |
| src/Public/js/app.cyber.js | — | Estaciones cyber |
| src/Public/js/app.legal.js | — | Asesoria legal |
| src/Public/js/app.inventario.js | — | CRUD inventario via AJAX |
| src/Public/js/app.roles.js | — | CRUD roles/permisos via AJAX |
| src/Public/js/app.proveedores.js | — | CRUD proveedores via AJAX |
| src/manifest.json | 15 | Manifiesto PWA |
| src/sw.js | 83 | Service Worker |
| src/offline.php | 43 | Pagina offline |
| src/Database/estructura.sql | 243 | Esquema BD v3.0 (21 tablas) |
| src/Database/seed_data.sql | — | Datos prueba |
| src/Database/seed_data_masivo.sql | — | Datos masivos prueba |
| src/Database/reportes_ejemplo.sql | — | Consultas ejemplo reportes |

---

## Explicacion Detallada por Archivo

### 1. `src/index.php` (21 lineas)

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Router;
$router = new Router();
$router->handle();
```

Punto de entrada unico (Front Controller). Todas las solicitudes pasan por aqui gracias a las reglas de reescritura de Apache (.htaccess). Ahora usa el autoloader de Composer y la clase `Router` con namespace.

---

### 2. `src/app/core/router.php` (clase OOP) - EL CEREBRO DE LA APLICACION

```php
<?php
namespace App\Core;

class Router
{
    private string $pagina;
    private const CONTROLLERS = [
        'clientes'          => ClienteController::class,
        'inventario'        => InventarioController::class,
        'ventas'            => VentaController::class,
        'roles'             => RolController::class,
        'proveedores'       => ProveedorController::class,
        'proveedores-gestion' => ProveedorGestionController::class,
        'asesorias'         => AsesoriaController::class,
        'ciberControl'      => CiberController::class,
        'dashboard'         => DashboardController::class,
        'reportes'          => ReporteController::class,
        'activos'           => ActivoController::class,
    ];

    public function __construct()
    {
        session_start();
        // Token CSRF: se genera una sola vez por sesión y se guarda en $_SESSION
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->pagina = $this->resolvePage();
    }

    public function handle(): void
    {
        if ($this->isAuthAction()) {
            $this->runAuthAction(); return;
        }
        if (array_key_exists($this->pagina, self::CONTROLLERS) && isset($_GET['action'])) {
            $this->dispatchAction(); return;
        }
        $this->render();
    }

    private function resolvePage(): string { /* valida con regex */ }
    private function isAuthAction(): bool { return $this->pagina === 'login_validate' || $this->pagina === 'logout'; }
    private function dispatchAction(): void { /* instancia el controlador segun CONTROLLERS[$pagina] y ejecuta handle() */ }
    private function runAuthAction(): void { /* AuthController::login() o logout() */ }
    private function render(): void { /* verifica autenticacion y carga vista/layout */ }
    private function requireAuth(): void { /* JSON error si no autenticado */ }
}
```

**Metodos clave**:

| Metodo | Explicacion |
|--------|-------------|
| `__construct()` | Inicia sesion, genera CSRF token, resuelve `$pagina` via `resolvePage()` |
| `handle()` | Determina el tipo de peticion y ejecuta la accion correspondiente |
| `CONTROLLERS` | Mapa `pagina => clase controlador` que centraliza la resolucion de controladores |
| `resolvePage()` | Lee `$_GET["pagina"]` y valida con `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` |
| `isAuthAction()` | True si `pagina=login_validate` o `pagina=logout` |
| `dispatchAction()` | Instancia el controlador indicado por `CONTROLLERS[$pagina]` y ejecuta su `handle()` para peticiones AJAX |
| `runAuthAction()` | Login: `new AuthController()->login()`, Logout: `->logout()` |
| `render()` | Verifica autenticacion, carga vista (publica) o el layout maestro con CSRF token |
| `requireAuth()` | Verifica `$_SESSION['logged_in']`, si no existe -> JSON error |

**Mejora sobre la version anterior**: Ahora usa un mapa `CONTROLLERS` que centraliza los 12 controladores AJAX, con `dispatchAction()` para despachar peticiones y `render()` para renderizar vistas, ademas de CSRF tokens, session hardening, verificacion de autenticacion en peticiones AJAX con respuesta JSON, y autoloading PSR-4.

---

### 3. `src/app/template/layout.php` (201 lineas) - LAYOUT MAESTRO

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a237e">
    <link rel="manifest" href="manifest.json">
    <title><?php echo $pageTitle; ?> - EIS System</title>
    <link rel="stylesheet" href="Public/css/material-icons.css">
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    <link rel="stylesheet" href="Public/css/styles.css">
    <script src="Public/js/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- Sidebar: 12 modulos -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <li><a href="?pagina=dashboard">Dashboard</a></li>
        <li><a href="?pagina=inventario">Inventario</a></li>
        <li><a href="?pagina=ventas">Ventas (POS)</a></li>
        <li><a href="?pagina=proveedores">Solicitudes</a></li>
        <li><a href="?pagina=ciberControl">Cyber</a></li>
        <li><a href="?pagina=reportes">Reportes</a></li>
        <li><a href="?pagina=activos">Activos</a></li>
        <li><a href="?pagina=asesorias">Asesoria Legal</a></li>
        <li><a href="?pagina=usuarios">Configuracion</a></li>
        <li><a href="?pagina=roles">Roles y Permisos</a></li>
        <li><a id="themeToggle">Modo Oscuro</a></li>
        <li><a href="?pagina=login">Cerrar Sesion</a></li>
    </ul>
    <header>
        <nav class="nav-extended indigo darken-3">
            <span><?php echo $pageTitle; ?></span>
            <span id="clock">Cargando...</span>
            <span id="notifBell">Notificaciones</span>
            <span>Admin</span>
            <?php if (!empty($headerExtra)) echo $headerExtra; ?>
        </nav>
    </header>
    <main>
        <div class="container"><?php require $contentView; ?></div>
    </main>

    <!-- Scripts globales -->
    <script src="Public/js/materialize.min.js"></script>
    <script src="Public/js/jquery.dataTables.min.js"></script>
    <script src="Public/js/dataTables.materialize.js"></script>
    <script src="Public/js/app.core.js"></script>
    <script src="Public/js/app.init.js"></script>
    <script src="Public/js/app.selects.js"></script>
    <script src="Public/js/app.tables.js"></script>
    <script src="Public/js/app.ui.js"></script>

    <!-- Scripts condicionales (modulos por pagina) -->
    <?php if ($pagina === 'ventas'): ?><script src="Public/js/app.pos.js"></script><?php endif; ?>
    <?php if ($pagina === 'ciberControl'): ?><script src="Public/js/app.cyber.js"></script><?php endif; ?>
    <?php if ($pagina === 'asesorias'): ?><script src="Public/js/app.legal.js"></script><?php endif; ?>
    <?php if ($pagina === 'inventario'): ?><script src="Public/js/app.inventario.js"></script><?php endif; ?>
    <?php if ($pagina === 'roles'): ?><script src="Public/js/app.roles.js"></script><?php endif; ?>
    <?php if ($pagina === 'proveedores'): ?><script src="Public/js/app.proveedores.js"></script><?php endif; ?>
    <?php if ($pagina === 'proveedores-gestion'): ?><script src="Public/js/app.proveedores-gestion.js"></script><?php endif; ?>
    <?php if ($pagina === 'clientes'): ?><script src="Public/js/app.clientes.js"></script><?php endif; ?>
    <?php if ($pagina === 'activos'): ?><script src="Public/js/app.activos.js"></script><?php endif; ?>
    <?php if ($pagina === 'reportes'): ?><script src="Public/js/app.reportes.js"></script><?php endif; ?>

    <!-- Service Worker -->
    <script>if ('serviceWorker' in navigator) { navigator.serviceWorker.register('sw.js'); }</script>
</body>
</html>
```

**Componentes del layout**:

| Componente | Descripcion |
|-----------|-------------|
| `sidenav` | Sidebar fijo con Materialize Sidenav (12 items: 10 modulos + theme toggle + cerrar sesion) |
| `nav` | Barra superior con titulo dinamico, reloj, notificaciones, usuario, header extra |
| `container` | Contenedor central donde se renderiza `$contentView` |
| `backToTop` | Boton flotante para volver arriba |
| Modulos JS | 5 archivos base + condicionales por pagina (pos, cyber, legal, inventario, roles, proveedores, proveedores-gestion, clientes, activos, reportes) |
| `sw.js` | Service Worker para cache offline |

**Variables PHP pasadas desde router.php**:

| Variable | Proposito |
|----------|-----------|
| `$pageTitle` | Titulo de la pagina (ej: "Panel de Control") |
| `$headerExtra` | HTML extra en el header (ej: badges de cyber) |
| `$contentView` | Ruta al archivo de vista a incluir |
| `$pagina` | Nombre de la pagina actual (para clase `active` en sidebar y carga condicional de JS) |

---

### 4. Core OOP: `Database.php` y `Model.php`

#### `src/app/core/Database.php` (81 lineas)
Clase que implementa el **patron Singleton** para la conexion PDO:

```php
namespace App\Core;
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = 'localhost'; $db = 'zona_web_lara';
            $user = 'root'; $pass = ''; $charset = 'utf8mb4';
            $dns = "mysql:host=$host;dbname=$db;charset=$charset";
            self::$instance = new PDO($dns, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$instance;
    }
}
```

#### `src/app/core/Model.php`
Clase base abstracta que todos los modelos extiende, con helpers de validacion reutilizables:

```php
namespace App\Core;
abstract class Model {
    protected PDO $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Helpers de sanitizacion
    protected function sanitizeString($value): string { return htmlspecialchars(trim($value), ...); }
    protected function sanitizeInt($value): int { return (int)$value; }
    protected function sanitizeFloat($value): float { return (float)$value; }
    
    // Helpers de validacion
    protected function validateNotEmpty($value, string $fieldName): void { /* lanza InvalidArgumentException */ }
    protected function validateMinLength($value, int $min, string $fieldName): void { /* lanza InvalidArgumentException */ }
    protected function validateLength($value, int $min, int $max, string $fieldName): void { /* lanza InvalidArgumentException */ }
    protected function validatePattern($value, string $pattern, string $fieldName, string $message): void { /* lanza InvalidArgumentException */ }
    protected function validatePositive($value, string $fieldName): void { /* lanza InvalidArgumentException */ }
    protected function validateGreaterOrEqual($value, int $min, string $fieldName): void { /* lanza InvalidArgumentException */ }
    protected function validateEmail($value, string $fieldName): void { /* lanza InvalidArgumentException */ }
    
    // Helpers de verificacion en BD
    protected function existeEnTabla(string $tabla, string $columna, $valor): bool { /* COUNT(*) > 0 */ }
}
```

#### `src/Config/database.php` (46 lineas) — Legacy
Configuracion de conexion PDO legacy (crea variable `$pdo`), usada por `crud_users.php` y `crud_asesorias.php`:
- Host: localhost
- Base de datos: `zona_web_lara`
- Charset: utf8mb4
- Modo error: PDO::ERRMODE_EXCEPTION
- Fetch mode: PDO::FETCH_ASSOC
- Prepared statements reales (EMULATE_PREPARES = false)

---

### 5. Modelos con Namespace (`src/app/Models/`)

El proyecto tiene 2 tipos de modelos:

#### 5.1 Modelos POO (namespace `App\Models`, extienden `Model`)

| Clase | Archivo | Tabla | Proposito |
|-------|---------|-------|-----------|
| `Cliente` | `Cliente.php` | `clientes` | CRUD clientes, validacion cedula (min 5), unicidad cedula |
| `Inventario` | `Inventario.php` | `productos` | CRUD productos, KPIs, movimientos stock, FK categoria |
| `Usuario` | `Usuario.php` | `usuarios` | CRUD usuarios, autenticacion con password_hash |
| `Proveedor` | `Proveedor.php` | `proveedores`, `orden_abastecimiento` | CRUD proveedores, ordenes de abastecimiento, FK proveedor/status |
| `ProveedorGestion` | `ProveedorGestion.php` | `proveedores` | CRUD proveedores (gestion), unicidad RIF, email format |
| `Rol` | `Rol.php` | `roles`, `permisos`, `permisos_rol`, `rol_usuarios` | CRUD roles, asignacion permisos, unicidad nombre, **descripcion** y **created_at** (v4.1) |
| `Asesoria` | `Asesoria.php` | `asesoria` | CRUD asesorias legales |
| `Activo` | `Activo.php` | `activos` | CRUD activos fijos |
| `Venta` | `Venta.php` | `orden_de_venta`, `lineas_venta` | CRUD ventas y lineas de venta |
| `Reporte` | `Reporte.php` | — | Generacion de reportes |
| `Dashboard` | `Dashboard.php` | — | Metricas del panel principal |
| `CiberControl` | `CiberControl.php` | `sesion_ciber` | Control de estaciones cyber |
| `CiberModel` | `CiberModel.php` | `sesion_ciber` | Sesiones de cybercafe |

Todos heredan de `App\Core\Model` y usan `$this->db` (conexion PDO Singleton).

**Validaciones por modelo:**
- **Cliente**: cedula min 5, nombre min 2, apellido min 2, todos no vacios, unicidad cedula
- **Inventario**: stock >= 0, stock_minimo >= 1, costo_compra >= 0, precio_venta > 0, FK categoria, unicidad codigo/nombre
- **Proveedor**: FK proveedor/status, fecha formato YYYY-MM-DD, numero no vacio, cantidad >= 1, precio > 0
- **ProveedorGestion**: RIF min 5, nombre min 2, email format, telefono no vacio, unicidad RIF
- **Rol**: nombre min 2, todos no vacios, unicidad nombre, proteccion admin (id=1)

#### 5.2 Modelos Legacy (funciones sueltas)

- `crud_users.php` (54 lineas) — 8 funciones CRUD para `usuarios`:
  `crearUsuario()`, `obtenerUsuarios()`, `obtenerUsuarioPorId()`, `obtenerUsuarioPorUsername()`, `autenticarUsuario()`, `actualizarUsuario()`, `actualizarPassword()`, `eliminarUsuario()`

- `crud_asesorias.php` (49 lineas) — 8 funciones CRUD para `asesoria`:
  `crearAsesoria()`, `obtenerAsesorias()`, `obtenerAsesoriasPorEstado()`, `obtenerAsesoriaPorId()`, `buscarAsesoriasPorCedula()`, `actualizarAsesoria()`, `eliminarAsesoria()`, `contarAsesoriasPorEstado()`

#### 5.3 Controladores (namespace `App\Controllers`)

| Clase | Archivo | Acciones |
|-------|---------|----------|
| `AuthController` | `AuthController.php` | `login()` — valida credenciales vs BD, `logout()` — destruye sesion + session_regenerate_id |
| `ClienteController` | `ClienteController.php` | `handle()` — CRUD clientes via AJAX con validacion |
| `InventarioController` | `inventarioController.php` | `handle()` — 10+ acciones CRUD via AJAX |
| `RolController` | `RolController.php` | `handle()` — CRUD roles y permisos via AJAX con proteccion admin |
| `ProveedorController` | `ProveedorController.php` | `handle()` — CRUD proveedores y ordenes via AJAX |
| `ProveedorGestionController` | `ProveedorGestionController.php` | `handle()` — CRUD proveedores (gestion) via AJAX |
| `ActivoController` | `ActivoController.php` | `handle()` — CRUD activos fijos via AJAX |
| `AsesoriaController` | `AsesoriaController.php` | `handle()` — CRUD asesorias via AJAX |
| `CiberController` | `CiberController.php` | `handle()` — control de estaciones cyber via AJAX |
| `DashboardController` | `DashboardController.php` | `handle()` — metricas del dashboard via AJAX |
| `ReporteController` | `ReporteController.php` | `handle()` — generacion de reportes via AJAX |
| `VentaController` | `VentaController.php` | `handle()` — ventas POS via AJAX |

**Seguridad en controladores:**
- CSRF token en todas las peticiones AJAX
- `session_regenerate_id(true)` en login/logout
- Prepared statements con PDO
- Sin double escaping (Model maneja sanitizacion)
- Excepciones `\PDOException` (DB oculta), `\InvalidArgumentException` (validacion), `\Exception` (generico)

---

### 6. `src/app/Views/login.php` (134 lineas)

Pagina de login con diseno Material Design. NO usa el layout (pagina publica).

**Caracteristicas**:
- Formulario con campos usuario y contraseña
- Tema oscuro/claro con localStorage
- Botones sociales (Google/GitHub) no funcionales
- Mensaje de error si `?error=1`
- Material Icons y Materialize CSS locales
- Carga `app.core.js` para el namespace EIS

---

### 7. `src/app/Views/login_validate.php` (30 lineas)

Procesa la autenticacion con credenciales hardcodeadas (admin/1234).
- Si exito: `$_SESSION['logged_in'] = true`, redirige a dashboard
- Si falla: redirige a `?pagina=login&error=1`

---

### 8. Vistas Autenticadas (Contenido Solo)

Todas las vistas autenticadas son solo fragmentos HTML sin estructura completa. El layout maestro provee el HTML comun.

#### 8.1 `dashboard.php` (130 lineas)
Panel principal con banner de bienvenida, 4 metricas (Ventas Hoy, Stock Critico, Sesiones Cyber, Solicitudes Pendientes), tablas de horas pico y productos sin stock, y actividad reciente.

#### 8.2 `ventas.php` (130 lineas) - PUNTO DE VENTA (POS) (FUNCIONAL CON BD)
Catalogo de productos desde BD con carrito modal. Toda la logica en `app.pos.js` + `VentaController.php` + `Venta.php`.
- Click en producto agrega al carrito (data-name, data-price)
- Modal del carrito con total, eliminar, vaciar y procesar
- Busqueda de productos con debounce 200ms
- Registro de ordenes de venta y lineas en BD

#### 8.3 `ciberControl.php` (133 lineas) - CONTROL DE CYBERCAFE (FUNCIONAL CON BD)
Estaciones organizadas en 3 zonas (Gamer, Estandar, VIP) con estados disponible/ocupada/mantenimiento. Conectado a BD via `CiberController.php` + `CiberControl.php`/`CiberModel.php` para registrar sesiones en `sesion_ciber`.

#### 8.4 `inventario.php` (474 lineas) - GESTION DE INVENTARIO (FUNCIONAL CON BD)
Modulo completo conectado a la base de datos via `inventario.php` (modelo POO). Incluye:
- **KPIs**: 4 tarjetas con total de productos, stock critico, stock bajo y valor total (desde BD)
- **Tabla de productos**: Listado completo con estado, stock, precios (desde BD)
- **Filtros**: Busqueda por texto con debounce + filtro por estado (OK, Critico, Sin stock)
- **CRUD completo**: Crear, editar y eliminar productos via AJAX
- **Movimientos de stock**: Entrada y salida con actualizacion de stock y bitacora
- **Historial**: Visualizacion de movimientos por producto
- **3 modales**: Producto (crear/editar), Movimientos (historial), Stock (entrada/salida)
- **JS**: `app.inventario.js` (450 lineas) para todas las operaciones AJAX

#### 8.5 `proveedores.php` — SOLICITUDES (FUNCIONAL CON BD)
Gestion de proveedores y ordenes de compra conectada a BD via `Proveedor.php` + `ProveedorController.php` + `app.proveedores.js`. Incluye:
- **CRUD completo**: Crear, editar y eliminar proveedores via AJAX
- **Ordenes de compra**: Creacion y seguimiento con estados
- **Gestion de productos por proveedor**
- **Busqueda y filtros** con debounce + filtro por estado
- **JS**: `app.proveedores.js` para todas las operaciones AJAX

#### 8.6 `reportes.php` (139 lineas) - REPORTES (FUNCIONAL CON BD)
Formulario de generacion con selectores (tipo, fechas, formato), 4 metricas mensuales, listado de reportes recientes. Conectado a BD via `ReporteController.php` + `Reporte.php`.
- Genera reportes reales desde la base de datos
- Exportacion a CSV/PDF via `Exporter.php` y `PdfBuilder.php`

#### 8.7 `activos.php` (207 lineas) - ACTIVOS (FUNCIONAL CON BD)
Activos agrupados por categoria (Equipos, Licencias, Herramientas) con resumen de totales. Conectado a BD via `ActivoController.php` + `Activo.php` + `app.activos.js`.

#### 8.8 `asesorias.php` (128 lineas) - ASESORIA LEGAL (FUNCIONAL CON BD)
Formulario de registro con validacion de documentos permitidos (11 tipos). Toda la logica en `app.legal.js` + `AsesoriaController.php` + `Asesoria.php`.
- Validacion en tiempo real: boton cambia de color segun tipo de documento
- Historial de asesorias registradas en la sesion
- Busqueda en el historial con debounce
- Eliminacion de registros

#### 8.9 `menu.php` (170 lineas)
Pagina alternativa con diseno tipo tarjeta, estilo independiente, enlaces a 7 modulos.

#### 8.10 `usuarios.php` — USUARIOS (FUNCIONAL CON BD)
Gestion de usuarios conectada a BD via `AuthController.php` + `app.core.js`. Incluye:
- **Crear usuarios**: Formulario con nombre, usuario, email, rol, contraseña
- **Listado**: Tabla con todos los usuarios del sistema
- **Editar/Eliminar**: Acciones via AJAX
- **Roles**: Asignacion de rol desde un select poblado desde BD

#### 8.11 `roles.php` — ROLES Y PERMISOS (FUNCIONAL CON BD)
Gestion de roles y permisos conectada a BD via `Rol.php` + `RolController.php` + `app.roles.js`. Incluye:
- **CRUD completo**: Crear, editar y eliminar roles via AJAX
- **Asignacion de permisos**: Checkbox por permiso en cada rol
- **Listado**: Tabla con todos los roles y sus permisos asociados
- **JS**: `app.roles.js` para todas las operaciones AJAX

---

## JavaScript Modular

### Arquitectura

El monolito `app.js` original se dividio en **15 archivos modulares** organizados por funcionalidad, mas el motor de **jQuery DataTables** (local):

| Archivo | Proposito | Carga |
|---------|-----------|-------|
| `app.core.js` | Namespace EIS, debounce, `EIS.toast` y helpers `EIS.datatable*` | Siempre |
| `app.init.js` | Init Materialize, reloj, tema, animaciones | Siempre |
| `app.selects.js` | Barra de busqueda en los selects (dropdowns) de Materialize | Siempre |
| `app.tables.js` | Punto de extension generico (la busqueda/filtro/paginacion la gestiona DataTables) | Siempre |
| `app.ui.js` | Notificaciones, botones, tooltips | Siempre |
| `jquery.dataTables.min.js` | Motor jQuery DataTables (ordenamiento/paginacion/busqueda) | Siempre |
| `dataTables.materialize.js` | Integracion DataTables + Materialize y config por defecto (es) | Siempre |
| `dataTables.materialize.css` | Tema de DataTables adaptado a Materialize y tema oscuro/claro | Siempre |
| `app.pos.js` | Sistema carrito POS | Solo ventas |
| `app.cyber.js` | Gestion estaciones cyber | Solo ciberControl |
| `app.legal.js` | Validacion documentos legales | Solo asesorias |
| `app.inventario.js` | CRUD inventario via AJAX | Solo inventario |
| `app.roles.js` | CRUD roles/permisos via AJAX | Solo roles |
| `app.proveedores.js` | CRUD proveedores/ordenes via AJAX | Solo proveedores |
| `app.proveedores-gestion.js` | CRUD proveedores (gestion) via AJAX | Solo proveedores-gestion |
| `app.clientes.js` | CRUD clientes via AJAX | Solo clientes |
| `app.activos.js` | CRUD activos via AJAX | Solo activos |
| `app.reportes.js` | Generacion de reportes via AJAX | Solo reportes |

### app.core.js - Funciones Compartidas

```javascript
var EIS = {};  // Namespace global

function debounce(fn, delay) { ... }
EIS.toast = function (msg, color, icon) { ... }

// Familia de helpers de jQuery DataTables:
EIS.datatable(selector, opts)                    // Inicializa DataTables (quita filas vacias colspan)
EIS.datatableRefresh(selector)                   // Recarga filas tras re-render AJAX
EIS.datatableWireSearch(selector, inputSel)      // Conecta un input a la busqueda global de DataTables
EIS.datatableWireColumnFilter(selector, sel, col) // Conecta un select a un filtro de columna
EIS.datatableDestroy(selector)                   // Destruye una instancia (tabla dinamica)
```

### app.init.js - Inicializacion

```javascript
$(function () {
    // Componentes Materialize: sidenav, select, tooltips, modal, tabs, etc.
    // Reloj digital: setInterval cada 1s
    // Tema oscuro/claro con localStorage
    // Animacion de transicion (fadeIn)
    // Animacion de contadores numericos
    // Boton volver arriba (scroll)
});
```

### app.tables.js - Datatable Transversal

```javascript
$(function () {
    // La busqueda, el filtro por estado y la paginacion de todas las tablas
    // principales las gestiona jQuery DataTables (ver EIS.datatable* en
    // app.core.js y la inicializacion en cada modulo app.<modulo>.js).
    // Este archivo se mantiene como punto de extension sin handlers manuales.
});
```

### DataTables en los modulos

Cada modulo inicializa su tabla con `EIS.datatable('#id-tabla')` y re-sincroniza el `<tbody>` tras re-render por AJAX con `EIS.datatableRefresh('#id-tabla')`. Las barras de busqueda y filtros existentes se conectan a las instancias:

- **Inventario**: `#tabla-productos`, `#tabla-categorias` (`pageLength` 5), `#searchProducto` y `#filterEstado` (filtro por columna 4)
- **Clientes**: `#tabla-clientes` + `#searchCliente`
- **Activos**: `#tabla-activos` + `#searchActivo` y `#filterTipo` (filtro por columna 2)
- **Roles**: `.rol-table` + `#searchRol` y `#filterRolEstado` (filtro por conteo de usuarios, columna 2)
- **Usuarios**: `.user-table` + `#searchUsuario` y `#filterRol` (columna 2)
- **Proveedores (ordenes)**: `#tabla-ordenes` + `#searchProveedor` y `#filterEstadoProv` (columna 4)
- **Proveedores (gestion)**: `#tabla-proveedores` + `#searchProveedorGestion`
- **Asesorias**: `#tabla-asesorias` + `#searchAsesoria`
- **Reportes**: `#tablaReporte` (se destruye/reinicializa al variar el numero de columnas)
- **Cyber historial**: `#tabla-historial` (tabla creada dinamicamente en el modal)
- **Dashboard**: `#tabla-ventas-dia`, `#tabla-stock-critico` (`pageLength` 5)

### app.ui.js - Interacciones UI

```javascript
$(function () {
    // #notifBell - Notificaciones demo
    // [data-confirm] - Botones con confirmacion
    // .btn-download - Descarga simulada
    // .btn-floating, .tooltip-me - Tooltips mejorados
});
```

> **Nota (v4.1):** En la corrección de bugs se **eliminaron** de `app.ui.js` los handlers demo de
> `#formReporte` (que hacía `e.preventDefault()` y leía un campo `name="format"` inexistente,
> en realidad el formato es `name="formato"`) y de `.btn-nuevo` (que mostraba un toast "(demo)").
> El submit del formulario de reportes lo maneja ahora el módulo real `app.reportes.js:83`, y
> `.btn-nuevo` lo manejan los módulos de cada vista (inventario, proveedores-gestión, etc.).

### app.pos.js - Sistema POS

```javascript
$(function () {
    var posCart = [];
    var posTotal = 0;

    $(document).on('click', '.pos-product', function () { ... });
    // actualizarPosUI(), actualizarMiniTotal(), actualizarCarritoModal()
    // .cart-item-remove, #openCartBtn, #procesarVenta, #vaciarCarrito
    // #posSearch - Busqueda productos con debounce 200ms
});
```

### app.cyber.js - Estaciones Cyber

```javascript
$(function () {
    function actualizarCyberContadores() { ... }
    // Click en .station-card: toggle disponible/ocupada con confirmacion
    // Click en .filter-btn: filtrar por estado
});
```

### app.legal.js - Asesoria Legal

```javascript
$(function () {
    var allowedDocs = [ /* 11 tipos permitidos */ ];
    var asesoriasRegistradas = [];

    function normalizarDoc(texto) { ... }
    function documentoPermitido(doc) { ... }
    function actualizarHistorial() { ... }
    function mostrarValidacion(tipo, mensaje, esPermitido) { ... }

    // #asesoriaForm - Submit: validacion y registro
    // #documento - Input: validacion en tiempo real
    // .btn-eliminar-asesoria - Eliminar registro
    // #searchAsesoria - Busqueda en historial
});
```

---

## Base de Datos

### Esquema General

- **Base de datos**: `zona_web_lara`
- **Motor**: InnoDB
- **Charset**: utf8mb4
- **Collation**: utf8mb4_spanish_ci
- **Version**: 3.0

### Tablas (21 total)

| # | Tabla | Proposito |
|---|-------|-----------|
| 1 | `roles` | Catalogo de roles de usuario (incluye `descripcion` y `created_at` desde v4.1) |
| 2 | `permisos` | Catalogo de permisos del sistema |
| 3 | `categoria` | Catalogo de categorias de productos |
| 4 | `clientes` | Clientes del sistema |
| 5 | `cliente_asesoria` | Clientes legales (datos especificos para asesorias) |
| 6 | `proveedores` | Proveedores |
| 7 | `status_seguimiento` | Estados para ordenes de abastecimiento |
| 8 | `tipo_asesoria` | Tipos de documentos de asesoria legal |
| 9 | `tarifas` | Tarifas de estaciones de cybercafe |
| 10 | `tipo_activo` | Catalogo de tipos de activos |
| 11 | `rol_usuarios` | Roles especificos de usuarios |
| 12 | `usuarios` | Usuarios del sistema (con password_hash) |
| 13 | `permisos_rol` | Relacion M:N roles-permisos |
| 14 | `productos` | Catalogo de productos |
| 15 | `orden_de_venta` | Registro de ventas |
| 16 | `lineas_venta` | Detalle de productos vendidos |
| 17 | `orden_abastecimiento` | Ordenes de compra a proveedores |
| 18 | `lineas_abastecimiento` | Detalle de productos en ordenes |
| 19 | `asesoria` | Casos de asesoria legal |
| 20 | `activos` | Activos fijos (equipos, licencias, herramientas) |
| 21 | `sesion_ciber` | Sesiones de uso de estaciones cyber |

Nota: El esquema SQL contiene 21 CREATE TABLE. La estructura completa de la base de datos se compone de estas 21 tablas.

### Indices
Claves foraneas en todas las relaciones (fk_*). Indices en columnas clave: cedula (UNIQUE), user_name (UNIQUE), codigo (UNIQUE), rif (UNIQUE).

### Vistas
No hay vistas definidas en el esquema actual. Los calculos (KPIs, estados de stock) se realizan via consultas SQL directas en los modelos.

### Objetos de BD
No hay stored procedures, functions, triggers ni events definidos actualmente. Toda la logica transaccional se maneja desde PHP con PDO.

---

## Offline y PWA

### Assets Locales

Todos los recursos que antes se cargaban desde CDN ahora son locales:

| Recurso | Antes (CDN) | Ahora (Local) |
|---------|-------------|---------------|
| Materialize CSS | cdnjs.cloudflare.com | `Public/css/materialize.min.css` |
| Materialize JS | cdnjs.cloudflare.com | `Public/js/materialize.min.js` |
| jQuery 3.7.1 | code.jquery.com | `Public/js/jquery-3.7.1.min.js` |
| Material Icons | fonts.googleapis.com | `Public/css/material-icons.css` + `Public/fonts/MaterialIcons-Regular.ttf` |

### Service Worker (`sw.js`)

```javascript
var CACHE_NAME = 'eis-cache-v4';
var STATIC_ASSETS = [
  'Public/css/material-icons.css', 'Public/css/materialize.min.css',
  'Public/css/styles.css', 'Public/css/dataTables.materialize.css',
  'Public/css/login.css',
  'Public/js/jquery-3.7.1.min.js', 'Public/js/materialize.min.js',
  'Public/js/jquery.dataTables.min.js', 'Public/js/dataTables.materialize.js',
  'Public/js/app.core.js', 'Public/js/app.init.js',
  'Public/js/app.selects.js', 'Public/js/app.tables.js', 'Public/js/app.ui.js',
  'Public/js/app.pos.js', 'Public/js/app.cyber.js', 'Public/js/app.legal.js',
  'Public/fonts/MaterialIcons-Regular.ttf',
  'manifest.json', 'offline.php'
];
```

**Estrategia de cache**:
- **Cache First** para assets estaticos (CSS, JS, fuentes)
- **Network First** con fallback a `offline.php` para navegacion PHP

### Manifest (`src/manifest.json`)

```json
{
  "name": "EIS System",
  "short_name": "EIS",
  "display": "standalone",
  "background_color": "#1a237e",
  "theme_color": "#1a237e",
  "icons": []
}
```

### Pagina Offline (`src/offline.php`)
Pagina de fallo que se muestra cuando el usuario esta sin conexion e intenta navegar. Incluye:
- Icono `cloud_off` de Material Icons
- Mensaje "Sin Conexion"
- Boton "Reintentar" que recarga la pagina

---

## CSS y Estilos

### `src/Public/css/styles.css` (1105 lineas)

Estilos personalizados que complementan Materialize CSS. Incluye:

**Variables CSS (Custom Properties)**: 22 variables para tema claro y 22 para tema oscuro (`[data-theme="dark"]`).

**Clases principales**:

| Clase | Proposito |
|-------|-----------|
| `.metric-card` | Tarjetas de metricas con borde colorido |
| `.station-card` | Tarjetas de estaciones cyber |
| `.pos-product` | Tarjetas de productos en POS |
| `.cart-item` | Items del carrito de compras |
| `.activity-item` | Items de actividad reciente |
| `.welcome-banner` | Banner de bienvenida en dashboard |
| `.result-count` | Contador de resultados de busqueda |
| `.legal-permitido` | Badge verde para documentos permitidos |
| `.legal-denegado` | Badge rojo para documentos no permitidos |
| `.zone-divider` | Separador de zonas en cybercafe |
| `.zone-title` | Titulo de zona en cybercafe |
| `.station-inner/header/body/footer` | Estructura interna de estaciones |
| `.pos-add-btn` | Boton "+" flotante en productos POS |
| `.filter-btn` | Botones de filtro en cybercafe |

### `src/Public/css/login.css` (138 lineas)
Estilos especificos para la pagina de login (independiente del layout).

### `src/Public/css/material-icons.css` (14 lineas)
Hoja de estilos local para Material Icons con referencia a la fuente TTF local.

---

## Resumen de Funcionalidad por Modulo

### Login System (Funcional con BD)
- **Archivos**: `login.php`, `AuthController.php`, `Usuario.php`
- **Flujo**: Formulario -> AuthController::login() -> valida vs BD con password_verify -> Sesion -> Dashboard
- **Logout**: AuthController::logout() -> destruye sesion -> redirige a login

### Dashboard (FUNCIONAL CON BD)
- **Archivos**: `dashboard.php`, `DashboardController.php`, `Dashboard.php`
- **Contenido**: 4 metricas, tablas de horas pico, productos sin stock, actividad reciente desde BD

### Inventario (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `inventario.php`, `Inventario.php`, `inventarioController.php`, `app.inventario.js`
- **Tabla**: `#tabla-productos` y `#tabla-categorias` con **DataTables** (`EIS.datatable`); busqueda `#searchProducto` y filtro de estado `#filterEstado` conectados a la instancia
- **BD**: Productos, KPIs, movimientos de stock

### Clientes (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `clientes.php`, `Cliente.php`, `ClienteController.php`, `app.clientes.js`
- **BD**: CRUD clientes con validacion de cedula

### Punto de Venta (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `ventas.php`, `Venta.php`, `VentaController.php`, `app.pos.js`
- **BD**: Registro de ordenes de venta y lineas

### Cyber Control (FUNCIONAL CON BD)
- **Archivos**: `ciberControl.php`, `CiberController.php`, `CiberControl.php`, `app.cyber.js`
- **BD**: Sesiones en `sesion_ciber`

### Proveedores (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `proveedores.php`, `Proveedor.php`, `ProveedorController.php`, `app.proveedores.js`
- **Tabla**: `#tabla-ordenes` con **DataTables** (`EIS.datatable`); busqueda `#searchProveedor` y filtro de estado `#filterEstadoProv` conectados a la instancia
- **BD**: Proveedores, ordenes de abastecimiento, lineas

### Reportes (FUNCIONAL CON BD)
- **Archivos**: `reportes.php`, `Reporte.php`, `ReporteController.php`, `app.reportes.js`
- **BD**: Generacion de reportes reales + exportacion CSV/PDF

### Activos (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `activos.php`, `Activo.php`, `ActivoController.php`, `app.activos.js`
- **BD**: CRUD de activos fijos agrupados por categoria

### Asesoria Legal (FUNCIONAL CON BD)
- **Archivos**: `asesorias.php`, `Asesoria.php`, `AsesoriaController.php`, `app.legal.js`
- **BD**: Registro de casos de asesoria con validacion de documentos

### Usuarios (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `usuarios.php`, `Usuario.php`, `AuthController.php`, `app.core.js`
- **BD**: Crear, editar, eliminar y listar usuarios

### Roles y Permisos (FUNCIONAL CON BD - CRUD AJAX)
- **Archivos**: `roles.php`, `Rol.php`, `RolController.php`, `app.roles.js`
- **BD**: Crear, editar roles + asignacion de permisos
- **v4.1**: soporte completo de `descripcion`/`created_at`; `asignarRolAUsuario()` resuelve `rol_usuarios.id` correctamente

---

## Correcciones v4.1 (7 bugs)

> Se corrigieron 7 bugs que impedían la funcionalidad completa del sistema:

| # | Modulo | Archivo | Correccion |
|---|--------|---------|-----------|
| 1-2 | Reportes / UI | `Public/js/app.ui.js` | Eliminados handlers demo de `#formReporte` y `.btn-nuevo` que interferian con los modulos reales (el submit de reportes lo maneja `app.reportes.js`) |
| 3 | Clientes | `ClienteController.php`, `Cliente.php` | `direccion` y `telefono` ahora opcionales (antes obligatorios, pero el front los trataba como opcionales) |
| 4 | Activos | `ActivoController.php` | Checkbox `activa` usa `isset($_POST['activa'])`; un activo desmarcado ya se guarda como inactivo |
| 5 | Roles | `Rol.php` | `asignarRolAUsuario()` resuelve `rol_usuarios.id` por `fk_rol` antes de escribir `usuarios.fk_rol_usuario` (antes escribia `roles.id` directo y el JOIN no resolvia el rol) |
| 6 | Roles | `estructura.sql`, `Rol.php`, `RolController.php`, `app.roles.js` | Soporte completo de `descripcion` y `created_at` (columnas nuevas, setters, persistencia y render formateado) |
| 7 | Seed data | `seed_data.sql` | INSERT de `cliente_asesoria` usa columnas reales `(fk_cliente, email, rif, tipo)` con IDs de clientes correctos |

**Migración BD (v4.1):** si la BD ya existe, aplicar a la tabla `roles`:
```sql
ALTER TABLE roles
  ADD COLUMN descripcion VARCHAR(500) DEFAULT NULL AFTER nombre_rol,
  ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER descripcion;
```

---

## Conclusiones y Recomendaciones

### Estado Actual
El proyecto cuenta con **todos los modulos conectados a la BD** y arquitectura OOP completa:
- Diseno Material Design con Materialize CSS
- Arquitectura MVC con clases y namespaces (PSR-4)
- **Router OOP**: Clase `Router` con mapa `CONTROLLERS` + `dispatchAction()` + auth + vistas + CSRF tokens
- **Database Singleton**: Clase `Database` con patron Singleton para PDO
- **Modelo base**: Clase abstracta `Model` con helpers de validacion (non-empty, min-length, FK existence, duplicates, patterns)
- **12 controladores** con namespace: Auth, Cliente, Inventario, Venta, Rol, Proveedor, ProveedorGestion, Asesoria, Ciber, Dashboard, Reporte, Activo
- **13 modelos POO**: Cliente, Inventario, Usuario, Proveedor, ProveedorGestion, Rol, Asesoria, Activo, Venta, Reporte, Dashboard, CiberControl, CiberModel
- **Navegacion funcional** con sidebar responsivo (13 modulos)
- **Login con BD**: Autenticacion via AuthController + password_verify + session_regenerate_id
- **Modulos conectados a BD**: Clientes, Inventario, Ventas, Cyber, Usuarios, Roles/Permisos, Proveedores, Activos, Asesorias, Reportes, Dashboard
- **Seguridad completa**: CSRF tokens, XSS sanitizacion, session hardening, prepared statements, validacion backend
- **Tema oscuro/claro** con persistencia
- **JavaScript modular** en 15 archivos especializados mas el motor de jQuery DataTables
- **Tablas con DataTables**: ordenamiento, paginacion y busqueda en las tablas principales de todos los modulos
- **Assets 100% locales** (sin dependencia de CDN)
- **Service Worker** para funcionamiento offline
- **PWA** con manifest.json
- Esquema de BD completo v3.0 (21 tablas)

### Pendiente:
1. **Usar .env** para credenciales de BD
2. Reforzar reportes avanzados y exportaciones

---

**Documentacion generada**: Julio 2026 (actualizada Sept 2026, v4.1)
**Version**: 4.1
**Autor**: Carlos Paez Guerra

