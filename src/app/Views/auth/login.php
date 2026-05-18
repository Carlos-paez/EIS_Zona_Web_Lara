<!DOCTYPE html>
<!-- ============================================================
     VISTA DE INICIO DE SESIÓN (LOGIN)
     ============================================================
     Página pública (no requiere autenticación). Se renderiza
     SIN el layout principal (usa renderPublic()).

     Muestra un formulario con diseño Material Design para que
     el usuario ingrese sus credenciales. Incluye:
       - Campo de usuario
       - Campo de contraseña
       - Botón de inicio de sesión
       - Mensaje de error si las credenciales son incorrectas
       - Botones de autenticación social (placeholders)
       - Selector de tema oscuro/claro
     ============================================================ -->
<html lang="es">
<head>
    <!-- Codificación UTF-8 para caracteres especiales -->
    <meta charset="utf-8">

    <!-- Diseño responsive para adaptarse a dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Evita que los buscadores indexen esta página de login -->
    <!-- noindex: no incluir en índice, nofollow: no seguir enlaces -->
    <meta name="robots" content="noindex,nofollow">

    <!-- Título de la pestaña del navegador -->
    <title>Login - EIS System</title>

    <!-- Google Material Icons (iconos vectoriales) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Materialize CSS v1.0.0 (framework Material Design) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <!-- Estilos específicos para la página de login -->
    <!-- Public/css/login.css contiene estilos para login-card, etc. -->
    <link rel="stylesheet" href="Public/css/login.css">
