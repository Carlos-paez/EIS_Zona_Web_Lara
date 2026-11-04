import {
    W, H, C, txt, rect, line, circle, ico, chip, badge, button, iconButton,
    card, kpi, field, selectField, textarea, table, tableActionIcons,
    shell, modalFrame, svgDoc
} from './lib.mjs';

const CX = 256;
const CW = 920;

/* ============================== 19 · REPORTES FILTROS ============================== */
function reportesFiltrosScene() {
    let b = '';
    const kpis = [
        ['Ventas del Mes', '$8,420.00', 'Octubre 2026', C.primary, 'money'],
        ['Productos Activos', '128', 'En catálogo activo', C.success, 'box'],
        ['Sesiones Cyber', '86', '12 activas', C.warning, 'monitor'],
        ['Solicitudes', '42', 'Abastecimiento procesadas', C.info, 'clipboard']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, 360, 558, { title: 'Filtros Estratégicos', icon: 'filter', pad: 18 });
    b += selectField(CX + 18, 240, 324, { label: 'Tipo de Reporte', value: 'Ventas por fecha' });
    b += txt(CX + 18, 310, 'RANGOS PREDEFINIDOS', { size: 8.5, color: C.muted, weight: 700, letter: 0.6 });
    const presets = [['Hoy', 56], ['Ayer', 56], ['7 días', 64], ['Este Mes', 76], ['Mes Ant.', 72], ['Este Año', 72]];
    let px = CX + 18;
    presets.forEach(([label, w], i) => {
        if (i === 3) { px = CX + 18; }
        const active = label === 'Este Mes';
        b += chip(px, i < 3 ? 322 : 352, label, { bg: active ? C.primary : '#fff', fg: active ? '#fff' : C.muted, size: 9.5, h: 24, w, bold: active ? 700 : 500 });
        px += w + 8;
    });
    b += field(CX + 18, 410, 156, { label: 'Fecha Inicio', value: '01/10/2026', icon: 'calendar' });
    b += field(CX + 186, 410, 156, { label: 'Fecha Fin', value: '31/10/2026', icon: 'calendar' });
    b += rect(CX + 18, 478, 324, 62, { fill: C.hover, r: 8 });
    b += txt(CX + 30, 496, 'Formato de exportación', { size: 10, weight: 700 });
    b += circle(CX + 34, 518, 6, { fill: C.primary });
    b += circle(CX + 34, 518, 2.5, { fill: '#fff' });
    b += txt(CX + 46, 522, 'PDF', { size: 10.5 });
    b += circle(CX + 96, 518, 6, { stroke: C.muted, sw: 1.2 });
    b += txt(CX + 108, 522, 'Excel', { size: 10.5 });
    b += circle(CX + 164, 518, 6, { stroke: C.muted, sw: 1.2 });
    b += txt(CX + 176, 522, 'CSV', { size: 10.5 });
    b += button(CX + 18, 556, 324, 40, 'Consultar Reporte', { icon: 'chart', r: 8, size: 11.5 });
    b += button(CX + 18, 606, 324, 40, 'Exportar Archivo', { bg: C.success, icon: 'download', r: 8, size: 11.5 });
    b += txt(CX + 18, 680, 'El reporte se genera con los filtros seleccionados.', { size: 10, color: C.muted });

    b += card(628, 180, 548, 558, {});
    b += ico('chart', 646, 198, 18, C.primary);
    b += txt(672, 214, 'Resultados del Reporte', { size: 15, weight: 700 });
    b += txt(1158, 214, '0 registros', { size: 10, color: C.muted, anchor: 'end' });
    b += line(628, 232, 1176, 232, { stroke: C.borderLight });
    b += circle(902, 404, 42, { fill: C.primarySoft });
    b += ico('chart', 880, 382, 44, C.primary);
    b += txt(902, 478, 'Configura los filtros y presiona', { size: 12.5, weight: 600, anchor: 'middle' });
    b += txt(902, 498, '«Consultar Reporte»', { size: 12.5, weight: 700, color: C.primary, anchor: 'middle' });
    b += txt(902, 522, 'Los resultados aparecerán en esta tabla.', { size: 10.5, color: C.muted, anchor: 'middle' });

    return svgDoc(shell({ active: 'reportes', title: 'Reportes', subtitle: 'Consultas y estadísticas del negocio', body: b }));
}

