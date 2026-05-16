# Documentacion de Integracion de jQuery en EIS System

## 1. Descripcion General

Se integro **jQuery 3.7.1** y **Materialize CSS 1.0.0** en la aplicacion EIS System (PHP vanilla) para estandarizar y modernizar la interfaz de usuario. La integracion incluyo:

- Adicion de jQuery v3.7.1 y Materialize CSS v1.0.0 via CDN en todas las paginas
- Creacion de un layout maestro (`layout.php`) que centraliza el HTML/JS comun
- Migracion de JS vanilla a jQuery para theme toggle, sidebar, busquedas, filtros
- Implementacion de componentes Materialize: sidenav, modals, selects, tooltips, tabs
- Refactorizacion de 7 vistas autenticadas para usar solo contenido (sin HTML repetido)
- Creacion de `app.js` con funcionalidad central (362 lineas)

---

## 2. Archivos Modificados y Creados

| Archivo | Tipo | Proposito |
|---------|------|-----------|
| `src/Public/js/app.js` | **Creado** | JS central con jQuery (362 lineas) |
| `src/app/template/layout.php` | **Creado** | Layout maestro con jQuery + Materialize |
| `src/app/core/router.php` | Modificado | Integracion del layout con titulos dinamicos |
| `src/app/Views/*.php` (7 vistas) | Modificadas | Eliminado HTML/JS duplicado, solo contenido |
| `src/app/Views/login.php` | Modificado | JS migrado a jQuery + theme toggle |
| `src/app/Views/menu.php` | Modificado | JS migrado a jQuery + theme toggle |
| `src/Public/css/styles.css` | Modificado | Reducido de 748 a 404 lineas (ahora complementa Materialize) |
| `src/Public/css/login.css` | Modificado | Reducido de 227 a 58 lineas |

---

## 3. Librerias Cargadas via CDN

### 3.1 Layout principal - `src/app/template/layout.php`

**Materialize CSS** (en `<head>`):
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
```

**Material Icons** (Google Fonts, en `<head>`):
```html
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
```

**jQuery 3.7.1** (en `<head>`):
```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
```

**Materialize JS** (al final del `<body>`):
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
```

**app.js** (al final del `<body>`):
```html
<script src="Public/js/app.js"></script>
```

### 3.2 Login - `src/app/Views/login.php`

```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js" ...></script>
```

### 3.3 Menu - `src/app/Views/menu.php`

```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js" ...></script>
```

---

## 4. Archivo `app.js` - Explicacion detallada

**Ruta**: `src/Public/js/app.js` (362 lineas)

### Estructura General

```javascript
var EIS = {};  // Namespace global para la aplicacion

$(function () {
    // 1. Inicializar todos los componentes Materialize
    // 2. Reloj en tiempo real (setInterval cada 1s)
    // 3. Sistema de notificaciones EIS.toast()
    // 4. Tema oscuro/claro con localStorage
    // 5. Animacion de transicion de pagina (fadeIn)
    // 6. Animacion de contadores numericos
    // 7. Busqueda en tablas con debounce
    // 8. Sistema POS (carrito de compras)
    // 9. Control de estaciones cyber
    // 10. Reportes (formulario simulado)
    // 11. Botones de accion generales
    // 12. Notificaciones demo (campana)
    // 13. Boton volver arriba
});
```

### 4.1 Inicializacion de Materialize (lineas 6-16)

```javascript
$('.sidenav').sidenav();
$('select').formSelect();
$('.tooltipped').tooltip();
$('.modal').modal();
$('.dropdown-trigger').dropdown();
$('.tabs').tabs();
$('.collapsible').collapsible();
$('.materialboxed').materialbox();
$('.parallax').parallax();
```

Cada linea inicializa un componente de Materialize CSS usando su plugin jQuery. Los selectores buscan elementos con las clases correspondientes en el DOM. Si no hay elementos que coincidan, la llamada simplemente no hace nada (seguro).

### 4.2 Reloj en Tiempo Real (lineas 18-27)

```javascript
function actualizarReloj() {
    var now = new Date();
    var opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    var timeStr = now.toLocaleTimeString('es-ES', opts);
    var dateStr = now.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
    $('#clock').text(timeStr + ' - ' + dateStr);
}
actualizarReloj();
setInterval(actualizarReloj, 1000);
```

- `new Date()` - Obtiene la fecha/hora actual del sistema
- `toLocaleTimeString('es-ES', opts)` - Formato hora espanol (24h): "14:30:25"
- `toLocaleDateString('es-ES', ...)` - Formato fecha: "15 abr 2026"
- `setInterval(actualizarReloj, 1000)` - Actualiza cada 1 segundo

### 4.3 Sistema de Notificaciones (lineas 30-35)

