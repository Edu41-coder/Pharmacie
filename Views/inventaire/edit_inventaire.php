<?php
$pageTitle = "Modifier un Produit dans l'Inventaire";
$additionalHeadContent = <<<EOT
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-produits-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/inventaire/edit_inventaire.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

// Récupérer l'item de la session
$item = $_SESSION['item'] ?? null;
unset($_SESSION['item']);
?>

<h1>Modifier un Produit dans l'Inventaire</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<?php if ($item): ?>
    <form action="/Pharmacie_S/PHP/inventaire/edit_inventaire.php?id=<?php echo htmlspecialchars($item['produit_id']); ?>" method="post" class="register-form">
        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($item['nom']); ?>" readonly required>
        
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($item['stock']); ?>" min="0" required>
        
        <button type="submit">Modifier</button>
    </form>
<?php else: ?>
    <p>Aucun produit trouvé.</p>
<?php endif; ?>
<a href="/Pharmacie_S/Views/inventaire/index_inventaire.php" class="back-link-gray">Retour à la liste de l'inventaire</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>