/* ============================== 20 · REPORTES RESULTADOS ============================== */
function reportesResultadosScene() {
    let b = '';
    const kpis = [
        ['Ventas del Mes', '$8,420.00', 'Octubre 2026', C.primary, 'money'],
        ['Productos Activos', '128', 'En catálogo activo', C.success, 'box'],
        ['Sesiones Cyber', '86', '12 activas', C.warning, 'monitor'],
        ['Solicitudes', '42', 'Abastecimiento procesadas', C.info, 'clipboard']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, 360, 558, { title: 'Filtros Estratégicos', icon: 'filter', pad: 18 });
    b += selectField(CX + 18, 240, 324, { label: 'Tipo de Reporte', value: 'Ventas por fecha' });
    b += txt(CX + 18, 310, 'RANGOS PREDEFINIDOS', { size: 8.5, color: C.muted, weight: 700, letter: 0.6 });
    const presets = [['Hoy', 56], ['Ayer', 56], ['7 días', 64], ['Este Mes', 76], ['Mes Ant.', 72], ['Este Año', 72]];
    let px = CX + 18;
    presets.forEach(([label, w], i) => {
        if (i === 3) px = CX + 18;
        const active = label === 'Este Mes';
        b += chip(px, i < 3 ? 322 : 352, label, { bg: active ? C.primary : '#fff', fg: active ? '#fff' : C.muted, size: 9.5, h: 24, w, bold: active ? 700 : 500 });
        px += w + 8;
    });
    b += field(CX + 18, 410, 156, { label: 'Fecha Inicio', value: '01/10/2026', icon: 'calendar' });
    b += field(CX + 186, 410, 156, { label: 'Fecha Fin', value: '31/10/2026', icon: 'calendar' });
    b += rect(CX + 18, 478, 324, 62, { fill: C.hover, r: 8 });
    b += txt(CX + 30, 496, 'Formato de exportación', { size: 10, weight: 700 });
    b += circle(CX + 34, 518, 6, { fill: C.primary });
    b += circle(CX + 34, 518, 2.5, { fill: '#fff' });
    b += txt(CX + 46, 522, 'PDF', { size: 10.5 });
    b += circle(CX + 96, 518, 6, { stroke: C.muted, sw: 1.2 });
    b += txt(CX + 108, 522, 'Excel', { size: 10.5 });
    b += circle(CX + 164, 518, 6, { stroke: C.muted, sw: 1.2 });
    b += txt(CX + 176, 522, 'CSV', { size: 10.5 });
    b += button(CX + 18, 556, 324, 40, 'Consultar Reporte', { icon: 'chart', r: 8, size: 11.5 });
    b += button(CX + 18, 606, 324, 40, 'Exportar Archivo', { bg: C.success, icon: 'download', r: 8, size: 11.5 });
    b += txt(CX + 18, 680, 'El reporte se genera con los filtros seleccionados.', { size: 10, color: C.muted });

    b += card(628, 180, 548, 558, {});
    b += ico('chart', 646, 198, 18, C.primary);
    b += txt(672, 214, 'Resultados del Reporte', { size: 15, weight: 700 });
    b += badge(1100, 198, '7 registros', { bg: C.primarySoft, fg: C.primary });
    b += line(628, 232, 1176, 232, { stroke: C.borderLight });

    const cards = [
        ['TOTAL VENTAS', '$8,420.00', C.primary],
        ['TRANSACCIONES', '96', C.success],
        ['TICKET PROMEDIO', '$87.71', C.info]
    ];
    cards.forEach(([label, value, color], i) => {
        const x = 646 + i * 172;
        b += rect(x, 246, 160, 64, { fill: C.surface, stroke: C.border, r: 8 });
        b += txt(x + 12, 266, label, { size: 8.5, color: C.muted, weight: 700, letter: 0.4 });
        b += txt(x + 12, 294, value, { size: 18, weight: 800, color });
    });

    const rows = [
        ['01/10/2026', { t: '8', align: 'right' }, { t: '$640.00', align: 'right', color: C.success, bold: true }],
        ['02/10/2026', { t: '12', align: 'right' }, { t: '$980.50', align: 'right', color: C.success, bold: true }],
        ['03/10/2026', { t: '10', align: 'right' }, { t: '$755.00', align: 'right', color: C.success, bold: true }],
        ['04/10/2026', { t: '15', align: 'right' }, { t: '$1,120.00', align: 'right', color: C.success, bold: true }],
        ['05/10/2026', { t: '18', align: 'right' }, { t: '$1,245.00', align: 'right', color: C.success, bold: true }],
        ['06/10/2026', { t: '9', align: 'right' }, { t: '$612.50', align: 'right', color: C.success, bold: true }],
        ['07/10/2026', { t: '14', align: 'right' }, { t: '$1,067.00', align: 'right', color: C.success, bold: true }]
    ];
    b += table(646, 326, 512, {
        cols: ['FECHA', 'TRANSACCIONES', 'TOTAL'],
        widths: [1.4, 1.2, 1],
        rows, rowH: 34, headH: 32, size: 11.5
    });
    b += txt(646, 606, 'Mostrando 7 de 96 transacciones del período.', { size: 10, color: C.muted });
    b += button(646, 632, 130, 34, 'Exportar PDF', { bg: C.success, icon: 'download', r: 17, size: 10.5 });
    b += button(786, 632, 120, 34, 'Ver detalle', { bg: '#fff', fg: C.primary, r: 17, size: 10.5, outline: true });

    return svgDoc(shell({ active: 'reportes', title: 'Reportes', subtitle: 'Resultados de ventas por fecha', body: b }));
}

