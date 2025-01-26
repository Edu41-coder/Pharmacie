<?php
$pageTitle = "Modifier la Commande";
$additionalHeadContent = <<<EOT
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<style>
.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
    padding: 8px 16px;
    margin-bottom: 15px;
    cursor: pointer;
    border-radius: 4px;
}
.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
}
.d-flex {
display: flex !important;
}
.justify-content-center {
justify-content: center !important;
}
.mt-3 {
margin-top: 1rem !important;
}
.mb-3 {
 margin-bottom: 1rem !important;
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-a-commander-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/commande/edit_commande.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

// Récupérer les messages de session
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);
?>

<div class="container">
    <h1>Modifier la Commande</h1>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <div id="message-container"></div>

    <?php if (isset($commande)): ?>
        <form id="editCommandeForm" action="/Pharmacie_S/PHP/commande/edit_commande.php?id=<?php echo htmlspecialchars($commande['commande_id']); ?>" method="post" class="edit-commande-form">
            <label for="date_commande">Date de la commande:</label>
            <input type="date" id="date_commande" name="date_commande" value="<?php echo htmlspecialchars($commande['date_commande']); ?>">

            <label for="statut">Statut:</label>
            <select id="statut" name="statut" required>
                <?php foreach ($statuts as $statut): ?>
                    <option value="<?php echo htmlspecialchars($statut); ?>" <?php echo ($statut == $commande['statut']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($statut); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <h2>Produits de la commande</h2>
            <table id="produitsTable">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr data-produit-id="<?php echo $produit['produit_id']; ?>">
                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                            <td>
                                <input type="number" name="quantites[<?php echo $produit['produit_id']; ?>]" value="<?php echo htmlspecialchars($produit['quantite']); ?>" min="0" class="quantite-input">
                            </td>
                            <td>
                                <button type="button" class="remove-produit">Supprimer</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Ajouter un nouveau produit</h2>
            <select id="new_produit_id" name="new_produit_id">
                <option value="">Sélectionnez un produit</option>
                <?php foreach ($produitsNonCommandes as $produit): ?>
                    <option value="<?php echo $produit['produit_id']; ?>"><?php echo htmlspecialchars($produit['nom']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="new_quantite" name="new_quantite" placeholder="Quantité" min="1">
            <button type="button" id="addProduitBtn">Ajouter le produit à la liste</button>
            <!-- Avant le bouton de mise à jour, ajoutez une div conteneur -->
            <div class="d-flex justify-content-center mt-3 mb-3">
                <button type="submit" name="update_commande" id="updateCommandeBtn" class="btn btn-warning">
                    <i class="fas fa-sync-alt"></i> Mettre à jour la commande
                </button>
            </div>
        </form>
    <?php else: ?>
        <p>Commande non trouvée.</p>
    <?php endif; ?>
    <a href="/Pharmacie_S/Views/commande/index_commande.php" class="back-link-gray">Retour à la liste des commandes</a>
</div>

<script>
    $(document).ready(function() {
        function updateAvailableProducts() {
            var usedProductIds = [];
            $('#produitsTable tbody tr').each(function() {
                usedProductIds.push($(this).data('produit-id'));
            });

            $('#new_produit_id option').each(function() {
                var productId = $(this).val();
                if (productId && usedProductIds.includes(parseInt(productId))) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }

        updateAvailableProducts();

        $('#addProduitBtn').click(function() {
            var produitId = $('#new_produit_id').val();
            var produitNom = $('#new_produit_id option:selected').text();
            var quantite = $('#new_quantite').val();

            if (produitId && quantite) {
                var newRow = $(`
                    <tr data-produit-id="${produitId}">
                        <td>${produitNom}</td>
                        <td>
                            <input type="number" name="quantites[${produitId}]" value="${quantite}" min="0" class="quantite-input">
                        </td>
                        <td>
                            <button type="button" class="remove-produit">Supprimer</button>
                        </td>
                    </tr>
                `);
                $('#produitsTable tbody').append(newRow);
                $('#new_produit_id').val('');
                $('#new_quantite').val('');
                updateAvailableProducts();
            } else {
                alert("Veuillez sélectionner un produit et spécifier une quantité.");
            }
        });

        $(document).on('click', '.remove-produit', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            updateAvailableProducts();
        });

        $('#editCommandeForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serializeArray();

            formData = formData.filter(function(item) {
                return !(item.name === 'date_commande' && item.value === '');
            });

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $.param(formData),
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        $('#message-container').html('<p style="color: green;">' + response.message + '</p>');
                        refreshCommandeData();
                    } else {
                        $('#message-container').html('<p style="color: red;">' + response.error + '</p>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    $('#message-container').html('<p style="color: red;">Erreur lors de la communication avec le serveur: ' + textStatus + '</p>');
                }
            });
        });

        function refreshCommandeData() {
            $.ajax({
                url: '/Pharmacie_S/PHP/commande/edit_commande.php',
                type: 'GET',
                data: {
                    id: <?php echo $commande['commande_id']; ?>
                },
                dataType: 'json',
                success: function(data) {
                    $('#date_commande').val(data.commande.date_commande);
                    $('#statut').val(data.commande.statut);

                    var produitsHtml = '';
                    data.produits.forEach(function(produit) {
                        produitsHtml += `
                            <tr data-produit-id="${produit.produit_id}">
                                <td>${produit.nom}</td>
                                <td>
                                    <input type="number" name="quantites[${produit.produit_id}]" value="${produit.quantite}" min="0" class="quantite-input">
                                </td>
                                <td>
                                    <button type="button" class="remove-produit">Supprimer</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#produitsTable tbody').html(produitsHtml);

                    var optionsHtml = '<option value="">Sélectionnez un produit</option>';
                    data.produitsNonCommandes.forEach(function(produit) {
                        optionsHtml += `<option value="${produit.produit_id}">${produit.nom}</option>`;
                    });
                    $('#new_produit_id').html(optionsHtml);

                    updateAvailableProducts();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erreur lors du rafraîchissement des données:', textStatus, errorThrown);
                }
            });
        }
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>