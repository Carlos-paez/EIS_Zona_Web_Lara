<!DOCTYPE html>
<!-- ============================================================
     LAYOUT PRINCIPAL (MASTER TEMPLATE)
     ============================================================
     Este archivo define la estructura HTML común a todas las
     páginas protegidas (que requieren autenticación).
     Incluye: sidebar de navegación, barra superior, contenedor
     principal, botón "volver arriba" y scripts globales.

     Variables esperadas (definidas en Controller.php):
       $pageTitle   — Título de la página actual
       $pagina      — Nombre de la página (?pagina=xxx)
       $headerExtra — HTML extra para la barra de navegación
       $contentView — Ruta absoluta al archivo de vista a incluir
     ============================================================ -->
<html lang="es">
<!-- lang="es" indica que el contenido está en español -->

<head>
    <!-- Meta tags básicos -->
    <meta charset="UTF-8">
    <!-- charset="UTF-8" permite usar caracteres especiales (tildes, ñ, etc.) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- viewport: diseño responsive que se adapta a móviles y tablets -->

    <!-- Título dinámico de la pestaña del navegador -->
    <!-- $pageTitle viene del controlador (ej: "Panel de Control") -->
    <title><?php echo $pageTitle; ?> - EIS System</title>

    <!-- Google Material Icons (fuente de iconos vectoriales) -->
    <!-- Cargada desde CDN de Google Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Materialize CSS v1.0.0 (framework de diseño Material Design) -->
    <!-- Cargado desde CDN de cdnjs -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <!-- Estilos personalizados de la aplicación -->
    <!-- Public/css/styles.css contiene variables CSS, temas oscuro/claro, -->
    <!-- estilos de tarjetas métricas, activity items, etc. -->
    <link rel="stylesheet" href="Public/css/styles.css">

    <!-- jQuery 3.7.1 (dependencia de Materialize JS y lógica de la app) -->
    <!-- Cargado desde CDN con integridad SHA256 para seguridad -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>

    <!-- ============================================
         BARRA LATERAL (SIDENAV)
         Menú de navegación fijo en el lado izquierdo.
         Materialize lo convierte en un panel deslizable
         en pantallas pequeñas.
         ============================================ -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <!-- Encabezado del sidebar con logo y nombre del sistema -->
        <li>
            <div class="user-view">
                <!-- Fondo de color (indigo oscuro) -->
                <div class="background indigo darken-4"></div>
                <!-- Nombre del sistema con icono de rayo -->
                <span class="white-text name" style="font-size:1.5rem;font-weight:700;">⚡ EIS System</span>
                <!-- Subtítulo descriptivo -->
                <span class="white-text email">Sistema de Gestión Integral</span>
            </div>
        </li>

        <!-- Cada ítem del menú: enlace a ?pagina=[sección] -->
        <!-- Se marca como 'active' si $pagina coincide con la sección -->
        <!-- Los iconos son de Material Icons -->
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

        <!-- Separador visual entre módulos y opciones de sistema -->
        <li>
            <div class="divider"></div>
        </li>

        <!-- Alternar tema oscuro/claro (manejado por JS en app.js) -->
        <li><a class="sidenav-link" id="themeToggle" style="cursor:pointer;"><i class="material-icons left"
                    id="themeIcon">dark_mode</i><span id="themeLabel">Modo Oscuro</span></a></li>

        <!-- Cerrar sesión (redirige al login, que reiniciará la sesión) -->
        <li><a href="?pagina=login" class="sidenav-link"><i class="material-icons left">logout</i>Cerrar Sesión</a></li>
    </ul>

    <!-- ============================================
         BARRA DE NAVEGACIÓN SUPERIOR
         Muestra el título de la página, reloj,
         notificaciones y usuario actual.
         ============================================ -->
    <header>
        <nav class="nav-extended indigo darken-3">
            <div class="nav-wrapper">
                <!-- Botón hamburguesa para mostrar/ocultar sidebar -->
                <!-- data-target="slide-out" se vincula con el id del sidenav -->
                <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>

                <!-- Título de la página visible en tablets y desktop -->
                <!-- hide-on-med-and-down: oculto en móviles -->
                <span class="brand-logo hide-on-med-and-down"
                    style="font-size:1.25rem;padding-left:1rem;"><?php echo $pageTitle; ?></span>

                <!-- Título centrado visible solo en móviles -->
                <!-- hide-on-large-only: oculto en desktop -->
                <span class="brand-logo hide-on-large-only"
                    style="font-size:1.1rem;left:50%;transform:translateX(-50%);"><?php echo $pageTitle; ?></span>

                <!-- Elementos de la derecha de la barra -->
                <ul id="nav-mobile" class="right">
                    <!-- Reloj digital actualizado por JavaScript -->
                    <li><span id="clock" class="white-text"
                            style="font-size:0.85rem;padding-right:1rem;opacity:0.85;"><i class="material-icons left"
                                style="font-size:1rem;">schedule</i>Cargando...</span></li>

                    <!-- Header extra opcional (ej: chips de estado en ciberControl) -->
                    <!-- Solo se muestra si $headerExtra tiene contenido -->
                    <?php if (!empty($headerExtra)): ?>
                    <li><?php echo $headerExtra; ?></li>
                    <?php endif; ?>

                    <!-- Campana de notificaciones con badge (contador) -->
                    <li>
                        <!-- tooltipped: muestra tooltip al hacer hover -->
                        <a class="tooltipped" data-position="bottom" data-tooltip="Notificaciones" id="notifBell"
                            style="cursor:pointer;position:relative;">
                            <i class="material-icons">notifications</i>
                            <!-- Badge rojo con contador de notificaciones (3 hardcodeado) -->
                            <span id="notifBadge" class="new badge red"
                                style="position:absolute;top:0;right:0;min-width:18px;height:18px;line-height:18px;font-size:0.65rem;padding:0 5px;border-radius:50%;">3</span>
                        </a>
                    </li>

                    <!-- Badge con el nombre del usuario (hardcodeado como Admin) -->
                    <li><span class="badge indigo lighten-2 white-text"
                            style="margin-right:1rem;font-size:0.8rem;padding:0.25rem 0.75rem;"><i
                                class="material-icons left" style="font-size:0.9rem;">person</i>Admin</span></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- ============================================
         CONTENIDO PRINCIPAL
         Aquí se inyecta la vista específica de cada página
         mediante la variable $contentView.
         ============================================ -->
    <main>
        <!-- container: contenedor centrado con padding -->
        <!-- max-width:1400px: ancho máximo para pantallas grandes -->
        <div class="container" style="padding-top:1.5rem;padding-bottom:2rem;max-width:1400px;width:95%;">
            <?php require $contentView; ?>
            <!-- $contentView es la ruta absoluta al archivo de vista -->
            <!-- Establecido por Controller::render() -->
        </div>
    </main>

    <!-- Botón flotante "Volver arriba" -->
    <!-- display:none por defecto; se muestra al hacer scroll vía JS -->
    <div id="backToTop" class="btn-floating indigo"
        style="position:fixed;bottom:2rem;right:2rem;z-index:999;display:none;">
        <i class="material-icons">keyboard_arrow_up</i>
    </div>

    <!-- Scripts globales al final del body para mejor rendimiento -->
    <!-- Materialize JS (depende de jQuery) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <!-- Lógica personalizada de la aplicación (carrito, filtros, tema, etc.) -->
    <script src="Public/js/app.js"></script>
</body>

</html>
