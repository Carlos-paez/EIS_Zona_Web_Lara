import {
    W, H, C, txt, rect, line, circle, ico, chip, badge, button, iconButton,
    card, kpi, field, selectField, textarea, table, tableActionIcons,
    shell, modalFrame, svgDoc
} from './lib.mjs';

const CX = 256;
const CW = 920;

function toggle(x, y, on = true) {
    return rect(x, y, 40, 20, { fill: on ? C.primary : C.border, r: 10 })
        + circle(on ? x + 30 : x + 10, y + 10, 7, { fill: '#fff' });
}

/* ============================== 25 · USUARIOS LISTADO ============================== */
function usuariosListadoScene() {
    let b = '';
    const kpis = [
        ['Total Usuarios', '12', 'Registrados en el sistema', C.primary, 'people'],
        ['Activos', '10', 'Con acceso al sistema', C.success, 'check'],
        ['Inactivos', '2', 'Sin acceso', C.warning, 'lock'],
        ['Administradores', '2', 'Con permisos totales', C.info, 'shield']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 430, { label: 'Buscar usuario', placeholder: 'Buscar por nombre, email o rol...', icon: 'search' });
    b += selectField(CX + 462, 196, 180, { label: 'Rol', value: 'Todos' });
    b += button(CX + 768, 196, 134, 46, 'Nuevo Usuario', { icon: 'plus', r: 23, size: 11.5 });

    b += card(CX, 260, CW, 478, {});
    b += ico('people', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Usuarios del Sistema', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '12 usuarios', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['Ana Rodríguez', 'ana.rodriguez@eis.com', { badge: { text: 'Administrador', bg: C.primarySoft, fg: C.primary } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'arodriguez'],
        ['Luis Martínez', 'luis.martinez@eis.com', { badge: { text: 'Vendedor', bg: C.infoSoft, fg: C.info } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'lmartinez'],
        ['Carla Sánchez', 'carla.sanchez@eis.com', { badge: { text: 'Vendedor', bg: C.infoSoft, fg: C.info } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'csanchez'],
        ['Pedro Gómez', 'pedro.gomez@eis.com', { badge: { text: 'Almacén', bg: C.warningSoft, fg: C.warning } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'pgomez'],
        ['María Pérez', 'maria.perez@eis.com', { badge: { text: 'Asesor', bg: C.successSoft, fg: C.success } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'mperez'],
        ['Jorge Díaz', 'jorge.diaz@eis.com', { badge: { text: 'Vendedor', bg: C.infoSoft, fg: C.info } }, { badge: { text: 'Inactivo', bg: C.dangerSoft, fg: C.danger } }, 'jdiaz'],
        ['Sofía Rivas', 'sofia.rivas@eis.com', { badge: { text: 'Almacén', bg: C.warningSoft, fg: C.warning } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'srivas'],
        ['Diego Mora', 'diego.mora@eis.com', { badge: { text: 'Administrador', bg: C.primarySoft, fg: C.primary } }, { badge: { text: 'Activo', bg: C.successSoft, fg: C.success } }, 'dmora'],
        ['Valeria Ortiz', 'valeria.ortiz@eis.com', { badge: { text: 'Asesor', bg: C.successSoft, fg: C.success } }, { badge: { text: 'Inactivo', bg: C.dangerSoft, fg: C.danger } }, 'vortiz']
    ].map((r) => [...r, '']);
    b += table(CX + 18, 310, 884, {
        cols: ['USUARIO', 'EMAIL', 'ROL', 'ESTADO', 'USERNAME', 'ACCIÓN'],
        widths: [1.5, 2, 1.3, 1, 1.2, 1],
        rows, rowH: 40, size: 11
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(1082, 310 + 34 + i * 40 + 7, { extra: 'lock' });
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, 'Los permisos de cada usuario se definen por su rol en el módulo Roles y Permisos.', { size: 10.5, color: C.muted });
    return svgDoc(shell({ active: 'usuarios', title: 'Configuración', subtitle: 'Usuarios del sistema', body: b }));
}

/* ============================== 26 · NUEVO USUARIO ============================== */
function usuariosNuevoScene() {
    const x = 320, y = 76, w = 560, h = 596;
    let b = '';
    b += field(344, 140, 250, { label: 'Nombre *', placeholder: 'Ej: Ana', icon: 'user', focus: true });
    b += field(610, 140, 246, { label: 'Apellido', placeholder: 'Ej: Rodríguez', icon: 'user' });
    b += field(344, 214, 250, { label: 'Nombre de usuario *', placeholder: 'Ej: arodriguez', icon: 'tag' });
    b += field(610, 214, 246, { label: 'Email *', placeholder: 'usuario@eis.com', icon: 'mail' });
    b += field(344, 288, 250, { label: 'Contraseña (mín. 8 caracteres) *', placeholder: '••••••••', icon: 'lock', help: 'Obligatoria al crear el usuario.' });
    b += selectField(610, 288, 246, { label: 'Rol del usuario', value: 'Vendedor', icon: 'shield' });
    b += selectField(344, 362, 250, { label: 'Estado', value: 'Activo', icon: 'check' });

    b += rect(344, 428, 512, 58, { fill: C.infoSoft, r: 8 });
    b += ico('info', 358, 446, 15, C.info);
    b += txt(386, 456, 'La contraseña se guarda cifrada y no puede consultarse.', { size: 10.5 });
    b += txt(386, 472, 'Al editar un usuario puede dejarla vacía para conservar la actual.', { size: 9.5, color: C.muted });

    const footer = button(x + w - 254, y + h - 48, 110, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11.5, outline: true })
        + button(x + w - 134, y + h - 48, 110, 34, 'Guardar', { r: 18, size: 11.5, icon: 'save' });
    return svgDoc(shell({
        active: 'usuarios', title: 'Configuración', subtitle: 'Usuarios del sistema',
        body: modalFrame({ x, y, w, h, title: 'Nuevo Usuario', icon: 'user', subtitle: 'Crea una cuenta y asígnale un rol', footer }) + b
    }));
}

/* ============================== 27 · ROLES LISTADO ============================== */
function rolesListadoScene() {
    let b = '';
    const kpis = [
        ['Total Roles', '5', 'Roles registrados', C.primary, 'shield'],
        ['Permisos', '12', 'Permisos del sistema', C.success, 'lock'],
        ['Usuarios', '12', 'Usuarios en el sistema', C.warning, 'people'],
        ['Con Rol', '11', 'Usuarios con rol asignado', C.info, 'user']
    ];
    kpis.forEach(([label, value, sub, color, icon], i) => {
        b += kpi(CX + i * 233, 84, 221, 84, { label, value, sub, color, icon });
    });

    b += card(CX, 180, CW, 68, {});
    b += field(CX + 18, 196, 420, { label: 'Buscar rol', placeholder: 'Buscar rol por nombre...', icon: 'search' });
    b += selectField(CX + 452, 196, 170, { label: 'Filtro', value: 'Todos' });
    b += button(CX + 636, 196, 128, 46, 'Asignar Rol', { bg: C.success, icon: 'users', r: 23, size: 11 });
    b += button(CX + 774, 196, 128, 46, 'Nuevo Rol', { icon: 'plus', r: 23, size: 11 });

    b += card(CX, 260, CW, 478, {});
    b += ico('shield', CX + 18, 278, 18, C.primary);
    b += txt(CX + 44, 294, 'Roles del Sistema', { size: 15, weight: 700 });
    b += txt(CX + 902, 294, '5 roles', { size: 10.5, color: C.muted, anchor: 'end' });

    const rows = [
        ['Administrador', 'Acceso total a todos los módulos', { t: '2', align: 'center' }, '01/09/2026', ''],
        ['Vendedor', 'Ventas, clientes y consulta de inventario', { t: '4', align: 'center' }, '01/09/2026', ''],
        ['Almacén', 'Inventario, proveedores y órdenes de compra', { t: '2', align: 'center' }, '03/09/2026', ''],
        ['Asesor', 'Asesoría legal y consulta de clientes', { t: '2', align: 'center' }, '05/09/2026', ''],
        ['Supervisor', 'Reportes y consulta general', { t: '1', align: 'center' }, '12/09/2026', '']
    ];
    b += table(CX + 18, 310, 884, {
        cols: ['ROL', 'DESCRIPCIÓN', 'USUARIOS', 'CREADO', 'ACCIÓN'],
        widths: [1.4, 3, 1, 1.2, 1.1],
        rows, rowH: 46, size: 11.5
    });
    rows.forEach((_, i) => {
        b += tableActionIcons(1050, 310 + 34 + i * 46 + 10, { extra: 'shield' });
    });
    b += line(CX + 18, 700, CX + 902, 700, { stroke: C.borderLight });
    b += txt(CX + 18, 722, 'El icono de escudo abre la edición de permisos del rol.', { size: 10.5, color: C.muted });
    return svgDoc(shell({ active: 'roles', title: 'Roles y Permisos', subtitle: 'Perfiles de acceso al sistema', body: b }));
}

/* ============================== 28 · ROLES PERMISOS ============================== */
function rolesPermisosScene() {
    const x = 300, y = 58, w = 600, h = 648;
    let b = '';
    const mods = [
        ['Dashboard', 'Ver el panel de control', true],
        ['Inventario', 'Gestionar productos y categorías', true],
        ['Ventas (POS)', 'Registrar ventas y clientes', true],
        ['Clientes', 'Crear y editar clientes', true],
        ['Órdenes de Compra', 'Gestionar solicitudes a proveedores', true],
        ['Proveedores', 'Administrar proveedores', true],
        ['Cyber', 'Controlar estaciones y sesiones', true],
        ['Reportes', 'Consultar y exportar reportes', true],
        ['Activos', 'Gestionar bienes de la empresa', true],
        ['Asesoría Legal', 'Registrar y validar asesorías', true],
        ['Usuarios', 'Administrar cuentas del sistema', true],
        ['Roles y Permisos', 'Definir perfiles y permisos', true]
    ];
    mods.forEach(([name, desc, on], i) => {
        const ry = 142 + i * 40;
        b += rect(x + 24, ry, w - 48, 34, { fill: i % 2 === 0 ? '#fff' : '#fbfcfe', r: 6 });
        b += txt(x + 36, ry + 14, name, { size: 11, weight: 700 });
        b += txt(x + 36, ry + 27, desc, { size: 8.5, color: C.muted });
        b += toggle(x + w - 74, ry + 7, on);
    });

    const footer = button(x + w - 288, y + h - 48, 120, 34, 'Cancelar', { bg: '#fff', fg: C.text, r: 18, size: 11, outline: true })
        + button(x + w - 158, y + h - 48, 134, 34, 'Guardar Permisos', { r: 18, size: 11, icon: 'save' });
    return svgDoc(shell({
        active: 'roles', title: 'Roles y Permisos', subtitle: 'Perfiles de acceso al sistema',
        body: modalFrame({
            x, y, w, h, title: 'Permisos: Administrador', icon: 'shield',
            subtitle: 'Selecciona los módulos a los que este rol tendrá acceso', footer
        }) + b
    }));
}

/* ============================== 29 · CONSEJOS DE SEGURIDAD ============================== */
function consejosSeguridadScene() {
    let b = '';
    b += rect(CX, 84, CW, 104, { fill: 'url(#heroGrad)', r: 12 });
    b += ico('book', CX + 26, 112, 34, '#fff');
    b += txt(CX + 76, 120, 'Manual de Usuario', { size: 21, weight: 800, color: '#fff' });
    b += txt(CX + 76, 144, 'Consejos generales y buenas prácticas de seguridad', { size: 11.5, color: '#e8eaf6' });
    b += chip(CX + 760, 112, 'Buenas prácticas', { bg: '#fff', fg: C.primary, icon: 'shield', size: 10, h: 26, w: 138 });

    const tips = [
        ['lock', 'Contraseña segura', 'Use al menos 8 caracteres con mayúsculas, números y símbolos. No comparta su clave.', C.primary],
        ['logout', 'Cierre la sesión', 'Al terminar, presione "Cerrar Sesión". Especialmente en equipos compartidos del cyber.', C.danger],
        ['shield', 'Rol mínimo necesario', 'Asigne a cada usuario solo los permisos que necesita para su cargo.', C.success],
        ['user', 'Datos personales', 'Registre solo la información necesaria de clientes y ciudadanos. No la difunda.', C.info],
        ['save', 'Respaldos periódicos', 'Solicite al administrador técnico copias de seguridad de la base de datos.', C.warning],
        ['warning', 'Reporte los errores', 'Anote la pantalla y el mensaje mostrado. Informe al administrador del sistema.', '#7c4dff']
    ];
    tips.forEach(([icon, title, text, color], i) => {
        const col = i % 3;
        const row = Math.floor(i / 3);
        const x = CX + col * 311;
        const y = 208 + row * 168;
        b += rect(x, y, 298, 150, { fill: C.surface, stroke: C.border, r: 12, filter: 'cardShadow' });
        b += circle(x + 34, y + 34, 20, { fill: color, opacity: 0.12 });
        b += ico(icon, x + 23, y + 23, 22, color);
        b += txt(x + 64, y + 34, title, { size: 12.5, weight: 700 });
        const words = text.split(' ');
        let lineTxt = '';
        let ly = y + 62;
        words.forEach((word) => {
            if ((lineTxt + ' ' + word).trim().length > 38) {
                b += txt(x + 18, ly, lineTxt.trim(), { size: 9.5, color: C.muted });
                lineTxt = word;
                ly += 15;
            } else {
                lineTxt += ' ' + word;
            }
        });
        if (lineTxt.trim()) b += txt(x + 18, ly, lineTxt.trim(), { size: 9.5, color: C.muted });
    });

    b += rect(CX, 552, CW, 76, { fill: C.warningSoft, stroke: '#ffe0b2', r: 10 });
    b += ico('warning', CX + 22, 576, 24, C.warning);
    b += txt(CX + 58, 580, 'Recuerde: cada acción queda registrada con el usuario de la sesión.', { size: 12, weight: 700 });
    b += txt(CX + 58, 600, 'Si detecta un acceso no autorizado, cambie la contraseña y notifique de inmediato al administrador.', { size: 10.5, color: C.muted });

    b += rect(CX, 644, CW, 94, { fill: C.surface, stroke: C.border, r: 10 });
    b += ico('info', CX + 22, 664, 20, C.info);
    b += txt(CX + 52, 680, '¿Necesita ayuda con un módulo?', { size: 12.5, weight: 700 });
    b += txt(CX + 52, 700, 'Consulte la sección correspondiente en el índice del manual o contacte al administrador del sistema.', { size: 10.5, color: C.muted });
    b += button(CX + 760, 668, 136, 36, 'Volver al índice', { bg: '#fff', fg: C.primary, outline: true, r: 18, size: 10.5 });

    return svgDoc(shell({ active: 'manual_usuario', title: 'Manual de Usuario', subtitle: 'Consejos generales y seguridad', body: b }));
}

export const scenes4 = [
    { name: '25-usuarios-listado', svg: usuariosListadoScene() },
    { name: '26-usuarios-nuevo', svg: usuariosNuevoScene() },
    { name: '27-roles-listado', svg: rolesListadoScene() },
    { name: '28-roles-permisos', svg: rolesPermisosScene() },
    { name: '29-consejos-seguridad', svg: consejosSeguridadScene() }
];