/* ============================== 21 · ACTIVOS LISTADO ============================== */
function activosListadoScene() {
    let b = '';
    const kpis = [
        ['Total Activos', '64', 'Registrados', C.primary, 'device'],
        ['Estaciones Cyber', '9', 'is_ciber = 1', C.info, 'monitor'],
        ['Ocupadas Ahora', '3', 'Sesiones abiertas', C.warning, 'clock'],
        ['Inactivos', '6', 'Desactivados', C.danger, 'gear']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 380, { label: 'Buscar activo', placeholder: 'Buscar por marca, tipo o descripción...', icon: 'search' });
    b += selectField(CX + 412, 196, 200, { label: 'Filtrar por tipo', value: 'Todos los tipos' });
    b += button(CX + 768, 196, 134, 46, 'Nuevo Activo', { icon: 'plus', r: 23, size: 11.5 });

    b += card(CX, 260, CW, 478, {});
    b += ico('device', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Activos', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '64 resultados', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['HP', 'Intel i5, 8GB RAM, 256GB SSD', 'Computadora', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['Dell', 'Optiplex 7090, Intel i7', 'Computadora', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['Lenovo', 'ThinkPad E14, Ryzen 5', 'Laptop', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['Samsung', 'Impresora multifuncional', 'Impresora', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['Oficina', 'Escritorio de madera 1.20m', 'Mobiliario', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['HP', 'Monitor LED 24"', 'Monitor', { badge: { text: 'Inactivo', bg: C.dangerSoft, fg: C.danger } }],
        ['Cisco', 'Router 24 puertos', 'Red', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['Epson', 'Proyector Full HD', 'Proyector', { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }],
        ['Genérico', 'Silla ergonómica', 'Mobiliario', { badge: { text: 'Inactivo', bg: C.dangerSoft, fg: C.danger } }]
    ].map((r) => [...r, '']);
    b += table(CX + 18, 310, 884, {
        cols: ['MARCA', 'DESCRIPCIÓN', 'TIPO', 'ESTADO', 'ACCIÓN'],
        widths: [1.1, 3, 1.4, 1.2, 1],
        rows, rowH: 40, size: 11.5
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(1090, 310 + 34 + i * 40 + 7, {});
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, '64 resultados · Los activos marcados como Cyber aparecen en el módulo Cyber.', { size: 10.5, color: C.muted });
    return svgDoc(shell({ active: 'activos', title: 'Activos', subtitle: 'Bienes y equipos de la empresa', body: b }));
}

/* ============================== 22 · NUEVO ACTIVO ============================== */
function activosNuevoScene() {
    const x = 320, y = 100, w = 560, h = 560;
    let b = '';
    b += field(344, 162, 250, { label: 'Marca *', placeholder: 'Ej: HP', icon: 'tag', focus: true });
    b += selectField(610, 162, 246, { label: 'Tipo de activo *', value: 'Computadora', icon: 'category' });
    b += textarea(344, 238, 512, 76, { label: 'Descripción', placeholder: 'Ej: Intel i5, 8GB RAM, 256GB SSD', icon: 'doc' });

    b += txt(344, 348, 'Condición y uso', { size: 11, color: C.muted, weight: 700 });
    b += rect(344, 360, 512, 70, { fill: C.hover, r: 8 });
    b += rect(360, 382, 18, 18, { fill: C.primary, r: 4 });
    b += ico('check', 362, 384, 14, '#fff', 2.4);
    b += txt(388, 396, 'Activo', { size: 12, weight: 600 });
    b += txt(388, 414, 'Disponible para su uso', { size: 9.5, color: C.muted });
    b += rect(560, 382, 18, 18, { fill: '#fff', stroke: C.muted, r: 4 });
    b += txt(588, 396, 'Estación de Cybercafé', { size: 12, weight: 600 });
    b += txt(588, 414, 'Se mostrará en el módulo Cyber', { size: 9.5, color: C.muted });

    b += rect(344, 450, 512, 56, { fill: C.infoSoft, r: 8 });
    b += ico('info', 358, 464, 15, C.info);
    b += txt(386, 478, 'Si marcas "Estación de Cybercafé", la PC aparecerá en el módulo Cyber.', { size: 10.5 });
    b += txt(386, 494, 'No podrás eliminar un activo con sesiones registradas.', { size: 10, color: C.muted });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });
    return svgDoc(shell({
        active: 'activos', title: 'Activos', subtitle: 'Bienes y equipos de la empresa',
        body: modalFrame({ x, y, w, h, title: 'Nuevo Activo', icon: 'device', subtitle: 'Registra un bien de la empresa', footer }) + b
    }));
}

