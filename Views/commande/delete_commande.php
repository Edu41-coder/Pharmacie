<?php
$pageTitle = "Supprimer une Commande ou des Produits";
$additionalHeadContent = <<<EOT
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<script src="/Pharmacie_S/js/select2.min.js"></script>
<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir effectuer cette suppression ?");
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-a-commander-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/commande/delete_commande.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

// Récupérer l'ID de la commande et du produit à supprimer s'ils sont passés dans l'URL
$commandeIdASupprimer = isset($_GET['commande_id']) ? intval($_GET['commande_id']) : null;
$produitIdASupprimer = isset($_GET['produit_id']) ? intval($_GET['produit_id']) : null;
?>

<div class="container">
    <h1>Supprimer une Commande ou des Produits</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="/Pharmacie_S/Views/commande/delete_commande.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
        <label for="commande_id">Sélectionner une commande :</label>
        <select id="commande_id" name="commande_id" required>
            <option value="">Sélectionnez une commande</option>
            <option value="all">Toutes les commandes</option>
            <?php foreach ($commandesExistantes as $commande): ?>
                <option value="<?php echo htmlspecialchars($commande['commande_id']); ?>"
                    <?php echo ($commande['commande_id'] == $commandeIdASupprimer) ? 'selected' : ''; ?>>
                    Commande #<?php echo htmlspecialchars($commande['commande_id']); ?> - 
                    <?php echo htmlspecialchars($commande['date_commande']); ?> - 
                    Total: <?php echo htmlspecialchars($commande['total']); ?> €
                </option>
            <?php endforeach; ?>
        </select>

        <div id="produits_commande" style="display: none;">
            <label for="produit_id">Sélectionner un produit à supprimer :</label>
            <select id="produit_id" name="produit_id">
                <option value="all">Tous les produits</option>
                <!-- Les options de produits seront chargées dynamiquement ici -->
            </select>
        </div>

        <button type="submit">Supprimer</button>
    </form>

    <a href="/Pharmacie_S/Views/commande/index_commande.php" class="back-link-gray">Retour à la liste des commandes</a>
</div>

<script>
    $(document).ready(function() {
        $('#commande_id').select2({
            placeholder: 'Sélectionnez une commande',
            allowClear: true,
            width: '100%'
        });

        $('#produit_id').select2({
            placeholder: 'Sélectionnez un produit',
            allowClear: true,
            width: '100%'
        });

        function loadProduits(commande_id, callback) {
            $.ajax({
                url: '/Pharmacie_S/PHP/commande/delete_commande.php',
                type: 'GET',
                data: {action: 'getProduits', commande_id: commande_id},
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="all">Tous les produits</option>';
                    $.each(response, function(index, produit) {
                        options += '<option value="' + produit.produit_id + '">' +
                                   produit.nom + ' - Quantité: ' + produit.quantite +
                                   '</option>';
                    });
                    $('#produit_id').html(options);
                    $('#produits_commande').show();
                    if (callback) callback();
                }
            });
        }

        $('#commande_id').change(function() {
            var commande_id = $(this).val();
            if (commande_id && commande_id !== 'all') {
                loadProduits(commande_id);
            } else {
                $('#produits_commande').hide();
            }
        });

        // Pré-sélectionner la commande et le produit si spécifiés dans l'URL
        var commandeId = <?php echo json_encode($commandeIdASupprimer); ?>;
        var produitId = <?php echo json_encode($produitIdASupprimer); ?>;
        
        if (commandeId) {
            $('#commande_id').val(commandeId).trigger('change');
            loadProduits(commandeId, function() {
                if (produitId) {
                    $('#produit_id').val(produitId).trigger('change');
                }
            });
        }
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>