import {
    W, H, C, txt, rect, line, circle, ico, chip, badge, button, iconButton,
    card, kpi, field, selectField, textarea, table, tableActionIcons,
    shell, modalFrame, svgDoc
} from './lib.mjs';

const CX = 256;
const CW = 920;

/* ============================== 01 · LOGIN ============================== */
function loginScene() {
    let b = rect(0, 0, W, H, { fill: 'url(#loginGrad)' });
    b += circle(120, 120, 210, { fill: '#5c6bc0', opacity: 0.18 });
    b += circle(1090, 660, 260, { fill: '#7c4dff', opacity: 0.16 });
    b += circle(980, 90, 120, { fill: '#3949ab', opacity: 0.22 });

    const x = 390, y = 66, w = 420, h = 628;
    b += rect(x, y, w, h, { fill: '#fff', r: 18, filter: 'modalShadow' });

    b += rect(560, 104, 80, 80, { fill: 'url(#brandGrad)', r: 22 });
    b += `<path d="M607 112l-18 26h13l-4 20 22-30h-13l4-16Z" fill="#fff"/>`;
    b += txt(600, 216, 'EIS System', { size: 25, weight: 800, anchor: 'middle' });
    b += txt(600, 240, 'Ingresa tus credenciales para continuar', { size: 12.5, color: C.muted, anchor: 'middle' });

    b += field(430, 278, 340, { label: 'Usuario', value: 'admin', icon: 'user' });
    b += field(430, 352, 340, { label: 'Contraseña', value: '••••••••', icon: 'lock', focus: true });
    b += txt(770, 420, '¿Olvidaste tu contraseña?', { size: 11, color: C.primary, anchor: 'end', weight: 600 });
    b += button(430, 440, 340, 48, 'Iniciar Sesión', { icon: 'logout', r: 8, size: 14 });

    b += line(430, 526, 548, 526, { stroke: C.border });
    b += txt(600, 531, 'O continúa con', { size: 11.5, color: C.muted, anchor: 'middle' });
    b += line(652, 526, 770, 526, { stroke: C.border });
    b += rect(456, 552, 150, 48, { fill: '#fff', stroke: C.border, r: 10 });
    b += txt(531, 581, 'G', { size: 18, weight: 800, color: '#4285F4', anchor: 'middle' });
    b += rect(594, 552, 150, 48, { fill: '#fff', stroke: C.border, r: 10 });
    b += circle(669, 576, 12, { fill: '#1B1F23' });
    b += txt(669, 581, 'gh', { size: 9, weight: 800, color: '#fff', anchor: 'middle' });

    b += txt(600, 640, '¿No tienes una cuenta?', { size: 11.5, color: C.muted, anchor: 'middle' });
    b += txt(706, 640, 'Regístrate', { size: 11.5, color: C.primary, weight: 700 });

    b += circle(1148, 40, 21, { fill: '#ffffff', opacity: 0.16 });
    b += ico('moon', 1138, 30, 20, '#fff');
    b += txt(600, 728, 'Sistema de Gestión Integral · Zona Web Lara', { size: 10, color: '#c7d2fe', anchor: 'middle' });
    return svgDoc(b);
}

