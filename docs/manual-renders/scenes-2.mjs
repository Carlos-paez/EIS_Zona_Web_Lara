import {
    W, H, C, txt, rect, line, circle, ico, chip, badge, button, iconButton,
    card, kpi, field, selectField, textarea, table, tableActionIcons,
    shell, modalFrame, svgDoc
} from './lib.mjs';

const CX = 256;
const CW = 920;

/* ============================== 11 · ÓRDENES DE COMPRA ============================== */
function ordenesListadoScene() {
    let b = '';
    const kpis = [
        ['Total Solicitudes', '42', 'Registradas', C.primary, 'clipboard'],
        ['Pendientes', '8', 'Por revisar', C.warning, 'clock'],
        ['Recibidas', '34', 'Completadas', C.success, 'check']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 309, 84, 297, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 420, { label: 'Buscar solicitud', placeholder: 'Buscar por proveedor o ID...', icon: 'search' });
    b += selectField(CX + 452, 196, 210, { label: 'Filtrar por estado', value: 'Todos los estados' });
    b += button(CX + 750, 196, 152, 46, 'Nueva Solicitud', { icon: 'plus', r: 23, size: 11.5 });

    b += card(CX, 260, CW, 478, {});
    b += ico('clipboard', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Solicitudes de Compra', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '42 resultados', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['OC-2026-0042', 'Distribuidora La Estrella, C.A.', 'J-30123456-7', '30/10/2026', { badge: { text: 'Recibida', bg: C.successSoft, fg: C.success } }],
        ['OC-2026-0041', 'Alimentos del Centro, S.A.', 'J-29876543-1', '29/10/2026', { badge: { text: 'Pendiente', bg: C.warningSoft, fg: C.warning } }],
        ['OC-2026-0040', 'Comercial Andina, C.A.', 'J-31456789-0', '28/10/2026', { badge: { text: 'Recibida', bg: C.successSoft, fg: C.success } }],
        ['OC-2026-0039', 'Suministros del Norte, C.A.', 'J-28765432-5', '27/10/2026', { badge: { text: 'Pendiente', bg: C.warningSoft, fg: C.warning } }],
        ['OC-2026-0038', 'Distribuidora La Estrella, C.A.', 'J-30123456-7', '26/10/2026', { badge: { text: 'Recibida', bg: C.successSoft, fg: C.success } }],
        ['OC-2026-0037', 'Inversiones Lara, C.A.', 'J-32567890-4', '25/10/2026', { badge: { text: 'Cancelada', bg: C.dangerSoft, fg: C.danger } }],
        ['OC-2026-0036', 'Alimentos del Centro, S.A.', 'J-29876543-1', '24/10/2026', { badge: { text: 'Recibida', bg: C.successSoft, fg: C.success } }],
        ['OC-2026-0035', 'Comercial Andina, C.A.', 'J-31456789-0', '23/10/2026', { badge: { text: 'Pendiente', bg: C.warningSoft, fg: C.warning } }]
    ].map((r) => [...r, '']);
    b += table(CX + 18, 310, 884, {
        cols: ['SOLICITUD', 'PROVEEDOR', 'RIF', 'FECHA', 'ESTADO', 'ACCIÓN'],
        widths: [1.5, 2.2, 1.3, 1.1, 1.2, 0.9],
        rows, rowH: 40, size: 11.5
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(1094, 310 + 34 + i * 40 + 7, {});
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, '42 resultados · Página 1 de 5', { size: 10.5, color: C.muted });
    return svgDoc(shell({ active: 'proveedores', title: 'Órdenes de Compra', subtitle: 'Solicitudes de compra a proveedores', body: b }));
}

/* ============================== 12 · NUEVA SOLICITUD ============================== */
function ordenesNuevaScene() {
    const x = 300, y = 110, w = 600, h = 520;
    let b = '';
    b += field(324, 172, 270, { label: 'Número de Orden', value: 'OC-2026-0043', icon: 'clipboard' });
    b += field(610, 172, 266, { label: 'Fecha *', value: '31/10/2026', icon: 'calendar' });
    b += selectField(324, 248, 270, { label: 'Proveedor *', value: 'Distribuidora La Estrella, C.A. (J-30123456-7)', icon: 'store' });
    b += selectField(610, 248, 266, { label: 'Estado *', value: 'Pendiente' });
    b += rect(324, 322, 552, 78, { fill: C.infoSoft, r: 8 });
    b += ico('info', 338, 338, 18, C.info);
    b += txt(366, 352, 'El número de orden se genera automáticamente.', { size: 11.5, color: C.text, weight: 600 });
    b += txt(366, 372, 'Al guardar la solicitud se habilitará la sección para agregar los productos (líneas).', { size: 10.5, color: C.muted });
    b += txt(366, 389, 'Si aún no tienes proveedores, créalos primero en Solicitudes → Proveedores.', { size: 10.5, color: C.muted });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });
    return svgDoc(shell({
        active: 'proveedores', title: 'Órdenes de Compra', subtitle: 'Solicitudes de compra a proveedores',
        body: modalFrame({ x, y, w, h, title: 'Nueva Solicitud', icon: 'clipboard', subtitle: 'Datos generales de la orden', footer }) + b
    }));
}

