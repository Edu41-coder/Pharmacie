// /Pharmacie_S/js/sort_clients.js

function sortTable(column, order, columnIndex) {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("clients-table");
    switching = true;
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i + 1].getElementsByTagName("TD")[columnIndex];
            
            if (column === 'id') {
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
            } else if (column === 'cheques_impayes') {
                const xValue = x.innerHTML.toLowerCase() === 'oui' ? 1 : 0;
                const yValue = y.innerHTML.toLowerCase() === 'oui' ? 1 : 0;
                if (order === "asc") {
                    if (xValue > yValue) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (order === "desc") {
                    if (xValue < yValue) {
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

document.addEventListener('DOMContentLoaded', function() {
    // Gestion du clic sur les en-têtes de colonnes
    const headers = document.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-sort');
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const currentOrder = this.classList.contains('sort-asc') ? 'desc' : 'asc';
            
            // Réinitialiser toutes les colonnes
            headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            
            // Appliquer le tri et la classe appropriée
            this.classList.add('sort-' + currentOrder);
            sortTable(column, currentOrder, columnIndex);
        });
    });
});