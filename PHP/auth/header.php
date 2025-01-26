<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$auth = new Authentification();
$auth->checkSessionValidity();
$user = $auth->checkAuthentication();

if ($user) {
    $role = $user['role_id'];
    $_SESSION['role_id'] = $role;
} else {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas authentifié
    header('Location: /Pharmacie_S/Views/auth/login.php');
    exit();
}
?>

<script>
    setInterval(function() {
        fetch('/Pharmacie_S/check_session.php')
            .then(response => {
                if (!response.ok) {
                    console.log('Session expirée, redirection vers login.php');
                    window.location.href = '/Pharmacie_S/Views/auth/login.php';
                }
            })
            .catch(error => console.error('Erreur lors de la vérification de la session:', error));
    }, 30000); // Vérifie toutes les 30 secondes
</script>