<?php
$pageTitle = "Accueil Pharmacie";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<style>
    .index-page {
        /* Vos styles spécifiques pour l'index ici */
        background-image: url('/Pharmacie_S/images/votre-image.jpg');
        background-size: cover;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

function getGreeting()
{
    $hour = date('H');
    if ($hour >= 6 && $hour < 18) {
        return "Bonjour";
    } elseif ($hour >= 18 && $hour < 22) {
        return "Bonsoir";
    } else {
        return "Bonne nuit";
    }
}

$greeting = getGreeting();
$currentTime = date('H:i');
?>

<div class="index-page">
    <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['prenom']); ?>! Il est <?php echo $currentTime; ?>.</h2>
    <p>Utilisez le menu ci-dessus pour naviguer dans l'application.</p>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>