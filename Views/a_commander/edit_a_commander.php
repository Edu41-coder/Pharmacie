<?php
$pageTitle = "modifier à commander";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/a_commander/edit_a_commander.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<script>
    document.body.className = "index-a-commander-page";
</script>

<h1>Modifier la Quantité d'un Produit dans la Commande</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<?php if (isset($item) && isset($produitDetails)): ?>
    <form action="/Pharmacie_S/Views/a_commander/edit_a_commander.php?id=<?php echo htmlspecialchars($item['produit_id']); ?>" method="post" class="register-form">
        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($produitDetails['nom']); ?>" readonly required>

        <label for="quantite">Quantité:</label>
        <input type="number" id="quantite" name="quantite" value="<?php echo htmlspecialchars($item['quantite']); ?>" min="0" required>

        <button type="submit">Modifier</button>
    </form>
<?php endif; ?>
<a href="/Pharmacie_S/Views/a_commander/index_a_commander.php" class="back-link-gray">Retour à la liste des produits à commander</a>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>