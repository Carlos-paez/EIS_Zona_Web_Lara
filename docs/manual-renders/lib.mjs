// Utilidades para generar los renders SVG del manual de usuario.
// Cada escena imita una pantalla real de EIS System.

export const W = 1200;
export const H = 760;

export const C = {
    primary: '#3949ab',
    primaryDark: '#283593',
    primaryLight: '#5c6bc0',
    primarySoft: '#e8eaf6',
    bg: '#f6f8fb',
    surface: '#ffffff',
    hover: '#f5f7fa',
    text: '#1f2937',
    muted: '#6b7280',
    light: '#9ca3af',
    border: '#e5e7eb',
    borderLight: '#eef2f7',
    success: '#43a047',
    successSoft: '#e8f5e9',
    warning: '#fb8c00',
    warningSoft: '#fff3e0',
    danger: '#e53935',
    dangerSoft: '#ffebee',
    info: '#1e88e5',
    infoSoft: '#e3f2fd',
    dark: '#0f172a'
};

const FONT = 'Segoe UI, Roboto, Helvetica, Arial, sans-serif';
const esc = (s) => String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

export function txt(x, y, text, o = {}) {
    const { size = 13, color = C.text, weight = 400, anchor = 'start', opacity = 1, family = FONT, letter = 0, style = 'normal' } = o;
    return `<text x="${x}" y="${y}" font-family="${family}" font-size="${size}" fill="${color}" font-weight="${weight}" text-anchor="${anchor}" opacity="${opacity}" letter-spacing="${letter}" font-style="${style}">${esc(text)}</text>`;
}

export function rect(x, y, w, h, o = {}) {
    const { fill = C.surface, stroke = 'none', sw = 1, r = 0, opacity = 1, dash = '', filter = '' } = o;
    return `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="${fill}" stroke="${stroke}" stroke-width="${sw}" opacity="${opacity}"${dash ? ` stroke-dasharray="${dash}"` : ''}${filter ? ` filter="url(#${filter})"` : ''}/>`;
}

export function line(x1, y1, x2, y2, o = {}) {
    const { stroke = C.border, sw = 1, dash = '', opacity = 1 } = o;
    return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${stroke}" stroke-width="${sw}"${dash ? ` stroke-dasharray="${dash}"` : ''} opacity="${opacity}" stroke-linecap="round"/>`;
}

export function circle(cx, cy, r, o = {}) {
    const { fill = 'none', stroke = 'none', sw = 1, opacity = 1 } = o;
    return `<circle cx="${cx}" cy="${cy}" r="${r}" fill="${fill}" stroke="${stroke}" stroke-width="${sw}" opacity="${opacity}"/>`;
}

const ICONS = {
    dashboard: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    box: '<path d="M21 8 12 3 3 8v8l9 5 9-5V8Z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
    cart: '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 7H6"/>',
    user: '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
    users: '<circle cx="9" cy="8" r="3.4"/><path d="M2 21c0-3.6 3.1-6 7-6s7 2.4 7 6"/><path d="M16 4.2a3.4 3.4 0 0 1 0 6.6M18.5 15c2.2.7 3.5 2.7 3.5 5.5"/>',
    doc: '<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/>',
    monitor: '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
    chart: '<rect x="3" y="12" width="4" height="9" rx="1"/><rect x="10" y="5" width="4" height="16" rx="1"/><rect x="17" y="9" width="4" height="12" rx="1"/>',
    gear: '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
    gavel: '<path d="M14 3l7 7-3 3-7-7 3-3Z"/><path d="M10 7l7 7M3 21l6-6M5 15l4 4"/>',
    shield: '<path d="M12 2l8 3v6c0 5-3.4 9-8 11-4.6-2-8-6-8-11V5l8-3Z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
    book: '<path d="M4 4h7a3 3 0 0 1 3 3v13a3 3 0 0 0-3-3H4V4Z"/><path d="M20 4h-3a3 3 0 0 0-3 3v13a3 3 0 0 1 3-3h3V4Z"/>',
    logout: '<path d="M9 3H4v18h5M14 8l4 4-4 4M8 12h10"/>',
    moon: '<path d="M20 14A8 8 0 0 1 10 4a8 8 0 1 0 10 10Z"/>',
    search: '<circle cx="10" cy="10" r="6"/><path d="M15 15l6 6"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    minus: '<path d="M5 12h14"/>',
    edit: '<path d="M4 20h4l11-11-4-4L4 16v4Z"/><path d="M13.5 5.5l4 4"/>',
    trash: '<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>',
    check: '<path d="M4 12l5 5L20 6"/>',
    warning: '<path d="M12 3l10 18H2L12 3Z"/><path d="M12 9v5M12 17h.01"/>',
    info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>',
    lock: '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    close: '<path d="M6 6l12 12M18 6L6 18"/>',
    arrowRight: '<path d="M4 12h16M14 6l6 6-6 6"/>',
    download: '<path d="M12 3v12M7 10l5 5 5-5M4 21h16"/>',
    play: '<path d="M8 4l12 8-12 8V4Z"/>',
    history: '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
    refresh: '<path d="M21 12a9 9 0 0 1-15 6.7L3 16M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5M3 21v-5h5"/>',
    money: '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    store: '<path d="M3 9l2-5h14l2 5M4 9v11h16V9M4 9h16"/><path d="M9 20v-6h6v6"/>',
    tag: '<path d="M3 12V4h8l9 9-7 7-10-8Z"/><circle cx="7.5" cy="7.5" r="1"/>',
    device: '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
    save: '<path d="M5 3h11l3 3v15H5V3Z"/><path d="M8 3v6h8V3M8 15h8v6H8z"/>',
    eye: '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
    wifi: '<path d="M4 11a11 11 0 0 1 16 0M7.5 14.5a6.5 6.5 0 0 1 9 0M10.5 18a2.5 2.5 0 0 1 3 0"/><circle cx="12" cy="21" r=".8"/>',
    clipboard: '<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h4"/>',
    calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    phone: '<path d="M5 3h4l2 5-2.5 1.5a12 12 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
    mail: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/>',
    filter: '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5Z"/>'
};