</head>
<body>

    <!-- Tarjeta de inicio de sesión centrada vertical y horizontalmente -->
    <!-- z-depth-4: sombra profunda de Materialize -->
    <!-- login-card: clase personalizada para estilos específicos -->
    <div class="card login-card z-depth-4 white">
        <div class="card-content" style="padding:0;">

            <!-- ========== LOGO Y ENCABEZADO ========== -->
            <div style="text-align:center;">
                <!-- Icono del sistema (emoji de rayo) con estilos CSS -->
                <div class="login-logo">⚡</div>
                <!-- Nombre del sistema en negrita -->
                <h4 class="card-title" style="font-weight:700;">EIS System</h4>
                <!-- Subtítulo descriptivo -->
                <p style="color:#78909c;margin-bottom:1.5rem;">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- ========== MENSAJE DE ERROR ========== -->
            <!-- Si la URL contiene ?error=1, mostrar alerta de credenciales incorrectas -->
            <!-- Esto ocurre cuando LoginController::validate() redirige con error=1 -->
            <?php if (isset($_GET['error'])): ?>
                <!-- Panel rojo claro con texto rojo oscuro -->
                <div class="card-panel red lighten-4 red-text text-darken-4" style="border-radius:8px;padding:0.75rem 1rem;">
                    <!-- Icono de advertencia -->
                    <i class="material-icons left" style="font-size:1.2rem;">warning</i>
                    Credenciales incorrectas. Por favor, intenta nuevamente.
                </div>
            <?php endif; ?>

            <!-- ========== FORMULARIO DE LOGIN ========== -->
            <!-- Envía POST a ?pagina=login_validate que es procesado -->
            <!-- por LoginController::validate() -->
            <form action="?pagina=login_validate" method="post">

                <!-- Campo de usuario con icono de persona -->
                <div class="input-field">
                    <!-- prefix: posiciona el icono a la izquierda del input -->
                    <i class="material-icons prefix">person</i>
                    <!-- type="text": campo de texto normal -->
                    <!-- required: validación HTML5 (no puede estar vacío) -->
                    <!-- autofocus: el cursor se posiciona aquí automáticamente -->
                    <input type="text" name="username" id="username" required autofocus>
                    <!-- label asociado al id del input -->
                    <label for="username">Usuario</label>
                </div>

                <!-- Campo de contraseña con icono de candado -->
                <div class="input-field">
                    <i class="material-icons prefix">lock</i>
                    <!-- type="password": oculta los caracteres escritos -->
                    <input type="password" name="password" id="password" required>
                    <label for="password">Contraseña</label>
                </div>

                <!-- Enlace "Olvidé mi contraseña" (placeholder no funcional) -->
                <!-- onclick="return false;" evita la navegación -->
                <div style="text-align:right;margin-bottom:1.5rem;">
                    <a href="#" onclick="return false;" style="color:var(--primary);">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- Botón de envío del formulario -->
                <!-- waves-effect: efecto de onda de Materialize al hacer clic -->
                <!-- width:100%: ocupa todo el ancho disponible -->
                <button type="submit" class="btn waves-effect waves-light indigo" style="width:100%;height:3rem;font-size:1rem;border-radius:8px;">
                    <i class="material-icons left">login</i>Iniciar Sesión
                </button>
            </form>

            <!-- ========== AUTENTICACIÓN SOCIAL ========== -->
            <!-- Separador visual "O continúa con" -->
            <div style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0 1rem;">
                <!-- Línea horizontal izquierda -->
                <div style="flex:1;height:1px;background:#e0e0e0;"></div>
                <span style="color:#90a4ae;font-size:0.9rem;">O continúa con</span>
                <!-- Línea horizontal derecha -->
                <div style="flex:1;height:1px;background:#e0e0e0;"></div>
            </div>

            <!-- Botones de inicio de sesión social (Google y GitHub) -->
            <!-- NO FUNCIONALES: muestran alerta al hacer clic -->
            <div style="display:flex;justify-content:center;gap:1rem;margin-bottom:1.5rem;">

                <!-- Botón de Google -->
                <button type="button" class="btn-flat" style="border:2px solid #e0e0e0;border-radius:12px;padding:0.75rem 1.5rem;" onclick="alert('Funcionalidad no disponible')">
                    <!-- SVG del logo de Google con 4 paths para cada color -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="20" height="20">
                        <!-- Azul: letra "G" -->
                        <path fill="#4285F4" d="M32 16.1c0-1.3-.1-2.7-.4-3.9H16v7.4h9c-.4 2-1.6 3.7-3.3 4.8v5h4.5c3.1-2.9 4.9-7.1 4.9-12.3z"/>
                        <!-- Verde: letra "o" -->
                        <path fill="#34A853" d="M16 32c4.3 0 7.9-1.4 10.5-3.8l-5-3.9c-1.4.9-3.2 1.5-5.5 1.5-4.2 0-7.8-2.8-9.1-6.6H1.4v4.1C3.9 28.3 9.4 32 16 32z"/>
                        <!-- Amarillo: letra "o" -->
                        <path fill="#FBBC05" d="M6.9 19.2c-.3-.9-.5-1.8-.5-2.8s.2-1.9.5-2.8V9.5H1.4C.5 11.2 0 13.5 0 16s.5 4.8 1.4 6.5l5.5-4.3z"/>
                        <!-- Rojo: letra "g" -->
                        <path fill="#EA4335" d="M16 6.3c2.3 0 4.3.8 5.9 2.3l4.4-4.4C23.9 1.8 20.3 0 16 0 9.4 0 3.9 3.7 1.4 9.5l5.5 4.3C8.2 9.1 11.8 6.3 16 6.3z"/>
                    </svg>
                </button>

                <!-- Botón de GitHub -->
                <button type="button" class="btn-flat" style="border:2px solid #e0e0e0;border-radius:12px;padding:0.75rem 1.5rem;" onclick="alert('Funcionalidad no disponible')">
                    <!-- SVG del logo de GitHub (un solo color #1B1F23) -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="20" height="20">
                        <path fill="#1B1F23" d="M16 0C7.2 0 0 7.2 0 16c0 7.1 4.6 13.1 10.9 15.2.8.1 1.1-.3 1.1-.8v-2.8c-4.4 1-5.4-2.1-5.4-2.1-.7-1.9-1.8-2.4-1.8-2.4-1.5-1 .1-1 .1-1 1.6.1 2.5 1.7 2.5 1.7 1.4 2.4 3.7 1.7 4.7 1.3.1-.8.6-1.4 1-1.7-3.5-.4-7.2-1.8-7.2-7.8 0-1.7.6-3.1 1.6-4.2-.2-.4-.7-1.9.2-4 0 0 1.3-.4 4.3 1.6 1.2-.4 2.6-.5 4-.5s2.7.2 4 .5c3-2 4.3-1.6 4.3-1.6.9 2.1.3 3.6.2 4 1 1.1 1.6 2.5 1.6 4.2 0 6.1-3.7 7.4-7.2 7.8.6.5 1.1 1.5 1.1 3v4.4c0 .4.3.9 1.1.8C27.4 29.1 32 23.1 32 16c0-8.8-7.2-16-16-16z"/>
                    </svg>
                </button>
            </div>

            <!-- Enlace de registro (placeholder no funcional) -->
            <p style="text-align:center;color:#90a4ae;font-size:0.9rem;">
                ¿No tienes una cuenta? <a href="#" onclick="return false;" style="color:var(--primary);">Regístrate</a>
            </p>
        </div>
    </div>

    <!-- Botón flotante para cambiar tema oscuro/claro -->
    <!-- fixed: siempre visible en la esquina superior derecha -->
    <!-- z-index:1000: se superpone a otros elementos -->
    <button class="btn-floating indigo" id="themeToggle" title="Cambiar tema" style="position:fixed;top:1rem;right:1rem;z-index:1000;">
        <i class="material-icons">dark_mode</i>
    </button>

    <!-- ========== SCRIPTS JAVASCRIPT ========== -->
    <!-- jQuery 3.7.1 (necesario para Materialize JS) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Materialize JS (componentes interactivos) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- Script de cambio de tema (oscuro/claro) -->
    <!-- Este script está inline porque es específico del login -->
    <!-- y debe ejecutarse antes de que el usuario interactúe -->
    <script>
        // ============================================
        // 1. INICIALIZAR TEMA DESDE LOCALSTORAGE
        // ============================================
        // Leer el tema guardado en localStorage del navegador.
        // Si no existe, usar 'light' (tema claro) como predeterminado.
        var currentTheme = localStorage.getItem('theme') || 'light';

        // Aplicar el tema al elemento <html> mediante el atributo data-theme.
        // Las reglas CSS en styles.css usan [data-theme="dark"] para
        // cambiar colores de fondo, texto, bordes, etc.
        $('html').attr('data-theme', currentTheme);

        // Actualizar el icono del botón de cambio de tema.
        // Si el tema es oscuro, mostrar icono de sol (light_mode).
        // Si el tema es claro, mostrar icono de luna (dark_mode).
        $('#themeToggle').html('<i class="material-icons">' + (currentTheme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');

        // ============================================
        // 2. MANEJAR CLIC EN EL BOTÓN DE TEMA
        // ============================================
        $('#themeToggle').on('click', function () {
            // Alternar entre 'dark' y 'light'
            var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';

            // Aplicar el nuevo tema al elemento <html>
            $('html').attr('data-theme', theme);

            // Guardar la preferencia en localStorage para que persista
            // entre sesiones del navegador (incluso cerrando la pestaña)
            localStorage.setItem('theme', theme);

            // Actualizar el icono del botón según el nuevo tema
            $(this).html('<i class="material-icons">' + (theme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');
        });
    </script>
</body>
</html>
