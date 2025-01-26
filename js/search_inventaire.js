function initializeInventorySearch() {
    $(document).ready(function() {
        var $select = $('#search-inventory');
        var $table = $('#inventory-table');

        // Initialiser Select2
        $select.select2({
            placeholder: 'Rechercher un produit',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Aucun résultat trouvé";
                }
            }
        });

        // Événement lors de la sélection d'un produit
        $select.on('select2:select', async function (e) {
            const productId = e.params.data.id;
            
            try {
                // Récupérer les paramètres de tri actuels
                const urlParams = new URLSearchParams(window.location.search);
                const sort = urlParams.get('sort') || 'produit_id';
                const direction = urlParams.get('direction') || 'asc';

                // Faire une requête pour trouver la page du produit
                const response = await axios.get('/Pharmacie_S/PHP/inventaire/find_product_page.php', {
                    params: {
                        productId: productId,
                        sort: sort,
                        direction: direction
                    }
                });

                if (response.data && response.data.page) {
                    // Rediriger vers la page avec le produit
                    window.location.href = `${window.location.pathname}?page=${response.data.page}&search=${productId}&sort=${sort}&direction=${direction}`;
                }
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
            }
        });

        // Gérer la recherche en temps réel
        $select.on('select2:open', function () {
            setTimeout(function() {
                $select.val(null).trigger('change');
            }, 0);
        });

        // Mettre en surbrillance le produit recherché
        const urlParams = new URLSearchParams(window.location.search);
        const searchId = urlParams.get('search');
        if (searchId) {
            const $row = $table.find(`tr[data-id="${searchId}"]`);
            if ($row.length) {
                $row.addClass('highlighted-row');
                // Faire défiler jusqu'à la ligne
                setTimeout(() => {
                    $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        }

        // Maintenir la sélection dans le select2
        if (searchId) {
            $select.val(searchId).trigger('change');
        }
    });
}

// Styles pour la surbrillance
const style = `
    .highlighted-row { 
        background-color: #fff3cd !important; 
        transition: background-color 0.5s ease; 
    }
    .highlighted-row:hover { 
        background-color: #ffe7b3 !important; 
    }
    .select2-selection__clear { 
        display: none !important; 
    }
`;

$('<style>' + style + '</style>').appendTo('head');