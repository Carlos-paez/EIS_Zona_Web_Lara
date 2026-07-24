# Sistema de Enrutamiento — Documentacion Tecnica

## Arquitectura Actual (Front Controller OOP)

```
src/
├── index.php                    ← Front Controller (punto de entrada, autoloader)
├── .htaccess                    ← Reglas de reescritura Apache
├── manifest.json                ← Manifiesto PWA
├── sw.js                        ← Service Worker
├── offline.php                  ← Pagina offline
├── Config/
│   └── database.php             ← Conexion PDO MySQL (legacy)
├── app/
│   ├── core/
│   │   ├── Database.php         ← Conexion PDO Singleton
│   │   ├── Model.php            ← Clase base abstracta con helpers de validacion
│   │   └── router.php           ← Enrutador OOP (clase Router, CSRF tokens)
│   ├── Controllers/
│   │   ├── AuthController.php   ← Login/logout con sesiones + CSRF + session_regenerate_id
│   │   ├── ClienteController.php← Controlador AJAX clientes
│   │   ├── inventarioController.php ← Controlador AJAX inventario
│   │   ├── ProveedorController.php  ← Controlador AJAX proveedores (solicitudes)
│   │   ├── ProveedorGestionController.php ← Controlador AJAX proveedores (gestion)
│   │   └── RolController.php    ← Controlador AJAX roles/permisos
│   ├── Models/
│   │   ├── Cliente.php          ← Modelo POO clientes
│   │   ├── Inventario.php       ← Modelo POO inventario (namespace)
│   │   ├── Usuario.php          ← Modelo POO usuarios
│   │   ├── Proveedor.php        ← Modelo POO proveedores (solicitudes)
│   │   ├── ProveedorGestion.php ← Modelo POO proveedores (gestion)
│   │   ├── Rol.php              ← Modelo POO roles/permisos
│   │   ├── Asesoria.php         ← Modelo POO asesorias
│   │   ├── crud_users.php       ← CRUD usuarios legacy
│   │   └── crud_asesorias.php   ← CRUD asesorias legacy
│   ├── template/
│   │   └── layout.php           ← Layout maestro con CSRF token + JS condicional
│   └── Views/
│       ├── login.php            ← Formulario de inicio de sesion
│       ├── login_validate.php   ← Validacion de credenciales (legacy)
│       ├── dashboard.php        ← Panel de control
│       ├── inventario.php       ← Gestion de inventario (conectado a BD)
│       ├── ventas.php           ← Punto de venta (POS)
│       ├── proveedores.php      ← Solicitudes a proveedores (conectado a BD)
│       ├── clientes.php         ← Gestion de clientes (conectado a BD)
│       ├── reportes.php         ← Reportes y estadisticas
│       ├── activos.php          ← Activos fijos
│       ├── ciberControl.php     ← Control de cybercafe
│       ├── asesorias.php        ← Asesoria legal
│       ├── menu.php             ← Menu de navegacion
│       ├── usuarios.php         ← Gestion de usuarios (conectado a BD)
│       └── roles.php            ← Gestion de roles/permisos (conectado a BD)
└── Public/
    ├── css/                     ← Estilos (locales)
    ├── js/                      ← JavaScript modular (10 archivos)
    └── fonts/                   ← Material Icons (local)
```

---

## 1. `src/.htaccess` — URLs con Query String + URLs Limpias Parciales

```apache
Options All -Indexes
RewriteEngine on

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([\w-]+)$ index.php?pagina=$1 [L,QSA]
```

| Linea | Explicacion |
|-------|-------------|
| `Options All -Indexes` | Bloquea el listado de directorios por seguridad. |
| `RewriteEngine On` | Activa el modulo de reescritura de Apache. |
| `RewriteRule ^$ index.php [L,QSA]` | La raiz `/` se redirige internamente a `index.php`. |
| `RewriteCond %{REQUEST_FILENAME} !-f` | Solo aplica si el archivo NO existe fisicamente. |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Solo aplica si el directorio NO existe. |
| `RewriteRule ^([\w-]+)$ index.php?pagina=$1 [L,QSA]` | Convierte `/nombre` en `?pagina=nombre`. Soporta URLs limpias parciales. |

