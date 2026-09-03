# Documentacion de Integracion de jQuery en EIS System

## 1. Descripcion General

Se integro **jQuery 3.7.1** y **Materialize CSS 1.0.0** en la aplicacion EIS System (PHP vanilla) para estandarizar y modernizar la interfaz de usuario.

La integracion incluyo:
- Adicion de jQuery v3.7.1 (local) y Materialize CSS v1.0.0 (local)
- Creacion de un layout maestro (`layout.php`) que centraliza el HTML/JS comun
- Migracion de JS vanilla a jQuery para theme toggle, sidebar, busquedas, filtros
- Implementacion de componentes Materialize: sidenav, modals, selects, tooltips, tabs
- Refactorizacion de vistas autenticadas para usar solo contenido (sin HTML repetido)
- Creacion de JavaScript modular en 15 archivos especializados mas el motor de jQuery DataTables
- Validacion de documentos en modulo de Asesoria Legal

---

## 2. Archivos del Proyecto

### JavaScript (15 modulos + DataTables)

| Archivo | Proposito | Carga |
|---------|-----------|-------|
| `Public/js/app.core.js` | Funciones compartidas: namespace EIS, debounce, EIS.toast y helpers `EIS.datatable*` | Siempre |
| `Public/js/app.init.js` | Inicializacion Materialize, reloj, tema oscuro/claro, animaciones | Siempre |
| `Public/js/app.selects.js` | Barra de busqueda en los selects (dropdowns) de Materialize | Siempre |
| `Public/js/app.tables.js` | Punto de extension generico (la busqueda/filtro/paginacion la gestiona DataTables) | Siempre |
| `Public/js/app.ui.js` | Notificaciones, botones de accion, reportes, tooltips | Siempre |
| `Public/js/jquery.dataTables.min.js` | Motor de DataTables 1.13.8 (local) | Siempre |
| `Public/js/dataTables.materialize.js` | Integracion de DataTables con Materialize y config por defecto (es) | Siempre |
| `Public/js/app.pos.js` | Sistema de carrito POS | Solo en pagina ventas |
| `Public/js/app.cyber.js` | Gestion de estaciones Cyber | Solo en pagina ciberControl |
| `Public/js/app.legal.js` | Validacion de documentos de asesoria legal | Solo en pagina asesorias |
| `Public/js/app.inventario.js` | CRUD de inventario via AJAX | Solo en pagina inventario |
| `Public/js/app.roles.js` | CRUD de roles y permisos via AJAX | Solo en pagina roles |
| `Public/js/app.proveedores.js` | CRUD de proveedores y ordenes via AJAX | Solo en pagina proveedores |
| `Public/js/app.proveedores-gestion.js` | CRUD de proveedores (gestion) via AJAX | Solo en pagina proveedores-gestion |
| `Public/js/app.clientes.js` | CRUD de clientes via AJAX | Solo en pagina clientes |
| `Public/js/app.activos.js` | CRUD de activos via AJAX | Solo en pagina activos |
| `Public/js/app.reportes.js` | Generacion de reportes via AJAX | Solo en pagina reportes |

### Librerias (locales)

| Archivo | Version |
|---------|---------|
| `Public/js/jquery-3.7.1.min.js` | jQuery 3.7.1 |
| `Public/js/materialize.min.js` | Materialize JS 1.0.0 |
| `Public/js/jquery.dataTables.min.js` | jQuery DataTables 1.13.8 |
| `Public/css/materialize.min.css` | Materialize CSS 1.0.0 |
| `Public/css/dataTables.materialize.css` | Tema de DataTables (Materialize/oscuro-claro) |
| `Public/css/material-icons.css` | Material Icons (local) |
| `Public/fonts/MaterialIcons-Regular.ttf` | Material Icons font |

---

## 3. Layout Maestro - `src/app/template/layout.php`

### Estructura de Carga de Scripts

```
<!DOCTYPE html>
<html>
<head>
    - Material Icons (local CSS)
    - Materialize CSS (local)
    - styles.css (personalizado)
    - jQuery 3.7.1 (local, en head)
</head>
<body>
    - Sidenav (sidebar con navegacion)
    - Header (nav con titulo, reloj, notificaciones)
    - Main container con $contentView
    - Back to top button

    <!-- Siempre -->
    Materialize JS (local)
    jquery.dataTables.min.js  (motor DataTables)
    dataTables.materialize.js (integracion DataTables + Materialize)
    app.core.js   (EIS, debounce, toast, EIS.datatable*)
    app.init.js   (Materialize init, reloj, tema, animaciones)
    app.selects.js (barra de busqueda en selects de Materialize)
    app.tables.js (punto de extension; la busqueda/filtro/paginacion la gestiona DataTables)
    app.ui.js     (Notificaciones, botones, reportes)

    <!-- Condicional por pagina -->
    app.pos.js         (solo en ventas)
    app.cyber.js       (solo en ciberControl)
    app.legal.js       (solo en asesorias)
    app.inventario.js  (solo en inventario)
    app.roles.js       (solo en roles)
    app.proveedores.js (solo en proveedores)
    app.proveedores-gestion.js (solo en proveedores-gestion)
    app.clientes.js    (solo en clientes)
    app.activos.js     (solo en activos)
    app.reportes.js    (solo en reportes)

    Service Worker registration
</body>
</html>
```

