# Sistema de Enrutamiento — Documentacion Tecnica

## Arquitectura Actual (Front Controller Procedural)

```
src/
├── index.php                    ← Front Controller (punto de entrada)
├── .htaccess                    ← Reglas de reescritura Apache
├── manifest.json                ← Manifiesto PWA
├── sw.js                        ← Service Worker
├── offline.php                  ← Pagina offline
├── app/
│   ├── core/
│   │   └── router.php           ← Enrutador procedural
│   ├── Controllers/
│   │   └── inventarioController.php ← Controlador AJAX inventario
│   ├── Models/
│   │   ├── crud_inventario.php  ← CRUD inventario (15+ funciones)
│   │   ├── crud_users.php       ← CRUD usuarios (8 funciones)
│   │   └── crud_asesorias.php   ← CRUD asesorias (8 funciones)
│   ├── template/
│   │   └── layout.php           ← Layout maestro con JS condicional
│   └── Views/
│       ├── login.php            ← Formulario de inicio de sesion
│       ├── login_validate.php   ← Validacion de credenciales
│       ├── dashboard.php        ← Panel de control
│       ├── inventario.php       ← Gestion de inventario
│       ├── ventas.php           ← Punto de venta (POS)
│       ├── proveedores.php      ← Solicitudes a proveedores
│       ├── reportes.php         ← Reportes y estadisticas
│       ├── activos.php          ← Activos fijos
│       ├── ciberControl.php     ← Control de cybercafe
│       ├── asesorias.php        ← Asesoria legal
│       ├── menu.php             ← Menu de navegacion
│       └── usuarios.php         ← Gestion de usuarios
├── Config/
│   └── database.php             ← Conexion PDO MySQL
└── Public/
    ├── css/                     ← Estilos (locales)
    ├── js/                      ← JavaScript modular (7 archivos)
    └── fonts/                   ← Material Icons (local)
```

---

## 1. `src/.htaccess` — URLs con Query String

```apache
Options All -Indexes
RewriteEngine On

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

| Linea | Explicacion |
|-------|-------------|
| `Options All -Indexes` | Bloquea el listado de directorios por seguridad. |
| `RewriteEngine On` | Activa el modulo de reescritura de Apache. |
| `RewriteRule ^$ index.php [L,QSA]` | La raiz `/` se redirige internamente a `index.php`. |
| `RewriteCond %{REQUEST_FILENAME} !-f` | Solo aplica si el archivo NO existe fisicamente. |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Solo aplica si el directorio NO existe. |
| `RewriteRule ^(.*)$ index.php [QSA,L]` | Cualquier otra ruta se envia a `index.php`. `QSA` preserva los query parameters. |

Actualmente las URLs usan el formato `?pagina=nombre`. No hay URLs limpias implementadas.

---

## 2. `src/index.php` — Front Controller

```php
<?php
require_once __DIR__.'/app/core/router.php';
```

| Linea | Explicacion |
|-------|-------------|
| `require_once __DIR__.'/app/core/router.php'` | Incluye el enrutador procedural. `__DIR__` apunta a `src/`. |

A diferencia de una arquitectura MVC, no hay autoloader de Composer involucrado en el enrutamiento. El flujo es directo: `index.php` -> `router.php`.

---

## 3. `src/app/core/router.php` — Enrutador Procedural

### Mapa de rutas

El enrutamiento se basa en el parametro GET `?pagina=`. Las rutas AJAX de inventario se desvian al controlador antes de cargar la vista. Las rutas validas son los archivos PHP existentes en `src/app/Views/`.

### Metodo `dispatch()` implicito (todo el archivo)

```php
<?php
session_start();

// 1. Determinar pagina solicitada
$pagina = "login";
if(!empty($_GET["pagina"])){
    $pagina = $_GET["pagina"];
}

// 2. Validar seguridad (solo alfanumerico y guiones)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
    $pagina = "login";
}

// 3. Control de acceso
$public_pages = ['login', 'login_validate'];
if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
    header("Location: ?pagina=login");
    exit;
}

// 3b. Ruta AJAX para inventario (controlador en lugar de vista)
if ($pagina === 'inventario' && isset($_GET['action'])) {
    require __DIR__ . '/../Controllers/inventarioController.php';
    exit;
}

// 4. Resolver ruta de la vista
$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';