/* ============================== 02 · DASHBOARD ============================== */
function dashboardScene() {
    let b = '';
    b += rect(CX, 84, CW, 76, { fill: 'url(#heroGrad)', r: 12 });
    b += txt(CX + 24, 116, '¡Bienvenido de nuevo a EIS One Manager!', { size: 19, weight: 800, color: '#fff' });
    b += txt(CX + 24, 139, 'Gestiona tu negocio de manera eficiente con EIS System', { size: 11.5, color: '#e8eaf6' });
    b += circle(1130, 122, 34, { fill: '#fff', opacity: 0.12 });
    b += ico('chart', 1115, 107, 30, '#fff');

    const kpis = [
        ['Ventas Hoy', '$1,245.00', '18 transacciones', C.primary, 'money'],
        ['Stock Crítico', '7', 'Productos bajo mínimo', C.danger, 'warning'],
        ['Sesiones Cyber', '4', 'Sesiones activas', C.warning, 'monitor'],
        ['Solicitudes Pend.', '3', 'Cuentas por pagar', C.info, 'clipboard']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 174, 221, 88, { label, value, sub, color, icon });
    });

    b += card(CX, 278, 452, 246, { title: 'Ventas por Día (7 días)', icon: 'chart' });
    b += table(CX + 18, 330, 416, {
        cols: ['FECHA', 'TRANSACCIONES', 'TOTAL'],
        widths: [1.5, 1.2, 1],
        rows: [
            ['28/10/2026', { t: '12', align: 'right', bold: true }, { t: '$845.00', align: 'right', color: C.success, bold: true }],
            ['29/10/2026', { t: '9', align: 'right', bold: true }, { t: '$612.50', align: 'right', color: C.success, bold: true }],
            ['30/10/2026', { t: '15', align: 'right', bold: true }, { t: '$1,120.00', align: 'right', color: C.success, bold: true }],
            ['31/10/2026', { t: '18', align: 'right', bold: true }, { t: '$1,245.00', align: 'right', color: C.success, bold: true }]
        ],
        rowH: 34, size: 11.5
    });

    b += card(CX + 468, 278, 452, 246, { title: 'Stock Crítico', icon: 'box' });
    b += table(CX + 486, 330, 416, {
        cols: ['PRODUCTO', 'STOCK', 'ESTADO'],
        widths: [2, 0.7, 0.9],
        rows: [
            ['Café molido 500g', { t: '5', align: 'right' }, { badge: { text: 'Bajo', bg: C.warningSoft, fg: C.warning } }],
            ['Harina de maíz 1kg', { t: '4', align: 'right' }, { badge: { text: 'Bajo', bg: C.warningSoft, fg: C.warning } }],
            ['Leche entera 1L', { t: '0', align: 'right' }, { badge: { text: 'Sin stock', bg: C.dangerSoft, fg: C.danger } }],
            ['Atún en lata 170g', { t: '3', align: 'right' }, { badge: { text: 'Bajo', bg: C.warningSoft, fg: C.warning } }]
        ],
        rowH: 34, size: 11.5
    });

    b += card(CX, 536, CW, 202, { title: 'Actividad Reciente', icon: 'history' });
    const acts = [
        ['money', C.successSoft, C.success, 'Venta registrada #V-1042', 'María Pérez · $24.50 · hace 8 minutos'],
        ['monitor', C.warningSoft, C.warning, 'Sesión de cyber iniciada', 'PC 03 · Carlos Ruiz · hace 22 minutos'],
        ['clipboard', C.infoSoft, C.info, 'Solicitud de compra actualizada', 'OC-2026-0042 · Recibida · hace 1 hora']
    ];
    acts.forEach(([icon, bg, color, title, detail], i) => {
        const y = 588 + i * 46;
        b += circle(CX + 38, y + 16, 16, { fill: bg });
        b += ico(icon, CX + 30, y + 8, 16, color);
        b += txt(CX + 64, y + 12, title, { size: 12.5, weight: 600 });
        b += txt(CX + 64, y + 29, detail, { size: 10.5, color: C.muted });
        if (i < 2) b += line(CX + 64, y + 42, CX + 880, y + 42, { stroke: C.borderLight });
    });
    return svgDoc(shell({ active: 'dashboard', title: 'Dashboard', subtitle: 'Panel de control · Resumen general', body: b }));
}

