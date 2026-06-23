<!DOCTYPE html>
<!-- ============================================================
     VISTA DE MENÚ PRINCIPAL
     Página protegida que muestra un menú tipo tarjeta con
     enlaces a los módulos principales del sistema.
     ============================================================ -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Configuración del viewport para diseño responsivo -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - EIS System</title>
    <!-- Hoja de estilos de Material Icons (archivo local) -->
    <link rel="stylesheet" href="Public/css/material-icons.css">
    <!-- Hoja de estilos de Materialize CSS (archivo local) -->
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    <!-- ===== ESTILOS CSS EMBEBIDOS ESPECÍFICOS PARA ESTA PÁGINA ===== -->
    <style>
        /* Estilos generales del body: ocupa toda la pantalla, centrado, fondo degradado oscuro */
        body {
            min-height: 100vh;
            min-height: 100dvh; /* Unidad dinámica para móviles */
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f23 0%, #1e1e3f 100%); /* Fondo degradado oscuro */
            padding: 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Tarjeta contenedora del menú con ancho máximo definido */
        .menu-card {
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            overflow: hidden; /* Oculta el desbordamiento de las esquinas redondeadas */
        }

        /* Media query para pantallas pequeñas (móviles) */
        @media only screen and (max-width: 600px) {
            body { padding: 0.5rem; align-items: flex-start; padding-top: 2rem; }
            .menu-card { max-width: 100%; border-radius: 8px; } /* Menos redondeado en móvil */
            .menu-card .card-content { padding: 1.25rem !important; }
            .menu-card .card-title { font-size: 1.2rem; }
            .menu-card .menu-item { padding: 0.85rem 1rem; font-size: 0.9rem; gap: 0.75rem; min-height: 44px; }
            .menu-card .menu-footer { margin-top: 1rem; padding-top: 1rem; }
            #themeToggle { width: 44px; height: 44px; }
            #themeToggle i { line-height: 44px; font-size: 1.3rem; }
        }

        /* Estilo de cada ítem del menú (enlace) */
        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            color: #263238;
            font-weight: 500;
            transition: all 0.2s ease;    /* Transición suave al hacer hover */
            text-decoration: none;
            background: #f5f7fa;
        }

        /* Efecto hover: fondo índigo, texto blanco y desplazamiento lateral */
        .menu-item:hover {
            background: #3949ab;           /* Fondo indigo al pasar el mouse */
            color: white;
            transform: translateX(4px);     /* Efecto de desplazamiento lateral */
        }

        /* Tamaño de los iconos en los ítems del menú */
        .menu-item .material-icons { font-size: 1.3rem; }

        /* Flecha indicadora hacia la derecha */
        .menu-arrow {
            margin-left: auto;              /* Empuja la flecha hacia la derecha */
            opacity: 0;                     /* Oculto por defecto */
            transition: all 0.2s ease;
        }

        /* Al hacer hover, la flecha se hace visible y se desplaza */
        .menu-item:hover .menu-arrow { opacity: 1; transform: translateX(4px); }

        /* Pie de la tarjeta con información del usuario */
        .menu-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eceff1;
        }

        /* ===== ESTILOS PARA TEMA OSCURO (data-theme="dark") ===== */
        [data-theme="dark"] body { background: linear-gradient(135deg, #0a0a1a 0%, #151530 100%); }
        [data-theme="dark"] .menu-card { background: #1e293b; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        [data-theme="dark"] .menu-card .card-title { color: #f1f5f9; }
        [data-theme="dark"] .menu-card p { color: #94a3b8; }
        [data-theme="dark"] .menu-item { background: #334155; color: #f1f5f9; }
        [data-theme="dark"] .menu-item:hover { background: #5c6bc0; color: white; }
        [data-theme="dark"] .menu-footer { border-top-color: #334155; }
        [data-theme="dark"] .menu-footer span { color: #94a3b8; }
    </style>
</head>
<body>

    <!-- Botón flotante de cambio de tema oscuro/claro -->
    <button class="btn-floating indigo" id="themeToggle" title="Cambiar tema" style="position:fixed;top:1rem;right:1rem;z-index:1000;">
        <i class="material-icons">dark_mode</i>
    </button>

    <!-- ===== TARJETA DEL MENÚ PRINCIPAL ===== -->
    <div class="card menu-card white z-depth-4">
        <div class="card-content" style="padding:2rem;">
            <!-- Encabezado con logo y título -->
            <div style="text-align:center;margin-bottom:2rem;">
                <!-- Logo: diamante con degradado -->
                <div style="width:60px;height:60px;background:linear-gradient(135deg,#3949ab,#7c4dff);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;color:white;">◆</div>
                <h4 class="card-title" style="font-weight:700;">Menú Principal</h4>
                <p style="color:#90a4ae;font-size:0.9rem;margin-top:0.25rem;">Selecciona una opción</p>
            </div>

            <!-- Lista de enlaces a los módulos del sistema -->
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <!-- Enlace al Dashboard -->
                <a href="?pagina=dashboard" class="menu-item">
                    <i class="material-icons">dashboard</i> Dashboard
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <!-- Enlace al módulo de Inventario -->
                <a href="?pagina=inventario" class="menu-item">
                    <i class="material-icons">inventory_2</i> Inventario
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <!-- Enlace al módulo de Cyber Control -->
                <a href="?pagina=ciberControl" class="menu-item">
                    <i class="material-icons">computer</i> Cyber Control
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <!-- Enlace al módulo de Proveedores -->
                <a href="?pagina=proveedores" class="menu-item">
                    <i class="material-icons">request_quote</i> Proveedores
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <!-- Enlace al módulo de Reportes -->
                <a href="?pagina=reportes" class="menu-item">
                    <i class="material-icons">bar_chart</i> Reportes
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <!-- Enlace al módulo de Ventas -->
                <a href="?pagina=ventas" class="menu-item">
                    <i class="material-icons">shopping_cart</i> Ventas
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <!-- Enlace al módulo de Activos -->
                <a href="?pagina=activos" class="menu-item">
                    <i class="material-icons">build</i> Activos
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
            </div>

            <!-- Pie con información del usuario autenticado -->
            <div class="menu-footer">
                <div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;color:#90a4ae;font-size:0.9rem;">
                    <!-- Avatar circular con la inicial del usuario -->
                    <span class="chip indigo white-text" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;padding:0;">A</span>
                    admin
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPTS JAVASCRIPT ===== -->
    <!-- Librería jQuery -->
    <script src="Public/js/jquery-3.7.1.min.js"></script>
    <!-- Framework Materialize JS -->
    <script src="Public/js/materialize.min.js"></script>
    <!-- Scripts personalizados de la aplicación -->
    <script src="Public/js/app.core.js"></script>
    <script src="Public/js/app.init.js"></script>
    <script src="Public/js/app.ui.js"></script>
    <!-- Script de cambio de tema oscuro/claro -->
    <script>
        // Lee el tema guardado en localStorage, o establece 'light' por defecto
        var currentTheme = localStorage.getItem('theme') || 'light';
        // Aplica el tema como atributo data-theme en el elemento <html>
        $('html').attr('data-theme', currentTheme);
        // Actualiza el ícono del botón según el tema actual
        $('#themeToggle').html('<i class="material-icons">' + (currentTheme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');

        // Manejador de clic para alternar entre tema oscuro y claro
        $('#themeToggle').on('click', function () {
            // Determina el nuevo tema (inverso del actual)
            var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
            // Aplica el nuevo tema
            $('html').attr('data-theme', theme);
            // Guarda la preferencia en localStorage para persistencia
            localStorage.setItem('theme', theme);
            // Actualiza el ícono del botón
            $(this).html('<i class="material-icons">' + (theme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');
        });
    </script>
</body>
</html>