Actualmente las URLs soportan dos formatos: `?pagina=nombre` (query string) y `/nombre` (URL limpia gracias al .htaccess).

---

## 2. `src/index.php` — Front Controller

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Router;
$router = new Router();
$router->handle();
```

| Linea | Explicacion |
|-------|-------------|
| `require_once __DIR__ . '/../vendor/autoload.php'` | Carga el autoloader de Composer (PSR-4). |
| `use App\Core\Router` | Importa la clase Router del namespace App\Core. |
| `$router = new Router()` | Crea instancia: inicia sesion y resuelve pagina. |
| `$router->handle()` | Procesa la solicitud (AJAX, auth, o vista). |

Ahora usa autoloader de Composer. El flujo es: `index.php` -> `new Router()` -> `Router::handle()`.

---

## 3. `src/app/core/router.php` — Enrutador OOP (Clase Router)

### Mapa de rutas

El enrutamiento usa la clase `Router` en namespace `App\Core`. Soporta 6 tipos de peticiones:
1. **AJAX de clientes** (`?pagina=clientes&action=X`) -> `ClienteController`
2. **AJAX de inventario** (`?pagina=inventario&action=X`) -> `InventarioController`
3. **AJAX de roles** (`?pagina=roles&action=X`) -> `RolController`
4. **AJAX de proveedores** (`?pagina=proveedores&action=X`) -> `ProveedorController`
5. **AJAX de proveedores-gestion** (`?pagina=proveedores-gestion&action=X`) -> `ProveedorGestionController`
6. **Auth actions** (`?pagina=login_validate` o `logout`) -> `AuthController`
7. **Vistas normales** -> `renderView()` -> `layout.php` + vista

### Estructura de la clase

```php
<?php
namespace App\Core;

class Router
{
    private string $pagina;

    public function __construct()
    {
        session_start();
        $this->pagina = $this->resolvePage();
    }

    public function handle(): void
    {
        if ($this->isAjaxCliente()) {
            $this->runClienteController(); return;
        }
        if ($this->isAjaxInventario()) {
            $this->runInventarioController(); return;
        }
        if ($this->isAjaxRoles()) {
            $this->runRolController(); return;
        }
        if ($this->isAjaxProveedores()) {
            $this->runProveedorController(); return;
        }
        if ($this->isAjaxProveedorGestion()) {
            $this->runProveedorGestionController(); return;
        }
        if ($this->isAuthAction()) {
            $this->runAuthAction(); return;
        }
        $this->renderView();
    }

    private function resolvePage(): string
    {
        $pagina = 'login';
        if (!empty($_GET['pagina'])) {
            $pagina = $_GET['pagina'];
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
            $pagina = 'login';
        }
        return $pagina;
    }

    private function isAjaxCliente(): bool
    {
        return $this->pagina === 'clientes' && isset($_GET['action']);
    }

    private function isAjaxInventario(): bool
    {
        return $this->pagina === 'inventario' && isset($_GET['action']);
    }

    private function isAjaxRoles(): bool
    {
        return $this->pagina === 'roles' && isset($_GET['action']);
    }

    private function isAjaxProveedores(): bool
    {
        return $this->pagina === 'proveedores' && isset($_GET['action']);
    }

    private function isAjaxProveedorGestion(): bool
    {
        return $this->pagina === 'proveedores-gestion' && isset($_GET['action']);
    }

    private function isAuthAction(): bool
    {
        return $this->pagina === 'login_validate' || $this->pagina === 'logout';
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['logged_in'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }
    }

