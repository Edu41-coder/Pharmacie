<?php
$pageTitle = "Détails de la Vente";
$additionalHeadContent = <<<EOT
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.id = "product-details-page";
        document.body.className = "index-produits-page";
    });
</script>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/mes_documents/show_vente.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<div class="product-details-container">
    <h1>Détails de la Vente</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <table class="product-details-table">
        <tr>
            <th>ID Vente</th>
            <td><?php echo htmlspecialchars($venteData['vente_id']); ?></td>
        </tr>
        <tr>
            <th>Client ID</th>
            <td><?php echo htmlspecialchars($venteData['client_id']); ?></td>
        </tr>
        <tr>
            <th>Client</th>
            <td><?php echo htmlspecialchars($venteData['client_nom']); ?></td>
        </tr>
        <tr>
            <th>User ID</th>
            <td><?php echo htmlspecialchars($venteData['user_id']); ?></td>
        </tr>
        <tr>
            <th>Utilisateur</th>
            <td><?php echo htmlspecialchars($venteData['user_nom']); ?></td>
        </tr>
        <tr>
            <th>Date</th>
            <td><?php echo htmlspecialchars($venteData['date']); ?></td>
        </tr>
        <tr>
            <th>Montant</th>
            <td><?php echo htmlspecialchars($venteData['montant']); ?> €</td>
        </tr>
        <tr>
            <th>Montant Réglé</th>
            <td><?php echo htmlspecialchars($venteData['montant_regle']); ?> €</td>
        </tr>
        <tr>
            <th>À Rembourser</th>
            <td><?php echo htmlspecialchars($venteData['a_rembourser']); ?> €</td>
        </tr>
        <tr>
            <th>Commentaire</th>
            <td>
                <div class="vente-commentaire"><?php echo nl2br(htmlspecialchars($venteData['commentaire'])); ?></div>
            </td>
        </tr>
    </table>
    <div class="centered-link">
        <a href="/Pharmacie_S/Views/mes_documents/mes_ventes.php" class="back-link-gray">Retour à la liste des ventes</a>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>