/* ============================== 23 · ASESORÍA · VALIDACIÓN ============================== */
function asesoriaBanner() {
    let b = rect(CX, 84, CW, 76, { fill: 'url(#heroGrad)', r: 12 });
    b += ico('gavel', CX + 24, 106, 30, '#fff');
    b += txt(CX + 70, 116, 'Asesoría Legal', { size: 19, weight: 800, color: '#fff' });
    b += txt(CX + 70, 138, 'Registro y validación de documentos para asesoría jurídica gratuita', { size: 11, color: '#e8eaf6' });
    b += chip(CX + 790, 108, '12 registradas hoy', { bg: '#fff', fg: C.primary, icon: 'clipboard', size: 10, h: 26, w: 132 });
    return b;
}

function asesoriaFormFields(filled = true) {
    let b = '';
    b += field(274, 340, 354, { label: 'Nombre del Ciudadano', value: filled ? 'María Pérez' : '', placeholder: 'Nombre y apellido', icon: 'user' });
    b += field(274, 396, 354, { label: 'Cédula de Identidad', value: filled ? 'V-12345678' : '', placeholder: 'Ej: V-12345678', icon: 'tag' });
    b += field(274, 452, 354, { label: 'Teléfono de Contacto', value: filled ? '0414-1234567' : '', placeholder: 'Opcional', icon: 'phone' });
    b += field(274, 508, 354, { label: 'Dirección', value: filled ? 'Av. Principal, Casa 12' : '', placeholder: 'Opcional', icon: 'store' });
    b += field(274, 564, 354, { label: 'Tipo de Documento / Asesoría', value: filled ? 'Consulta Laboral' : 'Demanda Formal', icon: 'doc' });
    b += textarea(274, 620, 354, 50, { label: 'Descripción / Motivo de la Consulta', value: filled ? 'Consulta sobre prestaciones sociales.' : 'Caso de demanda formal por incumplimiento.' });
    return b;
}

