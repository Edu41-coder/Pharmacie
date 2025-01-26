// /Pharmacie_S/js/sort_ordonnances.js
$(document).ready(function() {
    $('th[data-sort]').click(function() {
        var table = $(this).parents('table').eq(0);
        var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
        this.asc = !this.asc;
        if (!this.asc) {
            rows = rows.reverse();
        }
        for (var i = 0; i < rows.length; i++) {
            table.append(rows[i]);
        }
        setIcon($(this), this.asc);
    });

    function comparer(index) {
        return function(a, b) {
            var valA = getCellValue(a, index), valB = getCellValue(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
        };
    }

    function getCellValue(row, index) {
        return $(row).children('td').eq(index).text();
    }

    function setIcon(element, asc) {
        $("th").each(function(index) {
            $(this).removeClass("sort-asc").removeClass("sort-desc");
        });
        element.addClass(asc ? "sort-asc" : "sort-desc");
    }
});