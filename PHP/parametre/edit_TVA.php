<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();



$error = '';
$success = '';
$parametreModel = new Parametre(); // Assurez-vous que la classe Parametre est incluse

// Récupérer la valeur actuelle de la TVA
$tva = $parametreModel->getParametre('TVA');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelle_tva = $_POST['tva'];

    // Validation
    if (!is_numeric($nouvelle_tva) || $nouvelle_tva < 0) {
        $_SESSION['error'] = "La TVA doit être un nombre positif.";
    } else {
        if ($parametreModel->updateParametre('TVA', $nouvelle_tva)) {
            $_SESSION['success'] = "TVA modifiée avec succès.";
            header('Location: /Pharmacie_S/Views/parametre/edit_TVA.php');
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de la TVA.";
        }
    }
}

// Récupérer les messages de session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>