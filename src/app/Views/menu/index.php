<!DOCTYPE html>
<!-- ============================================================
     VISTA DE MENÚ PRINCIPAL
     ============================================================
     Página protegida que muestra un menú tipo tarjeta con
     enlaces a los módulos principales del sistema.

     NOTA: Esta vista tiene su propia estructura HTML completa
     (<!DOCTYPE html>, <html>, <head>, <body>) pero se renderiza
     DENTRO del layout principal (MenuController::index() usa
     render(), no renderPublic()). Esto puede causar anidamiento
     de etiquetas HTML. Es un comportamiento heredado de la
     versión original de la aplicación.
     ============================================================ -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- viewport: diseño responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - EIS System</title>

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Materialize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <!-- ========== ESTILOS CSS EM BEBIDOS ========== -->
    <!-- Estilos específicos para esta página. No están en styles.css
         porque esta página tiene un diseño único tipo dashboard/card
         que no se usa en ningún otro módulo. -->
    <style>
        /* === CONTENEDOR PRINCIPAL === */
        body {
            min-height: 100vh;                          /* Altura mínima = toda la ventana */
            display: flex;                              /* Flexbox para centrar */
            align-items: center;                        /* Centrado vertical */
            justify-content: center;                    /* Centrado horizontal */
            background: linear-gradient(135deg, #0f0f23 0%, #1e1e3f 100%); /* Fondo degradado oscuro */
            padding: 2rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* === TARJETA DEL MENÚ === */
        .menu-card {
            width: 100%;
            max-width: 420px;                           /* Ancho máximo en desktop */
            border-radius: 12px;
            overflow: hidden;                           /* Esquinas redondeadas */
        }

        /* === CADA ITEM DEL MENÚ === */
        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;                                  /* Espacio entre icono y texto */
            padding: 1rem 1.25rem;
            border-radius: 12px;
            color: #263238;
            font-weight: 500;
            transition: all 0.2s ease;                  /* Animación suave al hacer hover */
            text-decoration: none;                      /* Sin subrayado */
            background: #f5f7fa;                        /* Fondo gris claro */
        }

        .menu-item:hover {
            background: #3949ab;                        /* Fondo indigo al pasar el mouse */
            color: white;
            transform: translateX(4px);                  /* Se desplaza 4px a la derecha */
        }

        .menu-item .material-icons { font-size: 1.3rem; }

        /* === FLECHA INDICADORA === */
        .menu-arrow {
            margin-left: auto;                           /* Empuja la flecha hacia la derecha */
            opacity: 0;                                  /* Oculta por defecto */
            transition: all 0.2s ease;
        }

        .menu-item:hover .menu-arrow {
            opacity: 1;                                  /* Aparece al hacer hover */
            transform: translateX(4px);                  /* Se desplaza ligeramente */
        }

        /* === PIE DE PÁGINA DEL MENÚ === */
        .menu-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eceff1;               /* Línea separadora */
        }

        /* === ESTILOS PARA TEMA OSCURO === */
        /* Se activan cuando el atributo data-theme="dark" está en <html> */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0a0a1a 0%, #151530 100%);
        }
        [data-theme="dark"] .menu-card {
            background: #1e293b;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        [data-theme="dark"] .menu-card .card-title { color: #f1f5f9; }
        [data-theme="dark"] .menu-card p { color: #94a3b8; }
        [data-theme="dark"] .menu-item { background: #334155; color: #f1f5f9; }
        [data-theme="dark"] .menu-item:hover { background: #5c6bc0; color: white; }
        [data-theme="dark"] .menu-footer { border-top-color: #334155; }
        [data-theme="dark"] .menu-footer span { color: #94a3b8; }
    </style>
</head>
<body>

    <!-- Botón flotante de cambio de tema (esquina superior derecha) -->
    <button class="btn-floating indigo" id="themeToggle" title="Cambiar tema" style="position:fixed;top:1rem;right:1rem;z-index:1000;">
        <i class="material-icons">dark_mode</i>
    </button>

    <!-- ========== TARJETA DEL MENÚ PRINCIPAL ========== -->
    <div class="card menu-card white z-depth-4">
        <div class="card-content" style="padding:2rem;">

            <!-- Encabezado con logo y título -->
            <div style="text-align:center;margin-bottom:2rem;">
                <!-- Logo: cuadrado con degradado y símbolo de diamante -->
                <div style="width:60px;height:60px;background:linear-gradient(135deg,#3949ab,#7c4dff);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;color:white;">◆</div>
                <h4 class="card-title" style="font-weight:700;">Menú Principal</h4>
                <p style="color:#90a4ae;font-size:0.9rem;margin-top:0.25rem;">Selecciona una opción</p>
            </div>

            <!-- Lista de enlaces a los módulos del sistema -->
            <div style="display:flex;flex-direction:column;gap:0.75rem;">

                <!-- Enlace a Dashboard -->
                <a href="?pagina=dashboard" class="menu-item">
                    <i class="material-icons">dashboard</i> Dashboard
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

                <!-- Enlace a Inventario -->
                <a href="?pagina=inventario" class="menu-item">
                    <i class="material-icons">inventory_2</i> Inventario
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

                <!-- Enlace a Cyber Control -->
                <a href="?pagina=ciberControl" class="menu-item">
                    <i class="material-icons">computer</i> Cyber Control
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

                <!-- Enlace a Proveedores -->
                <a href="?pagina=proveedores" class="menu-item">
                    <i class="material-icons">request_quote</i> Proveedores
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

                <!-- Enlace a Reportes -->
                <a href="?pagina=reportes" class="menu-item">
                    <i class="material-icons">bar_chart</i> Reportes
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

                <!-- Enlace a Ventas -->
                <a href="?pagina=ventas" class="menu-item">
                    <i class="material-icons">shopping_cart</i> Ventas
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

                <!-- Enlace a Activos -->
                <a href="?pagina=activos" class="menu-item">
                    <i class="material-icons">build</i> Activos
                    <span class="menu-arrow material-icons">arrow_forward</span>
                </a>

            </div>

            <!-- Pie con información del usuario actual -->
            <div class="menu-footer">
                <div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;color:#90a4ae;font-size:0.9rem;">
                    <!-- Avatar circular con inicial del usuario -->
                    <span class="chip indigo white-text" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;padding:0;">A</span>
                    admin  <!-- Nombre de usuario -->
                </div>
            </div>

        </div>
    </div>

    <!-- ========== SCRIPTS JAVASCRIPT ========== -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- Script de cambio de tema oscuro/claro -->
    <script>
        // ============================================
        // INICIALIZAR TEMA DESDE LOCALSTORAGE
        // ============================================
        // Leer el tema guardado. Si no existe, usar 'light'.
        var currentTheme = localStorage.getItem('theme') || 'light';
        // Aplicar el tema al elemento <html>
        $('html').attr('data-theme', currentTheme);
        // Actualizar icono del botón según el tema actual
        $('#themeToggle').html('<i class="material-icons">' + (currentTheme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');

        // ============================================
        // MANEJAR CLIC EN BOTÓN DE TEMA
        // ============================================
        $('#themeToggle').on('click', function () {
            // Alternar entre dark y light
            var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
            // Aplicar el nuevo tema
            $('html').attr('data-theme', theme);
            // Guardar en localStorage para persistencia
            localStorage.setItem('theme', theme);
            // Actualizar icono
            $(this).html('<i class="material-icons">' + (theme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');
        });
    </script>
</body>
</html>