```javascript
EIS.toast = function (msg, color, icon) {
    color = color || 'indigo';
    icon = icon || 'check_circle';
    var html = '<i class="material-icons left" style="font-size:1.2rem;">' + icon + '</i>' + msg;
    M.toast({ html: html, classes: color + ' rounded', displayLength: 3000 });
};
```

Funcion global accesible como `EIS.toast('mensaje', 'color', 'icono')`:
- `msg` - Texto del mensaje
- `color` - Clase de color de Materialize (indigo, green, red, orange, etc.)
- `icon` - Nombre del icono Material Icons
- `M.toast()` - Plugin de Materialize para mostrar toasts

### 4.4 Tema Oscuro/Claro (lineas 37-54)

```javascript
function updateThemeUI(theme) {
    var isDark = theme === 'dark';
    $('#themeIcon').text(isDark ? 'light_mode' : 'dark_mode');
    $('#themeLabel').text(isDark ? 'Modo Claro' : 'Modo Oscuro');
}

var currentTheme = localStorage.getItem('theme') || 'light';
$('html').attr('data-theme', currentTheme);
updateThemeUI(currentTheme);

$(document).on('click', '#themeToggle', function () {
    var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
    $('html').attr('data-theme', theme);
    localStorage.setItem('theme', theme);
    updateThemeUI(theme);
    EIS.toast('Tema cambiado a ' + (theme === 'dark' ? 'oscuro' : 'claro'), 'indigo', 'palette');
});
```

- `localStorage.getItem('theme')` - Recupera preferencia guardada
- `$('html').attr('data-theme', theme)` - Aplica tema via atributo CSS
- `$(document).on('click', '#themeToggle', fn)` - Event delegation (funciona aunque el elemento se cargue dinamicamente)
- `localStorage.setItem('theme', theme)` - Persiste la preferencia

### 4.5 Animacion de Transicion (lineas 57-58)

```javascript
$('main').hide().fadeIn(400);
$('.container').hide().fadeIn(500);
```

Efecto de entrada suave al cargar cada pagina.

### 4.6 Animacion de Contadores (lineas 61-83)

```javascript
function animarContadores() {
    $('.metric-value').each(function () {
        var $el = $(this);
        var text = $el.text();
        var num = parseFloat(text.replace(/[^0-9.,-]/g, '').replace(',', ''));
        if (isNaN(num)) return;
        $({ val: 0 }).animate({ val: num }, {
            duration: 1200,
            step: function () {
                var v = isCurrency ? '$' + this.val.toFixed(2).replace(...) : prefix + Math.round(this.val);
                $el.text(v);
            }
        });
    });
}
```

- Selecciona elementos con clase `.metric-value`
- Extrae el valor numerico del texto (ej: "$1,245.50" -> 1245.50)
- Usa `$.animate()` para animar de 0 al valor final en 1200ms
- Soporta formato moneda ($) y numeros enteros

### 4.7 Busqueda en Tablas con Debounce (lineas 85-127)

```javascript
function debounce(fn, delay) {
    var timer;
    return function () {
        var ctx = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
}

function filtrarTabla(inputSelector, tableSelector, colIndex) {
    var q = $(inputSelector).val().toLowerCase();
    $(tableSelector + ' tbody tr').each(function () {
        var $row = $(this);
        var text = colIndex !== undefined
            ? $row.find('td').eq(colIndex).text().toLowerCase()
            : $row.text().toLowerCase();
        $row.toggle(text.indexOf(q) !== -1);
    });
}
```

**Debounce**: Tecnica que limita la frecuencia de ejecucion de una funcion. Aqui se usa para busqueda en tiempo real:
- `clearTimeout(timer)` - Cancela la ejecucion anterior
- `setTimeout(fn, delay)` - Programa nueva ejecucion tras 300ms sin escribir

**Busqueda por campos**: Buscadores para:
- `#searchProducto` - Filtra tabla de inventario por columna 1 (nombre)
- `#searchProveedor` - Filtra tabla de proveedores por columna 1
- `#searchActivo` - Filtra tabla de activos por columna 0
- `#posSearch` - Filtra productos POS con debounce de 200ms

### 4.8 Sistema POS (lineas 147-234)

```javascript
var posCart = [];
var posTotal = 0;

$(document).on('click', '.pos-product', function () {
    var name = $(this).data('name');
    var price = parseFloat($(this).data('price'));
    posCart.push({ name: name, price: price });
    posTotal += price;
    actualizarPosUI();
    EIS.toast(name + ' agregado al carrito', 'green', 'add_shopping_cart');
});
```

- `posCart` - Array de objetos `{name, price}`
- `posTotal` - Suma acumulada de precios
- `actualizarPosUI()` - Actualiza mini-total y modal del carrito
- `$(document).on('click', '.pos-product', fn)` - Event delegation para productos

**Funciones del POS**:

