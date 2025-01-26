<?php
$pageTitle = "Supprimer un Client";
$additionalHeadContent = <<<EOT
<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir supprimer ce client ?");
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-clients-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/clients/delete_client.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<h1>Supprimer un Client</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/Views/clients/delete_client.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
    <label for="client_id">Sélectionner un client à supprimer:</label>
    <select id="client_id" name="client_id" required>
        <?php foreach ($clients as $client): ?>
            <option value="<?php echo htmlspecialchars($client['client_id']); ?>"
                    <?php echo ($client['client_id'] == $clientIdASupprimer) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Supprimer</button>
</form>
<a href="/Pharmacie_S/Views/clients/index_clients.php" class="back-link-gray">Retour à la liste des clients</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>