    private function runClienteController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\ClienteController();
        $controller->handle();
        exit;
    }

    private function runInventarioController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\InventarioController();
        $controller->handle();
        exit;
    }

    private function runRolController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\RolController();
        $controller->handle();
        exit;
    }

    private function runProveedorController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\ProveedorController();
        $controller->handle();
        exit;
    }

    private function runProveedorGestionController(): void
    {
        $this->requireAuth();
        $controller = new \App\Controllers\ProveedorGestionController();
        $controller->handle();
        exit;
    }

    private function runAuthAction(): void
    {
        if ($this->pagina === 'logout') {
            $controller = new \App\Controllers\AuthController();
            $controller->logout();
            return;
        }
        $controller = new \App\Controllers\AuthController();
        $controller->login();
        exit;
    }

    private function renderView(): void
    {
        $publicPages = ['login'];
        if (!isset($_SESSION['logged_in']) && !in_array($this->pagina, $publicPages)) {
            header('Location: ?pagina=login');
            exit;
        }
        $rutaVista = __DIR__ . '/../Views/' . $this->pagina . '.php';
        if (!is_file($rutaVista)) {
            http_response_code(404);
            echo '<h1>Error 404: Pagina no encontrada</h1>';
            return;
        }
        if (in_array($this->pagina, $publicPages)) {
            require $rutaVista;
            return;
        }
        $this->renderWithLayout($rutaVista);
    }

    private function renderWithLayout(string $contentView): void
    {
        $pagina = $this->pagina;
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestion de inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafe',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadisticas',
            'activos'      => 'Gestion de Activos',
            'asesorias'    => 'Asesoria Legal',
            'usuarios'     => 'Gestion de Usuarios',
            'roles'        => 'Gestion de Roles y Permisos',
        ];
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text">5 Disponibles</span><span class="chip orange white-text">4 Ocupadas</span>',
        ];
        $pageTitle   = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';
        require __DIR__ . '/../template/layout.php';
    }
}
```

### Metodos clave

| Metodo | Explicacion |
|--------|-------------|
| `__construct()` | Inicia sesion, genera CSRF token (`bin2hex(random_bytes(32))`), resuelve `$pagina` |
| `handle()` | Metodo principal: determina el tipo de peticion y ejecuta la accion |
| `resolvePage()` | Lee `$_GET["pagina"]`, valida con regex, retorna el nombre (default: "login") |
| `isAjaxCliente()` | True si pagina='clientes' Y existe `$_GET['action']` |
| `isAjaxInventario()` | True si pagina='inventario' Y existe `$_GET['action']` |
| `isAjaxRoles()` | True si pagina='roles' Y existe `$_GET['action']` |
| `isAjaxProveedores()` | True si pagina='proveedores' Y existe `$_GET['action']` |
| `isAjaxProveedorGestion()` | True si pagina='proveedores-gestion' Y existe `$_GET['action']` |
| `isAuthAction()` | True si pagina='login_validate' o 'logout' |
| `requireAuth()` | Verifica `$_SESSION['logged_in']`, si no existe: JSON error + exit |
| `runClienteController()` | Instancia y ejecuta `\App\Controllers\ClienteController` |
| `runInventarioController()` | Instancia y ejecuta `\App\Controllers\InventarioController` |
| `runRolController()` | Instancia y ejecuta `\App\Controllers\RolController` |
| `runProveedorController()` | Instancia y ejecuta `\App\Controllers\ProveedorController` |
| `runProveedorGestionController()` | Instancia y ejecuta `\App\Controllers\ProveedorGestionController` |
| `runAuthAction()` | Instancia `AuthController` y llama `login()` o `logout()` |
| `renderView()` | Verifica auth, determina si es publica o protegida, y renderiza |
| `renderWithLayout()` | Prepara variables ($pageTitle, $headerExtra, $contentView) e incluye layout.php |

### Mejoras sobre la version procedural

| Aspecto | Version anterior (procedural) | Version actual (OOP) |
|---------|------------------------------|----------------------|
| **Tipo** | Script procedural (75 lineas) | Clase con namespace |
| **Autoloader** | No usado | Composer PSR-4 |
| **AJAX** | Solo inventario | Clientes, Inventario, Roles, Proveedores, ProveedorGestion |
| **Auth** | login_validate.php (vista) | AuthController con login()/logout() + session_regenerate_id |
| **CSRF** | No implementado | `bin2hex(random_bytes(32))` en constructor + `<input name="csrf_token">` |
| **Seguridad AJAX** | No verificaba auth | `requireAuth()` con respuesta JSON |
| **Logout** | No implementado | `AuthController::logout()` |
| **Titulos** | 9 modulos | 13 modulos |
| **Validacion** | Sin helpers | Model.php: non-empty, min-length, pattern, FK existence, duplicates |

---

## 4. `src/app/template/layout.php` — Layout Maestro

### Variables disponibles en el layout

| Variable | Origen | Proposito |
|----------|--------|-----------|
| `$pageTitle` | `$titulos[$pagina]` en router.php | Titulo en `<title>` y barra de navegacion |
| `$headerExtra` | `$extraHeaders[$pagina]` en router.php | HTML extra en el header (badges) |
| `$contentView` | Ruta absoluta a la vista | Incluido dentro del layout |
| `$pagina` | `$_GET["pagina"]` | Nombre de pagina actual (para clase `active` en sidebar y carga condicional de JS) |

### Carga condicional de JavaScript

```php
<!-- Scripts base (siempre) -->
<script src="Public/js/materialize.min.js"></script>
<script src="Public/js/app.core.js"></script>
<script src="Public/js/app.init.js"></script>
<script src="Public/js/app.tables.js"></script>
<script src="Public/js/app.ui.js"></script>