| Funcion | Descripcion |
|---------|-------------|
| `actualizarPosUI()` | Actualiza mini-total y modal |
| `actualizarMiniTotal()` | Actualiza `#posMiniTotal` y `#cartCountBadge` |
| `actualizarCarritoModal()` | Renderiza items en `#posCartItems` |
| `procesarVenta` | Simula venta (toast + reseteo) |
| `vaciarCarrito` | Limpia el carrito |

### 4.9 Control de Estaciones Cyber (lineas 237-291)

```javascript
function actualizarCyberContadores() {
    var total = $('.station-card').length;
    var disp = $('.station-card.disponible').length;
    var ocup = $('.station-card.ocupada').length;
    var mant = $('.station-card.mantenimiento').length;
    $('#countDisponibles').text(disp);
    $('#countOcupadas').text(ocup);
    $('#countMantenimiento').text(mant);
}

$(document).on('click', '.station-card', function () {
    var $card = $(this);
    var status = $card.data('status');
    // Toggle: disponible <-> ocupada, con animacion
    $card.find('.station-status').css({ transform: 'scale(0.8)', opacity: 0 })
         .animate({ transform: 'scale(1)', opacity: 1 }, 300);
    actualizarCyberContadores();
    EIS.toast('Sesion iniciada en ' + num, 'green', 'play_circle');
});
```

- Contadores dinamicos con `$('.station-card.disponible').length`
- Toggle de estados con animacion `$.animate()`
- Filtro visual con `$(document).on('click', '.filter-btn', fn)` y `.slideDown(200)` / `.hide()`

### 4.10 Otras Funcionalidades (lineas 293-362)

**Reportes** (lineas 294-302):
```javascript
$(document).on('submit', '#formReporte', function (e) {
    e.preventDefault();
    EIS.toast('Generando reporte...', 'indigo', 'download');
});
```

**Botones de accion** (lineas 305-314):
- `[data-confirm]` - Confirmacion antes de accion
- `.btn-nuevo` - Toast de "nuevo elemento"

**Paginacion** (lineas 317-325):
```javascript
$(document).on('click', '.pagination li:not(.disabled):not(.active) a', function (e) {
    e.preventDefault();
    // Cambia pagina activa y muestra toast
});
```

**Notificaciones demo** (lineas 339-347):
```javascript
$(document).on('click', '#notifBell', function () {
    var msgs = ['Stock critico: Mouse', 'Sesion Cyber #2 finalizada', ...];
    msgs.forEach(function (m) { EIS.toast(m, 'orange', 'notifications'); });
});
```

**Boton volver arriba** (lineas 350-360):
```javascript
$(window).on('scroll', function () {
    $('#backToTop').fadeIn(); // o fadeOut() segun scroll
});
$(document).on('click', '#backToTop', function () {
    $('html, body').animate({ scrollTop: 0 }, 400);
});
```

---

## 5. Layout Maestro - `src/app/template/layout.php`

### Estructura

```
<!DOCTYPE html>
<html>
<head>
    - Meta tags
    - Material Icons (Google Fonts)
    - Materialize CSS (CDN)
    - styles.css (personalizado)
    - jQuery 3.7.1 (CDN)                <-- Linea 10
</head>
<body>
    - Sidenav (sidebar con navegacion)   <-- Materialize Sidenav
    - Header (nav con titulo, reloj, notificaciones)
    - Main container
        - <?php require $contentView; ?> <-- Contenido especifico
    - Back to top button
    - Materialize JS (CDN)               <-- Final del body
    - app.js                             <-- Final del body
</body>
</html>
```

### Variables PHP

| Variable | Origen | Proposito |
|----------|--------|-----------|
| `$pageTitle` | `router.php` | Titulo de la pagina en nav y title |
| `$headerExtra` | `router.php` | HTML extra en el header (badges, etc.) |
| `$contentView` | `router.php` | Ruta al archivo de vista a incluir |
| `$pagina` | `router.php` | Nombre de la pagina actual (para active en nav) |

---

## 6. Router - `src/app/core/router.php`

### Integracion del layout (lineas 24-43)

```php
if (in_array($pagina, $public_pages)) {
    require $rutaVista;                    // login, login_validate -> standalone
} else {
    $pageTitle = $titulos[$pagina];        // Titulo dinamico del array
    $headerExtra = $extraHeaders[$pagina]; // Badges extra (ej: Cyber)
    $contentView = $rutaVista;             // Ruta al contenido especifico
    require __DIR__ . '/../template/layout.php';  // Envuelve en layout maestro
}
```

Arrays de configuracion:

