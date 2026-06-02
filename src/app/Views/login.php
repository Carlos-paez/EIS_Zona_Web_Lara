<!DOCTYPE html>
<!-- ============================================================
     VISTA DE INICIO DE SESIÓN (LOGIN)
     Página pública (no requiere autenticación).
     Muestra un formulario con diseño Materialize para que el
     usuario ingrese sus credenciales.
     ============================================================ -->
<html lang="es">
<head>
    <meta charset="utf-8">                           <!-- Codificación UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"> <!-- Diseño responsive con safe-area -->
    <meta name="theme-color" content="#1a237e">     <!-- Color de barra de navegación en móviles -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="robots" content="noindex,nofollow">  <!-- Evita que los buscadores indexen esta página -->
    <link rel="manifest" href="manifest.json">
    <title>Login - EIS System</title>
    <!-- Material Icons (locales) -->
    <link rel="stylesheet" href="Public/css/material-icons.css">
    <!-- Materialize CSS v1.0.0 (local) -->
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    <!-- Estilos específicos para la página de login -->
    <link rel="stylesheet" href="Public/css/login.css">
</head>
<body>

    <!-- Tarjeta de inicio de sesión centrada vertical y horizontalmente -->
    <div class="card login-card z-depth-4">
        <div class="card-content" style="padding:0;">
            <!-- Logo y encabezado -->
            <div style="text-align:center;">
                <div class="login-logo">⚡</div>                              <!-- Ícono del sistema -->
                <h4 class="card-title" style="font-weight:700;">EIS System</h4>
                <p style="color:var(--text-muted);margin-bottom:1.5rem;">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- Mensaje de error si viene ?error=1 en la URL (credenciales incorrectas) -->
            <?php if (isset($_GET['error'])): ?>
                <div class="card-panel red lighten-4 red-text text-darken-4" style="border-radius:8px;padding:0.75rem 1rem;">
                    <i class="material-icons left" style="font-size:1.2rem;">warning</i>
                    Credenciales incorrectas. Por favor, intenta nuevamente.
                </div>
            <?php endif; ?>

            <!-- Formulario que envía POST a ?pagina=login_validate -->
            <form action="?pagina=login_validate" method="post">
                <!-- Campo de usuario con ícono -->
                <div class="input-field">
                    <i class="material-icons prefix">person</i>
                    <input type="text" name="username" id="username" required autofocus> <!-- autofocus: cursor automático -->
                    <label for="username">Usuario</label>
                </div>

                <!-- Campo de contraseña con ícono -->
                <div class="input-field">
                    <i class="material-icons prefix">lock</i>
                    <input type="password" name="password" id="password" required>
                    <label for="password">Contraseña</label>
                </div>

                <!-- Enlace de "olvidé mi contraseña" (placeholder no funcional) -->
                <div style="text-align:right;margin-bottom:1.5rem;">
                    <a href="#" onclick="return false;" style="color:var(--primary);">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- Botón de envío del formulario -->
                <button type="submit" class="btn waves-effect waves-light indigo" style="width:100%;height:3rem;font-size:1rem;border-radius:8px;">
                    <i class="material-icons left">login</i>Iniciar Sesión
                </button>
            </form>

            <!-- Separador "O continúa con" (para autenticación social) -->
            <div style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0 1rem;">
                <div style="flex:1;height:1px;background:var(--border);"></div>
                <span style="color:var(--text-muted);font-size:0.9rem;">O continúa con</span>
                <div style="flex:1;height:1px;background:var(--border);"></div>
            </div>

            <!-- Botones de inicio de sesión social (Google y GitHub - no funcionales) -->
            <div class="social-login-row" style="display:flex;justify-content:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
                <button type="button" class="btn-flat waves-effect waves-light" style="border:2px solid var(--border);border-radius:12px;padding:0.75rem 1.5rem;min-width:48px;min-height:48px;display:flex;align-items:center;justify-content:center;" onclick="alert('Funcionalidad no disponible')">
                    <!-- SVG del logo de Google -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="20" height="20">
                        <path fill="#4285F4" d="M32 16.1c0-1.3-.1-2.7-.4-3.9H16v7.4h9c-.4 2-1.6 3.7-3.3 4.8v5h4.5c3.1-2.9 4.9-7.1 4.9-12.3z"/>
                        <path fill="#34A853" d="M16 32c4.3 0 7.9-1.4 10.5-3.8l-5-3.9c-1.4.9-3.2 1.5-5.5 1.5-4.2 0-7.8-2.8-9.1-6.6H1.4v4.1C3.9 28.3 9.4 32 16 32z"/>
                        <path fill="#FBBC05" d="M6.9 19.2c-.3-.9-.5-1.8-.5-2.8s.2-1.9.5-2.8V9.5H1.4C.5 11.2 0 13.5 0 16s.5 4.8 1.4 6.5l5.5-4.3z"/>
                        <path fill="#EA4335" d="M16 6.3c2.3 0 4.3.8 5.9 2.3l4.4-4.4C23.9 1.8 20.3 0 16 0 9.4 0 3.9 3.7 1.4 9.5l5.5 4.3C8.2 9.1 11.8 6.3 16 6.3z"/>
                    </svg>
                </button>
                <button type="button" class="btn-flat waves-effect waves-light" style="border:2px solid var(--border);border-radius:12px;padding:0.75rem 1.5rem;min-width:48px;min-height:48px;display:flex;align-items:center;justify-content:center;" onclick="alert('Funcionalidad no disponible')">
                    <!-- SVG del logo de GitHub -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="20" height="20">
                        <path fill="#1B1F23" d="M16 0C7.2 0 0 7.2 0 16c0 7.1 4.6 13.1 10.9 15.2.8.1 1.1-.3 1.1-.8v-2.8c-4.4 1-5.4-2.1-5.4-2.1-.7-1.9-1.8-2.4-1.8-2.4-1.5-1 .1-1 .1-1 1.6.1 2.5 1.7 2.5 1.7 1.4 2.4 3.7 1.7 4.7 1.3.1-.8.6-1.4 1-1.7-3.5-.4-7.2-1.8-7.2-7.8 0-1.7.6-3.1 1.6-4.2-.2-.4-.7-1.9.2-4 0 0 1.3-.4 4.3 1.6 1.2-.4 2.6-.5 4-.5s2.7.2 4 .5c3-2 4.3-1.6 4.3-1.6.9 2.1.3 3.6.2 4 1 1.1 1.6 2.5 1.6 4.2 0 6.1-3.7 7.4-7.2 7.8.6.5 1.1 1.5 1.1 3v4.4c0 .4.3.9 1.1.8C27.4 29.1 32 23.1 32 16c0-8.8-7.2-16-16-16z"/>
                    </svg>
                </button>
            </div>

            <!-- Enlace de registro (placeholder no funcional) -->
            <p style="text-align:center;color:var(--text-muted);font-size:0.9rem;">
                ¿No tienes una cuenta? <a href="#" onclick="return false;" style="color:var(--primary);">Regístrate</a>
            </p>
        </div>
    </div>

    <!-- Botón flotante para cambiar tema oscuro/claro -->
    <button class="btn-floating indigo" id="themeToggle" title="Cambiar tema" style="position:fixed;top:1rem;right:1rem;z-index:1000;">
        <i class="material-icons">dark_mode</i>
    </button>

    <!-- Scripts JavaScript -->
    <script src="Public/js/jquery-3.7.1.min.js"></script>
    <script src="Public/js/materialize.min.js"></script>
    <script src="Public/js/app.core.js"></script>
    <!-- Script de cambio de tema (oscuro/claro) -->
    <script>
        var currentTheme = localStorage.getItem('theme') || 'light';             // Lee el tema guardado en localStorage, o 'light' por defecto
        $('html').attr('data-theme', currentTheme);                               // Aplica el tema al elemento <html>
        $('#themeToggle').html('<i class="material-icons">' + (currentTheme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>'); // Actualiza el ícono

        $('#themeToggle').on('click', function () {
            var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark'; // Alterna entre dark y light
            $('html').attr('data-theme', theme);                                    // Aplica el nuevo tema
            localStorage.setItem('theme', theme);                                   // Guarda la preferencia en localStorage
            $(this).html('<i class="material-icons">' + (theme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>'); // Actualiza el ícono
        });
    </script>

    <!-- Registrar Service Worker para funcionamiento offline -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js');
    }
    </script>
</body>
</html>
