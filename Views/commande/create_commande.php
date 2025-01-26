<?php
$pageTitle = "Création de Commande";
$additionalHeadContent = <<<EOT
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<script src="/Pharmacie_S/js/select2.min.js"></script>
<link rel="stylesheet" href="/Pharmacie_S/css/create_commande.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-a-commander-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/commande/create_commande.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div class="container">
    <h1>Création de la Commande</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($produitsACommander)): ?>
        <form id="add-all-form" class="register-form">
            <h2>Ajouter tous les produits depuis A Commander</h2>
            <select id="add-all-products" name="add_all" required>
                <option value="">Sélectionnez une option</option>
                <option value="all">Ajouter tous les produits (<?php echo count($produitsACommander); ?>)</option>
            </select>
            <button type="submit" name="submit_all">Ajouter</button>
        </form>
    <?php endif; ?>

    <h2>Ajouter des produits à la commande</h2>
    <form id="add-product-form" class="register-form">
        <label for="produit_id">Produit :</label>
        <select id="produit_id" name="produit_id" required>
            <option value="">Sélectionnez un produit</option>
            <?php foreach ($inventaire as $produit): ?>
                <?php if (!in_array($produit['produit_id'], $commandeProduitIds)): ?>
                    <option value="<?php echo htmlspecialchars($produit['produit_id']); ?>">
                        <?php echo htmlspecialchars($produit['nom']); ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="quantite">Quantité :</label>
        <input type="number" id="quantite" name="quantite" min="1" required>

        <button type="submit" id="add-product">Ajouter à la commande</button>
    </form>

    <h2>Produits dans la commande</h2>
    <table id="produits-commande" class="produits-commande">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les produits seront ajoutés ici dynamiquement -->
        </tbody>
    </table>

    <button id="finaliser-commande" class="btn-finaliser">Finaliser la commande</button>

    <a href="/Pharmacie_S/Views/commande/index_commande.php" class="back-link-gray">Retour à la liste des commandes</a>
</div>

<script>
    $(document).ready(function() {
        $('#produit_id').select2({
            placeholder: 'Sélectionnez un produit',
            allowClear: true,
            width: '100%'
        });

        $('#add-all-products').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true,
            width: '100%'
        });

        function ajouterProduitALaTable(produitId, produitNom, quantite) {
            $('#produits-commande tbody').append(`
                <tr data-produit-id="${produitId}">
                    <td>${produitNom}</td>
                    <td>${quantite}</td>
                    <td><button class="remove-produit">Supprimer</button></td>
                </tr>
            `);

            // Retirer le produit de la liste déroulante
            $(`#produit_id option[value="${produitId}"]`).remove();
        }
        $('#add-all-form').submit(function(e) {
            e.preventDefault();
            if ($('#add-all-products').val() === 'all') {
                $.ajax({
                    url: '/Pharmacie_S/PHP/commande/create_commande.php',
                    method: 'GET',
                    data: {
                        action: 'get_a_commander'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (Array.isArray(response) && response.length > 0) {
                            response.forEach(function(produit) {
                                ajouterProduitALaTable(produit.produit_id, produit.nom, produit.quantite);
                            });
                        } else {
                            alert('Aucun produit à commander trouvé.');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX error:', textStatus, errorThrown);
                        console.log('Response:', jqXHR.responseText);
                        alert('Erreur lors de la récupération des produits à commander. Vérifiez la console pour plus de détails.');
                    }
                });
            }
        });

        $('#add-product-form').submit(function(e) {
            e.preventDefault();
            var produitId = $('#produit_id').val();
            var produitNom = $('#produit_id option:selected').text();
            var quantite = $('#quantite').val();

            if (produitId && quantite) {
                ajouterProduitALaTable(produitId, produitNom, quantite);
                $('#produit_id').val('').trigger('change');
                $('#quantite').val('');
            }
        });

        $(document).on('click', '.remove-produit', function() {
            var row = $(this).closest('tr');
            var produitId = row.data('produit-id');
            var produitNom = row.find('td:first').text();

            // Remettre le produit dans la liste déroulante
            $('#produit_id').append(`<option value="${produitId}">${produitNom}</option>`);
            row.remove();
        });

        $('#finaliser-commande').click(function() {
            var produits = [];
            $('#produits-commande tbody tr').each(function() {
                produits.push({
                    produit_id: $(this).data('produit-id'),
                    quantite: $(this).find('td:eq(1)').text()
                });
            });

            $.ajax({
                url: '/Pharmacie_S/PHP/commande/create_commande.php',
                method: 'POST',
                data: {
                    action: 'finaliser',
                    produits: JSON.stringify(produits)
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert('Commande créée avec succès !');
                        window.location.href = '/Pharmacie_S/Views/commande/index_commande.php';
                    } else {
                        alert('Erreur lors de la création de la commande : ' + response.error);
                    }
                },
                error: function() {
                    alert('Erreur lors de la communication avec le serveur.');
                }
            });
        });
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>