**Nota**: jQuery se carga en el `<head>` para que este disponible inmediatamente cuando los scripts modulares se ejecutan al final del `<body>`.

---

## 4. Archivos JavaScript - Explicacion Detallada

### 4.1 app.core.js - Funciones Compartidas

```javascript
var EIS = {};  // Namespace global

function debounce(fn, delay) {
    var timer;
    return function () {
        var ctx = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
}

// filtrarTabla() es LEGACY: se conserva por compatibilidad pero ya no se usa
// (las tablas las filtra jQuery DataTables). La busqueda/filtro manuales de
// app.js y app.tables.js se eliminaron en favor de DataTables.
function filtrarTabla(inputSelector, tableSelector, colIndex) { ... }

EIS.toast = function (msg, color, icon) {
    color = color || 'indigo';
    icon = icon || 'check_circle';
    var html = '<i class="material-icons left" style="font-size:1.2rem;">'
        + icon + '</i>' + msg;
    M.toast({ html: html, classes: color + ' rounded', displayLength: 3000 });
};

// Familia de helpers de jQuery DataTables:
EIS.datatable(selector, opts)                      // Inicializa DataTables (quita filas vacias colspan)
EIS.datatableRefresh(selector)                     // Recarga filas tras re-render AJAX
EIS.datatableWireSearch(selector, inputSelector)   // Conecta un input a la busqueda global
EIS.datatableWireColumnFilter(selector, sel, col)  // Conecta un select a un filtro de columna
EIS.datatableDestroy(selector)                     // Destruye una instancia (tabla dinamica)
```

### 4.2 app.init.js - Inicializacion

```javascript
$(function () {
    // Inicializar componentes Materialize
    $('.sidenav').sidenav();
    $('select').formSelect();
    $('.tooltipped').tooltip();
    $('.modal').modal();
    $('.dropdown-trigger').dropdown();
    $('.tabs').tabs();
    $('.collapsible').collapsible();

    // Reloj en tiempo real
    function actualizarReloj() {
        var now = new Date();
        var timeStr = now.toLocaleTimeString('es-ES',
            { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        var dateStr = now.toLocaleDateString('es-ES',
            { day: 'numeric', month: 'short', year: 'numeric' });
        $('#clock').text(timeStr + ' - ' + dateStr);
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    // Tema oscuro/claro
    var currentTheme = localStorage.getItem('theme') || 'light';
    $('html').attr('data-theme', currentTheme);
    updateThemeUI(currentTheme);

    $(document).on('click', '#themeToggle', function () {
        var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
        $('html').attr('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateThemeUI(theme);
        EIS.toast('Tema cambiado a ' + (theme === 'dark' ? 'oscuro' : 'claro'),
            'indigo', 'palette');
    });

    // Animacion de transicion
    $('main').hide().fadeIn(400);
    $('.container').hide().fadeIn(500);

    // Animacion de contadores
    function animarContadores() {
        $('.metric-value').each(function () {
            var $el = $(this);
            var text = $el.text();
            var num = parseFloat(text.replace(/[^0-9.,-]/g, '').replace(',', ''));
            if (isNaN(num)) return;
            var prefix = text.replace(num.toString().replace(',', '.'), '')
                .replace(/[0-9]/g, '').trim();
            var isCurrency = text.indexOf('$') !== -1;
            $el.text(prefix + '0');
            $({ val: 0 }).animate({ val: num }, {
                duration: 1200,
                easing: 'swing',
                step: function () {
                    var v = isCurrency
                        ? '$' + this.val.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
                        : prefix + Math.round(this.val);
                    $el.text(v);
                },
                complete: function () { $el.text(text); }
            });
        });
    }
    animarContadores();

    // Boton volver arriba
    $(window).on('scroll', function () {
        $('#backToTop').fadeIn();
        // ... fadeOut si scroll < 400
    });
    $(document).on('click', '#backToTop', function () {
        $('html, body').animate({ scrollTop: 0 }, 400);
    });
});
```

