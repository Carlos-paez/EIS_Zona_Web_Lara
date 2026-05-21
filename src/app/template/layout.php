<!DOCTYPE html>
<!-- ============================================================
     LAYOUT PRINCIPAL (MASTER TEMPLATE)
     Este archivo define la estructura HTML común a todas las
     páginas protegidas (que requieren autenticación).
     Incluye: sidebar, barra de navegación, contenedor principal,
     botón "volver arriba" y scripts globales.
     La variable $contentView (definida en router.php) inyecta
     el contenido específico de cada página.
     ============================================================ -->
<html lang="es">

<head>
    <meta charset="UTF-8"> <!-- Codificación UTF-8 para caracteres especiales -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"> <!-- Diseño responsive con safe-area -->
    <meta name="theme-color" content="#1a237e"> <!-- Color de la barra de navegación del navegador en móviles -->
    <meta name="apple-mobile-web-app-capable" content="yes"> <!-- Permitir modo app en iOS -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"> <!-- Barra de estado translúcida en iOS -->
    <title><?php echo $pageTitle; ?> - EIS System</title> <!-- Título dinámico de la pestaña -->
    <!-- Google Material Icons (fuente de iconos) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Materialize CSS v1.0.0 (framework de diseño Material Design) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!-- Estilos personalizados de la aplicación -->
    <link rel="stylesheet" href="Public/css/styles.css">
    <!-- jQuery 3.7.1 (dependencia de Materialize JS y lógica de la app) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>

    <!-- ========== BARRA LATERAL (SIDENAV) ========== -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <li>
            <!-- Encabezado del sidebar con el logo y nombre del sistema -->
            <div class="user-view">
                <div class="background indigo darken-4"></div> <!-- Fondo de color -->
                <span class="white-text name" style="font-size:1.5rem;font-weight:700;">⚡ EIS System</span>
                <span class="white-text email">Sistema de Gestión Integral</span>
            </div>
        </li>
        <!-- Cada ítem del menú: enlace a ?pagina=[sección]; se marca como 'active' si coincide con $pagina actual -->
        <li><a href="?pagina=dashboard" class="sidenav-link<?php echo $pagina === 'dashboard' ? ' active' : ''; ?>"><i
                    class="material-icons left">dashboard</i>Dashboard</a></li>
        <li><a href="?pagina=inventario" class="sidenav-link<?php echo $pagina === 'inventario' ? ' active' : ''; ?>"><i
                    class="material-icons left">inventory_2</i>Inventario</a></li>
        <li><a href="?pagina=ventas" class="sidenav-link<?php echo $pagina === 'ventas' ? ' active' : ''; ?>"><i
                    class="material-icons left">shopping_cart</i>Ventas (POS)</a></li>
        <li><a href="?pagina=proveedores"
                class="sidenav-link<?php echo $pagina === 'proveedores' ? ' active' : ''; ?>"><i
                    class="material-icons left">request_quote</i>Solicitudes</a></li>
        <li><a href="?pagina=ciberControl"
                class="sidenav-link<?php echo $pagina === 'ciberControl' ? ' active' : ''; ?>"><i
                    class="material-icons left">computer</i>Cyber</a></li>
        <li><a href="?pagina=reportes" class="sidenav-link<?php echo $pagina === 'reportes' ? ' active' : ''; ?>"><i
                    class="material-icons left">bar_chart</i>Reportes</a></li>
        <li><a href="?pagina=activos" class="sidenav-link<?php echo $pagina === 'activos' ? ' active' : ''; ?>"><i
                    class="material-icons left">build</i>Activos</a></li>
        <li><a href="?pagina=asesorias" class="sidenav-link<?php echo $pagina === 'asesorias' ? ' active' : ''; ?>"><i
                    class="material-icons left">gavel</i>Asesoría Legal</a></li>
        <li>
            <div class="divider"></div>
        </li> <!-- Separador visual -->
        <li><a href="?pagina=usuarios" class="sidenav-link<?php echo $pagina === 'usuarios' ? ' active' : ''; ?>"><i
                    class="material-icons left">settings</i>Configuración</a></li>
        <!-- Alternar tema oscuro/claro (manejado por JS en app.js) -->
        <li><a class="sidenav-link" id="themeToggle" style="cursor:pointer;"><i class="material-icons left"
                    id="themeIcon">dark_mode</i><span id="themeLabel">Modo Oscuro</span></a></li>
        <li><a href="?pagina=login" class="sidenav-link"><i class="material-icons left">logout</i>Cerrar Sesión</a></li>
        <!-- Cierra sesión (redirige al login) -->
    </ul>

    <!-- ========== BARRA DE NAVEGACIÓN SUPERIOR ========== -->
    <header>
        <nav class="nav-extended indigo darken-3">
            <div class="nav-wrapper">
                <!-- Botón hamburguesa para mostrar/ocultar sidebar en pantallas pequeñas -->
                <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <!-- Título de la página (oculto en tablets/desktop) -->
                <span class="brand-logo hide-on-med-and-down"
                    style="font-size:1.25rem;padding-left:1rem;"><?php echo $pageTitle; ?></span>
                <!-- Título centrado en móviles -->
                <span class="brand-logo hide-on-large-only"
                    style="font-size:1rem;left:50%;transform:translateX(-50%);max-width:40%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo $pageTitle; ?></span>
                <ul id="nav-mobile" class="right">
                    <!-- Header extra opcional (ej: chips de estado en ciberControl) -->
                    <?php if (!empty($headerExtra)): ?>
                    <li class="hide-on-small-only"><?php echo $headerExtra; ?></li>
                    <li class="hide-on-med-and-up"><?php echo $headerExtra; ?></li>
                    <?php endif; ?>
                    <!-- Reloj digital actualizado por JS (solo ícono en móvil) -->
                    <li><span id="clock" class="white-text"
                            style="font-size:0.8rem;padding-right:0.5rem;opacity:0.85;"><i class="material-icons left"
                                style="font-size:1rem;">schedule</i><span class="hide-on-small-only">Cargando...</span></span></li>
                    <!-- Campana de notificaciones con badge (contador) -->
                    <li>
                        <a class="tooltipped" data-position="bottom" data-tooltip="Notificaciones" id="notifBell"
                            style="cursor:pointer;position:relative;display:flex;align-items:center;justify-content:center;min-width:44px;min-height:44px;">
                            <i class="material-icons">notifications</i>
                            <span id="notifBadge" class="new badge red"
                                style="position:absolute;top:6px;right:2px;min-width:20px;height:20px;line-height:20px;font-size:0.6rem;padding:0 5px;border-radius:50%;pointer-events:none;">3</span>
                        </a>
                    </li>
                    <!-- Badge con el nombre del usuario (solo avatar en móvil) -->
                    <li><span class="badge indigo lighten-2 white-text"
                            style="margin-right:0.5rem;font-size:0.8rem;padding:0.25rem 0.6rem;display:flex;align-items:center;min-height:44px;"><i
                                class="material-icons left" style="font-size:1rem;">person</i><span class="hide-on-small-only">Admin</span></span></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main>
        <div class="container" style="padding-top:1.5rem;padding-bottom:2rem;max-width:1400px;width:95%;">
            <?php require $contentView; ?>
            <!-- Aquí se inyecta la vista específica de cada página -->
        </div>
    </main>

    <!-- Botón flotante "Volver arriba" (visible al hacer scroll hacia abajo) -->
    <div id="backToTop" class="btn-floating indigo"
        style="position:fixed;bottom:2rem;right:2rem;z-index:999;display:none;">
        <i class="material-icons">keyboard_arrow_up</i>
    </div>

    <!-- Scripts globales -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <!-- JS de Materialize -->
    <script src="Public/js/app.js"></script> <!-- Lógica personalizada de la aplicación -->
</body>

</html>