export function ico(name, x, y, size = 18, color = C.primary, sw = 1.7) {
    const body = ICONS[name] || ICONS.info;
    return `<g transform="translate(${x} ${y}) scale(${size / 24})" fill="none" stroke="${color}" stroke-width="${sw}" stroke-linecap="round" stroke-linejoin="round">${body}</g>`;
}

export function chip(x, y, text, o = {}) {
    const { bg = C.primarySoft, fg = C.primary, icon = null, size = 11, w = 0, h = 22, bold = 600 } = o;
    const width = w || Math.max(48, text.length * (size * 0.55) + (icon ? 32 : 18));
    return rect(x, y, width, h, { fill: bg, r: h / 2 })
        + (icon ? ico(icon, x + 8, y + (h - 13) / 2, 13, fg) : '')
        + txt(x + (icon ? 26 : width / 2), y + h / 2 + size * 0.36, text, { size, color: fg, weight: bold, anchor: icon ? 'start' : 'middle' });
}

export function badge(x, y, text, o = {}) {
    const { bg = C.warningSoft, fg = C.warning, size = 10.5, w = 0, h = 19 } = o;
    const width = w || Math.max(44, text.length * 6 + 16);
    return rect(x, y, width, h, { fill: bg, r: 5 })
        + txt(x + width / 2, y + h / 2 + size * 0.36, text, { size, color: fg, weight: 700, anchor: 'middle' });
}

export function button(x, y, w, h, label, o = {}) {
    const { bg = C.primary, fg = '#fff', icon = null, r = 22, size = 12.5, outline = false, stroke = C.border } = o;
    const fill = outline ? '#fff' : bg;
    const color = outline ? C.text : fg;
    return rect(x, y, w, h, { fill, stroke: outline ? stroke : 'none', r, sw: 1 })
        + (icon ? ico(icon, x + 14, y + (h - 14) / 2, 14, outline ? C.muted : fg) : '')
        + txt(x + w / 2 + (icon ? 8 : 0), y + h / 2 + size * 0.36, label, { size, color, weight: 600, anchor: 'middle' });
}

export function iconButton(x, y, icon, o = {}) {
    const { bg = C.primarySoft, color = C.primary, size = 28, iconSize = 14 } = o;
    return circle(x + size / 2, y + size / 2, size / 2, { fill: bg })
        + ico(icon, x + (size - iconSize) / 2, y + (size - iconSize) / 2, iconSize, color);
}

