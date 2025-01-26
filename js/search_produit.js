function initializeProductSearch() {
    $(document).ready(function() {
        var $searchInput = $('#search-product');
        var $table = $('#products-table');
        var $rows = $table.find('tbody tr');
        var searchTimer;

        // Événement de recherche
        $searchInput.on('input', function() {
            clearTimeout(searchTimer);
            var searchText = $(this).val();

            if (searchText.length >= 2) {
                searchTimer = setTimeout(function() {
                    $.ajax({
                        url: '/Pharmacie_S/Views/produits/index_produits.php',
                        method: 'POST',
                        data: { search: searchText },
                        success: function(response) {
                            try {
                                const products = JSON.parse(response);
                                updateProductsTable(products);
                            } catch (e) {
                                console.error('Erreur lors du parsing JSON:', e);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erreur AJAX:', error);
                        }
                    });
                }, 300);
            } else if (searchText.length === 0) {
                window.location.reload();
            }
        });

        // Filtre produits supprimés (admin seulement)
        if ($table.find('th:contains("Supprimé")').length > 0) {
            var $filterDeleted = $('<select id="filter-deleted" class="form-control mt-2">' +
                '<option value="all">Tous les produits</option>' +
                '<option value="active">Produits actifs</option>' +
                '<option value="deleted">Produits supprimés</option>' +
                '</select>');
            
            $filterDeleted.insertAfter($searchInput);

            $filterDeleted.on('change', function() {
                var filter = $(this).val();
                if (filter === 'all') {
                    $rows.show();
                } else if (filter === 'active') {
                    $rows.show().filter('.deleted-product').hide();
                } else if (filter === 'deleted') {
                    $rows.hide().filter('.deleted-product').show();
                }
            });
        }

        // Fonction de mise à jour du tableau
        function updateProductsTable(products) {
            const tbody = $('#products-table tbody');
            tbody.empty();

            if (products.length === 0) {
                const colSpan = $('#products-table thead th').length;
                tbody.append(`<tr><td colspan="${colSpan}">Aucun produit trouvé.</td></tr>`);
                return;
            }

            products.forEach(function(produit) {
                const prix_ventettc = produit.prix_vente_ttc || 
                    (produit.prix_vente_ht * (1 + (window.tva / 100)));
                
                let row = `
                    <tr data-id="${produit.produit_id}" ${produit.is_deleted ? 'class="deleted-product"' : ''}>
                        <td>${produit.produit_id}</td>
                        <td>${produit.nom}</td>
                        <td>${Number(produit.prix_vente_ht).toLocaleString('fr-FR', {minimumFractionDigits: 2})} €</td>
                        <td>${Number(prix_ventettc).toLocaleString('fr-FR', {minimumFractionDigits: 2})} €</td>
                        <td>${produit.prescription}</td>
                        <td>${produit.taux_remboursement ? produit.taux_remboursement + '%' : '-'}</td>
                        <td>${produit.alerte}</td>
                        <td>${produit.declencher_alerte}</td>`;

                if (window.isAdmin) {
                    row += `<td>${produit.is_deleted ? 'Oui' : 'Non'}</td>`;
                }

                row += `<td>
                    <a href="/Pharmacie_S/Views/produits/show_produit.php?id=${produit.produit_id}" title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>`;

                if (window.isAdmin) {
                    row += `
                        <a href="/Pharmacie_S/Views/produits/edit_produit.php?id=${produit.produit_id}" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>`;
                    
                    if (!produit.is_deleted) {
                        row += `
                            <a href="/Pharmacie_S/Views/produits/delete_produit.php?id=${produit.produit_id}" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>`;
                    } else {
                        row += `
                            <a href="/Pharmacie_S/PHP/produits/restore_produit.php?id=${produit.produit_id}" title="Restaurer">
                                <i class="fas fa-undo"></i>
                            </a>`;
                    }
                }

                row += `</td></tr>`;
                tbody.append(row);
            });
        }

        // Initialiser le tri des colonnes
        $table.find('th[data-column]').click(function() {
            const column = $(this).data('column');
            const currentDirection = new URLSearchParams(window.location.search).get('direction') || 'asc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            
            window.location.href = `?sort=${column}&direction=${newDirection}`;
        });
    });
}