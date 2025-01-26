<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/a_commander/create_a_commander.php';

$pageTitle = "Création de la Liste à Commander";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<script src="/Pharmacie_S/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-a-commander-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div class="index-a-commander-page">
    <h1>Création de la Liste à Commander</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if ($isTableEmpty): ?>
        <form action="/Pharmacie_S/Views/a_commander/create_a_commander.php" method="post" class="register-form">
            <label for="add-all-products">Ajouter tous les produits :</label>
            <select id="add-all-products" name="add_all" required>
                <option value="">Sélectionnez une option</option>
                <option value="all">Ajouter tous les produits</option>
                <option value="alert">Ajouter tous les produits avec alerte</option>
            </select>
            <button type="submit" name="submit_all">Ajouter</button>
        </form>
    <?php endif; ?>

    <h2>Ajouter un produit individuel</h2>

    <form action="/Pharmacie_S/Views/a_commander/create_a_commander.php" method="post" class="register-form">
        <label for="produit_id">Produit :</label>
        <select id="produit_id" name="produit_id" required>
            <option value="">Sélectionnez un produit</option>
            <?php foreach ($inventaire as $produit): ?>
                <?php if (!in_array($produit['produit_id'], $aCommanderProduitIds)): ?>
                    <option value="<?php echo htmlspecialchars($produit['produit_id']); ?>">
                        <?php echo htmlspecialchars($produit['nom']); ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" name="add_single">Ajouter à la liste à commander</button>
    </form>

    <a href="/Pharmacie_S/Views/a_commander/index_a_commander.php" class="back-link-gray">Retour à la liste des produits à commander</a>
    
    <script>
    $(document).ready(function() {
        $('#produit_id').select2({
            placeholder: 'Sélectionnez un produit',
            allowClear: true,
            width: '100%'
        });
        $('#add-all-products').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true,
            width: '100%'
        });
    });
    </script>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>