/* ============================== 03 · INVENTARIO LISTADO ============================== */
function inventarioListadoScene() {
    let b = '';
    const kpis = [
        ['Total Productos', '128', 'Registrados', C.primary, 'box'],
        ['Stock Crítico', '7', 'Bajo el mínimo', C.danger, 'warning'],
        ['Stock Bajo', '12', 'Requieren reposición', C.warning, 'chart'],
        ['Valor Total', '$8,450.00', 'Inventario valorizado', C.info, 'money']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 396, { label: 'Buscar producto', placeholder: 'Buscar por nombre o código...', icon: 'search' });
    b += selectField(CX + 428, 196, 170, { label: 'Estado', value: 'Todo' });
    b += button(CX + 614, 196, 128, 46, 'Categorías', { bg: '#757575', icon: 'tag', r: 23, size: 11.5 });
    b += button(CX + 754, 196, 148, 46, 'Nuevo Producto', { icon: 'plus', r: 23, size: 11.5 });

    b += card(CX, 260, CW, 478, {});
    b += ico('box', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Lista de Productos', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '128 productos', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['Aceite de oliva 1L', 'P-001', '$8.50', '24', { badge: { text: 'OK', bg: C.successSoft, fg: C.success } }],
        ['Café molido 500g', 'P-002', '$6.20', '5', { badge: { text: 'Crítico', bg: C.warningSoft, fg: C.warning } }],
        ['Arroz blanco 1kg', 'P-003', '$1.80', '48', { badge: { text: 'OK', bg: C.successSoft, fg: C.success } }],
        ['Leche entera 1L', 'P-004', '$2.10', '0', { badge: { text: 'Sin stock', bg: C.dangerSoft, fg: C.danger } }],
        ['Azúcar 1kg', 'P-005', '$1.50', '12', { badge: { text: 'OK', bg: C.successSoft, fg: C.success } }],
        ['Harina de maíz 1kg', 'P-006', '$1.35', '4', { badge: { text: 'Crítico', bg: C.warningSoft, fg: C.warning } }],
        ['Atún en lata 170g', 'P-007', '$2.80', '36', { badge: { text: 'OK', bg: C.successSoft, fg: C.success } }],
        ['Jabón de baño', 'P-008', '$1.20', '9', { badge: { text: 'OK', bg: C.successSoft, fg: C.success } }]
    ].map((r) => [...r, '']);
    b += table(CX + 18, 310, 884, {
        cols: ['PRODUCTO', 'ID', 'PRECIO', 'STOCK', 'ESTADO', 'ACCIÓN'],
        widths: [3.2, 1.4, 1.3, 0.9, 1.3, 1.2],
        rows, rowH: 40, size: 12
    });
    rows.forEach((_, i) => {
        const y = 310 + 34 + i * 40 + 7;
        b += tableActionIcons(1072, y, {});
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, 'Mostrando 1–8 de 128 productos', { size: 10.5, color: C.muted });
    b += button(CX + 780, 708, 56, 28, '1', { bg: C.primarySoft, fg: C.primary, r: 6, size: 10.5 });
    b += button(CX + 840, 708, 56, 28, '2', { bg: '#fff', fg: C.muted, r: 6, size: 10.5, outline: true });

    return svgDoc(shell({ active: 'inventario', title: 'Inventario', subtitle: 'Productos, stock y categorías', body: b }));
}

/* ============================== 04 · NUEVO PRODUCTO ============================== */
function inventarioNuevoScene() {
    const x = 330, y = 60, w = 540, h = 640;
    let b = '';
    b += field(354, 146, 238, { label: 'Código *', placeholder: 'Ej: P-009', icon: 'tag' });
    b += field(608, 146, 238, { label: 'Nombre del Producto *', placeholder: 'Ej: Café molido 500g', icon: 'box' });
    b += textarea(354, 222, 492, 66, { label: 'Descripción', placeholder: 'Descripción opcional del producto...', icon: 'doc' });
    b += selectField(354, 328, 238, { label: 'Categoría *', value: 'Seleccione', icon: 'tag' });
    b += field(600, 328, 115, { label: 'Stock *', value: '0' });
    b += field(723, 328, 123, { label: 'Stock Mínimo *', value: '5' });
    b += field(354, 410, 238, { label: 'Costo de Compra ($)', value: '0.00', icon: 'money' });
    b += field(608, 410, 238, { label: 'Precio de Venta ($) *', value: '0.00', icon: 'money' });
    b += rect(354, 488, 492, 62, { fill: C.infoSoft, r: 8 });
    b += ico('info', 368, 500, 16, C.info);
    b += txt(392, 512, 'El producto aparecerá en el catálogo de Ventas (POS).', { size: 11, color: C.text });
    b += txt(392, 530, 'El stock se descuenta automáticamente con cada venta.', { size: 11, color: C.muted });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });

    return svgDoc(shell({
        active: 'inventario', title: 'Inventario', subtitle: 'Productos, stock y categorías',
        body: modalFrame({ x, y, w, h, title: 'Nuevo Producto', icon: 'box', subtitle: 'Completa los datos del producto', footer })
            + b
    }));
}

