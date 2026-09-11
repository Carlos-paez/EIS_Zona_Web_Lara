// =====================================================================
// ARCHIVO: app.init.js
// FUNCIÓN: Inicialización global de la aplicación.
//          Se ejecuta cuando el DOM está listo y configura:
//          - Componentes de Materialize (sidenav, selects, tooltips, modales)
//          - Reloj digital en la barra de navegación
//          - Tema oscuro/claro (persistente en localStorage)
//          - Animaciones de entrada (fadeIn)
//          - Animación de contadores (efecto de conteo progresivo)
//          - Botón "Volver arriba" (back to top)
// =====================================================================

// Espero a que el DOM esté completamente cargado antes de ejecutar
$(function () {

    // ================================================================
    // INICIALIZACIÓN DE COMPONENTES MATERIALIZE
    // ================================================================

    // Inicializo el menú lateral (sidenav) - sidebar responsive
    $('.sidenav').sidenav();
    // Inicializo los selects personalizados de Materialize (reemplaza <select> nativos)
    $('select').formSelect();
    // Inicializo los tooltips (globitos informativos al hover)
    $('.tooltipped').tooltip();
    // Inicializo todos los modales (ventanas emergentes)
    $('.modal').modal();

    // ================================================================
    // FUNCIÓN: actualizarReloj()
    // PROPÓSITO: Muestra la hora y fecha actual en el elemento #clock
    //            de la barra de navegación. Se actualiza cada segundo.
    // ================================================================
    function actualizarReloj() {
        var now = new Date(); // Obtengo la fecha/hora actual del sistema
        // Opciones de formato para la hora: 2 dígitos, formato 24h
        var opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        var timeStr = now.toLocaleTimeString('es-ES', opts); // Ej: "14:30:25"
        var dateStr = now.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' }); // Ej: "22 jun 2026"
        // Actualizo el contenido del span con id="clock"
        $('#clock').text(timeStr + ' - ' + dateStr);
    }
    actualizarReloj(); // Llamada inicial para mostrar la hora inmediatamente
    setInterval(actualizarReloj, 1000); // Actualizo cada 1000ms (1 segundo)

    // ================================================================
    // FUNCIÓN: updateThemeUI(theme)
    // PROPÓSITO: Actualiza los íconos y textos de la interfaz según
    //            el tema actual (oscuro o claro).
    // PARÁMETROS:
    //   theme - String: 'dark' o 'light'
    // ================================================================
    function updateThemeUI(theme) {
        var isDark = theme === 'dark'; // Determino si el tema es oscuro
        // Cambio el ícono: si es oscuro, muestro "light_mode" (sol); si claro, "dark_mode" (luna)
        $('#themeIcon').text(isDark ? 'light_mode' : 'dark_mode');
        // Cambio la etiqueta de texto según el tema
        $('#themeLabel').text(isDark ? 'Modo Claro' : 'Modo Oscuro');
    }

    // ================================================================
    // APLICACIÓN DEL TEMA GUARDADO
    // ================================================================

    // Leo el tema guardado en localStorage; si no existe, uso 'light' por defecto
    var currentTheme = localStorage.getItem('theme') || 'light';
    // Aplico el tema al atributo data-theme del elemento <html>
    $('html').attr('data-theme', currentTheme);
    // Actualizo los íconos y textos de la UI para que coincidan con el tema
    updateThemeUI(currentTheme);

    // ================================================================
    // EVENTO: Click en #themeToggle (cambio de tema)
    // Cuando el usuario hace clic en el ítem del sidebar para cambiar
    // entre tema oscuro y claro.
    // ================================================================
    $(document).on('click', '#themeToggle', function () {
        // Determino el nuevo tema: si es dark -> light, si no -> dark
        var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
        // Aplico el nuevo tema al <html>
        $('html').attr('data-theme', theme);
        // Guardo la preferencia en localStorage para que persista entre recargas
        localStorage.setItem('theme', theme);
        // Actualizo los íconos y textos de la UI
        updateThemeUI(theme);
        // Muestro un toast notificando el cambio de tema
        EIS.toast('Tema cambiado a ' + (theme === 'dark' ? 'oscuro' : 'claro'), 'indigo', 'palette');
    });

    // ================================================================
    // ANIMACIONES DE ENTRADA
    // ================================================================

    // El <main> aparece con un efecto fadeIn de 400ms
    $('main').hide().fadeIn(400);
    // El contenedor .container aparece con un efecto fadeIn de 500ms
    $('.container').hide().fadeIn(500);

    // ================================================================
    // FUNCIÓN: animarContadores()
    // PROPÓSITO: Anima los valores numéricos de los elementos con clase
    //            .metric-value (KPIs). Hace un conteo progresivo desde
    //            0 hasta el valor final con una duración de 1200ms.
    // ================================================================
    function animarContadores() {
        // Recorro cada elemento que muestra un valor métrico
        $('.metric-value').each(function () {
            var $el = $(this);
            var text = $el.text(); // Texto original (ej: "$1,234.56" o "42 productos")
            // Extraigo el valor numérico eliminando caracteres no numéricos excepto . y ,
            var num = parseFloat(text.replace(/[^0-9.,-]/g, '').replace(',', ''));
            if (isNaN(num)) return; // Si no es un número válido, salgo

            // Extraigo el prefijo (todo lo que no sea número)
            var prefix = text.replace(num.toString().replace(',', '.'), '').replace(/[0-9]/g, '').trim();
            // Detecto si el valor es monetario (tiene símbolo $)
            var isCurrency = text.indexOf('$') !== -1;

            // Inicio la animación desde 0
            $el.text(prefix + '0');
            // Uso animate de jQuery para hacer el conteo progresivo
            $({ val: 0 }).animate({ val: num }, {
                duration: 1200, // Duración de la animación en ms
                easing: 'swing', // Tipo de easing
                step: function () {
                    // En cada paso, actualizo el texto con formato apropiado
                    var v = isCurrency
                        ? '$' + this.val.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') // Formato moneda
                        : prefix + Math.round(this.val); // Formato número entero
                    $el.text(v);
                },
                complete: function () {
                    // Al finalizar, restauro el texto original exacto
                    $el.text(text);
                }
            });
        });
    }
    // Ejecuto la animación de contadores al cargar la página
    animarContadores();

    // ================================================================
    // EVENTO: Scroll del window - Control del botón "Volver arriba"
    // Muestra u oculta el botón flotante #backToTop según la posición
    // del scroll vertical.
    // ================================================================
    $(window).on('scroll', function () {
        // Si el scroll supera los 400px, muestro el botón; si no, lo oculto
        if ($(this).scrollTop() > 400) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });

    // ================================================================
    // EVENTO: Click en #backToTop - Scroll suave hacia arriba
    // Cuando el usuario hace clic en el botón flotante, la página
    // se desplaza suavemente hasta la parte superior.
    // ================================================================
    $(document).on('click', '#backToTop', function () {
        // Animo el scroll de html y body hasta la posición 0 (arriba del todo)
        $('html, body').animate({ scrollTop: 0 }, 400);
    });

});
