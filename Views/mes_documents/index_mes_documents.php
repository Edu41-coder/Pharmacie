<?php
$pageTitle = "Mes Documents";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "manage-users-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$auth = new Authentification();
$user = $auth->getCurrentUser();
?>

<div class="container">
    <h1>Mes Documents</h1>
    <div>
        <a href="/Pharmacie_S/Views/mes_documents/mes_ventes.php" class="back-link"><i class="fas fa-shopping-cart"></i> Mes Ventes</a>
        <?php if ($user['role_id'] == 1): ?>
            <a href="/Pharmacie_S/Views/mes_documents/statistiques_ventes.php" class="back-link"><i class="fas fa-chart-bar"></i> Statistiques des Ventes</a>
        <?php endif; ?>
        <?php if ($user['role_id'] == 1 || $user['role_id'] == 2): ?>
            <a href="/Pharmacie_S/Views/mes_documents/mes_ordonnances.php" class="back-link"><i class="fas fa-file-medical"></i> Mes Ordonnances</a>
        <?php endif; ?>
    </div>
    <a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'accueil</a>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>