/* ============================== 05 · CATEGORÍAS ============================== */
function inventarioCategoriasScene() {
    const x = 330, y = 100, w = 540, h = 560;
    let b = '';
    b += rect(354, 184, 492, 82, { fill: C.hover, r: 8 });
    b += field(370, 202, 300, { label: 'Nombre de la categoría', placeholder: 'Ej: Bebidas', icon: 'tag' });
    b += button(684, 202, 44, 44, '', { bg: C.success, icon: 'save', r: 22 });
    b += button(738, 202, 44, 44, '', { bg: '#9e9e9e', icon: 'close', r: 22 });

    const cats = ['Bebidas', 'Abarrotes', 'Limpieza', 'Lácteos', 'Snacks'];
    b += table(354, 292, 492, {
        cols: ['CATEGORÍA', 'ACCIÓN'],
        widths: [2, 1],
        rows: cats.map((c) => [c, '']),
        rowH: 36, headH: 32, size: 12
    });
    cats.forEach((_, i) => {
        b += tableActionIcons(790, 292 + 32 + i * 36 + 5, {});
    });
    b += txt(354, 522, 'Las categorías se usan para clasificar los productos del inventario.', { size: 10.5, color: C.muted });

    const footer = button(x + w - 108, y + h - 48, 84, 34, 'Cerrar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true });
    return svgDoc(shell({
        active: 'inventario', title: 'Inventario', subtitle: 'Productos, stock y categorías',
        body: modalFrame({ x, y, w, h, title: 'Gestionar Categorías', icon: 'tag', footer }) + b
    }));
}

/* ============================== 06 · POS CATÁLOGO ============================== */
const PRODUCTS = [
    ['Café Americano', 'Bebidas', '$2.50', '24 disponibles'],
    ['Hamburguesa Clásica', 'Comidas', '$5.00', '18 disponibles'],
    ['Combo Familiar', 'Comidas', '$12.00', '9 disponibles'],
    ['Agua Mineral 500ml', 'Bebidas', '$1.00', '40 disponibles'],
    ['Papas Fritas', 'Snacks', '$2.00', '15 disponibles'],
    ['Refresco 350ml', 'Bebidas', '$1.50', '32 disponibles'],
    ['Postre del Día', 'Postres', '$3.00', '7 disponibles'],
    ['Pizza Personal', 'Comidas', '$6.50', '11 disponibles']
];

function productCard(x, y, w, h, p, highlight = false) {
    let s = rect(x, y, w, h, { fill: '#fff', stroke: highlight ? C.primary : C.border, sw: highlight ? 1.8 : 1, r: 10, filter: 'cardShadow' });
    s += circle(x + 32, y + 30, 16, { fill: C.primarySoft });
    s += ico('box', x + 24, y + 22, 16, C.primary);
    s += chip(x + 16, y + 56, p[1], { bg: C.hover, fg: C.muted, size: 9.5, h: 18 });
    s += txt(x + 16, y + 94, p[0], { size: 12.5, weight: 700 });
    s += txt(x + 16, y + 120, p[2], { size: 17, weight: 800, color: C.primary });
    s += txt(x + 16, y + 141, p[3], { size: 10, color: C.muted });
    s += circle(x + w - 26, y + h - 26, 15, { fill: highlight ? C.primary : C.primarySoft });
    s += ico('plus', x + w - 33, y + h - 33, 14, highlight ? '#fff' : C.primary);
    return s;
}

function ventasCatalogoScene() {
    let b = '';
    b += card(CX, 84, 600, 72, {});
    b += ico('cart', CX + 20, 102, 22, C.primary);
    b += txt(CX + 52, 116, 'Punto de Venta', { size: 18, weight: 800 });
    b += txt(CX + 52, 136, 'Selecciona los productos y procesa la venta', { size: 11, color: C.muted });

    b += card(CX + 612, 84, 308, 72, {});
    b += txt(CX + 630, 108, 'TOTAL CARRITO', { size: 9.5, color: C.muted, weight: 700, letter: 0.6 });
    b += txt(CX + 630, 136, '$24.50', { size: 22, weight: 800, color: C.primary });
    b += button(CX + 760, 100, 142, 42, 'Carrito', { icon: 'cart', r: 21, size: 12 });
    b += circle(CX + 890, 112, 9, { fill: '#fff' });
    b += txt(CX + 890, 116, '3', { size: 10, weight: 800, color: C.primary, anchor: 'middle' });

    b += card(CX, 168, CW, 570, {});
    b += field(CX + 18, 198, 500, { label: 'Buscar producto', placeholder: 'Buscar producto por nombre...', icon: 'search' });
    b += txt(CX + 902, 218, '8 productos disponibles', { size: 10.5, color: C.muted, anchor: 'end' });

    PRODUCTS.forEach((p, i) => {
        const col = i % 4, row = Math.floor(i / 4);
        b += productCard(CX + 18 + col * 224, 260 + row * 182, 210, 164, p, i === 1);
    });
    b += rect(CX + 18, 626, 884, 50, { fill: C.infoSoft, r: 8 });
    b += ico('info', CX + 32, 642, 16, C.info);
    b += txt(CX + 56, 656, 'Haz clic sobre la tarjeta de un producto para agregarlo al carrito. Puedes repetir el clic para aumentar la cantidad.', { size: 11, color: C.text });
    b += txt(CX + 18, 708, 'Los productos con stock 0 no se muestran en el catálogo.', { size: 10.5, color: C.muted });

    return svgDoc(shell({ active: 'ventas', title: 'Ventas (POS)', subtitle: 'Punto de venta · Catálogo de productos', body: b }));
}

/* ============================== 07 · CARRITO ============================== */
function cartModal(dim = true) {
    const x = 290, y = 35, w = 620, h = 690;
    let b = '';
    const items = [
        ['Hamburguesa Clásica', 'Cantidad: 2 · $5.00 c/u', '$10.00'],
        ['Café Americano', 'Cantidad: 2 · $2.50 c/u', '$5.00'],
        ['Postre del Día', 'Cantidad: 3 · $3.00 c/u', '$9.00']
    ];
    items.forEach(([name, detail, total], i) => {
        const yy = 104 + i * 62;
        b += circle(336, yy + 20, 17, { fill: C.primarySoft });
        b += ico('box', 328, yy + 12, 16, C.primary);
        b += txt(366, yy + 16, name, { size: 12.5, weight: 700 });
        b += txt(366, yy + 34, detail, { size: 10.5, color: C.muted });
        b += circle(744, yy + 22, 11, { fill: C.primarySoft });
        b += ico('minus', 738, yy + 16, 12, C.primary);
        b += txt(766, yy + 27, String(i === 0 ? 2 : i === 1 ? 2 : 3), { size: 12, weight: 700, anchor: 'middle' });
        b += circle(788, yy + 22, 11, { fill: C.primarySoft });
        b += ico('plus', 782, yy + 16, 12, C.primary);
        b += txt(876, yy + 27, total, { size: 13, weight: 800, color: C.primary, anchor: 'end' });
        b += ico('close', 846, yy + 15, 13, C.danger);
        if (i < 2) b += line(314, yy + 48, 886, yy + 48, { stroke: C.borderLight });
    });

    b += line(314, 296, 886, 296, { stroke: C.border });
    b += ico('user', 314, 316, 16, C.primary);
    b += txt(338, 330, 'Datos del Cliente', { size: 13, weight: 700 });
    b += selectField(314, 350, 572, { label: 'Cliente registrado', value: 'María Pérez — V-12345678', icon: 'users' });
    b += field(314, 414, 278, { label: 'Cliente *', value: 'María Pérez', icon: 'user' });
    b += field(608, 414, 278, { label: 'Cédula *', value: 'V-12345678', icon: 'tag' });
    b += field(314, 486, 278, { label: 'Dirección', value: 'Av. Principal, Casa 12', icon: 'store' });
    b += field(608, 486, 278, { label: 'Teléfono', value: '0414-1234567', icon: 'phone' });

    b += txt(314, 684, 'TOTAL', { size: 10, color: C.muted, weight: 700, letter: 0.6 });
    b += txt(314, 714, '$24.50', { size: 26, weight: 800, color: C.primary });
    b += button(688, 688, 86, 34, 'Vaciar', { bg: C.danger, icon: 'trash', r: 18, size: 11 });
    b += button(786, 688, 100, 34, 'Procesar', { bg: C.success, icon: 'money', r: 18, size: 11 });

    return modalFrame({
        x, y, w, h, title: 'Carrito de Compras', icon: 'cart', subtitle: '3 productos en el carrito',
        children: b,
        footer: ''
    });
}

function ventasCarritoScene() {
    return svgDoc(shell({
        active: 'ventas', title: 'Ventas (POS)', subtitle: 'Punto de venta · Carrito de compras',
        body: cartModal()
    }));
}

/* ============================== 08 · CONFIRMACIÓN ============================== */
function ventasConfirmacionScene() {
    let b = cartModal();
    b += rect(0, 0, W, H, { fill: C.dark, opacity: 0.38 });
    b += rect(390, 268, 420, 208, { fill: '#fff', r: 14, filter: 'modalShadow' });
    b += circle(600, 308, 24, { fill: C.warningSoft });
    b += ico('money', 588, 296, 24, C.warning);
    b += txt(600, 352, '¿Procesar venta por $24.50?', { size: 16, weight: 800, anchor: 'middle' });
    b += txt(600, 376, 'Se descontará el stock y se generará el comprobante.', { size: 11, color: C.muted, anchor: 'middle' });
    b += txt(600, 394, 'Esta acción no se puede deshacer.', { size: 10.5, color: C.danger, anchor: 'middle', weight: 600 });
    b += button(456, 420, 120, 36, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true });
    b += button(588, 420, 156, 36, 'Aceptar y procesar', { bg: C.success, r: 18, size: 11 });
    return svgDoc(shell({
        active: 'ventas', title: 'Ventas (POS)', subtitle: 'Punto de venta · Confirmación',
        body: b
    }));
}