### 4.3 DataTables - Tablas con Ordenamiento, Paginacion y Busqueda

La busqueda, el filtro por estado y la paginacion de todas las tablas principales la gestiona **jQuery DataTables** (local). `app.tables.js` se mantiene como punto de extension generico sin handlers manuales (para evitar conflictos con las instancias de DataTables).

Helpers en `app.core.js`:

```javascript
// Inicializa DataTables sobre una tabla (quita filas vacias con <td colspan>)
EIS.datatable('#tabla-productos', { pageLength: 5 });

// Re-sincroniza filas tras un re-render del <tbody> por AJAX
EIS.datatableRefresh('#tabla-productos');

// Conecta un input existente a la busqueda global de DataTables (debounce 250ms)
EIS.datatableWireSearch('#tabla-productos', '#searchProducto');

// Conecta un select a un filtro de columna (ej: filtro por estado = columna 4)
EIS.datatableWireColumnFilter('#tabla-productos', '#filterEstado', 4);

// Destruye una instancia (util en tablas dinamicas cuyo numero de columnas varia, p. ej. reportes)
EIS.datatableDestroy('#tablaReporte');
```

Cada modulo inicializa su tabla con `EIS.datatable()` y conecta sus barras de busqueda/filtros a la instancia correspondiente.

### 4.4 app.ui.js - Interacciones UI

```javascript
// Notificaciones demo
$(document).on('click', '#notifBell', function () {
    var msgs = ['Stock critico: Mouse Inalambrico',
                'Sesion Cyber #2 finalizada',
                'Nueva solicitud de proveedor'];
    msgs.forEach(function (m) { EIS.toast(m, 'orange', 'notifications'); });
    $('#notifBadge').hide();
});

// Botones de accion
$(document).on('click', '[data-confirm]', function () { ... });
$(document).on('click', '.btn-download', function () { ... });

// Tooltips mejorados
$(document).on('mouseenter', '.btn-floating, .tooltip-me', function () { ... });
```

> **Nota (v4.1):** Se eliminaron los handlers demo de `#formReporte` (que hacía `preventDefault()` y
> leía `input[name="format"]`, inexistente; el real es `name="formato"`) y de `.btn-nuevo` (que
> mostraba un toast "(demo)"). El envío del reporte lo gestiona ahora `app.reportes.js`, y el botón
> "nuevo" lo gestionan los módulos reales de cada vista.

### 4.5 app.pos.js - Sistema POS

```javascript
var posCart = [];      // Array de objetos {name, price}
var posTotal = 0;

// Agregar producto al carrito
$(document).on('click', '.pos-product', function () {
    var name = $(this).data('name');
    var price = parseFloat($(this).data('price'));
    posCart.push({ name: name, price: price });
    posTotal += price;
    actualizarPosUI();
    EIS.toast(name + ' agregado al carrito', 'green', 'add_shopping_cart');
});

// Funciones de UI
function actualizarPosUI() {
    actualizarMiniTotal();
    actualizarCarritoModal();
}

function actualizarMiniTotal() {
    $('#posMiniTotal').text('$' + posTotal.toFixed(2));
    $('#cartCountBadge').text(posCart.length);
}

function actualizarCarritoModal() {
    // Renderiza cada item en #posCartItems
    // Muestra mensaje si el carrito esta vacio
}

// Eliminar item
$(document).on('click', '.cart-item-remove', function () { ... });

// Abrir modal
$(document).on('click', '#openCartBtn', function () { ... });

// Procesar venta (simulado)
$(document).on('click', '#procesarVenta', function () { ... });

// Vaciar carrito
$(document).on('click', '#vaciarCarrito', function () { ... });

// Busqueda de productos
$(document).on('input', '#posSearch', debounce(function () { ... }, 200));
```

### 4.6 app.cyber.js - Estaciones Cyber

```javascript
function actualizarCyberContadores() {
    var disp = $('.station-card.disponible').length;
    var ocup = $('.station-card.ocupada').length;
    $('#countDisponibles').text(disp);
    $('#countOcupadas').text(ocup);
}

// Toggle de estado con confirmacion
$(document).on('click', '.station-card', function () {
    var status = $(this).data('status');
    var num = $(this).find('.station-badge').text();

    if (status === 'disponible') {
        if (confirm('¿Iniciar sesion en estacion ' + num + '?')) {
            // Cambia a ocupada con animacion
            $(this).removeClass('disponible').addClass('ocupada');
            $(this).data('status', 'ocupada');
            $(this).find('.station-status')
                .css({ transform: 'scale(0.8)', opacity: 0 })
                .animate({ transform: 'scale(1)', opacity: 1 }, 300);
            actualizarCyberContadores();
        }
    } else if (status === 'ocupada') {
        // Cambia a disponible
    }
    // Mantenimiento: muestra toast informativo
});

// Filtro visual
$(document).on('click', '.filter-btn', function () {
    var filter = $(this).data('filter');
    $('.station-card').each(function () {
        var $col = $(this).closest('.col');
        if (filter === 'all') {
            $col.slideDown(200);
        } else {
            var match = $(this).data('status') === filter;
            match ? $col.slideDown(200) : $col.hide();
        }
    });
});
```

