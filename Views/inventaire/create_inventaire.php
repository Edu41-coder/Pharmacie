<?php
$pageTitle = "Création Inventaire";
$additionalHeadContent = <<<EOT
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<script src="/Pharmacie_S/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-produits-page";
        $(document).ready(function() {
            $('#produit_id').select2({
                placeholder: 'Sélectionnez un produit',
                allowClear: true,
                width: '100%'
            });
        });
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/inventaire/create_inventaire.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<h1>Création Inventaire</h1>
<?php if (!empty($message)): ?>
    <p style="color: <?php echo strpos($message, 'Erreur') !== false ? 'red' : 'green'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="register-form">
    <button type="submit" name="create_all">Créer un inventaire avec tous les produits (quantité de 10)</button>
</form>

<h2>Ou ajouter un seul produit</h2>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="register-form">
    <label for="produit_id">Produit:</label>
    <select id="produit_id" name="produit_id" required>
        <option value="">Sélectionnez un produit</option>
        <?php if (empty($produits) || count($produits) === count($inventaireProduitIds)): ?>
            <option disabled><?php echo 'L\'inventaire est déjà rempli avec tous les produits existants.'; ?></option>
        <?php else: ?>
            <?php foreach ($produits as $produit): ?>
                <?php if (!in_array($produit['produit_id'], $inventaireProduitIds)): ?>
                    <option value="<?php echo htmlspecialchars($produit['produit_id']); ?>">
                        <?php echo htmlspecialchars($produit['nom']); ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>

    <label for="stock">Quantité en stock:</label>
    <input type="number" id="stock" name="stock" min="0" required>

    <button type="submit" name="add_single">Ajouter à l'inventaire</button>
</form>

<a href="/Pharmacie_S/Views/inventaire/index_inventaire.php" class="back-link-gray">Retour à l'inventaire</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>