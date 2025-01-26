<?php
$pageTitle = "Supprimer un Produit de l'Inventaire";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir supprimer ce produit de l'inventaire ?");
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-produits-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/inventaire/delete_inventaire.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

// Récupérer l'ID du produit passé dans l'URL
$selectedProductId = isset($_GET['id']) ? $_GET['id'] : '';

// Récupérer le message passé dans l'URL
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<h1>Supprimer un Produit de l'Inventaire</h1>
<?php if (!empty($message)): ?>
    <p style="color: <?php echo strpos($message, 'Erreur') !== false ? 'red' : 'green'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?>

<form action="/Pharmacie_S/PHP/inventaire/delete_inventaire.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
    <label for="produit_id">Sélectionner un produit à supprimer de l'inventaire:</label>
    <select id="produit_id" name="produit_id" required>
        <option value="">Sélectionnez un produit</option>
        <option value="all">Tous les produits</option>
        <?php foreach ($inventaire as $item): ?>
            <option value="<?php echo htmlspecialchars($item['produit_id']); ?>"
                <?php echo ($item['produit_id'] == $selectedProductId) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($item['nom'] . ' - ' . $item['stock'] . ' en stock'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Supprimer</button>
</form>
<a href="/Pharmacie_S/Views/inventaire/index_inventaire.php" class="back-link-gray">Retour à la liste de l'inventaire</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>