### 4.7 app.legal.js - Asesoria Legal

```javascript
var allowedDocs = [
    'consulta laboral', 'consulta civil', 'consulta familiar',
    'orientacion legal general', 'revision de contrato',
    'elaboracion de documento simple', 'asesoria prevencional'
];
var asesoriasRegistradas = [];

function normalizarDoc(texto) {
    return texto.toLowerCase().replace(/\s+/g, ' ').trim();
}

function documentoPermitido(doc) {
    return allowedDocs.indexOf(normalizarDoc(doc)) !== -1;
}

// Submit del formulario
$(document).on('submit', '#asesoriaForm', function (e) {
    e.preventDefault();
    var ciudadano = $('#ciudadano').val().trim();
    var cedula = $('#cedula').val().trim();
    var documento = $('#documento').val().trim();

    if (documentoPermitido(documento)) {
        // Registrar como permitido
    } else {
        // Derivar a oficina oficial
    }
    actualizarHistorial();
});

// Validacion en tiempo real
$(document).on('input', '#documento', function () {
    var val = $(this).val().trim();
    if (documentoPermitido(val)) {
        $('#btnRegistrar').removeClass('red').addClass('indigo')
            .html('<i class="material-icons left">verified</i>Validar y Registrar');
    } else {
        $('#btnRegistrar').removeClass('indigo').addClass('red')
            .html('<i class="material-icons left">warning</i>Derivar a Oficina Oficial');
    }
});
```

---

## 5. Funcionalidades jQuery por Modulo

| Funcionalidad | Modulo | Metodo jQuery |
|---------------|--------|---------------|
| Inicializar sidenav | Todos | `.sidenav()` |
| Inicializar selects | Donde hay selects | `.formSelect()` |
| Inicializar modals | POS, Legal | `.modal()` |
| Inicializar tooltips | Todos | `.tooltip()` |
| Tema oscuro/claro | Todos | `.attr()`, `localStorage` |
| Reloj digital | Todos | `.text()`, `setInterval` |
| Animacion metricas | Dashboard | `.animate()` |
| Busqueda tablas | Inventario, Proveedores, Activos | `.each()`, `.toggle()` |
| Filtro por estado | Inventario, Proveedores | `.show()`, `.hide()` |
| Carrito POS | Ventas | Clases, eventos, animaciones |
| Toggle estaciones | Cyber | `.addClass()`, `.removeClass()`, `.animate()` |
| Filtro estaciones | Cyber | `.slideDown()`, `.hide()` |
| Validacion legal | Asesorias | `.val()`, `.html()`, `.prop()` |
| Paginacion | Inventario, Proveedores | `.closest()`, `.find()` |
| Notificaciones | Todas | `M.toast()` |
| Volver arriba | Todas | `.animate()`, `.scrollTop()` |

---

## 6. Variables CSS para Tema Oscuro/Claro

El tema se controla mediante el atributo `data-theme` en `<html>`:

```css
:root {
    --primary: #3949ab;
    --bg: #f5f7fa;
    --surface: #ffffff;
    --text: #263238;
    --border: #cfd8dc;
}

[data-theme="dark"] {
    --primary: #7986cb;
    --bg: #121212;
    --surface: #1e1e1e;
    --text: #e0e0e0;
    --border: #424242;
}
```

22 variables CSS se sobrescriben para el tema oscuro, permitiendo a jQuery cambiar todo el tema modificando un solo atributo.

---

## 7. Dependencias

| Libreria | Version | Tipo |
|----------|---------|------|
| jQuery | 3.7.1 | Local (`Public/js/jquery-3.7.1.min.js`) |
| Materialize CSS | 1.0.0 | Local (`Public/css/materialize.min.css`) |
| Materialize JS | 1.0.0 | Local (`Public/js/materialize.min.js`) |
| Material Icons | - | Local (`Public/css/material-icons.css` + TTF) |

**No hay dependencias CDN.** Todos los recursos se sirven localmente.

---

**Documentacion**: Junio 2026
**Version**: 2.2

