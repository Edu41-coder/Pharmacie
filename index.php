<?php
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
$currentTime = date('H:i'); // Format 24 heures pour l'heure actuelle
?>

<head>
    <link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
</head>

<body class="index-page">

    <main>
        <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['prenom']); ?>! Il est <?php echo $currentTime; ?>.</h2>
        <p>Utilisez le menu ci-dessus pour naviguer dans l'application.</p>
    </main>

</body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>