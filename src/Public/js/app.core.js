var EIS = {};

function debounce(fn, delay) {
    var timer;
    return function () {
        var ctx = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
}

function filtrarTabla(inputSelector, tableSelector, colIndex) {
    var q = $(inputSelector).val().toLowerCase();
    $(tableSelector + ' tbody tr').each(function () {
        var $row = $(this);
        var text = colIndex !== undefined
            ? $row.find('td').eq(colIndex).text().toLowerCase()
            : $row.text().toLowerCase();
        $row.toggle(text.indexOf(q) !== -1);
    });
    var visibles = $(tableSelector + ' tbody tr:visible').length;
    var total = $(tableSelector + ' tbody tr').length;
    $(tableSelector).closest('.card').find('.result-count').text('Mostrando ' + visibles + ' de ' + total + ' resultados');
}

EIS.toast = function (msg, color, icon) {
    color = color || 'indigo';
    icon = icon || 'check_circle';
    var html = '<i class="material-icons left" style="font-size:1.2rem;">' + icon + '</i>' + msg;
    M.toast({ html: html, classes: color + ' rounded', displayLength: 3000 });
};
