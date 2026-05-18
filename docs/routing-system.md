# Sistema de Enrutamiento Procedural — Documentación Técnica

## Arquitectura Actual

```
src/
├── index.php                    ← Punto de entrada (Front Controller)
├── .htaccess                    ← Reglas de reescritura (URLs query string)
├── app/
│   ├── core/
│   │   └── router.php           ← Enrutador procedural (68 líneas)
│   ├── template/
│   │   └── layout.php           ← Layout maestro (sidebar, header)
│   ├── Views/                   ← Archivos de vista (HTML + PHP mínimo)
│   │   ├── login.php
│   │   ├── login_validate.php
│   │   ├── dashboard.php
│   │   ├── inventario.php
│   │   ├── ventas.php
│   │   ├── proveedores.php
│   │   ├── reportes.php
│   │   ├── ciberControl.php
│   │   ├── activos.php
│   │   ├── asesorias.php
│   │   └── menu.php
│   └── Models/
│       ├── crud_users.php
│       └── crud_asesorias.php
├── Config/
│   └── database.php
└── Public/
    ├── css/
    └── js/
```

**NOTA**: Esta es la arquitectura actual (procedural). Existe documentación previa (`DOCUMENTACION_COMPLETA.md`) que describe una arquitectura MVC planificada con clases `Router`, `Request`, `Controller` y controladores con namespace, pero dichas clases **NO existen en el código actual**. Este documento describe el sistema real.

---

## 1. `composer.json` — Autoloading PSR-4

```json
{
    "name": "carlospez/clase",
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "require": {}
}
```

| Línea | Explicación |
|-------|-------------|
| `"App\\": "src/"` | Mapea el namespace `App\` al directorio `src/`. Preparado para futura migración MVC. |
| `require: {}` | Sin dependencias externas. Sistema 100% vanilla PHP. |

Actualmente solo el autoloader de Composer está en `vendor/`. Las clases con namespace (`App\Core\Router`, `App\Controllers\*`) no existen en disco.

---

## 2. `src/.htaccess` — URLs con Query String

```apache
Options All -Indexes
RewriteEngine On

RewriteRule ^$ index.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

| Línea | Explicación |
|-------|-------------|
| `Options All -Indexes` | Bloquea el listado de directorios por seguridad. |
| `RewriteEngine On` | Activa el módulo de reescritura de Apache. |
| `RewriteRule ^$ index.php [L,QSA]` | La raíz `/` se redirige internamente a `index.php`. |
| `RewriteCond %{REQUEST_FILENAME} !-f` | Solo aplica la regla si el archivo NO existe físicamente (permite servir CSS, JS, imágenes). |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Solo aplica si el directorio NO existe. |
| `RewriteRule ^(.*)$ index.php [QSA,L]` | Cualquier otra ruta se envía a `index.php`. `QSA` preserva los query parameters. |

**Actualmente** las URLs usan el formato `?pagina=nombre`. No hay URLs limpias implementadas.

---

## 3. `src/index.php` — Front Controller

```php
<?php
// Punto de entrada único de la aplicación (Front Controller).
// Todas las solicitudes HTTP pasan por aquí gracias a las reglas de reescritura de Apache (.htaccess).

    require_once __DIR__.'/app/core/router.php'; // Incluye el router, que maneja la lógica de navegación y autenticación
?>
```

| Línea | Explicación |
|-------|-------------|
| `require_once __DIR__.'/app/core/router.php'` | Incluye el archivo del enrutador procedural. Todo el flujo de la aplicación se delega a `router.php`. |

A diferencia de un front controller MVC, aquí **no** hay:
- `session_start()` (se hace en router.php)
- Autoloader de Composer (no hay clases con namespace)
- Definición de rutas (se manejan internamente en router.php)

---

## 4. `src/app/core/router.php` — Enrutador Procedural (68 líneas)

```php
<?php
session_start();

$pagina = "login";

if(!empty($_GET["pagina"])){
    $pagina = $_GET["pagina"];
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
    $pagina = "login";
}

$public_pages = ['login', 'login_validate'];
if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
    header("Location: ?pagina=login");
    exit;
}

$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';

if(is_file($rutaVista)){
    if (in_array($pagina, $public_pages)) {
        require $rutaVista;
    } else {
        $titulos = [
            'dashboard'    => 'Panel de Control',
            'inventario'   => 'Gestión de Inventario',
            'ventas'       => 'Punto de Venta (POS)',
            'ciberControl' => 'Control de Cybercafé',
            'proveedores'  => 'Solicitudes a Proveedores',
            'reportes'     => 'Reportes y Estadísticas',
            'activos'      => 'Gestión de Activos',
            'asesorias'    => 'Asesoría Legal',
        ];
        $extraHeaders = [
            'ciberControl' => '<span class="chip green white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">5 Disponibles</span><span class="chip orange white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">4 Ocupadas</span>',
        ];
        $pageTitle = $titulos[$pagina] ?? 'EIS System';
        $headerExtra = $extraHeaders[$pagina] ?? '';
        $contentView = $rutaVista;
        require __DIR__ . '/../template/layout.php';
    }
} else {
    http_response_code(404);
    echo "<h1>Error 404: Página no encontrada</h1>";
    echo "<p>La página <strong>{$pagina}</strong> no existe.</p>";
    echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
}
?>
```

