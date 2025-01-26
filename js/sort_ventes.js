document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('ventes-table');
    const headers = table.querySelectorAll('th[data-sort]');

    // Récupérer les paramètres de tri actuels de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort') || 'date';
    const currentDirection = urlParams.get('direction') || 'desc';

    // Appliquer les classes de tri aux en-têtes
    headers.forEach(header => {
        const sortColumn = header.dataset.sort;
        if (sortColumn === currentSort) {
            header.classList.add(`th-sort-${currentDirection}`);
        }
    });

    // Ajouter les écouteurs d'événements pour le tri
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const sortColumn = this.dataset.sort;
            let newDirection = 'asc';

            if (sortColumn === currentSort) {
                newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            }

            // Construire la nouvelle URL avec tous les paramètres existants
            const newUrlParams = new URLSearchParams(window.location.search);
            newUrlParams.set('sort', sortColumn);
            newUrlParams.set('direction', newDirection);
            
            // Conserver la page actuelle et les autres filtres
            if (!newUrlParams.has('page')) {
                newUrlParams.set('page', '1');
            }

            // Redirection avec la nouvelle URL
            window.location.href = `${window.location.pathname}?${newUrlParams.toString()}`;
        });

        // Ajouter le style de curseur pointer
        header.style.cursor = 'pointer';
    });
});