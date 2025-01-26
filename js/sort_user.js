// /Pharmacie_S/js/sort_user.js

function sortTable(column, order) {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.querySelector(".user-table");
    switching = true;
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[column];
            y = rows[i + 1].getElementsByTagName("TD")[column];
            
            // Vérifier si c'est une colonne numérique (ID)
            if (column === 0) {
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

document.addEventListener('DOMContentLoaded', function() {
    // Ajout des attributs data-sort et des événements de clic aux en-têtes
    const headers = document.querySelectorAll('.user-table th');
    headers.forEach((header, index) => {
        header.setAttribute('data-sort', index);
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-sort');
            const currentOrder = this.classList.contains('sort-asc') ? 'desc' : 'asc';
            
            // Réinitialiser toutes les colonnes
            headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            
            // Appliquer le tri et la classe appropriée
            this.classList.add('sort-' + currentOrder);
            sortTable(column, currentOrder);
        });
    });
});