export function card(x, y, w, h, o = {}) {
    const { title = '', icon = '', children = '', fill = C.surface, stroke = C.border, r = 12, titleSize = 15, pad = 18, badgeText = '', subtitle = '' } = o;
    let s = rect(x, y, w, h, { fill, stroke, r, filter: 'cardShadow' });
    if (title) {
        s += ico(icon, x + pad, y + pad - 2, 18, C.primary);
        s += txt(x + pad + 26, y + pad + 14, title, { size: titleSize, weight: 700 });
        if (subtitle) s += txt(x + pad + 26, y + pad + 32, subtitle, { size: 11, color: C.muted });
        if (badgeText) s += badge(x + w - pad - Math.max(44, badgeText.length * 6 + 16), y + pad, badgeText, { bg: C.primarySoft, fg: C.primary });
    }
    return s + children;
}

export function kpi(x, y, w, h, o = {}) {
    const { label, value, sub = '', color = C.primary, icon = 'chart' } = o;
    return rect(x, y, w, h, { fill: C.surface, stroke: C.border, r: 12, filter: 'cardShadow' })
        + circle(x + 36, y + 36, 21, { fill: color, opacity: 0.12 })
        + ico(icon, x + 25, y + 25, 22, color)
        + txt(x + 66, y + 30, label, { size: 11.5, color: C.muted, weight: 600 })
        + txt(x + 66, y + 58, value, { size: 24, color: color === C.primary ? C.text : color, weight: 800 })
        + (sub ? txt(x + 66, y + 76, sub, { size: 10.5, color: C.muted }) : '');
}

export function field(x, y, w, o = {}) {
    const { label = '', value = '', icon = null, placeholder = '', focus = false, h = 46, help = '' } = o;
    const shown = value || placeholder;
    let s = txt(x, y - 7, label, { size: 11, color: focus ? C.primary : C.muted, weight: 600 });
    s += rect(x, y, w, h, { fill: '#fff', stroke: focus ? C.primary : C.border, sw: focus ? 1.8 : 1, r: 6 });
    if (icon) s += ico(icon, x + 12, y + h / 2 - 8, 16, focus ? C.primary : C.muted);
    s += txt(x + (icon ? 38 : 12), y + h / 2 + 5, shown, { size: 13, color: value ? C.text : C.light });
    if (help) s += txt(x + (icon ? 38 : 12), y + h + 14, help, { size: 10, color: C.muted });
    return s;
}

export function selectField(x, y, w, o = {}) {
    const { label = '', value = '', icon = null, h = 46, focus = false } = o;
    let s = txt(x, y - 7, label, { size: 11, color: focus ? C.primary : C.muted, weight: 600 });
    s += rect(x, y, w, h, { fill: '#fff', stroke: focus ? C.primary : C.border, sw: focus ? 1.8 : 1, r: 6 });
    if (icon) s += ico(icon, x + 12, y + h / 2 - 8, 16, C.muted);
    s += txt(x + (icon ? 38 : 12), y + h / 2 + 5, value, { size: 13, color: value ? C.text : C.light });
    s += `<path d="M${x + w - 24} ${y + h / 2 - 2}l5 5 5-5" fill="none" stroke="${C.muted}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>`;
    return s;
}

export function textarea(x, y, w, h, o = {}) {
    const { label = '', value = '', icon = null, placeholder = '', focus = false } = o;
    const shown = value || placeholder;
    let s = txt(x, y - 7, label, { size: 11, color: focus ? C.primary : C.muted, weight: 600 });
    s += rect(x, y, w, h, { fill: '#fff', stroke: focus ? C.primary : C.border, sw: focus ? 1.8 : 1, r: 6 });
    if (icon) s += ico(icon, x + 12, y + 13, 16, C.muted);
    s += txt(x + (icon ? 38 : 12), y + 22, shown, { size: 12.5, color: value ? C.text : C.light });
    return s;
}

function renderCell(cx, cy, cw, cell, size) {
    if (cell === null || cell === undefined || cell === '') return '';
    if (typeof cell === 'string') cell = { t: cell };
    const align = cell.align || 'left';
    const x = align === 'right' ? cx + cw - 10 : align === 'center' ? cx + cw / 2 : cx + 10;
    const anchor = align === 'right' ? 'end' : align === 'center' ? 'middle' : 'start';
    if (cell.badge) {
        const b = cell.badge;
        const bw = Math.max(44, b.text.length * 6 + 16);
        const bx = align === 'right' ? cx + cw - 10 - bw : align === 'center' ? cx + cw / 2 - bw / 2 : cx + 10;
        return badge(bx, cy - 9, b.text, { bg: b.bg, fg: b.fg, w: bw });
    }
    let s = '';
    if (cell.icon) s += ico(cell.icon, x - 2, cy - 8, 14, cell.iconColor || C.muted);
    s += txt(x + (cell.icon ? 18 : 0), cy + 4, cell.t, {
        size: cell.size || size,
        color: cell.color || C.text,
        weight: cell.bold ? 700 : 400,
        anchor
    });
    return s;
}

