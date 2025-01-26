function initializeSearch(searchElementId, tableElementId) {
    $(document).ready(function() {
        var $select = $('#' + searchElementId);
        
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

        // Ajouter une option "Tous les produits" au début de la liste
        var allProductsOption = new Option("Tous les produits", "all", false, false);
        $select.prepend(allProductsOption).trigger('change');

        $select.on('select2:select', function (e) {
            var productId = e.params.data.id;
            if (productId === 'all') {
                $('#' + tableElementId + ' tbody tr').show();
            } else {
                filterTableById(tableElementId, productId);
            }
        });

        $select.on('select2:clear', function (e) {
            $('#' + tableElementId + ' tbody tr').show();
        });

        // Gérer la recherche en temps réel
        $select.on('select2:open', function () {
            $('.select2-search__field').on('input', function () {
                var searchText = $(this).val().toLowerCase();
                filterTableByText(tableElementId, searchText);
            });
        });
    });
}

function filterTableById(tableElementId, productId) {
    $('#' + tableElementId + ' tbody tr').each(function() {
        var rowId = $(this).find('td:first').text();
        $(this).toggle(rowId === productId);
    });
}

function filterTableByText(tableElementId, searchText) {
    $('#' + tableElementId + ' tbody tr').each(function() {
        var found = false;
        $(this).find('td').each(function() {
            if ($(this).text().toLowerCase().indexOf(searchText) >= 0) {
                found = true;
                return false; // Break the inner loop
            }
        });
        $(this).toggle(found);
    });
}