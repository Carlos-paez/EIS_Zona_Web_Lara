<!DOCTYPE html>
<!-- ============================================================
     VISTA DE MENÚ PRINCIPAL
     Página protegida que muestra un menú tipo tarjeta con
     enlaces a los módulos principales del sistema.
     ============================================================ -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - EIS System</title>
    <!-- Material Icons (locales) -->
    <link rel="stylesheet" href="Public/css/material-icons.css">
    <!-- Materialize CSS (local) -->
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    <!-- Estilos CSS embebidos específicos para esta página -->
    <style>
        body {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f23 0%, #1e1e3f 100%); /* Fondo degradado oscuro */
            padding: 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .menu-card {
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            overflow: hidden;
        }

        @media only screen and (max-width: 600px) {
            body { padding: 0.5rem; align-items: flex-start; padding-top: 2rem; }
            .menu-card { max-width: 100%; border-radius: 8px; }
            .menu-card .card-content { padding: 1.25rem !important; }
            .menu-card .card-title { font-size: 1.2rem; }
            .menu-card .menu-item { padding: 0.85rem 1rem; font-size: 0.9rem; gap: 0.75rem; min-height: 44px; }
            .menu-card .menu-footer { margin-top: 1rem; padding-top: 1rem; }
            #themeToggle { width: 44px; height: 44px; }
            #themeToggle i { line-height: 44px; font-size: 1.3rem; }
        }

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

        .menu-item:hover {
            background: #3949ab;           /* Fondo indigo al pasar el mouse */
            color: white;
            transform: translateX(4px);     /* Efecto de desplazamiento lateral */
        }

        .menu-item .material-icons { font-size: 1.3rem; }

        .menu-arrow {
            margin-left: auto;              /* Empuja la flecha hacia la derecha */
            opacity: 0;                     /* Oculto por defecto */
            transition: all 0.2s ease;
        }

        .menu-item:hover .menu-arrow { opacity: 1; transform: translateX(4px); } /* Aparece al hover */

        .menu-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eceff1;
        }

        /* Estilos para tema oscuro (data-theme="dark") */
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

    <!-- Botón flotante de cambio de tema -->
    <button class="btn-floating indigo" id="themeToggle" title="Cambiar tema" style="position:fixed;top:1rem;right:1rem;z-index:1000;">
        <i class="material-icons">dark_mode</i>
    </button>

    <!-- Tarjeta del menú principal -->
    <div class="card menu-card white z-depth-4">
        <div class="card-content" style="padding:2rem;">
            <!-- Encabezado con logo y título -->
            <div style="text-align:center;margin-bottom:2rem;">
                <div style="width:60px;height:60px;background:linear-gradient(135deg,#3949ab,#7c4dff);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;color:white;">◆</div>
                <h4 class="card-title" style="font-weight:700;">Menú Principal</h4>
                <p style="color:#90a4ae;font-size:0.9rem;margin-top:0.25rem;">Selecciona una opción</p>
            </div>

            <!-- Lista de enlaces a los módulos -->
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <a href="?pagina=dashboard" class="menu-item">
                    <i class="material-icons">dashboard</i> Dashboard
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <a href="?pagina=inventario" class="menu-item">
                    <i class="material-icons">inventory_2</i> Inventario
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <a href="?pagina=ciberControl" class="menu-item">
                    <i class="material-icons">computer</i> Cyber Control
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <a href="?pagina=proveedores" class="menu-item">
                    <i class="material-icons">request_quote</i> Proveedores
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <a href="?pagina=reportes" class="menu-item">
                    <i class="material-icons">bar_chart</i> Reportes
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <a href="?pagina=ventas" class="menu-item">
                    <i class="material-icons">shopping_cart</i> Ventas
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
                <a href="?pagina=activos" class="menu-item">
                    <i class="material-icons">build</i> Activos
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>
            </div>

            <!-- Pie con información del usuario -->
            <div class="menu-footer">
                <div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;color:#90a4ae;font-size:0.9rem;">
                    <span class="chip indigo white-text" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;padding:0;">A</span>
                    admin
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="Public/js/jquery-3.7.1.min.js"></script>
    <script src="Public/js/materialize.min.js"></script>
    <script src="Public/js/app.core.js"></script>
    <script src="Public/js/app.init.js"></script>
    <script src="Public/js/app.ui.js"></script>
    <!-- Script de cambio de tema oscuro/claro -->
    <script>
        var currentTheme = localStorage.getItem('theme') || 'light';             // Lee el tema guardado
        $('html').attr('data-theme', currentTheme);                               // Aplica el tema al HTML
        $('#themeToggle').html('<i class="material-icons">' + (currentTheme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');

        $('#themeToggle').on('click', function () {
            var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark'; // Alterna el tema
            $('html').attr('data-theme', theme);
            localStorage.setItem('theme', theme);
            $(this).html('<i class="material-icons">' + (theme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');
        });
    </script>
</body>
</html>
