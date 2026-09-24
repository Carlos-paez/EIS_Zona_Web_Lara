<!-- ============================================================
     VISTA: MANUAL DE USUARIO (EIS SYSTEM)
     Página estática de ayuda/documentación incrustada en el sistema.
     Muestra, por módulo, cómo usar cada función del sistema.
     ============================================================ -->

<style>
    .manual-hero {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        border-radius: var(--radius);
        padding: 1.75rem 1.5rem;
        color: #fff;
        margin-bottom: 1.5rem;
    }
    .manual-hero h2 { margin: 0; font-weight: 700; font-size: 1.6rem; color: #fff; }
    .manual-hero p { margin: 0.35rem 0 0; opacity: 0.9; font-size: 0.95rem; }
    .manual-section {
        scroll-margin-top: 90px;
        margin-bottom: 1.25rem;
    }
    .manual-section .card { border-radius: var(--radius); }
    .manual-section .card-title {
        font-size: 1.15rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .manual-section .card-title i { color: var(--primary); }
    [data-theme="dark"] .manual-section .card-title i { color: var(--primary-light); }
    .manual-toc a { display: flex; align-items: center; gap: 0.5rem; }
    .manual-toc .material-icons { color: var(--primary); font-size: 1.15rem; }
    [data-theme="dark"] .manual-toc .material-icons { color: var(--primary-light); }
    .manual-step {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        margin-bottom: 0.6rem;
    }
    .manual-step .step-num {
        flex: 0 0 26px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 0.15rem;
    }
    [data-theme="dark"] .manual-step .step-num { background: var(--primary-light); color: #1e1e1e; }
    .manual-tip {
        display: flex;
        gap: 0.65rem;
        align-items: flex-start;
        background: #e8f1fb;
        border-left: 4px solid var(--info);
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
    [data-theme="dark"] .manual-tip { background: #1e2a3a; }
    .manual-tip i { color: var(--info); margin-top: 0.1rem; }
    .manual-warn {
        display: flex;
        gap: 0.65rem;
        align-items: flex-start;
        background: #fff3e0;
        border-left: 4px solid var(--warning);
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
    [data-theme="dark"] .manual-warn { background: #33291a; }
    .manual-warn i { color: var(--warning); margin-top: 0.1rem; }
    .manual-table th { color: var(--primary); }
    [data-theme="dark"] .manual-table th { color: var(--primary-light); }
    @media only screen and (max-width: 600px) {
        .manual-hero h2 { font-size: 1.3rem; }
    }
</style>

<!-- ===== ENCABEZADO ===== -->
<div class="manual-hero">
    <h2><i class="material-icons" style="vertical-align:middle;margin-right:0.25rem;">menu_book</i> Manual de Usuario</h2>
    <p>Guía práctica de uso del Sistema de Gestión Integral <strong>EIS System</strong> (Zona Web Lara)</p>
    <p style="font-size:0.85rem;opacity:0.8;margin-top:0.4rem;">Versión 1.0 · Septiembre 2026</p>
</div>

<!-- ===== ÍNDICE ===== -->
<div class="card manual-toc">
    <div class="card-content">
        <span class="card-title" style="font-size:1.05rem;"><i class="material-icons">toc</i> Contenido</span>
        <ul class="collection" style="border:0;">
            <li class="collection-item"><a href="#inicio-sesion"><i class="material-icons">lock_outline</i> 1. Iniciar sesión</a></li>
            <li class="collection-item"><a href="#dashboard"><i class="material-icons">dashboard</i> 2. Panel de control (Dashboard)</a></li>
            <li class="collection-item"><a href="#inventario"><i class="material-icons">inventory_2</i> 3. Inventario</a></li>
            <li class="collection-item"><a href="#ventas"><i class="material-icons">shopping_cart</i> 4. Ventas (Punto de Venta)</a></li>
            <li class="collection-item"><a href="#clientes"><i class="material-icons">badge</i> 5. Clientes</a></li>
            <li class="collection-item"><a href="#solicitudes"><i class="material-icons">request_quote</i> 6. Solicitudes (Órdenes de compra y Proveedores)</a></li>
            <li class="collection-item"><a href="#cyber"><i class="material-icons">computer</i> 7. Control de Ciber (Cybercafé)</a></li>
            <li class="collection-item"><a href="#reportes"><i class="material-icons">bar_chart</i> 8. Reportes</a></li>
            <li class="collection-item"><a href="#activos"><i class="material-icons">build</i> 9. Activos Fijos</a></li>
            <li class="collection-item"><a href="#asesorias"><i class="material-icons">gavel</i> 10. Asesoría Legal</a></li>
            <li class="collection-item"><a href="#configuracion"><i class="material-icons">settings</i> 11. Configuración (Usuarios y Roles)</a></li>
            <li class="collection-item"><a href="#consejos"><i class="material-icons">lightbulb</i> 12. Consejos generales y seguridad</a></li>
        </ul>
    </div>
</div>

<!-- ===== 1. INICIO DE SESIÓN ===== -->
<div class="manual-section card" id="inicio-sesion">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">lock_outline</i> 1. Iniciar sesión</span>
        <p>Para acceder al sistema es necesario autenticarse con un usuario y contraseña asignados por el administrador.</p>
        <div class="manual-step"><span class="step-num">1</span><span>Abra la dirección del sistema en su navegador (Chrome, Edge o Firefox).</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>En la pantalla de <strong>Inicio de Sesión</strong>, escriba su <strong>nombre de usuario</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Escriba su <strong>contraseña</strong>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione el botón <span class="chip">Ingresar</span>.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si los datos son correctos entrará al <strong>Panel de control</strong>. Si son incorrectos aparecerá un mensaje de error; vuelva a intentarlo.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>La contraseña no se muestra mientras se escribe por seguridad. Verifique que no esté activada la tecla <strong>Bloq Mayús</strong>.</span></div>
    </div>
</div>

<!-- ===== 2. DASHBOARD ===== -->
<div class="manual-section card" id="dashboard">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">dashboard</i> 2. Panel de control (Dashboard)</span>
        <p>Es la página principal tras iniciar sesión. Resume la actividad del negocio en tiempo real:</p>
        <ul class="collection">
            <li class="collection-item"><strong>Ventas Hoy:</strong> total en dinero y número de transacciones del día.</li>
            <li class="collection-item"><strong>Stock Crítico:</strong> cantidad de productos por debajo del mínimo, que deben reabastecerse.</li>
            <li class="collection-item"><strong>Sesiones Cyber:</strong> estaciones de cibercafé ocupadas en este momento.</li>
            <li class="collection-item"><strong>Solicitudes Pendientes:</strong> peticiones de compra a proveedores sin atender.</li>
            <li class="collection-item"><strong>Ventas por Día / Productos sin Stock / Actividad Reciente.</strong></li>
        </ul>
        <div class="manual-tip"><i class="material-icons">info</i><span>Use el menú lateral izquierdo para navegar entre los módulos. El icono <i class="material-icons" style="font-size:1rem;">menu</i> en la parte superior abre y cierra el menú en pantallas pequeñas.</span></div>
    </div>
</div>

<!-- ===== 3. INVENTARIO ===== -->
<div class="manual-section card" id="inventario">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">inventory_2</i> 3. Inventario</span>
        <p>Gestiona los productos, su stock y su precio de venta.</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Inventario</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Verá la tabla de productos: código, nombre, descripción, stock y precio.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>agregar un producto</strong> presione <span class="chip">Nuevo Producto</span> y complete el formulario.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Para <strong>editar</strong> use el botón de edición en la fila del producto; para <strong>eliminar</strong>, el botón de eliminar (el producto se desactiva y se oculta del listado).</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Use la <strong>barra de búsqueda</strong> sobre la tabla para filtrar por nombre o código.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Cada vez que se registra una venta el stock se descuenta automáticamente. Si un producto alcanza el mínimo, aparecerá en el <strong>Stock Crítico</strong> del Dashboard.</span></div>
    </div>
</div>

<!-- ===== 4. VENTAS ===== -->
<div class="manual-section card" id="ventas">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">shopping_cart</i> 4. Ventas (Punto de Venta)</span>
        <p>Registra las ventas de productos de forma rápida, descontando el stock automáticamente.</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Ventas (POS)</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Busque el producto en la lista o en el buscador y presione el botón <span class="chip">Añadir</span> para agregarlo al carrito.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Indique la <strong>cantidad</strong> de cada artículo.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Diligencie los datos del cliente: <strong>nombre completo</strong> y <strong>cédula</strong> (obligatorios).</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Presione <span class="chip">Registrar Venta</span>. El sistema genera el número de orden, guarda las líneas y descuenta el stock.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si la cédula ya existe, el sistema actualiza los datos opcionales (dirección y teléfono) sin duplicar el cliente.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No puede regresar el botón <strong>Actualizar/Recargar</strong> después de registrar la venta, ya que podría duplicarla.</span></div>
    </div>
</div>

<!-- ===== 5. CLIENTES ===== -->
<div class="manual-section card" id="clientes">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">badge</i> 5. Clientes</span>
        <p>Administra el registro de clientes del negocio.</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Clientes</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Para <strong>registrar un cliente nuevo</strong> presione <span class="chip">Nuevo Cliente</span> y complete: cédula, nombre, apellido, dirección y teléfono.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>modificar</strong> un cliente use el botón de edición de su fila.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Para <strong>eliminar</strong>, use el botón de eliminar: el cliente quedará oculto pero su historial se conserva.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>El módulo de <strong>Ventas</strong>, <strong>Ciber</strong> y <strong>Asesoría Legal</strong> reutilizan la misma base de clientes: no es necesario registrarlos dos veces.</span></div>
    </div>
</div>

<!-- ===== 6. SOLICITUDES ===== -->
<div class="manual-section card" id="solicitudes">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">request_quote</i> 6. Solicitudes (Órdenes de compra y Proveedores)</span>
        <p>El menú <strong>Solicitudes</strong> del menú lateral agrupa dos opciones:</p>
        <ul class="collection">
            <li class="collection-item"><strong>Órdenes de Compra:</strong> registrar solicitudes de productos faltantes a abastecer. Complete el proveedor, producto y cantidad; la solicitud queda <em>pendiente</em> hasta su gestión.</li>
            <li class="collection-item"><strong>Proveedores:</strong> mantener actualizado el catálogo de proveedores (nombre, RIF, teléfono, correo y productos que suministran), con sus opciones de alta, edición y baja.</li>
        </ul>
        <div class="manual-tip"><i class="material-icons">info</i><span>Vincule cada orden de compra con el proveedor correcto para que el reporte de abastecimiento sea confiable.</span></div>
    </div>
</div>

<!-- ===== 7. CIBER ===== -->
<div class="manual-section card" id="cyber">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">computer</i> 7. Control de Ciber (Cybercafé)</span>
        <p>Controla las estaciones (PC) del cibercafé: quién las usa, cuánto tiempo y cuánto cobrar.</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Cyber</strong>. Verá las estaciones con su estado: <span class="chip green white-text">Disponible</span> u <span class="chip orange white-text">Ocupada</span>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Para <strong>iniciar una sesión</strong> seleccione una PC disponible, elija el cliente (o créelo), el tiempo y la tarifa.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>finalizar</strong>, presione el botón de cerrar sesión: el sistema calcula el tiempo usado y el costo a cobrar.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Consulte el <strong>historial</strong> de cada estación para ver sesiones anteriores.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>La opción de gestión permite <strong>crear, editar, activar/desactivar o eliminar</strong> estaciones (PC).</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Los indicadores <strong>Disponibles / Ocupadas</strong> junto al título se actualizan en tiempo real.</span></div>
    </div>
</div>

<!-- ===== 8. REPORTES ===== -->
<div class="manual-section card" id="reportes">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">bar_chart</i> 8. Reportes</span>
        <p>Consultas y estadísticas del negocio para la toma de decisiones.</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Reportes</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Seleccione el tipo de reporte (ventas, sesiones de cyber, inventario, asesorías, entre otros).</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Defina el <strong>rango de fechas</strong> si el reporte lo permite.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Use los botones de <strong>exportación</strong> para descargar los resultados (PDF/Excel) y presentarlos o archivarlos.</span></div>
    </div>
</div>

<!-- ===== 9. ACTIVOS ===== -->
<div class="manual-section card" id="activos">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">build</i> 9. Activos Fijos</span>
        <p>Registra y controla los bienes de la empresa (equipos, mobiliario, etc.).</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Activos</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Use <span class="chip">Nuevo Activo</span> para registrar un bien: descripción, marca y tipo.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Consulte el listado, <strong>edite</strong> los datos o <strong>desactive</strong> activos que ya no estén operativos.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Las PC del cibercafé también son activos: al marcarlas como <em>ciber</em> aparecen en el módulo <strong>Cyber</strong>.</span></div>
    </div>
</div>

<!-- ===== 10. ASESORÍA LEGAL ===== -->
<div class="manual-section card" id="asesorias">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">gavel</i> 10. Asesoría Legal</span>
        <p>Registra y da seguimiento a las asesorías legales atendidas.</p>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Asesoría Legal</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Presione <span class="chip">Nueva Asesoría</span> e ingrese el nombre del ciudadano, su cédula, el tipo de asesoría y la descripción.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Consulte las asesorías <strong>Permitidas</strong> o <strong>Denegadas</strong> mediante los filtros.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Para dar seguimiento, <strong>edite</strong> el documento o la descripción de una asesoría, o <strong>elimínela</strong> si fue registrada por error.</span></div>
    </div>
</div>

<!-- ===== 11. CONFIGURACIÓN ===== -->
<div class="manual-section card" id="configuracion">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">settings</i> 11. Configuración (Usuarios y Roles)</span>
        <p>Solo el personal con permiso de configuración accede a esta área.</p>
        <ul class="collection">
            <li class="collection-item"><strong>Configuración (Usuarios):</strong> crear usuarios, asignar rol, activar/desactivar y cambiar contraseñas.</li>
            <li class="collection-item"><strong>Roles y Permisos:</strong> definir qué permisos tiene cada rol, determinando qué módulos puede ver y editar cada perfil.</li>
        </ul>
        <div class="manual-step"><span class="step-num">1</span><span>En <strong>Usuarios</strong> presione <span class="chip">Nuevo Usuario</span>, complete los datos y asigne el rol.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>La contraseña se almacena cifrada (nadie, ni el administrador, puede verla). Si se olvida, debe restablecerse.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>En <strong>Roles y Permisos</strong> seleccione un rol y marque/desmarque los permisos según el cargo.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Si un usuario no ve un módulo en el menú, es porque su rol no tiene el permiso correspondiente. Contacte al administrador.</span></div>
    </div>
</div>

<!-- ===== 12. CONSEJOS ===== -->
<div class="manual-section card" id="consejos">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">lightbulb</i> 12. Consejos generales y seguridad</span>
        <div class="manual-table">
            <table class="highlight">
                <thead>
                    <tr><th>Acción</th><th>Cómo se hace</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Cerrar sesión</strong></td><td>Presione <em>Cerrar Sesión</em> al final del menú lateral, especialmente en equipos compartidos.</td></tr>
                    <tr><td><strong>Modo oscuro / claro</strong></td><td>Use la opción <em>Modo Oscuro</em> del menú lateral para alternar el tema de toda la aplicación.</td></tr>
                    <tr><td><strong>Seguir mis tareas</strong></td><td>Antes de ingresar datos, revise el <strong>Dashboard</strong>: stock crítico, solicitudes pendientes y sesiones activas.</td></tr>
                    <tr><td><strong>Trabajar sin conexión</strong></td><td>El sistema funciona como app instalable (PWA). Puede instalarlo y un <em>Service Worker</em> guarda las páginas vistas en caché.</td></tr>
                    <tr><td><strong>Protección de datos</strong></td><td>No comparta su usuario ni contraseña con otras personas. Cada sesión queda registrada con su nombre.</td></tr>
                    <tr><td><strong>Errores</strong></td><td>Si algo falla, anote la pantalla y el mensaje mostrado y consulte con el administrador técnico del sistema.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Para dudas sobre módulos específicos consulte también la documentación técnica del proyecto (<em>docs/</em>) o la guía de defensa del proyecto.</span></div>
    </div>
</div>