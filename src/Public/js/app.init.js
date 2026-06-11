$(function () {

    $('.sidenav').sidenav();
    $('select').formSelect();
    $('.tooltipped').tooltip();
    $('.modal').modal();

    function actualizarReloj() {
        var now = new Date();
        var opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        var timeStr = now.toLocaleTimeString('es-ES', opts);
        var dateStr = now.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
        $('#clock').text(timeStr + ' - ' + dateStr);
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    function updateThemeUI(theme) {
        var isDark = theme === 'dark';
        $('#themeIcon').text(isDark ? 'light_mode' : 'dark_mode');
        $('#themeLabel').text(isDark ? 'Modo Claro' : 'Modo Oscuro');
    }

    var currentTheme = localStorage.getItem('theme') || 'light';
    $('html').attr('data-theme', currentTheme);
    updateThemeUI(currentTheme);

    $(document).on('click', '#themeToggle', function () {
        var theme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
        $('html').attr('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateThemeUI(theme);
        EIS.toast('Tema cambiado a ' + (theme === 'dark' ? 'oscuro' : 'claro'), 'indigo', 'palette');
    });

    $('main').hide().fadeIn(400);
    $('.container').hide().fadeIn(500);

    function animarContadores() {
        $('.metric-value').each(function () {
            var $el = $(this);
            var text = $el.text();
            var num = parseFloat(text.replace(/[^0-9.,-]/g, '').replace(',', ''));
            if (isNaN(num)) return;
            var prefix = text.replace(num.toString().replace(',', '.'), '').replace(/[0-9]/g, '').trim();
            var isCurrency = text.indexOf('$') !== -1;
            $el.text(prefix + '0');
            $({ val: 0 }).animate({ val: num }, {
                duration: 1200,
                easing: 'swing',
                step: function () {
                    var v = isCurrency ? '$' + this.val.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') : prefix + Math.round(this.val);
                    $el.text(v);
                },
                complete: function () {
                    $el.text(text);
                }
            });
        });
    }
    animarContadores();

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 400) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });

    $(document).on('click', '#backToTop', function () {
        $('html, body').animate({ scrollTop: 0 }, 400);
    });

});
