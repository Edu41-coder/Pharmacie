document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('products-table');
    const headers = table.querySelectorAll('th');

    // Colonnes de type enum
    const enumColumns = ['prescription', 'declencher_alerte'];

    // Récupérer les paramètres de tri actuels de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort') || 'produit_id';
    const currentDirection = urlParams.get('direction') || 'asc';

    // Appliquer les classes de tri aux en-têtes
    headers.forEach(function(header) {
        if (header.dataset.column === currentSort) {
            header.classList.add(`th-sort-${currentDirection}`);
        }
    });

    // Ajouter les écouteurs d'événements pour le tri
    headers.forEach(function(header) {
        if (header.dataset.column) {
            header.addEventListener('click', function(event) {
                const columnName = header.dataset.column;
                let newDirection = 'asc';
                
                if (columnName === currentSort) {
                    newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                }

                // Construire l'URL avec les paramètres de tri
                const newUrlParams = new URLSearchParams(window.location.search);
                newUrlParams.set('sort', columnName);
                newUrlParams.set('direction', newDirection);
                newUrlParams.set('page', '1');

                // Redirection vers la nouvelle URL
                window.location.href = `${window.location.pathname}?${newUrlParams.toString()}`;
            });

            // Style et icône
            header.style.cursor = 'pointer';
            if (header.dataset.column !== 'actions' && !header.querySelector('.fas')) {
                const icon = document.createElement('i');
                icon.className = enumColumns.includes(header.dataset.column) 
                    ? 'fas fa-sort-alpha-down ml-1'
                    : 'fas fa-sort ml-1';
                header.appendChild(icon);
            }
        }
    });
});