<?php
$pageTitle = "Détails du Client";
$additionalHeadContent = <<<EOT
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.id = "product-details-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/clients/show_client.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<div class="centered-link">
    <a href="/Pharmacie_S/Views/clients/ventes_client.php?id=<?php echo $client['client_id']; ?>" class= "back-link-gray">Voir les ventes pour le  client</a>
</div>
<div class="product-details-container">
    <h1>Détails du Client</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    
    <table class="product-details-table">
        <tr>
            <th>ID</th>
            <td><?php echo htmlspecialchars($client['client_id']); ?></td>
        </tr>
        <tr>
            <th>Nom</th>
            <td><?php echo htmlspecialchars($client['nom']); ?></td>
        </tr>
        <tr>
            <th>Prénom</th>
            <td><?php echo htmlspecialchars($client['prenom']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($client['email']); ?></td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?php echo htmlspecialchars($client['telephone']); ?></td>
        </tr>
        <tr>
            <th>Numéro de Carte Vitale</th>
            <td><?php echo htmlspecialchars($client['numero_carte_vitale']); ?></td>
        </tr>
        <tr>
            <th>Adresse</th>
            <td><div class="product-description"><?php echo nl2br(htmlspecialchars($client['adresse'])); ?></div></td>
        </tr>
        <tr>
            <th>Commentaire</th>
            <td><div class="product-description"><?php echo nl2br(htmlspecialchars($client['commentaire'])); ?></div></td>
        </tr>
        <tr>
            <th>Impayés</th>
            <td><?php echo $client['cheques_impayes'] ? 'Oui' : 'Non'; ?></td>
        </tr>
    </table>
    
    <div class="centered-link">
        <a href="/Pharmacie_S/Views/clients/index_clients.php" class="back-link-gray">Retour à la liste des clients</a>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>