| Línea | Explicación |
|-------|-------------|
| `session_start()` | Inicia la sesión PHP para manejar autenticación. Se llama **antes** que cualquier salida al navegador. |
| `$pagina = "login"` | Valor por defecto: muestra el formulario de login si no se especifica otra página. |
| `$_GET["pagina"]` | Obtiene el nombre de la página desde la URL: `?pagina=nombre`. |
| `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` | **Validación de seguridad**: solo permite caracteres alfanuméricos, guiones y guiones bajos. Previene path traversal (ej: `?pagina=../../../etc/passwd`). |
| `$public_pages = ['login', 'login_validate']` | Array whitelist de páginas que no requieren autenticación. |
| `!isset($_SESSION['logged_in'])` | Verifica si el usuario NO ha iniciado sesión. |
| `$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php'` | Construye la ruta absoluta al archivo de vista. Ej: `/dashboard.php`. |
| `is_file($rutaVista)` | Verifica que el archivo exista en el sistema de archivos antes de incluirlo. |
| `$titulos = [...]` | Array asociativo que mapea cada nombre de página a su título mostrado en el `<title>` y `brand-logo`. Incluye 8 módulos. |
| `$pageTitle = $titulos[$pagina] ?? 'EIS System'` | Operador null coalescing: si la página no está en el array, usa 'EIS System' como fallback. |
| `$contentView = $rutaVista` | Variable que contiene la ruta de la vista a incluir dentro del layout. |
| `require __DIR__ . '/../template/layout.php'` | Incluye el layout maestro, que a su vez hará `require $contentView`. |
| `http_response_code(404)` | Establece código HTTP 404 si el archivo de vista no existe. |

### Flujo de autenticación

```
Petición → router.php → ¿$pagina en $public_pages?
  ├── Sí (login, login_validate) → carga directa sin layout
  └── No → ¿$_SESSION['logged_in'] === true?
       ├── Sí → carga layout.php + vista
       └── No → header('Location: ?pagina=login') + exit
```

### Seguridad implementada

1. **Validación regex del parámetro `pagina`**: Solo alfanumérico + guiones. Previene inclusión de archivos maliciosos.
2. **Whitelist de páginas públicas**: Solo `login` y `login_validate` son accesibles sin autenticación.
3. **Verificación de existencia de archivo**: `is_file()` antes de `require`. Si el archivo no existe, muestra 404.
4. **Sin inclusión dinámica de rutas**: La ruta se construye concatenando un directorio base fijo con el nombre validado.

---

## 5. `src/app/template/layout.php` — Layout Maestro (128 líneas)

Fragmento relevante del sidebar:

```php
<li><a href="?pagina=dashboard" class="sidenav-link<?php echo $pagina === 'dashboard' ? ' active' : ''; ?>">
    <i class="material-icons left">dashboard</i>Dashboard</a></li>
<li><a href="?pagina=asesorias" class="sidenav-link<?php echo $pagina === 'asesorias' ? ' active' : ''; ?>">
    <i class="material-icons left">gavel</i>Asesoría Legal</a></li>
<li><a href="?pagina=login" class="sidenav-link"><i class="material-icons left">logout</i>Cerrar Sesión</a></li>
```

| Característica | Descripción |
|----------------|-------------|
| URLs | `?pagina=nombre` (formato query string) |
| Assets | `Public/css/styles.css` (rutas relativas) |
| Cierre sesión | `?pagina=login` (redirige al login, no destruye sesión explícitamente) |
| Módulos | 8 + Theme toggle + Cerrar sesión |
| Clase active | Se agrega dinámicamente comparando `$pagina` con cada ruta |

---

## 6. Seguridad del Sistema

| Aspecto | Cómo se maneja |
|---------|----------------|
| **Inyección de rutas** | Regex `preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)` antes de construir la ruta. |
| **Autenticación** | Verificación de `$_SESSION['logged_in']` antes de cargar páginas protegidas. |
| **Existencia de archivos** | `is_file()` antes de `require`. Las páginas inexistentes reciben 404. |
| **CSRF** | Pendiente de implementar (token CSRF en formularios). |
| **SQL Injection** | Los modelos usan prepared statements con PDO. |
| **Path Traversal** | El router valida el parámetro `pagina` con regex y solo permite caracteres seguros. |

---

## 7. Cómo Agregar una Nueva Página

Para agregar una nueva página al sistema, sigue estos 3 pasos:

### Paso 1: Crear la vista

Crea `src/app/Views/mi-pagina.php` con el contenido HTML de la página (sin DOCTYPE, solo el contenido que irá dentro del layout):

