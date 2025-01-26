<?php
$pageTitle = "supprimer à commander";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/a_commander/delete_a_commander.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = "index-a-commander-page";
</script>

<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir supprimer ce produit de la commande ?");
    }
</script>


<h1>Supprimer un Produit de la Commande</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/Views/a_commander/delete_a_commander.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
    <label for="produit_id">Sélectionner un produit à supprimer de la commande:</label>
    <select id="produit_id" name="produit_id" required>
        <option value="">Sélectionnez un produit</option>
        <option value="all">Tous les produits</option>
        <?php foreach ($commandesExistantes as $item): ?>
            <option value="<?php echo htmlspecialchars($item['produit_id']); ?>"
                <?php echo ($item['produit_id'] == $produitIdASupprimer) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($item['nom'] . ' - ' . $item['quantite'] . ' commandé(s)'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Supprimer</button>
</form>
<a href="/Pharmacie_S/Views/a_commander/index_a_commander.php" class="back-link-gray">Retour à la liste des produits à commander</a>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>