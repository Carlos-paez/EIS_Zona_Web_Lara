# Sistema de Enrutamiento - EIS Zona Web Lara

El proceso de enrutamiento de esta aplicacion sigue el patron **Front Controller**, centralizando todas las peticiones en un unico punto de entrada. A continuacion, se detalla el funcionamiento paso a paso.

---

## 1. Nivel de Servidor: Redireccion (.htaccess)

### Raiz del proyecto (`/.htaccess`)

```apache
RewriteEngine On

# Si la URL no existe como archivo o directorio, redirige a src/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ src/$1 [L]

# Si la URL es la raiz, redirige a src/
RewriteRule ^$ src/ [L,R=301]
```

- `RewriteEngine On`: Activa el motor de reescritura de Apache.
- `RewriteCond %{REQUEST_FILENAME} !-f` y `!-d`: Verifican que NO sea un archivo o directorio real (imagenes, CSS, etc.).
- `RewriteRule ^(.*)$ src/$1 [L]`: Redirige internamente cualquier peticion a la carpeta `src/`.
- `RewriteRule ^$ src/ [L,R=301]`: La raiz redirige permanentemente a `src/`.

### Dentro de `src/` (`src/.htaccess`)

```apache
Options All -Indexes
RewriteEngine On

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

- `Options All -Indexes`: Bloquea el listado de directorios por seguridad.
- `RewriteRule ^$ index.php [L,QSA]`: La raiz de `src/` se sirve desde `index.php`.
- `RewriteRule ^(.*)$ index.php [QSA,L]`: Cualquier otra ruta (como `/dashboard`) se envia a `index.php`, preservando parametros con `QSA`.

---

## 2. Punto de Entrada: `src/index.php`

```php
<?php
require_once __DIR__.'/app/core/router.php';
```

- `require_once`: Incluye el archivo `router.php` una sola vez.
- `__DIR__`: Constante magica que apunta al directorio actual (`src/`).
- **No usa autoloader de Composer** para el enrutamiento (el router es procedural, no orientado a objetos).

---

## 3. El Router: `src/app/core/router.php`

### 3.1 Inicio de Sesion y Parametro de Pagina

```php
session_start();
$pagina = "login";

if(!empty($_GET["pagina"])){
    $pagina = $_GET["pagina"];
}
```

- `session_start()`: Inicia o reanuda la sesion PHP (debe ser lo primero).
- `$pagina = "login"`: Valor por defecto (pagina de inicio de sesion).
- `$_GET["pagina"]`: Toma el nombre de la pagina desde la URL (ej: `?pagina=dashboard`).

### 3.2 Validacion de Seguridad

```php
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
    $pagina = "login";
}
```

- `preg_match`: Valida que el parametro solo contenga caracteres alfanumericos y guiones.
- **Seguridad**: Previene path traversal (ej: `?pagina=../../../etc/passwd` se redirige a login).

### 3.3 Control de Acceso (Autenticacion)

```php
$public_pages = ['login', 'login_validate'];

if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
    header("Location: ?pagina=login");
    exit;
}
```

- `$public_pages`: Array con las paginas que no requieren autenticacion.
- Si el usuario NO esta autenticado Y la pagina NO es publica: redirige al login.
- `exit`: Detiene la ejecucion para evitar que se siga procesando.

### 3.4 Ruta para Controlador AJAX de Inventario

```php
// Si la pagina es "inventario" y tiene un parametro "action"
if ($pagina === 'inventario' && isset($_GET['action'])) {
    // Carga el controlador en lugar de la vista
    require __DIR__ . '/../Controllers/inventarioController.php';
    exit; // Termina aqui, no sigue a la carga de vista
}
```

Esto permite que las peticiones AJAX de `app.inventario.js` (ej: `?pagina=inventario&action=listar`) lleguen al controlador y devuelvan JSON, mientras que la carga normal de la pagina (`?pagina=inventario`) carga la vista completa.

### 3.5 Carga de la Vista

```php
$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';

