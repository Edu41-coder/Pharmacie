<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/auth/login.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
    <link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
</head>
<body class="login-page">
    <main>
        <h1>Bienvenu à l'application de gestion de votre pharmacie</h1>
        <h2>Veuillez vous connecter</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="post"> <!-- Chemin ajusté -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Se connecter</button>
        </form>
    </main>
</body>
</html>