/* ============================== 13 · DETALLE / LÍNEAS ============================== */
function ordenesDetalleScene() {
    const x = 220, y = 50, w = 760, h = 660;
    let b = '';
    b += field(244, 130, 220, { label: 'Número de Orden', value: 'OC-2026-0042', icon: 'clipboard' });
    b += field(480, 130, 220, { label: 'Fecha', value: '30/10/2026', icon: 'calendar' });
    b += selectField(716, 130, 240, { label: 'Proveedor', value: 'Distribuidora La Estrella' });
    b += selectField(244, 206, 220, { label: 'Estado', value: 'Recibida' });

    b += ico('box', 244, 264, 17, C.primary);
    b += txt(268, 278, 'Productos de la solicitud', { size: 13.5, weight: 700 });
    b += txt(956, 278, '3 líneas', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['Aceite de oliva 1L (P-001)', '24', '$6.00', '$144.00'],
        ['Café molido 500g (P-002)', '12', '$4.50', '$54.00'],
        ['Arroz blanco 1kg (P-003)', '40', '$1.20', '$48.00']
    ].map((r) => [...r, '']);
    b += table(244, 292, 712, {
        cols: ['PRODUCTO', 'CANTIDAD', 'PRECIO', 'SUBTOTAL', 'ACCIÓN'],
        widths: [3, 0.9, 1, 1.1, 0.8],
        rows, rowH: 36, headH: 32, size: 11.5
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(884, 292 + 32 + i * 36 + 5, { edit: false });
    });
    b += txt(800, 432, 'Total:', { size: 12, color: C.muted, anchor: 'end' });
    b += txt(956, 432, '$246.00', { size: 15, weight: 800, color: C.primary, anchor: 'end' });

    b += rect(244, 454, 712, 112, { fill: C.hover, r: 8 });
    b += ico('plus', 258, 468, 16, C.primary);
    b += txt(282, 481, 'Agregar Producto', { size: 12, weight: 700 });
    b += selectField(258, 500, 300, { label: '', value: 'Café molido 500g (P-002)' });
    b += field(570, 500, 80, { label: '', value: '12' });
    b += field(662, 500, 110, { label: '', value: '$4.50' });
    b += button(784, 500, 44, 46, '', { bg: C.success, icon: 'plus', r: 23 });
    b += txt(258, 552, 'El precio se carga desde el costo de compra del producto y puede ajustarse.', { size: 9.5, color: C.muted });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });
    return svgDoc(shell({
        active: 'proveedores', title: 'Órdenes de Compra', subtitle: 'Detalle de la solicitud y líneas de producto',
        body: modalFrame({ x, y, w, h, title: 'Solicitud OC-2026-0042', icon: 'clipboard', subtitle: 'Edita la cabecera y los productos', footer }) + b
    }));
}

