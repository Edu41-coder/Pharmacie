<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/produits/show_produit.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Détails du Produit</title>
    <link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
</head>

<body id="product-details-page">
    <div class="product-details-container">
        <h1>Détails du Produit</h1>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <table class="product-details-table">
            <tr>
                <th>Nom</th>
                <td><?php echo htmlspecialchars($produit['nom']); ?></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><div class="product-description"><?php echo nl2br(htmlspecialchars($produit['description'])); ?></div></td>
            </tr>
            <tr>
                <th>Prix Vente HT</th>
                <td><?php echo htmlspecialchars($produit['prix_vente_ht']); ?></td>
            </tr>
            <tr>
                <th>Prescription</th>
                <td><?php echo htmlspecialchars($produit['prescription']); ?></td>
            </tr>
            <tr>
                <th>Taux Remboursement</th>
                <td><?php echo htmlspecialchars($produit['taux_remboursement']); ?></td>
            </tr>
            <tr>
                <th>Alerte</th>
                <td><?php echo htmlspecialchars($produit['alerte']); ?></td>
            </tr>
            <tr>
                <th>Déclencher Alerte</th>
                <td><?php echo htmlspecialchars($produit['declencher_alerte']); ?></td>
            </tr>
        </table>
        <a href="/Pharmacie_S/Views/produits/index_produits.php" class="back-link-gray">Retour à la liste des produits</a>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>
</body>

</html>