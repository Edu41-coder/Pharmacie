<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /Pharmacie_S/login.php');
    exit();
}

// Instancier le modèle Produit
$produitModel = new Produit();

// Récupérer l'ID du produit à restaurer depuis l'URL
$produitIdARestaurer = isset($_GET['id']) ? $_GET['id'] : null;

if ($produitIdARestaurer) {
    // Appeler la méthode pour restaurer le produit
    if ($produitModel->restoreProduit($produitIdARestaurer)) {
        $_SESSION['success'] = "Produit restauré avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la restauration du produit.";
    }
} else {
    $_SESSION['error'] = "ID de produit non spécifié.";
}

// Rediriger vers la liste des produits
header('Location: /Pharmacie_S/Views/produits/index_produits.php');
exit();