/* ============================== 14 · PROVEEDORES LISTADO ============================== */
function proveedoresListadoScene() {
    let b = '';
    b += kpi(CX, 84, 221, 84, { label: 'Total Proveedores', value: '26', sub: 'Registrados', color: C.primary, icon: 'store' });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 480, { label: 'Buscar proveedor', placeholder: 'Buscar por nombre, RIF o email...', icon: 'search' });
    b += button(CX + 768, 196, 134, 46, 'Nuevo Proveedor', { icon: 'plus', r: 23, size: 11.5 });

    b += card(CX, 260, CW, 478, {});
    b += ico('store', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Proveedores', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '26 resultados', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['Distribuidora La Estrella, C.A.', 'J-30123456-7', 'ventas@laestrella.com', '0212-5551234'],
        ['Alimentos del Centro, S.A.', 'J-29876543-1', 'contacto@alimentoscentro.com', '0241-5559876'],
        ['Comercial Andina, C.A.', 'J-31456789-0', 'compras@andina.com.ve', '0212-5554567'],
        ['Suministros del Norte, C.A.', 'J-28765432-5', 'info@suministrosnorte.com', '0251-5553210'],
        ['Inversiones Lara, C.A.', 'J-32567890-4', 'admin@inversioneslara.com', '0251-5557890'],
        ['Distribuidora Central, C.A.', 'J-30987654-2', 'pedidos@distcentral.com', '0241-5556543'],
        ['Comercial El Progreso, C.A.', 'J-27654321-8', 'ventas@elprogreso.com', '0261-5552345'],
        ['Alimentos del Sur, C.A.', 'J-33678901-3', 'contacto@alimentossur.com', '0271-5558765'],
        ['Servicios Integrales, C.A.', 'J-29543210-6', 'info@serviciosintegrales.com', '0212-5553456']
    ].map((r) => [...r, '']);
    b += table(CX + 18, 310, 884, {
        cols: ['PROVEEDOR', 'RIF', 'EMAIL', 'TELÉFONO', 'ACCIÓN'],
        widths: [2.2, 1.2, 2.3, 1.3, 0.9],
        rows, rowH: 40, size: 11
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(1090, 310 + 34 + i * 40 + 7, {});
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, '26 resultados · Página 1 de 3', { size: 10.5, color: C.muted });
    return svgDoc(shell({ active: 'proveedores-gestion', title: 'Proveedores', subtitle: 'Catálogo de proveedores del negocio', body: b }));
}

/* ============================== 15 · NUEVO PROVEEDOR ============================== */
function proveedoresNuevoScene() {
    const x = 350, y = 120, w = 500, h = 520;
    let b = '';
    b += field(374, 182, 452, { label: 'RIF *', placeholder: 'Ej: J-30123456-7', icon: 'tag', focus: true });
    b += field(374, 256, 452, { label: 'Nombre *', placeholder: 'Razón social del proveedor', icon: 'store' });
    b += field(374, 330, 452, { label: 'Email', placeholder: 'correo@proveedor.com', icon: 'mail' });
    b += field(374, 404, 452, { label: 'Teléfono', placeholder: 'Ej: 0212-5551234', icon: 'phone' });
    b += rect(374, 476, 452, 46, { fill: C.infoSoft, r: 8 });
    b += ico('info', 388, 490, 15, C.info);
    b += txt(412, 503, 'No podrás eliminar un proveedor con solicitudes asociadas.', { size: 10.5, color: C.text });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });
    return svgDoc(shell({
        active: 'proveedores-gestion', title: 'Proveedores', subtitle: 'Catálogo de proveedores del negocio',
        body: modalFrame({ x, y, w, h, title: 'Nuevo Proveedor', icon: 'store', subtitle: 'Datos fiscales y de contacto', footer }) + b
    }));
}

/* ============================== 16 · CYBER ESTACIONES ============================== */
function stationCard(x, y, w, h, o) {
    const { name, status, client = '', time = '', price = '', type = 'PC' } = o;
    const occupied = status === 'Ocupada';
    let s = rect(x, y, w, h, { fill: '#fff', stroke: occupied ? '#ffe0b2' : C.border, r: 10, filter: 'cardShadow' });
    s += circle(x + 28, y + 28, 17, { fill: occupied ? C.warningSoft : C.successSoft });
    s += ico('monitor', x + 19, y + 19, 18, occupied ? C.warning : C.success);
    s += txt(x + 52, y + 26, name, { size: 12.5, weight: 700 });
    s += txt(x + 52, y + 42, type, { size: 9.5, color: C.muted });
    s += badge(x + w - 74, y + 14, status, { bg: occupied ? C.warningSoft : C.successSoft, fg: occupied ? C.warning : C.success });
    s += iconButton(x + w - 62, y + 44, 'edit', { bg: C.infoSoft, color: C.info, size: 24, iconSize: 12 });
    s += iconButton(x + w - 34, y + 44, 'trash', { bg: C.dangerSoft, color: C.danger, size: 24, iconSize: 12 });

    if (occupied) {
        s += line(x + 14, y + 70, x + w - 14, y + 70, { stroke: C.borderLight });
        s += txt(x + 16, y + 90, client, { size: 11, weight: 600 });
        s += txt(x + 16, y + 108, `Tiempo: ${time}`, { size: 10, color: C.muted });
        s += txt(x + 16, y + 126, `Tarifa: ${price}`, { size: 10, color: C.muted });
        s += txt(x + w - 16, y + 132, 'Clic para finalizar', { size: 9, color: C.warning, anchor: 'end', weight: 600 });
    } else {
        s += line(x + 14, y + 70, x + w - 14, y + 70, { stroke: C.borderLight });
        s += txt(x + 16, y + 96, 'Estación libre', { size: 11, color: C.muted });
        s += txt(x + 16, y + 116, 'Clic para iniciar sesión', { size: 10, color: C.success, weight: 600 });
    }
    return s;
}