if(is_file($rutaVista)){
    if (in_array($pagina, $public_pages)) {
        // Paginas publicas: se renderizan solas
        require $rutaVista;
    } else {
        // Paginas protegidas: se renderizan dentro del layout maestro
        $pageTitle = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';
        $contentView = $rutaVista;
        require __DIR__ . '/../template/layout.php';
    }
} else {
    // Error 404
    http_response_code(404);
    echo "<h1>Error 404: Pagina no encontrada</h1>";
    echo "<p>La pagina <strong>{$pagina}</strong> no existe.</p>";
    echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
}
```

- Si la vista existe:
  - **Publica**: Carga solo el archivo de vista (login, login_validate).
  - **Protegida**: Define variables (`$pageTitle`, `$headerExtra`, `$contentView`) e incluye el layout maestro.
- Si la vista NO existe: Muestra error 404 con enlace al dashboard.

### 3.5 Titulos y Extras por Pagina

```php
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
    'ciberControl' => '<span class="chip green white-text">5 Disponibles</span>'
                    . '<span class="chip orange white-text">4 Ocupadas</span>',
];
```

- `$titulos`: Array asociativo con el titulo de cada pagina (para el `<title>` y la barra de navegacion).
- `$extraHeaders`: HTML adicional para el header (actualmente solo para ciberControl, muestra contadores de estaciones).

---

## 4. Layout Maestro: `src/app/template/layout.php`

El layout recibe las siguientes variables desde `router.php`:

| Variable | Proposito |
|----------|-----------|
| `$pageTitle` | Titulo de la pagina en `<title>` y barra de navegacion |
| `$headerExtra` | HTML extra en el header (badges, chips) |
| `$contentView` | Ruta a la vista a incluir dentro del `<main>` |
| `$pagina` | Nombre de la pagina actual (para active en sidebar y carga condicional de JS) |

### Carga Condicional de JavaScript

El layout carga 4 archivos JS base siempre, y 3 archivos especificos segun la pagina:

```php
<!-- Siempre -->
<script src="Public/js/app.core.js"></script>
<script src="Public/js/app.init.js"></script>
<script src="Public/js/app.tables.js"></script>
<script src="Public/js/app.ui.js"></script>

<!-- Condicional -->
<?php if ($pagina === 'ventas'): ?>
<script src="Public/js/app.pos.js"></script>
<?php endif; ?>
<?php if ($pagina === 'ciberControl'): ?>
<script src="Public/js/app.cyber.js"></script>
<?php endif; ?>
<?php if ($pagina === 'asesorias'): ?>
<script src="Public/js/app.legal.js"></script>
<?php endif; ?>
```

---

## 5. Mapa de Rutas Completo

| Pagina (`?pagina=`) | Vista | Tipo | JS Adicional |
|---------------------|-------|------|--------------|
| `login` | `login.php` | Publica | Ninguno |
| `login_validate` | `login_validate.php` | Publica | Ninguno |
| `dashboard` | `dashboard.php` | Protegida | Ninguno |
| `inventario` | `inventario.php` | Protegida | Ninguno |
| `ventas` | `ventas.php` | Protegida | `app.pos.js` |
| `proveedores` | `proveedores.php` | Protegida | Ninguno |
| `ciberControl` | `ciberControl.php` | Protegida | `app.cyber.js` |
| `reportes` | `reportes.php` | Protegida | Ninguno |
| `activos` | `activos.php` | Protegida | Ninguno |
| `asesorias` | `asesorias.php` | Protegida | `app.legal.js` |
| `menu` | `menu.php` | Protegida | Ninguno |
| `usuarios` | `usuarios.php` | Protegida | Ninguno |

Cualquier otro valor de `?pagina=` devuelve error 404.

---

## 6. Flujo Completo de una Peticion

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
   ├── preg_match('/^[a-zA-Z0-9_-]+$/', "ventas") -> OK
   ├── ¿$_SESSION['logged_in']? -> Si (redirige a login si no)
   ├── $rutaVista = ".../Views/ventas.php" -> existe
   ├── ¿es publica? -> No
   ├── $pageTitle = 'Punto de Venta (POS)'
   ├── $contentView = ".../Views/ventas.php"
   └── require layout.php

4. layout.php renderiza:
   ├── <html><head> con CSS local + jQuery
   ├── Sidebar con navegacion
   ├── Header con reloj y notificaciones
   ├── <main> -> require $contentView (ventas.php)
   ├── Scripts base (core, init, tables, ui)
   ├── $pagina === 'ventas' -> app.pos.js
   └── Service Worker registration

5. Navegador recibe HTML + CSS + JS
   ├── jQuery inicializa componentes Materialize
   ├── Reloj comienza a actualizarse
   ├── app.pos.js prepara carrito POS
   └── Service Worker registrado para offline
```

---

## 7. Seguridad del Sistema de Enrutamiento

| Aspecto | Implementacion |
|---------|----------------|
| **Path Traversal** | Regex `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` en router.php |
| **Autenticacion** | Verificacion de `$_SESSION['logged_in']` antes de cargar vistas protegidas |
| **404** | `is_file()` verifica que la vista exista; si no, `http_response_code(404)` |
| **CSRF** | Pendiente de implementar |
| **SQL Injection** | Los modelos usan prepared statements con PDO (aunque no estan conectados a las vistas) |

---

## 8. Diferencias con una Arquitectura MVC

| Aspecto | Arquitectura Actual (Procedural) | MVC con Clases |
|---------|----------------------------------|----------------|
| **Enrutador** | `router.php` (69 lineas, procedural) | Clase Router con mapa de rutas |
| **Controladores** | No existen (logica en vistas) | Clases Controller por modulo |
| **Modelos** | Funciones procedurales incluidas manualmente | Clases con namespaces |
| **Vistas** | Planas en `/Views/` | En subdirectorios por modulo |
| **Layout** | `template/layout.php` | `Views/layouts/main.php` |
| **Autoloader** | No usado para el router | Composer PSR-4 |

---

**Documentacion**: Junio 2026

