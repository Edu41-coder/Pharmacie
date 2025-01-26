<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer


$auth = new Authentification();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($email, $password)) {
        header('Location: /Pharmacie_S/index.php'); // Rediriger vers la page d'accueil après authentification réussie
        exit();
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>