function cyberEstacionesScene() {
    let b = '';
    const kpis = [
        ['Disponibles', '5', 'Listas para usar', C.success, 'check'],
        ['Ocupadas', '3', 'En uso ahora', C.warning, 'clock'],
        ['Mantenimiento', '1', 'Fuera de servicio', C.danger, 'gear'],
        ['Total PCs', '9', 'Estaciones registradas', C.info, 'monitor']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, CW, 64, {});
    b += txt(CX + 18, 218, 'FILTRAR:', { size: 9.5, color: C.muted, weight: 700 });
    b += chip(CX + 78, 200, 'Todas', { bg: C.success, fg: '#fff', size: 10, h: 24, w: 60 });
    b += chip(CX + 144, 200, 'Disponibles', { bg: C.successSoft, fg: C.success, size: 10, h: 24, w: 82 });
    b += chip(CX + 232, 200, 'Ocupadas', { bg: C.warningSoft, fg: C.warning, size: 10, h: 24, w: 74 });
    b += button(CX + 470, 195, 92, 34, 'Nueva PC', { bg: C.primary, icon: 'plus', r: 17, size: 10 });
    b += button(CX + 568, 195, 108, 34, 'Nueva Sesión', { icon: 'play', r: 17, size: 10 });
    b += button(CX + 682, 195, 92, 34, 'Historial', { bg: '#616161', icon: 'history', r: 17, size: 10 });
    b += button(CX + 780, 195, 100, 34, 'Actualizar', { bg: C.success, icon: 'refresh', r: 17, size: 10 });

    b += ico('monitor', CX, 270, 15, C.muted);
    b += txt(CX + 22, 282, 'Computadoras de Escritorio', { size: 12.5, weight: 700 });
    const row1 = [
        { name: 'PC 01', status: 'Disponible' },
        { name: 'PC 02', status: 'Ocupada', client: 'Carlos Ruiz', time: '01:12:40', price: '$2.50' },
        { name: 'PC 03', status: 'Disponible' },
        { name: 'PC 04', status: 'Ocupada', client: 'Ana Gómez', time: '00:45:10', price: '$1.80' }
    ];
    row1.forEach((s, i) => { b += stationCard(CX + i * 233, 292, 221, 150, { ...s, type: 'Escritorio' }); });

    b += ico('device', CX, 466, 15, C.muted);
    b += txt(CX + 22, 478, 'Laptops', { size: 12.5, weight: 700 });
    const row2 = [
        { name: 'Laptop 01', status: 'Disponible' },
        { name: 'Laptop 02', status: 'Ocupada', client: 'Luis Martínez', time: '00:28:05', price: '$2.00' },
        { name: 'Laptop 03', status: 'Disponible' },
        { name: 'Laptop 04', status: 'Disponible' }
    ];
    row2.forEach((s, i) => { b += stationCard(CX + i * 233, 488, 221, 150, { ...s, type: 'Laptop' }); });

    b += rect(CX, 660, CW, 66, { fill: C.infoSoft, r: 8 });
    b += ico('info', CX + 18, 678, 16, C.info);
    b += txt(CX + 42, 690, 'Haz clic en una estación disponible para abrir "Iniciar Sesión".', { size: 11, weight: 600 });
    b += txt(CX + 42, 708, 'Si está ocupada, el sistema pedirá confirmación para finalizar la sesión y calcular el cobro.', { size: 10.5, color: C.muted });

    return svgDoc(shell({ active: 'ciberControl', title: 'Cyber', subtitle: 'Control de estaciones y sesiones', body: b }));
}

