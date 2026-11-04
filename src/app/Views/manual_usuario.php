<!-- ============================================================
     VISTA: MANUAL DE USUARIO (EIS SYSTEM)
     Guía paso a paso de todos los módulos del sistema.
     Las ilustraciones son renders vectoriales ubicados en
     Public/img/manual/ y se abren en tamaño completo al hacer clic.
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
    .manual-hero p { margin: 0.35rem 0 0; opacity: 0.92; font-size: 0.95rem; }
    .manual-hero .manual-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.85rem;
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 0.78rem;
        letter-spacing: 0.02em;
    }
    .manual-hero .manual-meta i { font-size: 1rem; }

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
        line-height: 1.35;
    }
    .manual-section .card-title i { color: var(--primary); }
    [data-theme="dark"] .manual-section .card-title i { color: var(--primary-light); }
    .manual-section .card-content > p:first-of-type { margin-top: 0.35rem; }

    .manual-subtitle {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin: 1.4rem 0 0.55rem;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
    }
    .manual-subtitle i { font-size: 1.15rem; color: var(--primary); }
    [data-theme="dark"] .manual-subtitle i { color: var(--primary-light); }

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
    .manual-step > span:last-child { flex: 1 1 auto; }

    .manual-tip,
    .manual-warn {
        display: flex;
        gap: 0.65rem;
        align-items: flex-start;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        margin-top: 0.65rem;
    }
    .manual-tip {
        background: #e8f1fb;
        border-left: 4px solid var(--info);
    }
    .manual-warn {
        background: #fff3e0;
        border-left: 4px solid var(--warning);
    }
    [data-theme="dark"] .manual-tip { background: #1e2a3a; }
    [data-theme="dark"] .manual-warn { background: #33291a; }
    .manual-tip i { color: var(--info); margin-top: 0.1rem; }
    .manual-warn i { color: var(--warning); margin-top: 0.1rem; }
    .manual-tip > span,
    .manual-warn > span { flex: 1 1 auto; }

    .manual-toc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
        gap: 0.6rem;
        margin-top: 0.75rem;
    }
    .manual-toc-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.7rem 0.8rem;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        background: var(--surface);
        color: var(--text);
        transition: var(--transition);
    }
    .manual-toc-item:hover,
    .manual-toc-item:focus {
        border-color: var(--primary);
        background: var(--surface-hover);
        transform: translateY(-1px);
    }
    .manual-toc-item > i { color: var(--primary); font-size: 1.35rem; margin-top: 0.1rem; }
    [data-theme="dark"] .manual-toc-item > i { color: var(--primary-light); }
    .manual-toc-item strong { display: block; font-size: 0.9rem; line-height: 1.3; }
    .manual-toc-item small { display: block; margin-top: 0.15rem; color: var(--text-muted); font-size: 0.76rem; line-height: 1.35; }

    .manual-figure { margin: 0.85rem 0 1.3rem; }
    .manual-figure > a {
        display: block;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        background: var(--surface);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }
    .manual-figure > a:hover,
    .manual-figure > a:focus {
        border-color: var(--primary);
        box-shadow: var(--shadow);
    }
    .manual-figure img {
        display: block;
        width: 100%;
        height: auto;
    }
    .manual-figure figcaption {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.25rem 0.6rem;
        margin-top: 0.5rem;
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.45;
    }
    .manual-figure .fig-num { font-weight: 700; color: var(--primary); }
    [data-theme="dark"] .manual-figure .fig-num { color: var(--primary-light); }
    .manual-figure .fig-open {
        margin-left: auto;
        font-weight: 600;
        color: var(--primary);
        white-space: nowrap;
    }
    [data-theme="dark"] .manual-figure .fig-open { color: var(--primary-light); }
    .manual-figure .fig-open i { font-size: 0.95rem; vertical-align: middle; }

    .manual-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(215px, 1fr));
        gap: 0.7rem;
        margin: 0.75rem 0;
    }
    .manual-box {
        border: 1px solid var(--border-light);
        border-radius: 8px;
        padding: 0.75rem 0.9rem;
        background: var(--surface-hover);
    }
    .manual-box strong { display: block; margin-bottom: 0.2rem; font-size: 0.9rem; }
    .manual-box span { display: block; font-size: 0.83rem; color: var(--text-muted); line-height: 1.45; }

    .manual-table { overflow-x: auto; }
    .manual-table table { min-width: 560px; }
    .manual-table th { color: var(--primary); }
    [data-theme="dark"] .manual-table th { color: var(--primary-light); }
    .manual-table td { vertical-align: top; }

    .manual-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.05rem 0.5rem;
        border-radius: 999px;
        background: var(--surface-hover);
        border: 1px solid var(--border-light);
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
    }
    .manual-chip.ok { color: #1b5e20; background: #e8f5e9; border-color: #c8e6c9; }
    .manual-chip.bad { color: #b71c1c; background: #ffebee; border-color: #ffcdd2; }
    .manual-chip.warn { color: #e65100; background: #fff3e0; border-color: #ffe0b2; }
    [data-theme="dark"] .manual-chip.ok { background: #14301f; border-color: #1f5132; color: #86efac; }
    [data-theme="dark"] .manual-chip.bad { background: #3a1a1e; border-color: #6b2630; color: #fca5a5; }
    [data-theme="dark"] .manual-chip.warn { background: #3a2a12; border-color: #6b4a1c; color: #fcd34d; }

    @media only screen and (max-width: 600px) {
        .manual-hero { padding: 1.35rem 1.1rem; }
        .manual-hero h2 { font-size: 1.3rem; }
        .manual-figure .fig-open { margin-left: 0; }
        .manual-step { gap: 0.55rem; }
        .manual-table table { min-width: 480px; }
    }
</style>

<!-- ===== ENCABEZADO ===== -->
<div class="manual-hero">
    <h2><i class="material-icons" style="vertical-align:middle;margin-right:0.25rem;">menu_book</i> Manual de Usuario</h2>
    <p>Guía paso a paso del Sistema de Gestión Integral <strong>EIS System</strong> (Zona Web Lara)</p>
    <span class="manual-meta"><i class="material-icons">verified</i> Versión 1.1 · Noviembre 2026 · 29 ilustraciones</span>
</div>

<!-- ===== ÍNDICE ===== -->
<div class="card manual-toc">
    <div class="card-content">
        <span class="card-title" style="font-size:1.05rem;"><i class="material-icons">toc</i> Contenido</span>
        <div class="manual-toc-grid">
            <a class="manual-toc-item" href="#acceso">
                <i class="material-icons">lock_open</i>
                <span><strong>1. Acceso y navegación</strong><small>Inicio de sesión, menú lateral y cierre de sesión</small></span>
            </a>
            <a class="manual-toc-item" href="#dashboard">
                <i class="material-icons">dashboard</i>
                <span><strong>2. Panel de control</strong><small>Indicadores, ventas, stock y actividad reciente</small></span>
            </a>
            <a class="manual-toc-item" href="#inventario">
                <i class="material-icons">inventory_2</i>
                <span><strong>3. Inventario</strong><small>Productos, stock, precios y categorías</small></span>
            </a>
            <a class="manual-toc-item" href="#ventas">
                <i class="material-icons">point_of_sale</i>
                <span><strong>4. Ventas (POS)</strong><small>Catálogo, carrito, cliente y cobro</small></span>
            </a>
            <a class="manual-toc-item" href="#clientes">
                <i class="material-icons">badge</i>
                <span><strong>5. Clientes</strong><small>Registro, edición y eliminación</small></span>
            </a>
            <a class="manual-toc-item" href="#ordenes">
                <i class="material-icons">request_quote</i>
                <span><strong>6. Órdenes de compra</strong><small>Solicitudes, estados y líneas de producto</small></span>
            </a>
            <a class="manual-toc-item" href="#proveedores">
                <i class="material-icons">storefront</i>
                <span><strong>7. Proveedores</strong><small>Catálogo de proveedores y contacto</small></span>
            </a>
            <a class="manual-toc-item" href="#cyber">
                <i class="material-icons">computer</i>
                <span><strong>8. Control de Ciber</strong><small>Estaciones, sesiones, cobro e historial</small></span>
            </a>
            <a class="manual-toc-item" href="#reportes">
                <i class="material-icons">bar_chart</i>
                <span><strong>9. Reportes</strong><small>Filtros, consultas y exportación de archivos</small></span>
            </a>
            <a class="manual-toc-item" href="#activos">
                <i class="material-icons">devices_other</i>
                <span><strong>10. Activos fijos</strong><small>Equipos, mobiliario y estaciones de cyber</small></span>
            </a>
            <a class="manual-toc-item" href="#asesorias">
                <i class="material-icons">gavel</i>
                <span><strong>11. Asesoría legal</strong><small>Validación de documentos y registro</small></span>
            </a>
            <a class="manual-toc-item" href="#usuarios">
                <i class="material-icons">manage_accounts</i>
                <span><strong>12. Usuarios</strong><small>Altas, roles, estado y contraseñas</small></span>
            </a>
            <a class="manual-toc-item" href="#roles">
                <i class="material-icons">admin_panel_settings</i>
                <span><strong>13. Roles y permisos</strong><small>Permisos por módulo y asignación de roles</small></span>
            </a>
            <a class="manual-toc-item" href="#consejos">
                <i class="material-icons">security</i>
                <span><strong>14. Consejos y seguridad</strong><small>Buenas prácticas y referencia rápida</small></span>
            </a>
        </div>
    </div>
</div>

<!-- ===== 1. ACCESO Y NAVEGACIÓN ===== -->
<div class="manual-section card" id="acceso">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">lock_open</i> 1. Acceso y navegación</span>
        <p>Antes de usar cualquier módulo debe autenticarse con un usuario y una contraseña asignados por el administrador. El sistema abre siempre en el <strong>Panel de control</strong>.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/01-login.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/01-login.svg" alt="Pantalla de inicio de sesión de EIS System con campos Usuario y Contraseña" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 1.</span> Pantalla de inicio de sesión. <a class="fig-open" href="Public/img/manual/01-login.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">login</i> Iniciar sesión</div>
        <div class="manual-step"><span class="step-num">1</span><span>Abra en el navegador la dirección del sistema (Chrome, Edge o Firefox actualizados).</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Escriba su <strong>Usuario</strong> en el primer campo.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Escriba su <strong>Contraseña</strong>. Por seguridad, los caracteres se muestran ocultos.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione <span class="manual-chip">Iniciar Sesión</span>. Si los datos son correctos entrará al Panel de control.</span></div>

        <div class="manual-tip"><i class="material-icons">info</i><span>El botón del icono de luna en la esquina superior derecha cambia entre <strong>modo claro y oscuro</strong>. La preferencia se conserva en el navegador.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Si aparece un mensaje de credenciales incorrectas, revise que <strong>Bloq Mayús</strong> no esté activado y que el usuario esté activo. Tras varios intentos fallidos, contacte al administrador.</span></div>

        <div class="manual-subtitle"><i class="material-icons">menu</i> Recorrer el sistema</div>
        <p>El <strong>menú lateral izquierdo</strong> es la vía principal de navegación. En pantallas pequeñas se abre con el botón de menú de la barra superior.</p>
        <div class="manual-grid">
            <div class="manual-box"><strong>Dashboard</strong><span>Resumen general del negocio.</span></div>
            <div class="manual-box"><strong>Inventario</strong><span>Productos, stock y categorías.</span></div>
            <div class="manual-box"><strong>Ventas</strong><span>Punto de venta y cobro.</span></div>
            <div class="manual-box"><strong>Clientes</strong><span>Base de clientes compartida.</span></div>
            <div class="manual-box"><strong>Solicitudes</strong><span>Submenú con <em>Órdenes de Compra</em> y <em>Proveedores</em>.</span></div>
            <div class="manual-box"><strong>Cyber Control</strong><span>Estaciones y sesiones del cibercafé.</span></div>
            <div class="manual-box"><strong>Reportes</strong><span>Consultas y exportaciones.</span></div>
            <div class="manual-box"><strong>Activos</strong><span>Bienes y equipos de la empresa.</span></div>
            <div class="manual-box"><strong>Asesoría Legal</strong><span>Registro y validación de casos.</span></div>
            <div class="manual-box"><strong>Configuración</strong><span>Usuarios del sistema.</span></div>
            <div class="manual-box"><strong>Roles y Permisos</strong><span>Acceso por perfil.</span></div>
            <div class="manual-box"><strong>Manual de Usuario</strong><span>Esta guía, disponible en todo momento.</span></div>
        </div>
        <div class="manual-step"><span class="step-num">1</span><span>Para desplegar <strong>Solicitudes</strong>, haga clic en la opción: se mostrarán <em>Órdenes de Compra</em> y <em>Proveedores</em>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>La opción activa se resalta con fondo índigo. El título de la página confirma el módulo en el que está.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Al terminar, use <span class="manual-chip">Cerrar Sesión</span> al final del menú, sobre todo en equipos compartidos.</span></div>

        <div class="manual-tip"><i class="material-icons">info</i><span>Si un módulo no aparece en el menú, su rol no tiene el permiso correspondiente. Solicite la asignación al administrador; no comparta credenciales para “probar” otro perfil.</span></div>
    </div>
</div>

<!-- ===== 2. DASHBOARD ===== -->
<div class="manual-section card" id="dashboard">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">dashboard</i> 2. Panel de control (Dashboard)</span>
        <p>Es la página principal tras iniciar sesión. Resume en tiempo real la operación diaria y sirve como punto de partida para detectar pendientes.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/02-dashboard.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/02-dashboard.svg" alt="Panel de control con indicadores de ventas, stock crítico, sesiones cyber y solicitudes pendientes" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 2.</span> Panel de control con los indicadores y tablas de resumen. <a class="fig-open" href="Public/img/manual/02-dashboard.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">analytics</i> Leer los indicadores</div>
        <div class="manual-grid">
            <div class="manual-box"><strong>Ventas Hoy</strong><span>Dinero cobrado y número de transacciones del día.</span></div>
            <div class="manual-box"><strong>Stock Crítico</strong><span>Productos agotados o por debajo del mínimo; requiere reposición.</span></div>
            <div class="manual-box"><strong>Sesiones Cyber</strong><span>Estaciones del cibercafé ocupadas en este momento.</span></div>
            <div class="manual-box"><strong>Solicitudes Pend.</strong><span>Órdenes de compra aún no recibidas.</span></div>
        </div>
        <div class="manual-step"><span class="step-num">1</span><span>Revise los cuatro indicadores superiores al comenzar la jornada.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Consulte <strong>Ventas por Día (7 días)</strong> para comparar la actividad de la semana.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>En <strong>Stock Crítico</strong> identifique los productos que debe reabastecer y vaya a <em>Inventario</em> u <em>Órdenes de Compra</em>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>En <strong>Actividad Reciente</strong> verifique las últimas operaciones registradas.</span></div>

        <div class="manual-tip"><i class="material-icons">info</i><span>Los indicadores se recalculan al abrir la página. Si acaba de registrar una venta o cerrar una sesión, vuelva a entrar al Dashboard para ver el dato actualizado.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>El Dashboard es informativo: no permite editar datos. Para corregir una operación, entre al módulo correspondiente.</span></div>
    </div>
</div>

<!-- ===== 3. INVENTARIO ===== -->
<div class="manual-section card" id="inventario">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">inventory_2</i> 3. Inventario</span>
        <p>Administra el catálogo de productos, su existencia, su costo y su precio de venta. Cada venta descuenta el stock automáticamente.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/03-inventario-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/03-inventario-listado.svg" alt="Listado de inventario con indicadores, buscador, filtro de estado y tabla de productos" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 3.</span> Inventario: indicadores, filtros y listado de productos. <a class="fig-open" href="Public/img/manual/03-inventario-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">search</i> Consultar y filtrar productos</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Inventario</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Revise los indicadores: <strong>Total Productos</strong>, <strong>Stock Crítico</strong>, <strong>Stock Bajo</strong> y <strong>Valor Total</strong> del inventario.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Use el buscador <em>Buscar producto</em> para localizar por nombre o código.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>En el filtro <strong>Estado</strong> elija <em>Todo</em>, <em>Stock OK</em>, <em>Crítico</em> o <em>Sin stock</em>.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>La tabla muestra el producto con su categoría, el precio de venta, el stock disponible y los botones de acción.</span></div>

        <div class="manual-subtitle"><i class="material-icons">add_box</i> Registrar un producto nuevo</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/04-inventario-nuevo-producto.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/04-inventario-nuevo-producto.svg" alt="Modal Nuevo Producto con código, nombre, descripción, categoría, stock, costo y precio" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 4.</span> Formulario <em>Nuevo Producto</em>. <a class="fig-open" href="Public/img/manual/04-inventario-nuevo-producto.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Nuevo Producto</span>, en la esquina superior derecha del listado.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Complete <strong>Código</strong> (único), <strong>Nombre del Producto</strong> y <strong>Descripción</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Seleccione la <strong>Categoría</strong>. Si no existe, créela primero desde <em>Categorías</em>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Indique <strong>Stock</strong> inicial y <strong>Stock Mínimo</strong> (nivel de alerta para reposición).</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Escriba el <strong>Costo de Compra</strong> y el <strong>Precio de Venta</strong>. El precio no puede ser menor que el costo.</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Presione <span class="manual-chip">Guardar</span>. El producto aparece de inmediato en la tabla.</span></div>

        <div class="manual-subtitle"><i class="material-icons">edit</i> Editar o eliminar un producto</div>
        <div class="manual-step"><span class="step-num">1</span><span>Para <strong>editar</strong>, presione el botón con el lápiz en la fila del producto. Se abre el mismo formulario con el título <em>Editar Producto</em>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Modifique los campos necesarios y presione <span class="manual-chip">Guardar</span>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>eliminar</strong>, presione el botón rojo con el cesto de basura.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Confirme en el mensaje <em>¿Está seguro de eliminar el producto "…"?</em>. El producto se desactiva y deja de mostrarse, pero su historial se conserva.</span></div>

        <div class="manual-tip"><i class="material-icons">info</i><span>Cuando el stock llega al mínimo, el producto aparece en <strong>Stock Bajo</strong>; si llega a cero, en <strong>Stock Crítico</strong>. Ambos casos se reflejan en el Dashboard.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Eliminar un producto no borra las ventas ya registradas. Si solo desea ocultarlo temporalmente, puede dejarlo con stock en cero o desactivarlo; el sistema conserva la trazabilidad.</span></div>

        <div class="manual-subtitle"><i class="material-icons">category</i> Gestionar categorías</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/05-inventario-categorias.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/05-inventario-categorias.svg" alt="Modal Gestionar Categorías con formulario para crear y tabla de categorías existentes" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 5.</span> Ventana <em>Gestionar Categorías</em>. <a class="fig-open" href="Public/img/manual/05-inventario-categorias.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Categorías</span>, junto a <em>Nuevo Producto</em>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Escriba el <strong>Nombre de la categoría</strong> y presione el botón verde con el signo <strong>+</strong> para agregarla.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>editar</strong>, use el lápiz de la fila; el nombre se carga en el campo superior. Cámbielo y confirme con el botón verde.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Para <strong>eliminar</strong>, use el cesto de basura y confirme el mensaje. La categoría se desactiva y deja de aparecer en el formulario de productos.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Presione <span class="manual-chip">Cerrar</span> al terminar.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Cree las categorías antes de registrar productos para mantener el inventario ordenado y los reportes agrupados correctamente.</span></div>
    </div>
</div>

<!-- ===== 4. VENTAS ===== -->
<div class="manual-section card" id="ventas">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">point_of_sale</i> 4. Ventas (Punto de Venta)</span>
        <p>Registra una venta en pocos pasos: agregue productos al carrito, identifique al cliente, confirme el total y el sistema descuenta el stock automáticamente.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/06-ventas-catalogo.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/06-ventas-catalogo.svg" alt="Punto de Venta con catálogo de productos, buscador, total del carrito y botón Carrito" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 6.</span> Catálogo del Punto de Venta. <a class="fig-open" href="Public/img/manual/06-ventas-catalogo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">add_shopping_cart</i> Agregar productos al carrito</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Ventas</strong>. Se muestra el catálogo con los productos disponibles.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Use <em>Buscar producto</em> para filtrar por nombre.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Haga clic sobre la tarjeta del producto para agregar una unidad. Repita hasta completar la venta.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>El contador y el <strong>Total carrito</strong> de la barra superior se actualizan con cada producto agregado.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Cuando termine, presione <span class="manual-chip">Carrito</span> para revisar la venta.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si un producto no tiene stock, el sistema muestra un aviso y no permite superar la cantidad disponible.</span></div>

        <div class="manual-subtitle"><i class="material-icons">shopping_cart</i> Revisar el carrito y los datos del cliente</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/07-ventas-carrito.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/07-ventas-carrito.svg" alt="Carrito de Compras con productos, cantidades, total y formulario de datos del cliente" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 7.</span> Carrito de Compras y datos del cliente. <a class="fig-open" href="Public/img/manual/07-ventas-carrito.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Verifique los productos, el precio unitario y el subtotal de cada línea.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Ajuste la <strong>cantidad</strong> con los controles de cada producto. Para quitar una línea, use la <strong>X</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Si el cliente ya existe, selecciónelo en <strong>Cliente registrado</strong>: se cargan nombre y cédula automáticamente.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Si es un cliente nuevo, escriba <strong>Cliente *</strong> y <strong>Cédula *</strong>. Al salir del campo cédula, el sistema busca coincidencias y precarga dirección y teléfono si existen.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Complete <strong>Dirección</strong> y <strong>Teléfono</strong> si desea actualizar el contacto (son opcionales).</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Revise el <strong>TOTAL</strong> antes de cobrar.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>El cliente se reutiliza por cédula: si ya existe, no se duplica; solo se actualizan sus datos de contacto.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>El botón <span class="manual-chip bad">Vaciar</span> elimina todo el carrito y pide confirmación. Úselo solo si desea comenzar la venta desde cero.</span></div>

        <div class="manual-subtitle"><i class="material-icons">paid</i> Procesar y confirmar la venta</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/08-ventas-confirmacion.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/08-ventas-confirmacion.svg" alt="Confirmación de venta registrada con número de orden y total cobrado" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 8.</span> Confirmación y comprobante de la venta. <a class="fig-open" href="Public/img/manual/08-ventas-confirmacion.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip ok">Procesar</span> en el pie del carrito.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Confirme el mensaje <em>¿Procesar venta por $…?</em> con <strong>Aceptar</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>El sistema genera el número de orden, guarda las líneas, descuenta el stock y muestra el mensaje de venta registrada.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>El carrito se vacía y el catálogo se actualiza con las existencias nuevas.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No use el botón <strong>Actualizar/Recargar</strong> del navegador inmediatamente después de cobrar: podría reenviar la operación. Espere a que el sistema confirme y refresque el catálogo.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si el cobro fue un error, no elimine la venta desde este módulo. Registre el ajuste según el procedimiento interno del negocio y consulte al administrador.</span></div>
    </div>
</div>

<!-- ===== 5. CLIENTES ===== -->
<div class="manual-section card" id="clientes">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">badge</i> 5. Clientes</span>
        <p>Mantiene la base única de clientes que utilizan Ventas, Control de Ciber y Asesoría Legal. Registrar un cliente aquí evita duplicarlo en los demás módulos.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/09-clientes-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/09-clientes-listado.svg" alt="Listado de clientes con buscador y tabla de cédula, nombre, dirección y teléfono" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 9.</span> Listado de clientes. <a class="fig-open" href="Public/img/manual/09-clientes-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">person_add</i> Registrar un cliente</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/10-clientes-nuevo.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/10-clientes-nuevo.svg" alt="Modal Nuevo Cliente con cédula, nombre, apellido, dirección y teléfono" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 10.</span> Formulario <em>Nuevo Cliente</em>. <a class="fig-open" href="Public/img/manual/10-clientes-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Clientes</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Presione <span class="manual-chip">Nuevo Cliente</span>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Complete <strong>Cédula</strong>, <strong>Nombre</strong>, <strong>Apellido</strong>, <strong>Dirección</strong> y <strong>Teléfono</strong>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione <span class="manual-chip">Guardar</span>. El cliente queda disponible de inmediato en Ventas, Ciber y Asesoría Legal.</span></div>

        <div class="manual-subtitle"><i class="material-icons">manage_search</i> Buscar, editar y eliminar</div>
        <div class="manual-step"><span class="step-num">1</span><span>Use <em>Buscar cliente</em> para filtrar por nombre, cédula o teléfono.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Para <strong>editar</strong>, presione el lápiz de la fila, modifique los datos y guarde.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>eliminar</strong>, presione el cesto de basura y confirme el mensaje.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No se puede eliminar un cliente que tenga <strong>asesorías, ventas o sesiones de cyber</strong> asociadas. En ese caso, el sistema muestra un error y conserva el registro para no perder el historial.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Antes de crear un cliente, búsquelo por cédula. La base es compartida y un duplicado dificulta los reportes por cliente.</span></div>
    </div>
</div>

<!-- ===== 6. ÓRDENES DE COMPRA ===== -->
<div class="manual-section card" id="ordenes">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">request_quote</i> 6. Órdenes de compra (Solicitudes)</span>
        <p>Registra las solicitudes de productos a proveedores. Cada orden tiene una cabecera (número, fecha, proveedor y estado) y una o varias líneas de producto.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/11-ordenes-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/11-ordenes-listado.svg" alt="Listado de órdenes de compra con indicadores, filtros y acciones por solicitud" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 11.</span> Listado de Órdenes de Compra. <a class="fig-open" href="Public/img/manual/11-ordenes-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">search</i> Consultar solicitudes</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral despliegue <strong>Solicitudes</strong> y elija <strong>Órdenes de Compra</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Revise los indicadores <strong>Total Solicitudes</strong>, <strong>Pendientes</strong> y <strong>Recibidas</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Use <em>Buscar solicitud</em> para filtrar por proveedor o número de orden.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Use el filtro <strong>Estado</strong> para ver todas, pendientes o recibidas.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>La tabla muestra número de solicitud, proveedor, RIF, fecha, estado y las acciones disponibles.</span></div>

        <div class="manual-subtitle"><i class="material-icons">note_add</i> Crear una orden de compra</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/12-ordenes-nueva.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/12-ordenes-nueva.svg" alt="Modal Nueva Solicitud con número automático, fecha, proveedor y estado" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 12.</span> Cabecera de una <em>Nueva Solicitud</em>. <a class="fig-open" href="Public/img/manual/12-ordenes-nueva.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Nueva Solicitud</span>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>El <strong>Número de Orden</strong> se genera automáticamente y no se edita.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Confirme la <strong>Fecha</strong> (viene con la fecha actual).</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Seleccione el <strong>Proveedor</strong> y el <strong>Estado</strong> inicial de la solicitud.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Presione <span class="manual-chip">Guardar</span>. La orden se crea y se habilita la sección de líneas de producto.</span></div>

        <div class="manual-subtitle"><i class="material-icons">playlist_add</i> Agregar productos a la orden</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/13-ordenes-detalle-lineas.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/13-ordenes-detalle-lineas.svg" alt="Detalle de orden con tabla de productos, cantidades, precios, subtotales y total" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 13.</span> Detalle con líneas de producto y total de la orden. <a class="fig-open" href="Public/img/manual/13-ordenes-detalle-lineas.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>En <strong>Agregar Producto</strong>, seleccione el producto. El precio de compra se carga automáticamente y puede ajustarse.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Indique la <strong>Cantidad</strong> y verifique el <strong>Precio</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Presione el botón verde con el signo <strong>+</strong>. La línea aparece en la tabla con su subtotal.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Repita para cada producto. El <strong>Total</strong> se recalcula automáticamente.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Para quitar una línea, use el cesto de basura y confirme <em>¿Eliminar este producto de la solicitud?</em>.</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Presione <span class="manual-chip">Guardar</span> para cerrar la orden con todos sus productos.</span></div>

        <div class="manual-subtitle"><i class="material-icons">visibility</i> Ver, editar o eliminar una orden</div>
        <div class="manual-step"><span class="step-num">1</span><span><strong>Ver detalles:</strong> presione el botón gris con el ojo. Se abre la orden en modo consulta con sus líneas y total.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span><strong>Editar:</strong> presione el lápiz. Puede cambiar la cabecera y agregar o quitar líneas; luego guarde.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span><strong>Eliminar:</strong> presione el cesto rojo y confirme <em>Eliminar la solicitud #…? También se eliminarán sus productos asociados.</em></span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Primero guarde la cabecera y después agregue las líneas. Si cierra el modal antes de guardar, la orden puede quedar creada sin productos.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Mantenga el estado actualizado (pendiente/recibida) para que los indicadores y el reporte de abastecimiento reflejen la realidad.</span></div>
    </div>
</div>

<!-- ===== 7. PROVEEDORES ===== -->
<div class="manual-section card" id="proveedores">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">storefront</i> 7. Proveedores</span>
        <p>Catálogo de empresas o personas que suministran productos. Los proveedores registrados aquí se seleccionan al crear órdenes de compra.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/14-proveedores-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/14-proveedores-listado.svg" alt="Listado de proveedores con buscador y tabla de nombre, RIF, email y teléfono" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 14.</span> Listado de Proveedores. <a class="fig-open" href="Public/img/manual/14-proveedores-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">add_business</i> Registrar un proveedor</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/15-proveedores-nuevo.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/15-proveedores-nuevo.svg" alt="Modal Nuevo Proveedor con RIF, nombre, email y teléfono" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 15.</span> Formulario <em>Nuevo Proveedor</em>. <a class="fig-open" href="Public/img/manual/15-proveedores-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>En <strong>Solicitudes</strong> elija <strong>Proveedores</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Presione <span class="manual-chip">Nuevo Proveedor</span>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Complete <strong>RIF</strong> y <strong>Nombre</strong> (obligatorios). Agregue <strong>Email</strong> y <strong>Teléfono</strong> si los tiene.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione <span class="manual-chip">Guardar</span>. El proveedor queda disponible en el selector de órdenes de compra.</span></div>

        <div class="manual-subtitle"><i class="material-icons">manage_search</i> Buscar, editar y eliminar</div>
        <div class="manual-step"><span class="step-num">1</span><span>Use <em>Buscar proveedor</em> para filtrar por nombre, RIF o email.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Para <strong>editar</strong>, presione el lápiz, ajuste los datos y guarde.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>eliminar</strong>, presione el cesto rojo y confirme el mensaje.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No se puede eliminar un proveedor que tenga <strong>solicitudes asociadas</strong>. El sistema conserva el registro para no romper el historial de compras.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Verifique el RIF antes de guardar: identifica al proveedor en las órdenes y en los reportes de compras.</span></div>
    </div>
</div>

<!-- ===== 8. CYBER ===== -->
<div class="manual-section card" id="cyber">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">computer</i> 8. Control de Ciber (Cybercafé)</span>
        <p>Controla las estaciones del cibercafé: disponibilidad, sesiones activas, tiempo transcurrido, cobro e historial por equipo.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/16-cyber-estaciones.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/16-cyber-estaciones.svg" alt="Panel de control de ciber con estaciones disponibles y ocupadas, filtros y botones de gestión" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 16.</span> Estaciones del cibercafé y sus estados. <a class="fig-open" href="Public/img/manual/16-cyber-estaciones.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">desktop_windows</i> Interpretar las estaciones</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Cyber Control</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Revise los indicadores <strong>Disponibles</strong>, <strong>Ocupadas</strong>, <strong>Mantenimiento</strong> y <strong>Total PCs</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Use los filtros <strong>Todas</strong>, <strong>Disponibles</strong> y <strong>Ocupadas</strong> para reducir la vista.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Cada tarjeta muestra la PC, su tipo, estado y, si está ocupada, el cliente y el tiempo en curso.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Presione <span class="manual-chip">Actualizar</span> para refrescar los estados sin recargar la página.</span></div>

        <div class="manual-subtitle"><i class="material-icons">play_circle</i> Iniciar una sesión</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/17-cyber-iniciar-sesion.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/17-cyber-iniciar-sesion.svg" alt="Modal Iniciar Sesión con datos del cliente, estación, tarifa y tiempo" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 17.</span> Formulario para <em>Iniciar Sesión</em> en una estación. <a class="fig-open" href="Public/img/manual/17-cyber-iniciar-sesion.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Haga clic sobre una estación <span class="manual-chip ok">Disponible</span> o presione <span class="manual-chip">Nueva Sesión</span>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Escriba la <strong>Cédula</strong>. Al salir del campo, el sistema precarga nombre, teléfono y dirección si el cliente ya existe.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Complete o ajuste <strong>Cliente</strong>, <strong>Teléfono</strong> y <strong>Dirección</strong>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Seleccione la <strong>Estación</strong> y la <strong>Tarifa</strong>.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Indique el <strong>Tiempo (HH:MM:SS)</strong>. Ejemplo: <em>01:30:00</em> equivale a 1 hora y 30 minutos.</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Presione <span class="manual-chip ok">Iniciar</span>. La estación cambia a <span class="manual-chip warn">Ocupada</span> y comienza el conteo.</span></div>

        <div class="manual-subtitle"><i class="material-icons">stop_circle</i> Finalizar una sesión</div>
        <div class="manual-step"><span class="step-num">1</span><span>Haga clic sobre la estación <span class="manual-chip warn">Ocupada</span> que desea cerrar.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Confirme el mensaje <em>¿Finalizar la sesión en la estación …?</em>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>El sistema calcula el tiempo usado y el costo, cierra la sesión y libera la estación.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si no hay estaciones disponibles, el botón <em>Nueva Sesión</em> muestra el aviso <em>No hay estaciones disponibles</em>. Libere una estación o registre una nueva PC.</span></div>

        <div class="manual-subtitle"><i class="material-icons">history</i> Consultar el historial</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/18-cyber-historial.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/18-cyber-historial.svg" alt="Modal Historial de Sesiones con selector de PC y tabla de cliente, tiempo, precio y estado" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 18.</span> Historial de sesiones por estación. <a class="fig-open" href="Public/img/manual/18-cyber-historial.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Historial</span>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Seleccione la PC en la lista de botones. Se muestran sus sesiones con cliente, tiempo, precio y estado.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Presione <span class="manual-chip">Cerrar</span> al terminar la consulta.</span></div>

        <div class="manual-subtitle"><i class="material-icons">settings</i> Crear, editar o eliminar una PC</div>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Nueva PC</span>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Complete <strong>Marca</strong>, <strong>Descripción</strong> (por ejemplo, <em>Intel i5, 8 GB RAM</em>) y <strong>Tipo de PC</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Elija el estado <strong>Activa</strong> o <strong>Mantenimiento</strong>. Una PC en mantenimiento no puede recibir sesiones.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione <span class="manual-chip">Guardar PC</span>.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Para <strong>editar</strong>, use el lápiz azul de la tarjeta; para <strong>eliminar</strong>, el cesto rojo y confirme en el modal.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Solo se puede eliminar una PC si <strong>no tiene sesiones registradas</strong>. Si ya se usó, cambie su estado a mantenimiento o desactívela en lugar de borrarla.</span></div>
    </div>
</div>

<!-- ===== 9. REPORTES ===== -->
<div class="manual-section card" id="reportes">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">bar_chart</i> 9. Reportes</span>
        <p>Genera consultas estratégicas del negocio y descárgalas en PDF, Excel o CSV. Los filtros cambian según el tipo de reporte seleccionado.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/19-reportes-filtros.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/19-reportes-filtros.svg" alt="Panel de filtros estratégicos con tipo de reporte, presets de fecha, filtros contextuales y formato" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 19.</span> Filtros estratégicos del módulo de Reportes. <a class="fig-open" href="Public/img/manual/19-reportes-filtros.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">tune</i> Configurar la consulta</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Reportes</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>En <strong>Tipo de Reporte</strong> elija una de las opciones agrupadas por área:
            <ul class="browser-default" style="margin:0.35rem 0 0 1rem;font-size:0.9rem;">
                <li><strong>Comercial y Ventas:</strong> ventas por fecha, por producto, por cliente y arqueo diario de caja.</li>
                <li><strong>Inventario y Operaciones:</strong> estado y valorización, stock bajo, movimientos y rentabilidad.</li>
                <li><strong>Proveedores y Compras:</strong> historial de solicitudes y detalle de compras por producto.</li>
                <li><strong>Servicios y Asesorías:</strong> sesiones de cyber y registro de asesorías legales.</li>
            </ul>
        </span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Defina el período con un preset: <strong>Hoy</strong>, <strong>Ayer</strong>, <strong>7 días</strong>, <strong>Este Mes</strong>, <strong>Mes Ant.</strong> o <strong>Este Año</strong>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>También puede escribir <strong>Fecha Inicio</strong> y <strong>Fecha Fin</strong> manualmente.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Complete los filtros contextuales que aparezcan: categoría, cliente, proveedor, estado de solicitud o estado de sesión cyber.</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Elija el formato de descarga: <strong>PDF</strong>, <strong>Excel</strong> o <strong>CSV</strong>.</span></div>
        <div class="manual-step"><span class="step-num">7</span><span>Presione <span class="manual-chip">Consultar Reporte</span>.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Al abrir la página se ejecuta automáticamente una primera consulta con el reporte <em>Ventas por fecha</em> y el rango <em>Este Mes</em>.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Los reportes de <strong>inventario, stock bajo y sesiones cyber</strong> no usan fechas: el sistema oculta ese rango. Si escribe fechas, la fecha inicial no puede ser posterior a la final.</span></div>

        <div class="manual-subtitle"><i class="material-icons">table_chart</i> Leer y exportar los resultados</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/20-reportes-resultados.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/20-reportes-resultados.svg" alt="Resultados del reporte con indicadores, tabla de datos y botón Exportar Archivo" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 20.</span> Resultados del reporte y exportación. <a class="fig-open" href="Public/img/manual/20-reportes-resultados.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Revise los indicadores de resumen que aparecen sobre la tabla (totales, promedios o conteos según el reporte).</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Explore la tabla de resultados: puede ordenar por columna y usar la paginación inferior.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para descargar, presione <span class="manual-chip">Exportar Archivo</span>. Se usará el formato elegido y los filtros activos.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>El archivo se descarga en su equipo con el nombre del reporte y el período consultado.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si cambia el tipo de reporte, los filtros se reajustan. Vuelva a consultar antes de exportar para evitar descargar datos del reporte anterior.</span></div>
    </div>
</div>

<!-- ===== 10. ACTIVOS ===== -->
<div class="manual-section card" id="activos">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">devices_other</i> 10. Activos fijos</span>
        <p>Registra y controla los bienes de la empresa: equipos, mobiliario y estaciones de cybercafé. Un activo marcado como estación de cyber aparece automáticamente en el módulo Cyber.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/21-activos-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/21-activos-listado.svg" alt="Listado de activos con indicadores, buscador, filtro por tipo y tabla de marca, descripción y estado" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 21.</span> Listado de Activos fijos. <a class="fig-open" href="Public/img/manual/21-activos-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">inventory</i> Consultar activos</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Activos</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Revise los indicadores <strong>Total Activos</strong>, <strong>Estaciones Cyber</strong>, <strong>Ocupadas Ahora</strong> e <strong>Inactivos</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Use <em>Buscar activo</em> para filtrar por marca, tipo o descripción.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Use <strong>Filtrar por tipo</strong> para ver una categoría específica.</span></div>

        <div class="manual-subtitle"><i class="material-icons">add_circle</i> Registrar un activo</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/22-activos-nuevo.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/22-activos-nuevo.svg" alt="Modal Nuevo Activo con marca, tipo, descripción, casilla Activo y casilla Estación de Cybercafé" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 22.</span> Formulario <em>Nuevo Activo</em>. <a class="fig-open" href="Public/img/manual/22-activos-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Nuevo Activo</span>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Complete <strong>Marca</strong>, seleccione el <strong>Tipo de activo</strong> y escriba una <strong>Descripción</strong> clara.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Deje marcada la casilla <strong>Activo</strong> si el bien está operativo.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Marque <strong>Estación de Cybercafé</strong> solo si el activo es una PC que se controlará en el módulo Cyber.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Presione <span class="manual-chip">Guardar</span>.</span></div>

        <div class="manual-subtitle"><i class="material-icons">edit</i> Editar o eliminar un activo</div>
        <div class="manual-step"><span class="step-num">1</span><span>Para <strong>editar</strong>, use el lápiz de la fila, ajuste los datos y guarde.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Para <strong>eliminar</strong>, use el cesto rojo y confirme el mensaje.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No se puede eliminar un activo con <strong>sesiones de cybercafé asociadas</strong>. Si el equipo ya no se usa, desmarque la casilla <em>Activo</em> y guarde.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si marca un activo como estación de cyber, aparecerá en <strong>Cyber Control</strong> con estado disponible. Complete primero la marca y la descripción para identificarlo fácilmente.</span></div>
    </div>
</div>

<!-- ===== 11. ASESORÍA LEGAL ===== -->
<div class="manual-section card" id="asesorias">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">gavel</i> 11. Asesoría legal</span>
        <p>Registra consultas de asesoría jurídica gratuita y valida si el tipo de documento puede atenderse en la oficina. Los casos no permitidos se derivan a una oficina oficial.</p>

        <div class="manual-subtitle"><i class="material-icons">fact_check</i> Validar el tipo de documento</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/23-asesoria-validacion.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/23-asesoria-validacion.svg" alt="Formulario de asesoría con validación de documento permitido y lista de documentos aceptados" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 23.</span> Validación de un documento permitido. <a class="fig-open" href="Public/img/manual/23-asesoria-validacion.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Asesoría Legal</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>En <strong>Tipo de Documento / Asesoría</strong> escriba o seleccione el motivo de la consulta.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>El botón cambia según el texto: <span class="manual-chip ok">Validar y Registrar</span> si el documento está permitido, o <span class="manual-chip bad">Derivar a Oficina Oficial</span> si no lo está.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Consulte la tarjeta <strong>Documentos Permitidos</strong>. Los tipos aceptados son: consulta laboral, consulta civil, consulta familiar, orientación legal general, revisión de contrato, elaboración de documento simple y asesoría prevencional.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Al registrar, el sistema muestra un panel verde con <strong>DOCUMENTO PERMITIDO</strong> o un panel rojo con <strong>DOCUMENTO NO PERMITIDO</strong> y la indicación de derivación.</span></div>

        <div class="manual-subtitle"><i class="material-icons">note_add</i> Registrar la asesoría</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/24-asesoria-registro.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/24-asesoria-registro.svg" alt="Formulario completo de asesoría legal e historial de casos registrados" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 24.</span> Registro de la asesoría e historial de casos. <a class="fig-open" href="Public/img/manual/24-asesoria-registro.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>Complete <strong>Nombre del Ciudadano</strong> y <strong>Cédula de Identidad</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Agregue <strong>Teléfono de Contacto</strong> y <strong>Dirección</strong> si están disponibles.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Escriba la <strong>Descripción / Motivo de la Consulta</strong> con el detalle necesario.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione <span class="manual-chip">Validar y Registrar</span> (o <span class="manual-chip bad">Derivar a Oficina Oficial</span> si corresponde).</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>El caso aparece en <strong>Historial de Asesorías</strong> con ciudadano, cédula, documento, estado y fecha.</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Use <em>Buscar en historial</em> para localizar por ciudadano, cédula o documento.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si la cédula ya existe en el historial, el sistema reutiliza el cliente y actualiza sus datos de contacto; no crea un duplicado.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>El historial es de solo consulta: no hay botones para editar o eliminar asesorías. Si un caso se registró con error, documente la corrección y consulte al administrador.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>Los documentos no listados (juicios, demandas, apelaciones, herencias, materia penal, etc.) <strong>no se atienden directamente</strong>: deben derivarse a una oficina oficial de atención legal.</span></div>
    </div>
</div>

<!-- ===== 12. USUARIOS ===== -->
<div class="manual-section card" id="usuarios">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">manage_accounts</i> 12. Usuarios</span>
        <p>Administra las cuentas que acceden al sistema. Cada usuario tiene un rol que determina los módulos y acciones disponibles.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/25-usuarios-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/25-usuarios-listado.svg" alt="Listado de usuarios con indicadores, buscador, filtro por rol y acciones de edición, estado y eliminación" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 25.</span> Listado de Usuarios del sistema. <a class="fig-open" href="Public/img/manual/25-usuarios-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">person_add</i> Crear un usuario</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/26-usuarios-nuevo.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/26-usuarios-nuevo.svg" alt="Modal Nuevo Usuario con nombre, apellido, username, email, contraseña, rol y estado" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 26.</span> Formulario <em>Nuevo Usuario</em>. <a class="fig-open" href="Public/img/manual/26-usuarios-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Configuración</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Presione <span class="manual-chip">Nuevo Usuario</span>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Complete <strong>Nombre</strong>, <strong>Apellido</strong>, <strong>Nombre de usuario</strong> y <strong>Email</strong>.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Escriba una <strong>Contraseña</strong> de al menos 8 caracteres.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Seleccione el <strong>Rol del usuario</strong> y el <strong>Estado</strong> (Activo o Inactivo).</span></div>
        <div class="manual-step"><span class="step-num">6</span><span>Presione <span class="manual-chip">Guardar</span>. El usuario puede iniciar sesión si está activo y tiene rol.</span></div>

        <div class="manual-subtitle"><i class="material-icons">manage_search</i> Buscar y editar usuarios</div>
        <div class="manual-step"><span class="step-num">1</span><span>Use <em>Buscar usuario</em> para filtrar por nombre, email o rol.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Use el filtro <strong>Rol</strong> para ver solo un perfil.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>editar</strong>, presione el lápiz. El nombre de usuario se muestra como solo lectura.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>La contraseña es opcional al editar: déjela vacía para conservar la actual; escríbala solo si desea cambiarla.</span></div>
        <div class="manual-step"><span class="step-num">5</span><span>Guarde los cambios.</span></div>

        <div class="manual-subtitle"><i class="material-icons">toggle_on</i> Activar, desactivar o eliminar</div>
        <div class="manual-step"><span class="step-num">1</span><span>Use el botón de estado de la fila para <strong>activar o desactivar</strong> al usuario. Confirme el mensaje <em>¿Activar/Desactivar este usuario?</em>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Un usuario inactivo conserva su historial pero no puede iniciar sesión.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Para <strong>eliminar</strong>, presione el cesto rojo y confirme el mensaje.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No puede desactivar ni eliminar su <strong>propio usuario</strong>. Tampoco se puede eliminar un usuario con registros asociados; en ese caso, desactívelo.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>La contraseña se almacena cifrada: nadie puede verla, ni siquiera el administrador. Si se olvida, debe restablecerse desde la edición del usuario.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Si un usuario no ve un módulo, revise primero su rol en <strong>Roles y Permisos</strong>. No es necesario crear otra cuenta para cambiar de funciones.</span></div>
    </div>
</div>

<!-- ===== 13. ROLES ===== -->
<div class="manual-section card" id="roles">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">admin_panel_settings</i> 13. Roles y permisos</span>
        <p>Los roles agrupan permisos por módulo. Al asignar un rol a un usuario, este hereda los accesos definidos para ese perfil.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/27-roles-listado.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/27-roles-listado.svg" alt="Listado de roles con indicadores, buscador, filtro y botones Nuevo Rol y Asignar Rol" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 27.</span> Listado de Roles del sistema. <a class="fig-open" href="Public/img/manual/27-roles-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">add_moderator</i> Crear un rol</div>
        <div class="manual-step"><span class="step-num">1</span><span>En el menú lateral presione <strong>Roles y Permisos</strong>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Presione <span class="manual-chip">Nuevo Rol</span>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Escriba el <strong>Nombre del rol</strong> y una <strong>Descripción</strong> que explique su función.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Presione <span class="manual-chip">Guardar</span>. El rol aparece en la tabla con cero usuarios asignados.</span></div>

        <div class="manual-subtitle"><i class="material-icons">security</i> Configurar los permisos</div>
        <figure class="manual-figure">
            <a href="Public/img/manual/28-roles-permisos.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/28-roles-permisos.svg" alt="Modal Permisos del rol con interruptores por módulo y botón Guardar Permisos" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 28.</span> Ventana <em>Permisos: rol</em> con los módulos del sistema. <a class="fig-open" href="Public/img/manual/28-roles-permisos.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>
        <div class="manual-step"><span class="step-num">1</span><span>En la fila del rol, presione el botón de seguridad (escudo) para abrir <em>Permisos: nombre del rol</em>.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Active o desactive los módulos a los que este rol tendrá acceso.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Presione <span class="manual-chip">Guardar Permisos</span>. Los usuarios con ese rol verán el menú actualizado en su próximo inicio de sesión.</span></div>

        <div class="manual-subtitle"><i class="material-icons">supervisor_account</i> Asignar un rol a un usuario</div>
        <div class="manual-step"><span class="step-num">1</span><span>Presione <span class="manual-chip">Asignar Rol</span>, en la parte superior del listado.</span></div>
        <div class="manual-step"><span class="step-num">2</span><span>Seleccione el <strong>Usuario</strong> y el <strong>Rol</strong>.</span></div>
        <div class="manual-step"><span class="step-num">3</span><span>Presione <span class="manual-chip">Asignar Rol</span> en el pie del modal. El cambio se refleja en la tabla de usuarios.</span></div>
        <div class="manual-step"><span class="step-num">4</span><span>Para <strong>editar</strong> el nombre o la descripción de un rol, use el lápiz; para <strong>eliminar</strong>, el cesto rojo y confirme.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>No se puede eliminar el rol <strong>Administrador</strong> ni un rol que tenga <strong>usuarios asignados</strong>. Reasigne primero esos usuarios a otro rol.</span></div>
        <div class="manual-warn"><i class="material-icons">warning</i><span>El rol Administrador está protegido y no se asigna desde el modal <em>Asignar Rol</em>. Se define al editar el usuario correspondiente.</span></div>
        <div class="manual-tip"><i class="material-icons">info</i><span>Revise los permisos antes de crear un rol nuevo. Un permiso de más puede exponer módulos sensibles; uno de menos impide trabajar al usuario.</span></div>
    </div>
</div>

<!-- ===== 14. CONSEJOS Y SEGURIDAD ===== -->
<div class="manual-section card" id="consejos">
    <div class="card-content">
        <span class="card-title"><i class="material-icons">security</i> 14. Consejos, seguridad y referencia rápida</span>
        <p>Aplicar estas prácticas reduce errores operativos y protege la información del negocio y de los clientes.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/29-consejos-seguridad.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/29-consejos-seguridad.svg" alt="Resumen de buenas prácticas de seguridad, respaldo y uso responsable del sistema" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 29.</span> Buenas prácticas de seguridad y uso del sistema. <a class="fig-open" href="Public/img/manual/29-consejos-seguridad.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">checklist</i> Buenas prácticas</div>
        <div class="manual-table">
            <table class="highlight">
                <thead>
                    <tr><th>Práctica</th><th>Cómo aplicarla</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Cerrar sesión</strong></td><td>Presione <em>Cerrar Sesión</em> al final del menú lateral, especialmente en equipos compartidos o de atención al público.</td></tr>
                    <tr><td><strong>Contraseñas seguras</strong></td><td>Use al menos 8 caracteres, combine letras, números y símbolos. No comparta su contraseña ni la escriba en notas visibles.</td></tr>
                    <tr><td><strong>Cuentas individuales</strong></td><td>Cada persona debe usar su propio usuario. Así la actividad queda registrada con el responsable correcto.</td></tr>
                    <tr><td><strong>Revisar el Dashboard</strong></td><td>Al iniciar la jornada, revise stock crítico, solicitudes pendientes y sesiones cyber activas.</td></tr>
                    <tr><td><strong>Datos completos</strong></td><td>Registre cédula, nombre y contacto correctos. La base de clientes es compartida por Ventas, Ciber y Asesoría Legal.</td></tr>
                    <tr><td><strong>No duplicar operaciones</strong></td><td>Después de cobrar o guardar, espere la confirmación. Evite recargar la página o pulsar dos veces el mismo botón.</td></tr>
                    <tr><td><strong>Modo oscuro / claro</strong></td><td>Use la opción <em>Modo Oscuro</em> del menú lateral para adaptar la interfaz a la iluminación del entorno.</td></tr>
                    <tr><td><strong>Trabajo sin conexión</strong></td><td>El sistema es una aplicación web instalable (PWA). Si se instala, un <em>Service Worker</em> guarda en caché las páginas visitadas; las operaciones que requieren servidor necesitan conexión.</td></tr>
                    <tr><td><strong>Errores</strong></td><td>Anote el módulo, la acción realizada y el mensaje exacto. Envíe esa información al administrador técnico.</td></tr>
                    <tr><td><strong>Respaldo</strong></td><td>No borre registros para “limpiar” el sistema. Los historiales de ventas, compras, cyber y asesorías deben conservarse.</td></tr>
                </tbody>
            </table>
        </div>

        <div class="manual-subtitle"><i class="material-icons">map</i> Referencia rápida por módulo</div>
        <div class="manual-table">
            <table class="highlight">
                <thead>
                    <tr><th>Módulo</th><th>Acciones principales</th><th>Precaución clave</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Inventario</strong></td><td>Crear, editar y eliminar productos; gestionar categorías; filtrar por estado.</td><td>El precio de venta no puede ser menor que el costo.</td></tr>
                    <tr><td><strong>Ventas (POS)</strong></td><td>Agregar productos, ajustar cantidades, identificar cliente, procesar y vaciar carrito.</td><td>No recargue la página después de cobrar.</td></tr>
                    <tr><td><strong>Clientes</strong></td><td>Registrar, buscar, editar y eliminar clientes.</td><td>No se elimina si tiene ventas, asesorías o sesiones.</td></tr>
                    <tr><td><strong>Órdenes de compra</strong></td><td>Crear cabecera, agregar líneas, ver detalle, editar y eliminar.</td><td>Guarde la cabecera antes de agregar productos.</td></tr>
                    <tr><td><strong>Proveedores</strong></td><td>Registrar, buscar, editar y eliminar proveedores.</td><td>No se elimina si tiene solicitudes asociadas.</td></tr>
                    <tr><td><strong>Cyber</strong></td><td>Iniciar y finalizar sesiones, crear/editar PCs, consultar historial y actualizar.</td><td>Una PC con sesiones no se puede eliminar.</td></tr>
                    <tr><td><strong>Reportes</strong></td><td>Elegir tipo, período y filtros; consultar y exportar en PDF, Excel o CSV.</td><td>Vuelva a consultar si cambia el tipo de reporte.</td></tr>
                    <tr><td><strong>Activos</strong></td><td>Registrar, buscar, editar y eliminar activos; marcar estaciones cyber.</td><td>No se elimina si tiene sesiones de cybercafé.</td></tr>
                    <tr><td><strong>Asesoría Legal</strong></td><td>Validar documento, registrar caso y consultar historial.</td><td>Los documentos no permitidos se derivan a oficina oficial.</td></tr>
                    <tr><td><strong>Usuarios</strong></td><td>Crear, editar, activar/desactivar y eliminar usuarios.</td><td>No puede desactivar ni eliminar su propio usuario.</td></tr>
                    <tr><td><strong>Roles</strong></td><td>Crear roles, configurar permisos y asignar rol a usuarios.</td><td>No elimine roles con usuarios asignados.</td></tr>
                </tbody>
            </table>
        </div>

        <div class="manual-tip"><i class="material-icons">info</i><span>Las ilustraciones de este manual son representaciones de la interfaz y pueden variar ligeramente según los datos, el rol del usuario y el tema claro u oscuro. Los nombres de botones y campos descritos son los que utiliza el sistema.</span></div>
        <div class="manual-tip"><i class="material-icons">menu_book</i><span>Para dudas sobre la instalación, la base de datos o la configuración del servidor, consulte la documentación técnica del proyecto en la carpeta <em>docs/</em> o contacte al administrador del sistema.</span></div>
    </div>
</div>

