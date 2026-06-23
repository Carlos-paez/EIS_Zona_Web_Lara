<!DOCTYPE html>
<!-- ============================================================
     VISTA DE INICIO DE SESIÓN (LOGIN)
     Página pública (no requiere autenticación).
     Muestra un formulario con diseño Materialize para que el
     usuario ingrese sus credenciales.
     ============================================================ -->
<html lang="es">
<head>
    <!-- Definición de codificación de caracteres UTF-8 para soporte de tildes y caracteres especiales -->
    <meta charset="utf-8">
    <!-- Configuración de viewport para diseño responsive en dispositivos móviles, incluye safe-area para notch -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <!-- Color de tema que se muestra en la barra de navegación del navegador móvil -->
    <meta name="theme-color" content="#1a237e">
    <!-- Permite que la página se comporte como una aplicación web progresiva (PWA) en iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <!-- Estilo de la barra de estado en iOS (translúcida negra) -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <!-- Indica a los motores de búsqueda que no indexen esta página ni sigan los enlaces -->
    <meta name="robots" content="noindex,nofollow">
    <!-- Enlace al manifiesto de la aplicación web progresiva (PWA) -->
    <link rel="manifest" href="manifest.json">
    <!-- Título de la página que se muestra en la pestaña del navegador -->
    <title>Login - EIS System</title>
    <!-- Hoja de estilos de Material Icons (íconos locales) -->
    <link rel="stylesheet" href="Public/css/material-icons.css">
    <!-- Hoja de estilos de Materialize CSS v1.0.0 (archivo local) -->
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    <!-- Hoja de estilos personalizada específicamente para la página de login -->
    <link rel="stylesheet" href="Public/css/login.css">