// 5. Cargar vista
if(is_file($rutaVista)){
    if (in_array($pagina, $public_pages)) {
        require $rutaVista;  // Paginas publicas: standalone
    } else {
        // Paginas protegidas: dentro del layout
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestion de Inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafe',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadisticas',
            'activos'      => 'Gestion de Activos',
            'asesorias'    => 'Asesoria Legal',
            'usuarios'     => 'Gestion de Usuarios',
        ];
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text">5 Disponibles</span><span class="chip orange white-text">4 Ocupadas</span>',
        ];
        $pageTitle = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';
        $contentView = $rutaVista;
        require __DIR__ . '/../template/layout.php';
    }
} else {
    // 6. Error 404
    http_response_code(404);
    echo "<h1>Error 404: Pagina no encontrada</h1>";
    echo "<p>La pagina <strong>{$pagina}</strong> no existe.</p>";
    echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
}
?>
```

| Paso | Explicacion |
|------|-------------|
| `session_start()` | Inicia la sesion PHP. Debe llamarse antes de cualquier salida. |
| `$pagina = $_GET["pagina"] ?? 'login'` | Lee el parametro de la URL. Por defecto: login. |
| `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` | Validacion de seguridad: solo caracteres seguros. Previene path traversal. |
| `$public_pages` | Array con paginas que no requieren autenticacion (login, login_validate). |
| `!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)` | Redirige al login si la pagina requiere auth y el usuario no ha iniciado sesion. |
| `$pagina === 'inventario' && isset($_GET['action'])` | Ruta AJAX: carga el controlador `inventarioController.php` en lugar de la vista. |
| `is_file($rutaVista)` | Verifica que el archivo de vista exista en el sistema de archivos. |
| `$titulos[$pagina]` | Array asociativo con titulos para cada pagina (usado en el layout). |
| `$contentView` | Ruta de la vista a incluir dentro del layout. |
| `http_response_code(404)` | Establece codigo HTTP 404 si la pagina no existe. |

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
<script src="Public/js/app.core.js"></script>
<script src="Public/js/app.init.js"></script>
<script src="Public/js/app.tables.js"></script>
<script src="Public/js/app.ui.js"></script>

<!-- Scripts especificos por pagina -->
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
```

---

## 5. Mapa de Paginas

| Parametro | Vista | Publica? | JS Adicional |
|-----------|-------|----------|-------------|
| `login` | `login.php` | Si | Ninguno |
| `login_validate` | `login_validate.php` | Si | Ninguno |
| `dashboard` | `dashboard.php` | No | Ninguno |
| `inventario` | `inventario.php` | No | `app.inventario.js` |
| `inventario&action=X` | `inventarioController.php` (JSON) | No | (AJAX) |
| `ventas` | `ventas.php` | No | `app.pos.js` |
| `ciberControl` | `ciberControl.php` | No | `app.cyber.js` |
| `proveedores` | `proveedores.php` | No | Ninguno |
| `reportes` | `reportes.php` | No | Ninguno |
| `activos` | `activos.php` | No | Ninguno |
| `asesorias` | `asesorias.php` | No | `app.legal.js` |
| `menu` | `menu.php` | No | Ninguno |
| `usuarios` | `usuarios.php` | No | Ninguno |

---

## 6. Vistas

### Vistas publicas (standalone, sin layout)
- `login.php` — Tiene su propio DOCTYPE, head, body. Carga jQuery, Materialize JS y app.core.js.

### Vistas protegidas (dentro del layout)
Todas las demas vistas son solo fragmentos HTML sin estructura completa de pagina. El layout maestro provee el HTML comun.

```
Views/
├── login.php                   # Publica, standalone
├── login_validate.php          # Publica, solo PHP
├── dashboard.php               # Protegida, dentro del layout
├── inventario.php
├── ventas.php
├── proveedores.php
├── reportes.php
├── activos.php
├── ciberControl.php
├── asesorias.php
├── menu.php
└── usuarios.php
```

---

## 7. Seguridad del Sistema

| Aspecto | Implementacion |
|---------|----------------|
| **Path Traversal** | Regex `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` en router.php |
| **Autenticacion** | Verificacion de `$_SESSION['logged_in']` antes de cargar vistas protegidas |
| **404** | `is_file()` verifica existencia del archivo de vista |
| **CSRF** | Pendiente de implementar |
| **SQL Injection** | Los modelos usan prepared statements con PDO |
| **Listado Directorios** | `Options All -Indexes` en .htaccess |

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

## 10. Diferencia con una Arquitectura MVC (Potencial)

| Aspecto | Actual (Procedural) | MVC con Clases |
|---------|-------------------|----------------|
| **Enrutador** | `router.php` (69 lineas, procedural) | Clase Router con mapa de rutas y dispatch |
| **Punto de entrada** | `require_once router.php` directo | `vendor/autoload.php` + `new Router()` + `->dispatch()` |
| **Controladores** | No existen (logica en vistas) | Clases en `Controllers/` con namespace |
| **Layout** | `template/layout.php` | `Views/layouts/main.php` |
| **Vistas** | `Views/dashboard.php` (plano) | `Views/dashboard/index.php` (subdirectorios) |
| **Autoloader** | No usado para el enrutamiento | Composer PSR-4 |
| **Logica de login** | `Views/login_validate.php` (vista) | LoginController::validate() |
| **Datos de Cyber** | En la vista (`ciberControl.php`) | En el controlador |
| **Titulos** | Array en router.php | Propiedad de clase Controller |

---

## 11. Pruebas Realizadas

| Prueba | Resultado |
|--------|-----------|
| Cargar login.php sin sesion | PASS |
| Login con credenciales correctas (admin/1234) -> dashboard | PASS |
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

---

**Documentacion**: Junio 2026

