# Documentacion de Integracion de jQuery en EIS System

## 1. Descripcion General

Se integro **jQuery 3.7.1** y **Materialize CSS 1.0.0** en la aplicacion EIS System (PHP vanilla) para estandarizar y modernizar la interfaz de usuario.

La integracion incluyo:
- Adicion de jQuery v3.7.1 (local) y Materialize CSS v1.0.0 (local)
- Creacion de un layout maestro (`layout.php`) que centraliza el HTML/JS comun
- Migracion de JS vanilla a jQuery para theme toggle, sidebar, busquedas, filtros
- Implementacion de componentes Materialize: sidenav, modals, selects, tooltips, tabs
- Refactorizacion de vistas autenticadas para usar solo contenido (sin HTML repetido)
- Creacion de JavaScript modular en 7 archivos especializados
- Validacion de documentos en modulo de Asesoria Legal

---

## 2. Archivos del Proyecto

### JavaScript (10 modulos)

| Archivo | Proposito | Carga |
|---------|-----------|-------|
| `Public/js/app.core.js` | Funciones compartidas: namespace EIS, debounce, filtrarTabla, EIS.toast | Siempre |
| `Public/js/app.init.js` | Inicializacion Materialize, reloj, tema oscuro/claro, animaciones | Siempre |
| `Public/js/app.tables.js` | Busqueda en tablas con debounce, filtro por estado, paginacion | Siempre |
| `Public/js/app.ui.js` | Notificaciones, botones de accion, reportes, tooltips | Siempre |
| `Public/js/app.pos.js` | Sistema de carrito POS | Solo en pagina ventas |
| `Public/js/app.cyber.js` | Gestion de estaciones Cyber | Solo en pagina ciberControl |
| `Public/js/app.legal.js` | Validacion de documentos de asesoria legal | Solo en pagina asesorias |
| `Public/js/app.inventario.js` | CRUD de inventario via AJAX | Solo en pagina inventario |
| `Public/js/app.roles.js` | CRUD de roles y permisos via AJAX | Solo en pagina roles |
| `Public/js/app.proveedores.js` | CRUD de proveedores y ordenes via AJAX | Solo en pagina proveedores |

### Librerias (locales)

| Archivo | Version |
|---------|---------|
| `Public/js/jquery-3.7.1.min.js` | jQuery 3.7.1 |
| `Public/js/materialize.min.js` | Materialize JS 1.0.0 |
| `Public/css/materialize.min.css` | Materialize CSS 1.0.0 |
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
    app.core.js       (EIS, debounce, toast)
    app.init.js       (Materialize init, reloj, tema, animaciones)
    app.tables.js     (Busqueda y filtros de tablas)
    app.ui.js         (Notificaciones, botones, reportes)

    <!-- Condicional por pagina -->
    app.pos.js         (solo en ventas)
    app.cyber.js       (solo en ciberControl)
    app.legal.js       (solo en asesorias)
    app.inventario.js  (solo en inventario)
    app.roles.js       (solo en roles)
    app.proveedores.js (solo en proveedores)

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

function filtrarTabla(inputSelector, tableSelector, colIndex) {
    var q = $(inputSelector).val().toLowerCase();
    $(tableSelector + ' tbody tr').each(function () {
        var $row = $(this);
        var text = colIndex !== undefined
            ? $row.find('td').eq(colIndex).text().toLowerCase()
            : $row.text().toLowerCase();
        $row.toggle(text.indexOf(q) !== -1);
    });
    var visibles = $(tableSelector + ' tbody tr:visible').length;
    var total = $(tableSelector + ' tbody tr').length;
    $(tableSelector).closest('.card').find('.result-count')
        .text('Mostrando ' + visibles + ' de ' + total + ' resultados');
}

EIS.toast = function (msg, color, icon) {
    color = color || 'indigo';
    icon = icon || 'check_circle';
    var html = '<i class="material-icons left" style="font-size:1.2rem;">'
        + icon + '</i>' + msg;
    M.toast({ html: html, classes: color + ' rounded', displayLength: 3000 });
};
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

### 4.3 app.tables.js - Busqueda en Tablas

Usa event delegation para buscar y filtrar tablas:

```javascript
// Busqueda en inventario (columna 1)
$(document).on('input', '#searchProducto', debounce(function () {
    filtrarTabla('#searchProducto', '.responsive-table', 1);
}, 300));

// Busqueda en proveedores (columna 1)
$(document).on('input', '#searchProveedor', debounce(function () {
    filtrarTabla('#searchProveedor', '.responsive-table', 1);
}, 300));

// Busqueda en activos (columna 0)
$(document).on('input', '#searchActivo', debounce(function () {
    filtrarTabla('#searchActivo', '.striped', 0);
}, 300));

// Filtro por estado (select)
$(document).on('change', '#filterEstado, #filterEstadoProv', function () { ... });

// Paginacion
$(document).on('click', '.pagination li:not(.disabled):not(.active) a', function () { ... });
```

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

// Generador de reportes simulado
$(document).on('submit', '#formReporte', function (e) {
    e.preventDefault();
    EIS.toast('Generando reporte...', 'indigo', 'download');
});

// Botones de accion
$(document).on('click', '[data-confirm]', function () { ... });
$(document).on('click', '.btn-nuevo', function () { ... });
$(document).on('click', '.btn-download', function () { ... });

// Tooltips mejorados
$(document).on('mouseenter', '.btn-floating, .tooltip-me', function () { ... });
```

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

