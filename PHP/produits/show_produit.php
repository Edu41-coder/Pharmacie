<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$error = '';
$success = '';

if (isset($_GET['id'])) {
    $produitId = $_GET['id'];
    $produitModel = new Produit();
    $produit = $produitModel->getProduitById($produitId,true);

    if (!$produit) {
        $_SESSION['error'] = "Produit non trouvé.";
        header('Location: /Pharmacie_S/Views/produits/manage_produits.php');
        exit();
    }
} else {
    $_SESSION['error'] = "ID de produit non spécifié.";
    header('Location: /Pharmacie_S/Views/produits/manage_produits.php');
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