export function table(x, y, w, o = {}) {
    const { cols = [], rows = [], widths = [], rowH = 38, headH = 34, size = 12, headerBg = C.hover, zebra = true } = o;
    const total = widths.reduce((a, b) => a + b, 0) || cols.length;
    const ws = widths.length ? widths.map((n) => (n / total) * w) : cols.map(() => w / cols.length);
    const xs = [];
    let acc = x;
    ws.forEach((cw) => { xs.push(acc); acc += cw; });
    const h = headH + rows.length * rowH;
    let s = rect(x, y, w, h, { fill: '#fff', stroke: C.border, r: 8 });
    s += rect(x, y, w, headH, { fill: headerBg, r: 8 });
    s += rect(x, y + headH - 8, w, 8, { fill: headerBg });
    cols.forEach((col, i) => {
        s += txt(xs[i] + 10, y + headH / 2 + 4, col, { size: 10.5, color: C.muted, weight: 700, letter: 0.4 });
    });
    rows.forEach((row, ri) => {
        const ry = y + headH + ri * rowH;
        if (zebra && ri % 2 === 1) s += rect(x + 1, ry, w - 2, rowH, { fill: '#fbfcfe' });
        s += line(x, ry, x + w, ry, { stroke: C.borderLight });
        row.forEach((cell, ci) => {
            s += renderCell(xs[ci], ry + rowH / 2, ws[ci], cell, size);
        });
    });
    return s;
}

export function tableActionIcons(x, y, o = {}) {
    const { edit = true, del = true, extra = null } = o;
    let s = '';
    let cx = x;
    if (extra) { s += iconButton(cx, y, extra, { bg: C.infoSoft, color: C.info, size: 26, iconSize: 13 }); cx += 32; }
    if (edit) { s += iconButton(cx, y, 'edit', { bg: C.infoSoft, color: C.info, size: 26, iconSize: 13 }); cx += 32; }
    if (del) { s += iconButton(cx, y, 'trash', { bg: C.dangerSoft, color: C.danger, size: 26, iconSize: 13 }); }
    return s;
}

const NAV = [
    ['dashboard', 'Dashboard'],
    ['inventario', 'Inventario'],
    ['ventas', 'Ventas (POS)'],
    ['clientes', 'Clientes'],
    ['solicitudes', 'Solicitudes', [['proveedores', 'Órdenes de Compra'], ['proveedores-gestion', 'Proveedores']]],
    ['ciberControl', 'Cyber'],
    ['reportes', 'Reportes'],
    ['activos', 'Activos'],
    ['asesorias', 'Asesoría Legal'],
    ['usuarios', 'Configuración'],
    ['roles', 'Roles y Permisos'],
    ['manual_usuario', 'Manual de Usuario']
];

function sidebar(active) {
    let s = rect(0, 0, 232, H, { fill: '#fff' });
    s += line(232, 0, 232, H, { stroke: C.border });
    s += rect(18, 16, 38, 38, { fill: 'url(#brandGrad)', r: 10 });
    s += txt(37, 42, 'E', { size: 21, color: '#fff', weight: 800, anchor: 'middle' });
    s += txt(66, 33, 'EIS System', { size: 15, weight: 800 });
    s += txt(66, 50, 'Zona Web Lara', { size: 10, color: C.muted });
    s += txt(20, 84, 'MENÚ PRINCIPAL', { size: 9, color: C.light, weight: 700, letter: 1.1 });

    let y = 96;
    NAV.forEach(([key, label, sub]) => {
        const isParent = key === 'solicitudes';
        const isActive = active === key || (sub && sub.some(([k]) => k === active));
        if (isActive) {
            s += rect(10, y, 212, 34, { fill: C.primarySoft, r: 8 });
            s += rect(0, y, 3.5, 34, { fill: C.primary });
        }
        s += ico(key === 'solicitudes' ? 'doc' : key, 22, y + 9, 16, isActive ? C.primary : C.muted);
        s += txt(48, y + 22, label, { size: 12.5, color: isActive ? C.primary : C.text, weight: isActive ? 700 : 500 });
        if (isParent) {
            s += `<path d="M${204} ${y + 13}l4 4 4-4" fill="none" stroke="${isActive ? C.primary : C.muted}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>`;
        }
        y += 36;
        if (sub) {
            sub.forEach(([sk, sl]) => {
                const subActive = active === sk;
                if (subActive) s += rect(46, y, 176, 25, { fill: C.primarySoft, r: 6 });
                s += circle(56, y + 12.5, 2.5, { fill: subActive ? C.primary : C.light });
                s += txt(66, y + 17, sl, { size: 11.5, color: subActive ? C.primary : C.muted, weight: subActive ? 700 : 400 });
                y += 26;
            });
        }
    });

    s += line(16, 656, 216, 656, { stroke: C.borderLight });
    s += ico('moon', 22, 672, 16, C.muted);
    s += txt(48, 684, 'Modo Oscuro', { size: 12, color: C.text });
    s += `<rect x="184" y="674" width="28" height="15" rx="7.5" fill="${C.border}"/><circle cx="190" cy="681.5" r="5.5" fill="#fff"/>`;
    s += ico('logout', 22, 708, 16, C.muted);
    s += txt(48, 720, 'Cerrar Sesión', { size: 12, color: C.text });
    return s;
}

