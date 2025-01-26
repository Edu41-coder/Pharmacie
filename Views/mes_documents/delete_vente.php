<?php
$pageTitle = "Supprimer une Vente";
$additionalHeadContent = <<<EOT
<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir supprimer cette vente ?");
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-produits-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/mes_documents/delete_vente.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<h1>Supprimer une Vente</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/Views/mes_documents/delete_vente.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
    <label for="vente_id">Sélectionner une vente à supprimer:</label>
    <select id="vente_id" name="vente_id" required>
        <?php foreach ($ventes as $vente): ?>
            <option value="<?php echo htmlspecialchars($vente['vente_id']); ?>"
                    <?php echo ($vente['vente_id'] == $venteIdASupprimer) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($vente['vente_id'] . ' - ' . $vente['date'] . ' - ' . $vente['montant'] . '€'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Supprimer</button>
</form>
<a href="/Pharmacie_S/Views/mes_documents/mes_ventes.php" class="back-link-gray">Retour à la liste des ventes</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>