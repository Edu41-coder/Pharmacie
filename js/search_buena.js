function initializeSearch(searchElementId, tableElementId) {
    $(document).ready(function() {
        var $select = $('#' + searchElementId);
        var $table = $('#' + tableElementId);
        var $rows = $table.find('tbody tr');

        // Initialiser Select2
        $select.select2({
            placeholder: 'Rechercher un produit',
            allowClear: false,
            width: '100%',
            language: {
                noResults: function() {
                    return "Aucun résultat trouvé";
                }
            }
        });

        // Ajouter une option "Tous les produits" au début de la liste
        var allProductsOption = new Option("Tous les produits", "all", false, false);
        $select.prepend(allProductsOption).trigger('change');

        // Événement lors de la sélection d'un produit
        $select.on('select2:select', function (e) {
            var productId = e.params.data.id;
            if (productId === 'all') {
                // Afficher toutes les lignes si "Tous les produits" est sélectionné
                $rows.show();
            } else {
                // Masquer toutes les lignes et afficher uniquement celle qui correspond à l'ID sélectionné
                $rows.hide();
                $table.find('tbody tr[data-id="' + productId + '"]').show();
            }
        });
         // Ajouter une option "Tous les clients" au début de la liste
         var allClientsOption = new Option("Tous les clients", "all", false, false);
         $select.prepend(allClientsOption).trigger('change');
 
         // Événement lors de la sélection d'un client
         $select.on('select2:select', function (e) {
             var clientId = e.params.data.id;
             if (clientId === 'all') {
                 // Afficher toutes les lignes si "Tous les clients" est sélectionné
                 $rows.show();
             } else {
                 // Masquer toutes les lignes et afficher uniquement celle qui correspond à l'ID sélectionné
                 $rows.hide();
                 $table.find('tbody tr[data-id="' + clientId + '"]').show();
             }
         });

        // Gérer la recherche en temps réel
        $select.on('select2:open', function () {
            // Réinitialiser le select après la sélection
            setTimeout(function() {
                $select.val(null).trigger('change');
            }, 0);
        });
    });
}

// CSS pour cacher le "x" de Select2
$('<style>.select2-selection__clear { display: none !important; }</style>').appendTo('head');