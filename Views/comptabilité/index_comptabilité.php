<?php
$pageTitle = "Comptabilité";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css"
<link rel="stylesheet" href="/Pharmacie_S/css/index_comptabilité.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "comptabilité-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div class="container">
    <h1>Comptabilité</h1>
    <div class="links-container">
        <a href="/Pharmacie_S/Views/comptabilité/index_cheques.php" class="back-link"><i class="fas fa-money-check"></i> Chèques</a>
        <a href="/Pharmacie_S/Views/comptabilité/impayes.php" class="back-link"><i class="fas fa-exclamation-circle"></i> Impayés</a>
        <a href="/Pharmacie_S/Views/comptabilité/tva.php" class="back-link"><i class="fas fa-percent"></i> TVA</a>
        <a href="/Pharmacie_S/Views/comptabilité/remboursements.php" class="back-link"><i class="fas fa-undo"></i> Remboursements</a>
        <a href="/Pharmacie_S/Views/comptabilité/commandes.php" class="back-link"><i class="fas fa-shopping-basket"></i> Commandes</a>
        <a href="/Pharmacie_S/Views/comptabilité/chiffre_affaires.php" class="back-link"><i class="fas fa-chart-line"></i> Chiffre d'affaires</a>
    </div>
    <a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'accueil</a>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>