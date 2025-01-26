<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$error = '';
$success = '';

if (isset($_GET['id'])) {
    $clientId = $_GET['id'];
    $clientModel = new Client();
    $client = $clientModel->getClientById($clientId);

    if (!$client) {
        $_SESSION['error'] = "Client non trouvé.";
        header('Location: /Pharmacie_S/Views/clients/index_clients.php');
        exit();
    }
} else {
    $_SESSION['error'] = "ID de client non spécifié.";
    header('Location: /Pharmacie_S/Views/clients/index_clients.php');
    exit();
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