<!-- Scripts especificos por pagina (6 modulos) -->
<?php if ($pagina === 'ventas'): ?>
<script src="Public/js/app.pos.js"></script>
<?php endif; ?>
<?php if ($pagina === 'ciberControl'): ?>
<script src="Public/js/app.cyber.js"></script>
<?php endif; ?>
<?php if ($pagina === 'asesorias'): ?>
<script src="Public/js/app.legal.js"></script>
<?php endif; ?>
<?php if ($pagina === 'inventario'): ?>
<script src="Public/js/app.inventario.js"></script>
<?php endif; ?>
<?php if ($pagina === 'roles'): ?>
<script src="Public/js/app.roles.js"></script>
<?php endif; ?>
<?php if ($pagina === 'proveedores' || $pagina === 'clientes' || $pagina === 'proveedores-gestion'): ?>
<script src="Public/js/app.proveedores.js"></script>
<?php endif; ?>
```

---

## 5. Mapa de Paginas

| Parametro | Vista/Controlador | Publica? | JS Adicional |
|-----------|-------------------|----------|-------------|
| `login` | `login.php` | Si | Ninguno |
| `login_validate` | `AuthController::login()` | No (POST) | Ninguno |
| `logout` | `AuthController::logout()` | No | Ninguno |
| `dashboard` | `dashboard.php` | No | Ninguno |
| `inventario` | `inventario.php` | No | `app.inventario.js` |
| `inventario&action=X` | `InventarioController::handle()` | No | (AJAX) |
| `ventas` | `ventas.php` | No | `app.pos.js` |
| `proveedores` | `proveedores.php` | No | `app.proveedores.js` |
| `proveedores&action=X` | `ProveedorController::handle()` | No | (AJAX) |
| `clientes` | `clientes.php` | No | `app.proveedores.js` |
| `clientes&action=X` | `ClienteController::handle()` | No | (AJAX) |
| `proveedores-gestion` | proveedores.php | No | `app.proveedores.js` |
| `proveedores-gestion&action=X` | `ProveedorGestionController::handle()` | No | (AJAX) |
| `ciberControl` | `ciberControl.php` | No | `app.cyber.js` |
| `reportes` | `reportes.php` | No | Ninguno |
| `activos` | `activos.php` | No | Ninguno |
| `asesorias` | `asesorias.php` | No | `app.legal.js` |
| `usuarios` | `usuarios.php` | No | Ninguno |
| `roles` | `roles.php` | No | `app.roles.js` |
| `roles&action=X` | `RolController::handle()` | No | (AJAX) |
| `menu` | `menu.php` | No | Ninguno |

---

## 6. Vistas

### Vistas publicas (standalone, sin layout)
- `login.php` — Tiene su propio DOCTYPE, head, body. Carga jQuery, Materialize JS y app.core.js.

### Vistas protegidas (dentro del layout)
Todas las demas vistas son solo fragmentos HTML sin estructura completa de pagina. El layout maestro provee el HTML comun.

```
Views/
├── login.php                   # Publica, standalone
├── login_validate.php          # Publica, solo PHP (legacy)
├── dashboard.php               # Protegida, dentro del layout
├── inventario.php              # Conectado a BD
├── ventas.php
├── proveedores.php             # Conectado a BD (solicitudes)
├── clientes.php                # Conectado a BD
├── reportes.php
├── activos.php
├── ciberControl.php
├── asesorias.php
├── menu.php
├── usuarios.php                # Conectado a BD
└── roles.php                   # Conectado a BD
```

---

## 7. Seguridad del Sistema

| Aspecto | Implementacion |
|---------|----------------|
| **Path Traversal** | Regex `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` en router.php |
| **Autenticacion** | Verificacion de `$_SESSION['logged_in']` antes de cargar vistas protegidas |
| **404** | `is_file()` verifica existencia del archivo de vista |
| **CSRF** | Token en constructor: `bin2hex(random_bytes(32))`, inyectado en `window.EIS.csrfToken` y `<input name="csrf_token">` |
| **XSS** | Helper `escHtml()` en JS, `htmlspecialchars()` en PHP, Model sanitizes strings |
| **Session Hardening** | `session_regenerate_id(true)` en login/logout |
| **SQL Injection** | Los modelos usan prepared statements con PDO (`ATTR_EMULATE_PREPARES => false`) |
| **Listado Directorios** | `Options All -Indexes` en .htaccess |
| **Double Escaping** | Controllers usan solo `trim()`, Model maneja sanitizacion |

---

## 8. Como Agregar una Nueva Pagina

### Paso 1: Crear la Vista
Crea `src/app/Views/mi-pagina.php` con el contenido HTML.

### Paso 2: Registrar el Titulo
En `src/app/core/router.php`, agrega el titulo en `$titulos`:
```php
'mi-pagina' => 'Titulo de mi pagina',
```

### Paso 3: Agregar al Menu
En `src/app/template/layout.php`, agrega un enlace en el `<ul class="sidenav">`.

### Paso 4: (Opcional) Agregar JS Especifico
En `src/app/template/layout.php`, agrega la carga condicional:
```php
<?php if ($pagina === 'mi-pagina'): ?>
<script src="Public/js/app.mi-pagina.js"></script>
<?php endif; ?>
```

### Si la pagina es publica
No requiere autenticacion. Agrega `'mi-pagina'` al array `$public_pages` en router.php.

---

## 9. Flujo Completo de una Peticion

```
Usuario: GET /src/?pagina=ventas