```html
<div class="card">
    <h2>Mi Nueva Página</h2>
    <p>Contenido aquí...</p>
</div>
```

### Paso 2: Registrar el título

En `src/app/core/router.php`, agrega la entrada en el array `$titulos`:

```php
$titulos = [
    ...
    'mi-pagina' => 'Título de mi página',
];
```

También puedes agregar `$extraHeaders` si necesitas HTML extra en el header.

### Paso 3: Agregar al menú

En `src/app/template/layout.php`, agrega un enlace en el `<ul class="sidenav">`:

```php
<li><a href="?pagina=mi-pagina" class="sidenav-link<?php echo $pagina === 'mi-pagina' ? ' active' : ''; ?>">
    <i class="material-icons left">icon_name</i>Mi Página</a></li>
```

### Si la página no requiere autenticación

Agrega el nombre al array `$public_pages` en router.php:

```php
$public_pages = ['login', 'login_validate', 'mi-pagina-publica'];
```

---

## 8. Mapa de Páginas Actual

| Parámetro | Archivo | Autenticación | Título |
|-----------|---------|---------------|--------|
| `login` | `login.php` | No | Login |
| `login_validate` | `login_validate.php` | No | — |
| `dashboard` | `dashboard.php` | Sí | Panel de Control |
| `inventario` | `inventario.php` | Sí | Gestión de Inventario |
| `ventas` | `ventas.php` | Sí | Punto de Venta (POS) |
| `ciberControl` | `ciberControl.php` | Sí | Control de Cybercafé |
| `proveedores` | `proveedores.php` | Sí | Solicitudes a Proveedores |
| `reportes` | `reportes.php` | Sí | Reportes y Estadísticas |
| `activos` | `activos.php` | Sí | Gestión de Activos |
| `asesorias` | `asesorias.php` | Sí | Asesoría Legal |
| `menu` | `menu.php` | Sí | — |

---

## 9. Diferencia con la Arquitectura MVC Planificada

| Aspecto | Actual (Procedural) | Planificado (MVC) |
|---------|---------------------|-------------------|
| **Enrutador** | `router.php` (68 líneas, procedural) | `Core/Router.php` (clase con namespace) |
| **Controladores** | No existen (la lógica está en vistas) | `Controllers/*Controller.php` |
| **Request** | Uso directo de `$_GET`, `$_POST` | `Core/Request.php` (clase encapsuladora) |
| **Layout** | Variable `$contentView` + `require` | `Controller::renderWithLayout()` |
| **URLs** | `?pagina=nombre` | `/nombre` (URLs limpias) |
| **Middleware** | No existe (if directo en router) | Sistema de middleware con `auth` |
| **Autoloader** | No usado (includes directos) | Composer PSR-4 (vendor/autoload.php) |
| **Base URL** | No implementada | Constante `BASE_URL` calculada automáticamente |

---

## 10. Resumen del Flujo de una Petición

```
Usuario escribe:  http://localhost/eis_zona_web_lara/src/?pagina=dashboard

1. Apache recibe GET /src/?pagina=dashboard
2. src/.htaccess: ¿Existe el archivo /src/? No → rewrite a index.php
3. index.php: require_once router.php
4. router.php:
   a. session_start()
   b. $pagina = "dashboard"
   c. preg_match: validación de seguridad → OK
   d. ¿$_SESSION['logged_in']? → Sí
   e. $rutaVista = /../Views/dashboard.php
   f. is_file() → Sí
   g. $pageTitle = 'Panel de Control'
   h. $contentView = $rutaVista
   i. require layout.php
5. layout.php:
   a. <html><head> con Materialize + jQuery
   b. Sidebar con enlaces a módulos
   c. Header con reloj y notificaciones
   d. <main> → require dashboard.php
   e. Scripts: Materialize JS + app.js
6. app.js (cliente):
   a. Inicializa componentes Materialize
   b. Actualiza reloj digital
   c. Anima contadores de métricas
7. Se envía HTML completo al navegador
```

---

## 11. Pruebas Realizadas

| Prueba | Resultado |
|--------|-----------|
| Cargar login.php sin sesión | ✅ PASS |
| Login con credenciales correctas (admin/1234) → dashboard | ✅ PASS |
| Login con credenciales incorrectas → error | ✅ PASS |
| Acceder a dashboard sin sesión → redirige a login | ✅ PASS |
| Cargar inventario, ventas, etc. con sesión | ✅ PASS |
| Parámetro `?pagina=inexistente` → 404 | ✅ PASS |
| Parámetro malicioso `?pagina=../../../etc` → redirige a login | ✅ PASS |
| Tema oscuro/claro persiste en localStorage | ✅ PASS |
| Carrito POS agrega/elimina productos | ✅ PASS |
| Cyber toggle de estados | ✅ PASS |
| Asesoría valida documentos permitidos/denegados | ✅ PASS |
| Búsqueda en tablas filtra correctamente | ✅ PASS |
| Paginación cambia de página visualmente | ✅ PASS |
| Botón volver arriba funciona | ✅ PASS |
