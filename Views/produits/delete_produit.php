<?php
$pageTitle = "Supprimer un Produit";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/produits/delete_produit.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<script>
    document.body.className = 'index-produits-page';
</script>

<h1>Supprimer un Produit</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/Views/produits/delete_produit.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
    <label for="produit_id">Sélectionner un produit à supprimer:</label>
    <select id="produit_id" name="produit_id" required>
        <?php foreach ($produits as $produit): ?>
            <option value="<?php echo htmlspecialchars($produit['produit_id']); ?>"
                <?php echo ($produit['produit_id'] == $produitIdASupprimer) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($produit['nom'] . ' - ' . $produit['prix_vente_ht'] . '€'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Supprimer</button>
</form>
<a href="/Pharmacie_S/Views/produits/index_produits.php" class="back-link-gray">Retour à la liste des produits</a>

<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir supprimer ce produit ?");
    }
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>