function topbar(title, subtitle) {
    let s = rect(232, 0, 968, 64, { fill: '#fff' });
    s += line(232, 64, 1200, 64, { stroke: C.border });
    s += txt(260, 28, title, { size: 15.5, weight: 700 });
    s += txt(260, 46, subtitle, { size: 10.5, color: C.muted });
    s += ico('search', 1010, 24, 17, C.muted);
    s += ico('info', 1050, 24, 17, C.muted);
    s += ico('user', 1082, 24, 17, C.muted);
    s += circle(1150, 32, 15, { fill: C.primarySoft });
    s += txt(1150, 37, 'A', { size: 13, color: C.primary, weight: 800, anchor: 'middle' });
    s += txt(1128, 29, 'Administrador', { size: 11.5, weight: 700, anchor: 'end' });
    s += txt(1128, 43, 'Sesión activa', { size: 9.5, color: C.muted, anchor: 'end' });
    return s;
}

export function shell(o = {}) {
    const { active = 'dashboard', title = 'Dashboard', subtitle = 'Panel de control', body = '' } = o;
    return rect(0, 0, W, H, { fill: C.bg }) + sidebar(active) + topbar(title, subtitle) + body;
}

export function modalFrame(o = {}) {
    const { x = 290, y = 70, w = 620, h = 620, title = '', icon = 'info', subtitle = '', children = '', footer = '' } = o;
    let s = rect(0, 0, W, H, { fill: C.dark, opacity: 0.5 });
    s += rect(x, y, w, h, { fill: '#fff', r: 14, filter: 'modalShadow' });
    s += ico(icon, x + 24, y + 22, 20, C.primary);
    s += txt(x + 54, y + 37, title, { size: 17, weight: 700 });
    if (subtitle) s += txt(x + 54, y + 54, subtitle, { size: 10.5, color: C.muted });
    s += circle(x + w - 32, y + 32, 13, { fill: C.hover });
    s += ico('close', x + w - 39, y + 25, 14, C.muted);
    s += line(x, y + 64, x + w, y + 64, { stroke: C.borderLight });
    if (footer) {
        s += line(x, y + h - 62, x + w, y + h - 62, { stroke: C.borderLight });
        s += footer;
    }
    return s + children;
}

export function svgDoc(body) {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" role="img">
<defs>
  <linearGradient id="brandGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${C.primary}"/><stop offset="1" stop-color="#7c4dff"/></linearGradient>
  <linearGradient id="heroGrad" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="${C.primaryDark}"/><stop offset="1" stop-color="${C.primaryLight}"/></linearGradient>
  <linearGradient id="loginGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0f172a"/><stop offset="0.55" stop-color="#1a237e"/><stop offset="1" stop-color="#3949ab"/></linearGradient>
  <filter id="cardShadow" x="-10%" y="-10%" width="120%" height="130%"><feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="#0f172a" flood-opacity="0.07"/></filter>
  <filter id="modalShadow" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="18" stdDeviation="22" flood-color="#0f172a" flood-opacity="0.35"/></filter>
</defs>
${rect(0, 0, W, H, { fill: C.bg })}
${body}
</svg>`;
}