function asesoriaValidacionScene() {
    let b = asesoriaBanner();
    b += card(CX, 180, 390, 558, { title: 'Registrar Asesoría', icon: 'doc', pad: 18 });
    b += rect(274, 218, 354, 100, { fill: C.dangerSoft, stroke: '#ffcdd2', r: 8 });
    b += ico('warning', 286, 230, 18, C.danger);
    b += txt(312, 244, 'DOCUMENTO NO PERMITIDO', { size: 11.5, weight: 800, color: C.danger });
    b += txt(312, 262, 'El documento "Demanda Formal" no corresponde', { size: 9.5, color: C.text });
    b += txt(312, 276, 'a los tipos de asesoría que podemos atender.', { size: 9.5, color: C.text });
    b += txt(312, 290, 'Debe derivarse a una Oficina de Atención Legal Oficial.', { size: 9.5, color: C.text, weight: 600 });
    b += txt(312, 306, 'El botón cambia a "Derivar a Oficina Oficial".', { size: 9, color: C.muted });
    b += asesoriaFormFields(false);
    b += button(274, 686, 354, 40, 'Derivar a Oficina Oficial', { bg: C.danger, icon: 'warning', r: 8, size: 11.5 });

    b += card(660, 180, 516, 262, { title: 'Documentos Permitidos', icon: 'check' });
    const docs = ['Consulta Laboral', 'Consulta Civil', 'Consulta Familiar', 'Orientación Legal General', 'Revisión de Contrato', 'Elaboración de Documento Simple', 'Asesoría Prevencional'];
    const widths = [126, 110, 126, 168, 132, 188, 146];
    let dx = 678, dy = 228;
    docs.forEach((d, i) => {
        if (dx + widths[i] > 1158) { dx = 678; dy += 30; }
        b += chip(dx, dy, '✓ ' + d, { bg: C.successSoft, fg: C.success, size: 9, h: 24, w: widths[i] });
        dx += widths[i] + 6;
    });
    b += txt(678, 350, 'Los documentos no listados (juicios, demandas, apelaciones,', { size: 9.5, color: C.muted });
    b += txt(678, 364, 'herencias, penal, etc.) requieren derivación a una oficina oficial.', { size: 9.5, color: C.muted });
    b += rect(678, 384, 480, 40, { fill: C.infoSoft, r: 8 });
    b += ico('info', 690, 396, 15, C.info);
    b += txt(714, 409, 'La validación ocurre al escribir el tipo de documento.', { size: 9.5, color: C.text });

    b += card(660, 456, 516, 282, { title: 'Historial de Asesorías', icon: 'history' });
    b += table(678, 506, 480, {
        cols: ['CIUDADANO', 'DOCUMENTO', 'ESTADO'],
        widths: [1.4, 1.7, 1],
        rows: [
            ['María Pérez', 'Consulta Laboral', { badge: { text: 'Permitido', bg: C.successSoft, fg: C.success } }],
            ['Carlos Ruiz', 'Demanda Formal', { badge: { text: 'Denegado', bg: C.dangerSoft, fg: C.danger } }],
            ['Ana Gómez', 'Consulta Civil', { badge: { text: 'Permitido', bg: C.successSoft, fg: C.success } }]
        ],
        rowH: 36, headH: 30, size: 10.5
    });

    return svgDoc(shell({ active: 'asesorias', title: 'Asesoría Legal', subtitle: 'Registro y validación de documentos', body: b }));
}