/* ============================== 09 · CLIENTES ============================== */
function clientesListadoScene() {
    let b = '';
    b += kpi(CX, 84, 221, 84, { label: 'Total Clientes', value: '342', sub: 'Registrados', color: C.primary, icon: 'users' });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 480, { label: 'Buscar cliente', placeholder: 'Buscar por nombre, cédula o teléfono...', icon: 'search' });
    b += button(CX + 770, 196, 132, 46, 'Nuevo Cliente', { icon: 'plus', r: 23, size: 11.5 });

    b += card(CX, 260, CW, 478, {});
    b += ico('users', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Clientes', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '342 resultados', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['V-12345678', 'María Pérez', 'Av. Principal, Casa 12', '0414-1234567'],
        ['V-18765432', 'Carlos Ruiz', 'Calle 5, Edif. Sol, Apto 3B', '0424-7654321'],
        ['V-20123456', 'Ana Gómez', 'Urb. Los Olivos, Casa 8', '0412-2345678'],
        ['V-15987654', 'Luis Martínez', 'Av. Bolívar, Local 4', '0416-9876543'],
        ['V-22456789', 'Sofía Hernández', 'Calle Miranda, Qta. 21', '0426-4567890'],
        ['V-19876543', 'Pedro Sánchez', 'Av. Libertador, Torre Norte', '0414-8765432'],
        ['V-17543210', 'Laura Díaz', 'Calle Sucre, Casa 45', '0424-5432109'],
        ['V-23987654', 'José Ramírez', 'Urb. El Paraíso, Casa 3', '0412-9876543'],
        ['V-14678901', 'Carmen Torres', 'Av. Universidad, Local 7', '0416-6789012']
    ].map((r) => [...r, '']);
    b += table(CX + 18, 310, 884, {
        cols: ['CÉDULA', 'CLIENTE', 'DIRECCIÓN', 'TELÉFONO', 'ACCIÓN'],
        widths: [1.2, 1.7, 2.5, 1.3, 1],
        rows, rowH: 40, size: 11.5
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(1090, 310 + 34 + i * 40 + 7, {});
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, '342 resultados · Página 1 de 38', { size: 10.5, color: C.muted });
    b += button(CX + 830, 708, 70, 28, 'Siguiente', { bg: '#fff', fg: C.primary, r: 6, size: 10, outline: true });
    return svgDoc(shell({ active: 'clientes', title: 'Clientes', subtitle: 'Registro y gestión de clientes', body: b }));
}

