$(function () {

    $(document).on('input', '#searchProducto', debounce(function () {
        filtrarTabla('#searchProducto', '.striped', 1);
    }, 300));

    $(document).on('input', '#searchActivo', debounce(function () {
        filtrarTabla('#searchActivo', '.striped', 0);
    }, 300));

    $(document).on('change', '#filterEstado, #filterEstadoProv', function () {
        var val = $(this).val().toLowerCase();
        var table = $(this).closest('.card').next('.card').find('table');
        if (!table.length) table = $(this).closest('.row').siblings('.card').find('table');
        table.find('tbody tr').each(function () {
            var badge = $(this).find('.new-badge, .new.badge, .badge').text().trim().toLowerCase();
            if (!val || badge.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        var visibles = table.find('tbody tr:visible').length;
        var total = table.find('tbody tr').length;
        $('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
    });

    $(document).on('click', '.pagination li:not(.disabled):not(.active) a', function (e) {
        e.preventDefault();
        var $li = $(this).closest('li');
        var $ul = $li.closest('.pagination');
        $ul.find('li.active').removeClass('active indigo');
        $li.addClass('active indigo');
        var page = $(this).text().trim();
        EIS.toast('Navegando a página ' + page, 'indigo', 'chevron_right');
    });

});