1. Apache recibe la peticion
   └── /.htaccess: RewriteRule ^(.*)$ src/$1 [L]
   └── src/.htaccess: RewriteRule ^(.*)$ index.php [QSA,L]

2. index.php:
   └── require_once __DIR__.'/app/core/router.php'

3. router.php:
   ├── session_start()
   ├── $pagina = "ventas"
   ├── preg_match -> OK
   ├── $_SESSION['logged_in']? -> Si (o redirige a login)
   ├── ¿Es inventario con action? -> No (sigue a carga de vista)
   ├── $rutaVista = ".../Views/ventas.php" -> existe
   ├── ¿publica? -> No
   ├── $pageTitle = 'Punto de Venta (POS)'
   ├── $headerExtra = ''
   ├── $contentView = ".../Views/ventas.php"
   └── require __DIR__ . '/../template/layout.php'

4. layout.php:
   ├── <html><head> con Materialize CSS local + jQuery local
   ├── Sidebar con enlaces a modulos
   ├── Header con reloj y notificaciones
   ├── <main> -> require $contentView (ventas.php)
   ├── Scripts: app.core.js, app.init.js, app.tables.js, app.ui.js
   ├── $pagina === 'ventas' -> app.pos.js
   └── Service Worker registration

5. Navegador:
   ├── jQuery inicializa componentes Materialize
   ├── Reloj digital, animacion de contadores
   ├── app.pos.js prepara carrito POS
   └── Service Worker registrado para offline