/* ============================== 10 · NUEVO CLIENTE ============================== */
function clientesNuevoScene() {
    const x = 340, y = 100, w = 520, h = 560;
    let b = '';
    b += field(364, 160, 472, { label: 'Cédula *', placeholder: 'Ej: V-12345678', icon: 'tag', focus: true });
    b += field(364, 234, 228, { label: 'Nombre *', placeholder: 'Ej: María', icon: 'user' });
    b += field(608, 234, 228, { label: 'Apellido *', placeholder: 'Ej: Pérez' });
    b += field(364, 308, 472, { label: 'Dirección', placeholder: 'Dirección del cliente (opcional)', icon: 'store' });
    b += field(364, 382, 472, { label: 'Teléfono', placeholder: 'Ej: 0414-1234567', icon: 'phone' });
    b += rect(364, 454, 472, 52, { fill: C.infoSoft, r: 8 });
    b += ico('info', 378, 468, 15, C.info);
    b += txt(400, 480, 'La cédula identifica al cliente en Ventas, Cyber y Asesoría Legal.', { size: 10.5, color: C.text });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });
    return svgDoc(shell({
        active: 'clientes', title: 'Clientes', subtitle: 'Registro y gestión de clientes',
        body: modalFrame({ x, y, w, h, title: 'Nuevo Cliente', icon: 'user', subtitle: 'Completa los datos del cliente', footer }) + b
    }));
}

export const scenes1 = [
    { name: '01-login', svg: loginScene() },
    { name: '02-dashboard', svg: dashboardScene() },
    { name: '03-inventario-listado', svg: inventarioListadoScene() },
    { name: '04-inventario-nuevo-producto', svg: inventarioNuevoScene() },
    { name: '05-inventario-categorias', svg: inventarioCategoriasScene() },
    { name: '06-ventas-catalogo', svg: ventasCatalogoScene() },
    { name: '07-ventas-carrito', svg: ventasCarritoScene() },
    { name: '08-ventas-confirmacion', svg: ventasConfirmacionScene() },
    { name: '09-clientes-listado', svg: clientesListadoScene() },
    { name: '10-clientes-nuevo', svg: clientesNuevoScene() }
];
