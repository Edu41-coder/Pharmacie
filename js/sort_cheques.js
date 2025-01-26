function sortTable(column, order) {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("cheques-table");
    switching = true;
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[column];
            y = rows[i + 1].getElementsByTagName("TD")[column];
            
            if (column === 0 || column === 3 || column === 5) {
                if (order === "asc") {
                    if (Number(x.innerHTML) > Number(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (order === "desc") {
                    if (Number(x.innerHTML) < Number(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                }
            } else {
                if (order === "asc") {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (order === "desc") {
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
}

$(document).ready(function() {
    $('th[data-sort]').click(function() {
        var column = $(this).data('sort');
        var columnIndex = $(this).index();
        var currentOrder = $(this).hasClass('sort-asc') ? 'desc' : 'asc';
        
        $('th').removeClass('sort-asc sort-desc');
        
        $(this).addClass('sort-' + currentOrder);
        sortTable(columnIndex, currentOrder);

        // Update the URL with sorting parameters
        var url = new URL(window.location.href);
        url.searchParams.set('sortColumn', column);
        url.searchParams.set('sortOrder', currentOrder);
        window.location.href = url.toString();
    });
});