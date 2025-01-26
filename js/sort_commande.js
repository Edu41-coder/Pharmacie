document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('commandes-table');
    const headers = table.querySelectorAll('th[data-sort]');

    // Récupérer les paramètres de tri actuels de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sortColumn') || 'date_commande';
    const currentOrder = urlParams.get('sortOrder') || 'DESC';

    // Appliquer les classes de tri aux en-têtes
    headers.forEach(header => {
        const sortColumn = header.dataset.sort;
        if (sortColumn === currentSort) {
            header.classList.add(`sort-${currentOrder.toLowerCase()}`);
        }
    });

    // Ajouter les écouteurs d'événements pour le tri
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const sortColumn = this.dataset.sort;
            let newOrder = 'ASC';

            if (sortColumn === currentSort) {
                newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            }

            // Construire la nouvelle URL avec tous les paramètres existants
            const newUrlParams = new URLSearchParams(window.location.search);
            newUrlParams.set('sortColumn', sortColumn);
            newUrlParams.set('sortOrder', newOrder);
            
            // Redirection avec la nouvelle URL
            window.location.href = `${window.location.pathname}?${newUrlParams.toString()}`;
        });
    });
});