```php
$titulos = [
    'dashboard'    => 'Panel de Control',
    'inventario'   => 'Gestion de Inventario',
    'ventas'       => 'Punto de Venta (POS)',
    'ciberControl' => 'Control de Cybercafe',
    'proveedores'  => 'Solicitudes a Proveedores',
    'reportes'     => 'Reportes y Estadisticas',
    'activos'      => 'Gestion de Activos',
];

$extraHeaders = [
    'ciberControl' => '<span class="chip green white-text">7 Disponibles</span>...',
];
```

---

## 7. Vistas Refactorizadas - De HTML Completo a Solo Contenido

### Antes (ejemplo: `dashboard.php` - 198 lineas):

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - EIS System</title>
    <link rel="stylesheet" href="Public/css/styles.css">
</head>
<body>
    <div class="app">
        <aside class="sidebar" id="sidebar">...</aside>
        <main class="main">
            <header class="top-header">...</header>
            <div class="content">
                <!-- Contenido especifico -->
            </div>
        </main>
    </div>
    <script>
        // JS duplicado en cada vista (sidebar, theme toggle)
    </script>
</body>
</html>
```

### Despues (`dashboard.php` - 135 lineas):

```php
<!-- Welcome Banner -->
<div class="welcome-banner">...</div>
<!-- Metrics Grid -->
<div class="row">
    <div class="col s12 m6 l3">
        <div class="metric-card">...</div>
    </div>
</div>
<!-- Tables -->
<div class="row">
    <div class="col s12 l6"><div class="card"><table>...</table></div></div>
</div>
<!-- Recent Activity -->
<div class="card">...</div>
```

**Beneficio**: Se eliminaron ~70 lineas de HTML/JS duplicado por vista. Ahora el layout proporciona:
- DOCTYPE, `<html>`, `<head>`, `<body>`
- Sidebar con navegacion Materialize Sidenav
- Header con nav, reloj, notificaciones
- jQuery CDN, Materialize CSS/JS y app.js
- Cierre de etiquetas

---

## 8. Resumen de Beneficios de la Migracion

| Aspecto | Antes (Vanilla JS + CSS propio) | Despues (jQuery + Materialize) |
|---------|-------------------------------|-------------------------------|
| **Selectores** | `document.getElementById('id')` | `$('#id')` |
| **Atributos** | `element.setAttribute('data-theme', x)` | `$('html').attr('data-theme', x)` |
| **Clases CSS** | `element.classList.toggle('open')` | `$('#sidebar').toggleClass('open')` |
| **Eventos** | `element.addEventListener('click', fn)` | `$(document).on('click', '#btn', fn)` |
| **Animaciones** | CSS transitions manuales | `$.fadeIn()`, `$.slideDown()`, `$.animate()` |
| **Framework UI** | CSS propio (748 lineas) | Materialize CSS (CDN) + CSS propio (404 lineas) |
| **Componentes UI** | Sidebar manual, tablas basicas | Sidenav, modals, selects, tooltips, tabs, chips |
| **Codigo duplicado** | 7 copias del mismo JS + HTML | 1 solo layout + 1 solo app.js |
| **Lineas CSS** | 975 total (styles + login) | 462 total (404 + 58) |
| **Lineas JS en vistas** | ~200 lineas dispersas | 0 (todo en app.js) |
| **Iconos** | Emojis | Material Icons (vectorial, escalable) |
| **Tema oscuro** | No existia | Si, con localStorage |

---

## 9. Estructura Final de Archivos Relacionados

```
src/
+-- app/
|   +-- core/
|   |   +-- router.php              <- Usa layout para paginas auth
|   +-- template/
|   |   +-- layout.php              <- Layout con Materialize + jQuery
|   +-- Views/
|       +-- login.php               <- Standalone con jQuery + theme toggle
|       +-- login_validate.php      <- Solo PHP (sin cambios)
|       +-- menu.php                <- Standalone con jQuery + theme toggle
|       +-- dashboard.php           <- Solo contenido
|       +-- inventario.php          <- Solo contenido
|       +-- ventas.php              <- Solo contenido (POS JS en app.js)
|       +-- ciberControl.php        <- Solo contenido (station JS en app.js)
|       +-- proveedores.php         <- Solo contenido
|       +-- reportes.php            <- Solo contenido
|       +-- activos.php             <- Solo contenido
+-- Public/
    +-- css/
    |   +-- styles.css              <- Estilos personalizados (404 lineas)
    |   +-- login.css               <- Estilos login (58 lineas)
    +-- js/
        +-- app.js                  <- JS central con jQuery (362 lineas)
```

---

## 10. Dependencias CDN

| Libreria | Version | URL |
|----------|---------|-----|
| jQuery | 3.7.1 | `https://code.jquery.com/jquery-3.7.1.min.js` |
| Materialize CSS | 1.0.0 | `https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css` |
| Materialize JS | 1.0.0 | `https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js` |
| Material Icons | - | `https://fonts.googleapis.com/icon?family=Material+Icons` |