/* ============================== 17 · INICIAR SESIÓN CYBER ============================== */
function cyberSesionScene() {
    const x = 360, y = 80, w = 480, h = 600;
    let b = '';
    b += field(384, 146, 432, { label: 'Cliente *', value: 'Carlos Ruiz', icon: 'user' });
    b += field(384, 212, 432, { label: 'Cédula *', value: 'V-18765432', icon: 'tag', focus: true, help: 'Al salir del campo se precargan los datos del cliente' });
    b += field(384, 290, 432, { label: 'Teléfono', value: '0424-7654321', icon: 'phone' });
    b += field(384, 356, 432, { label: 'Dirección', value: 'Calle 5, Edif. Sol, Apto 3B', icon: 'store' });
    b += selectField(384, 422, 432, { label: 'Estación *', value: 'PC 02 — Escritorio', icon: 'monitor' });
    b += selectField(384, 488, 432, { label: 'Tarifa *', value: '$2.50/hora — $2.50 (tiempo)', icon: 'money' });
    b += field(384, 554, 432, { label: 'Tiempo (HH:MM:SS)', value: '01:00:00', icon: 'clock' });
    b += txt(384, 620, 'Ej: 01:30:00 = 1 hora 30 minutos', { size: 10, color: C.muted });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Iniciar', { bg: C.success, icon: 'play', r: 18, size: 11.5 });
    return svgDoc(shell({
        active: 'ciberControl', title: 'Cyber', subtitle: 'Control de estaciones y sesiones',
        body: modalFrame({ x, y, w, h, title: 'Iniciar Sesión', icon: 'play', subtitle: 'PC 02 · Estación disponible', footer }) + b
    }));
}

/* ============================== 18 · HISTORIAL CYBER ============================== */
function cyberHistorialScene() {
    const x = 200, y = 70, w = 800, h = 620;
    let b = '';
    b += txt(224, 158, 'Selecciona una PC para ver su historial de sesiones', { size: 10.5, color: C.muted });
    const stations = [['PC 02', true], ['PC 04', false], ['Laptop 02', false], ['PC 01', false]];
    let sx = 224;
    stations.forEach(([name, active]) => {
        b += chip(sx, 176, name, { bg: active ? C.primary : C.hover, fg: active ? '#fff' : C.muted, size: 10.5, h: 26, w: 88 });
        sx += 96;
    });

    b += txt(224, 232, 'Historial de PC 02', { size: 12.5, weight: 700 });
    const rows = [
        ['1', 'Carlos Ruiz', '01:12:40', '$2.50', { badge: { text: 'Activa', bg: C.warningSoft, fg: C.warning } }],
        ['2', 'María Pérez', '00:45:00', '$1.90', { badge: { text: 'Finalizada', bg: C.successSoft, fg: C.success } }],
        ['3', 'Luis Martínez', '01:30:15', '$3.75', { badge: { text: 'Finalizada', bg: C.successSoft, fg: C.success } }],
        ['4', 'Ana Gómez', '00:20:00', '$0.85', { badge: { text: 'Finalizada', bg: C.successSoft, fg: C.success } }],
        ['5', 'Pedro Sánchez', '02:00:00', '$5.00', { badge: { text: 'Finalizada', bg: C.successSoft, fg: C.success } }],
        ['6', 'Sofía Hernández', '00:55:30', '$2.30', { badge: { text: 'Finalizada', bg: C.successSoft, fg: C.success } }]
    ];
    b += table(224, 248, 752, {
        cols: ['#', 'CLIENTE', 'TIEMPO', 'PRECIO', 'ESTADO'],
        widths: [0.5, 2.2, 1.2, 1, 1.2],
        rows, rowH: 40, size: 11.5
    });
    b += txt(224, 552, 'Las sesiones activas se muestran con su tiempo transcurrido y tarifa estimada.', { size: 10.5, color: C.muted });

    const footer = button(x + w - 108, y + h - 48, 84, 34, 'Cerrar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true });
    return svgDoc(shell({
        active: 'ciberControl', title: 'Cyber', subtitle: 'Historial de sesiones por estación',
        body: modalFrame({ x, y, w, h, title: 'Historial de Sesiones', icon: 'history', footer }) + b
    }));
}

export const scenes2 = [
    { name: '11-ordenes-listado', svg: ordenesListadoScene() },
    { name: '12-ordenes-nueva', svg: ordenesNuevaScene() },
    { name: '13-ordenes-detalle-lineas', svg: ordenesDetalleScene() },
    { name: '14-proveedores-listado', svg: proveedoresListadoScene() },
    { name: '15-proveedores-nuevo', svg: proveedoresNuevoScene() },
    { name: '16-cyber-estaciones', svg: cyberEstacionesScene() },
    { name: '17-cyber-iniciar-sesion', svg: cyberSesionScene() },
    { name: '18-cyber-historial', svg: cyberHistorialScene() }
];