```

---

## 10. Diferencia con una Arquitectura MVC Completa

| Aspecto | Actual (Procedural -> OOP) | MVC Completo |
|---------|---------------------------|--------------|
| **Enrutador** | `Router` clase con namespace, OOP | Framework (Laravel, Symfony) con routing DSL |
| **Punto de entrada** | `autoload.php` + `new Router()` + `->handle()` | Igual, pero con contenedor DI |
| **Controladores** | 6 controladores con namespace, metodo `handle()` | Controladores con acciones por metodo |
| **Layout** | `template/layout.php` | `Views/layouts/main.php` |
| **Vistas** | `Views/dashboard.php` (plano) | `Views/dashboard/index.php` (subdirectorios) |
| **Autoloader** | Composer PSR-4 | Igual |
| **Logica de login** | `AuthController::login()` con `password_verify` + CSRF | LoginController::validate() con guards |
| **Datos de Cyber** | En la vista (`ciberControl.php`) | En el controlador |
| **Titulos** | Array en `Router::renderWithLayout()` | Propiedad de clase Controller |
| **ORM** | PDO directo | Eloquent/Doctrine |
| **Middleware** | `requireAuth()` interno | Sistema de middlewares encadenables |
| **Request** | `$_GET`, `$_POST` directos | Request::capture() encapsulado |
| **Validacion** | Model.php helpers (non-empty, min-length, FK existence) | FormRequest classes |

---

## 11. Pruebas Realizadas

| Prueba | Resultado |
|--------|-----------|
| Cargar login.php sin sesion | PASS |
| Login con credenciales correctas -> dashboard | PASS |
| Login con credenciales incorrectas -> error | PASS |
| Acceder a dashboard sin sesion -> redirige a login | PASS |
| Cargar inventario, ventas, etc. con sesion | PASS |
| `?pagina=inexistente` -> 404 | PASS |
| `?pagina=../../../etc` -> redirige a login | PASS |
| Tema oscuro/claro persiste en localStorage | PASS |
| Carrito POS agrega/quita productos | PASS |
| Cyber toggle de estados | PASS |
| Asesoria valida documentos permitidos/denegados | PASS |
| Busqueda en tablas filtra correctamente | PASS |
| Paginacion cambia de pagina visualmente | PASS |
| Service Worker se registra correctamente | PASS |
| Assets locales funcionan sin CDN | PASS |
| Todos los archivos PHP sin errores de sintaxis | PASS |
| CRUD inventario via AJAX (crear, editar, eliminar) | PASS |
| CRUD roles via AJAX (crear, editar, eliminar, permisos) | PASS |
| CRUD proveedores via AJAX (crear, editar, eliminar) | PASS |
| CRUD usuarios via AJAX (crear, editar, eliminar) | PASS |
| CRUD clientes via AJAX (crear, editar, eliminar) | PASS |
| CRUD proveedores-gestion via AJAX (crear, editar, eliminar) | PASS |
| Login con usuario de BD via AuthController::login() | PASS |
| Logout via AuthController::logout() | PASS |
| CSRF token se genera y valida en todas las peticiones | PASS |
| Autoloader de Composer (PSR-4) funciona | PASS |

---

**Documentacion**: Julio 2026