</head>
<body>

    <!-- ================================================================ -->
    <!-- TARJETA PRINCIPAL DE INICIO DE SESIÓN -->
    <!-- Contenedor centrado vertical y horizontalmente con sombra (z-depth-4) -->
    <!-- ================================================================ -->
    <div class="card login-card z-depth-4">
        <!-- Contenido interno de la tarjeta, sin padding propio -->
        <div class="card-content" style="padding:0;">

            <!-- ================================================================ -->
            <!-- ENCABEZADO: LOGO Y TÍTULO DEL SISTEMA -->
            <!-- ================================================================ -->
            <div style="text-align:center;">
                <!-- Ícono representativo del sistema (rayo) usando un emoji -->
                <div class="login-logo">⚡</div>
                <!-- Nombre del sistema en negrita -->
                <h4 class="card-title" style="font-weight:700;">EIS System</h4>
                <!-- Texto de instrucción para el usuario -->
                <p style="color:var(--text-muted);margin-bottom:1.5rem;">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- ================================================================ -->
            <!-- MENSAJE DE ERROR (si existe) -->
            <!-- Se muestra solo si la URL contiene ?error=1 (credenciales incorrectas) -->
            <!-- ================================================================ -->
            <?php
            // Verifico si el parámetro 'error' viene en la URL (GET)
            if (isset($_GET['error'])): ?>
                <!-- Panel de error con fondo rojo claro y texto rojo oscuro -->
                <div class="card-panel red lighten-4 red-text text-darken-4" style="border-radius:8px;padding:0.75rem 1rem;">
                    <!-- Ícono de advertencia (triángulo con exclamación) -->
                    <i class="material-icons left" style="font-size:1.2rem;">warning</i>
                    <!-- Mensaje de error informando al usuario que las credenciales son incorrectas -->
                    Credenciales incorrectas. Por favor, intenta nuevamente.
                </div>
            <?php
            // Fin del bloque condicional
            endif; ?>

            <!-- ================================================================ -->
            <!-- FORMULARIO DE INICIO DE SESIÓN -->
            <!-- Envía los datos por método POST al controlador login_validate -->
            <!-- ================================================================ -->
            <form action="?pagina=login_validate" method="post">

                <!-- ---------------------------------------------------------------- -->
                <!-- CAMPO DE TEXTO: USUARIO -->
                <!-- ---------------------------------------------------------------- -->
                <div class="input-field">
                    <!-- Ícono de persona (prefijo dentro del input) -->
                    <i class="material-icons prefix">person</i>
                    <!-- Input de tipo texto para el nombre de usuario; required = obligatorio; autofocus = cursor automático al cargar -->
                    <input type="text" name="username" id="username" required autofocus>
                    <!-- Etiqueta flotante que se mueve al escribir -->
                    <label for="username">Usuario</label>
                </div>

                <!-- ---------------------------------------------------------------- -->
                <!-- CAMPO DE CONTRASEÑA -->
                <!-- ---------------------------------------------------------------- -->
                <div class="input-field">
                    <!-- Ícono de candado (prefijo dentro del input) -->
                    <i class="material-icons prefix">lock</i>
                    <!-- Input de tipo password para ocultar los caracteres mientras se escribe -->
                    <input type="password" name="password" id="password" required>
                    <!-- Etiqueta flotante -->
                    <label for="password">Contraseña</label>
                </div>

                <!-- ---------------------------------------------------------------- -->
                <!-- ENLACE: "¿OLVIDASTE TU CONTRASEÑA?" -->
                <!-- Placeholder no funcional, el onclick=false evita navegación -->
                <!-- ---------------------------------------------------------------- -->
                <div style="text-align:right;margin-bottom:1.5rem;">
                    <a href="#" onclick="return false;" style="color:var(--primary);">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- ---------------------------------------------------------------- -->
                <!-- BOTÓN DE ENVÍO DEL FORMULARIO -->
                <!-- ---------------------------------------------------------------- -->
                <button type="submit" class="btn waves-effect waves-light indigo" style="width:100%;height:3rem;font-size:1rem;border-radius:8px;">
                    <!-- Ícono de login a la izquierda del texto -->
                    <i class="material-icons left">login</i>Iniciar Sesión
                </button>
            </form>

            <!-- ================================================================ -->
            <!-- SEPARADOR: "O CONTINÚA CON" -->
            <!-- Línea decorativa con texto en medio para separar login normal del social -->
            <!-- ================================================================ -->
            <div style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0 1rem;">
                <!-- Línea izquierda del separador -->
                <div style="flex:1;height:1px;background:var(--border);"></div>
                <!-- Texto del separador -->
                <span style="color:var(--text-muted);font-size:0.9rem;">O continúa con</span>
                <!-- Línea derecha del separador -->
                <div style="flex:1;height:1px;background:var(--border);"></div>
            </div>

            <!-- ================================================================ -->
            <!-- BOTONES DE INICIO DE SESIÓN SOCIAL -->
            <!-- Google y GitHub. No funcionales, muestran alerta al hacer clic -->
            <!-- ================================================================ -->
            <div class="social-login-row" style="display:flex;justify-content:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
                <!-- Botón de inicio de sesión con Google -->
                <button type="button" class="btn-flat waves-effect waves-light" style="border:2px solid var(--border);border-radius:12px;padding:0.75rem 1.5rem;min-width:48px;min-height:48px;display:flex;align-items:center;justify-content:center;" onclick="alert('Funcionalidad no disponible')">
                    <!-- SVG del logo de Google con los 4 colores característicos (azul, verde, amarillo, rojo) -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="20" height="20">
                        <!-- Letra "G" color azul (#4285F4) -->
                        <path fill="#4285F4" d="M32 16.1c0-1.3-.1-2.7-.4-3.9H16v7.4h9c-.4 2-1.6 3.7-3.3 4.8v5h4.5c3.1-2.9 4.9-7.1 4.9-12.3z"/>
                        <!-- Letra "G" color verde (#34A853) -->
                        <path fill="#34A853" d="M16 32c4.3 0 7.9-1.4 10.5-3.8l-5-3.9c-1.4.9-3.2 1.5-5.5 1.5-4.2 0-7.8-2.8-9.1-6.6H1.4v4.1C3.9 28.3 9.4 32 16 32z"/>
                        <!-- Letra "G" color amarillo (#FBBC05) -->
                        <path fill="#FBBC05" d="M6.9 19.2c-.3-.9-.5-1.8-.5-2.8s.2-1.9.5-2.8V9.5H1.4C.5 11.2 0 13.5 0 16s.5 4.8 1.4 6.5l5.5-4.3z"/>
                        <!-- Letra "G" color rojo (#EA4335) -->
                        <path fill="#EA4335" d="M16 6.3c2.3 0 4.3.8 5.9 2.3l4.4-4.4C23.9 1.8 20.3 0 16 0 9.4 0 3.9 3.7 1.4 9.5l5.5 4.3C8.2 9.1 11.8 6.3 16 6.3z"/>
                    </svg>
                </button>
                <!-- Botón de inicio de sesión con GitHub -->
                <button type="button" class="btn-flat waves-effect waves-light" style="border:2px solid var(--border);border-radius:12px;padding:0.75rem 1.5rem;min-width:48px;min-height:48px;display:flex;align-items:center;justify-content:center;" onclick="alert('Funcionalidad no disponible')">
                    <!-- SVG del logo de GitHub (ícono del gato) -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="20" height="20">
                        <!-- Silueta de GitHub en color casi negro (#1B1F23) -->
                        <path fill="#1B1F23" d="M16 0C7.2 0 0 7.2 0 16c0 7.1 4.6 13.1 10.9 15.2.8.1 1.1-.3 1.1-.8v-2.8c-4.4 1-5.4-2.1-5.4-2.1-.7-1.9-1.8-2.4-1.8-2.4-1.5-1 .1-1 .1-1 1.6.1 2.5 1.7 2.5 1.7 1.4 2.4 3.7 1.7 4.7 1.3.1-.8.6-1.4 1-1.7-3.5-.4-7.2-1.8-7.2-7.8 0-1.7.6-3.1 1.6-4.2-.2-.4-.7-1.9.2-4 0 0 1.3-.4 4.3 1.6 1.2-.4 2.6-.5 4-.5s2.7.2 4 .5c3-2 4.3-1.6 4.3-1.6.9 2.1.3 3.6.2 4 1 1.1 1.6 2.5 1.6 4.2 0 6.1-3.7 7.4-7.2 7.8.6.5 1.1 1.5 1.1 3v4.4c0 .4.3.9 1.1.8C27.4 29.1 32 23.1 32 16c0-8.8-7.2-16-16-16z"/>
                    </svg>
                </button>
            </div>

            <!-- ================================================================ -->
            <!-- ENLACE DE REGISTRO -->
            <!-- Placeholder no funcional para "¿No tienes una cuenta? Regístrate" -->
            <!-- ================================================================ -->
            <p style="text-align:center;color:var(--text-muted);font-size:0.9rem;">
                ¿No tienes una cuenta? <a href="#" onclick="return false;" style="color:var(--primary);">Regístrate</a>
            </p>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- BOTÓN FLOTANTE PARA CAMBIAR TEMA (OSCURO / CLARO) -->
    <!-- Posición fija en la esquina superior derecha, siempre visible -->
    <!-- ================================================================ -->
    <button class="btn-floating indigo" id="themeToggle" title="Cambiar tema" style="position:fixed;top:1rem;right:1rem;z-index:1000;">
        <!-- Ícono de modo oscuro (luna) por defecto, se cambia dinámicamente con JS -->
        <i class="material-icons">dark_mode</i>
    </button>

    <!-- ================================================================ -->
    <!-- SCRIPTS JAVASCRIPT -->
    <!-- Carga de librerías y lógica de la aplicación -->
    <!-- ================================================================ -->

    <!-- jQuery 3.7.1 (minificado) -->
    <script src="Public/js/jquery-3.7.1.min.js"></script>
    <!-- Materialize JS v1.0.0 (minificado) para componentes como modales, tooltips, etc. -->
    <script src="Public/js/materialize.min.js"></script>
    <!-- Archivo JS principal de la aplicación (funciones globales) -->
    <script src="Public/js/app.core.js"></script>

    <!-- ================================================================ -->
    <!-- SCRIPT: CAMBIO DE TEMA (OSCURO / CLARO) -->
    <!-- Lee y guarda la preferencia en localStorage, alterna al hacer clic -->
    <!-- ================================================================ -->
    <script>
        // Obtiene el tema guardado en localStorage, o 'light' como valor por defecto si no existe
        var currentTheme = localStorage.getItem('theme') || 'light';
        // Aplica el tema al atributo data-theme del elemento <html> (usado por CSS para variables)
        $('html').attr('data-theme', currentTheme);
        // Actualiza el ícono del botón según el tema actual: luna para dark, sol para light
        $('#themeToggle').html('<i class="material-icons">' + (currentTheme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');

        // Asigna un evento click al botón de cambio de tema
        $('#themeToggle').on('click', function () {
            // Determina el nuevo tema alternando: si es dark pasa a light, si es light pasa a dark
            var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
            // Aplica el nuevo tema al elemento <html>
            $('html').attr('data-theme', theme);
            // Guarda la preferencia en localStorage para que persista entre sesiones
            localStorage.setItem('theme', theme);
            // Actualiza el ícono del botón según el nuevo tema
            $(this).html('<i class="material-icons">' + (theme === 'dark' ? 'light_mode' : 'dark_mode') + '</i>');
        });
    </script>

    <!-- ================================================================ -->
    <!-- SCRIPT: REGISTRO DEL SERVICE WORKER -->
    <!-- Habilita la funcionalidad offline (PWA) registrando el Service Worker -->
    <!-- ================================================================ -->
    <script>
    // Verifica si el navegador soporta Service Workers (navegadores modernos)
    if ('serviceWorker' in navigator) {
        // Registra el archivo sw.js como Service Worker para cachear recursos
        navigator.serviceWorker.register('sw.js');
    }
    </script>
</body>
</html>
