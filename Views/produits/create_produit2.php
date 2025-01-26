<?php
$pageTitle = "Ajouter un Produit";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/produits/create_produit.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = 'index-produits-page';
</script>


<h1>Créer un Produit</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/PHP/produits/create_produit.php" method="post" class="register-form">
    <label for="nom">Nom:</label>
    <input type="text" id="nom" name="nom" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description"></textarea>

    <label for="prix_vente_ht">Prix Vente HT:</label>
    <input type="number" id="prix_vente_ht" name="prix_vente_ht" step="0.01" min="0" required>

    <label for="prescription">Prescription:</label>
    <select id="prescription" name="prescription" required>
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select>

    <label for="taux_remboursement">Taux Remboursement:</label>
    <input type="number" id="taux_remboursement" name="taux_remboursement" min="0" max="100" step="1">

    <label for="alerte">Alerte:</label>
    <input type="number" id="alerte" name="alerte" min="0">

    <label for="declencher_alerte">Déclencher Alerte:</label>
    <select id="declencher_alerte" name="declencher_alerte">
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select>

    <button type="submit">Créer</button>
</form>
<a href="/Pharmacie_S/Views/produits/index_produits.php" class="back-link-gray">Retour à la liste des produits</a>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>