/* ============================== 24 · ASESORÍA · REGISTRO ============================== */
function asesoriaRegistroScene() {
    let b = asesoriaBanner();
    b += card(CX, 180, 390, 558, { title: 'Registrar Asesoría', icon: 'doc', pad: 18 });
    b += rect(274, 218, 354, 72, { fill: C.successSoft, stroke: '#c8e6c9', r: 8 });
    b += ico('check', 286, 230, 18, C.success, 2.4);
    b += txt(312, 244, 'DOCUMENTO PERMITIDO', { size: 11.5, weight: 800, color: C.success });
    b += txt(312, 262, 'El documento "Consulta Laboral" puede ser atendido.', { size: 9.5, color: C.text });
    b += txt(312, 278, 'Completa los datos y presiona "Validar y Registrar".', { size: 9.5, color: C.muted });
    b += asesoriaFormFields(true);
    b += button(274, 686, 354, 40, 'Validar y Registrar', { icon: 'check', r: 8, size: 11.5 });

    b += card(660, 180, 516, 262, { title: 'Documentos Permitidos', icon: 'check' });
    const docs = ['Consulta Laboral', 'Consulta Civil', 'Consulta Familiar', 'Orientación Legal General', 'Revisión de Contrato', 'Elaboración de Documento Simple', 'Asesoría Prevencional'];
    const widths = [126, 110, 126, 168, 132, 188, 146];
    let dx = 678, dy = 228;
    docs.forEach((d, i) => {
        if (dx + widths[i] > 1158) { dx = 678; dy += 30; }
        b += chip(dx, dy, '✓ ' + d, { bg: C.successSoft, fg: C.success, size: 9, h: 24, w: widths[i] });
        dx += widths[i] + 6;
    });
    b += txt(678, 350, 'Documentos no listados requieren derivación a una', { size: 9.5, color: C.muted });
    b += txt(678, 364, 'oficina oficial de atención legal.', { size: 9.5, color: C.muted });
    b += rect(678, 384, 480, 40, { fill: C.successSoft, r: 8 });
    b += ico('check', 690, 396, 15, C.success, 2.2);
    b += txt(714, 409, 'La asesoría quedará registrada en el historial.', { size: 9.5, color: C.text });

    b += card(660, 456, 516, 282, { title: 'Historial de Asesorías', icon: 'history' });
    b += table(678, 506, 480, {
        cols: ['CIUDADANO', 'DOCUMENTO', 'ESTADO'],
        widths: [1.4, 1.7, 1],
        rows: [
            ['María Pérez', 'Consulta Laboral', { badge: { text: 'Permitido', bg: C.successSoft, fg: C.success } }],
            ['Carlos Ruiz', 'Demanda Formal', { badge: { text: 'Denegado', bg: C.dangerSoft, fg: C.danger } }],
            ['Ana Gómez', 'Consulta Civil', { badge: { text: 'Permitido', bg: C.successSoft, fg: C.success } }]
        ],
        rowH: 36, headH: 30, size: 10.5
    });

    return svgDoc(shell({ active: 'asesorias', title: 'Asesoría Legal', subtitle: 'Registro y validación de documentos', body: b }));
}

export const scenes3 = [
    { name: '19-reportes-filtros', svg: reportesFiltrosScene() },
    { name: '20-reportes-resultados', svg: reportesResultadosScene() },
    { name: '21-activos-listado', svg: activosListadoScene() },
    { name: '22-activos-nuevo', svg: activosNuevoScene() },
    { name: '23-asesoria-validacion', svg: asesoriaValidacionScene() },
    { name: '24-asesoria-registro', svg: asesoriaRegistroScene() }
];
