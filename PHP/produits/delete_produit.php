<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';
$produits = [];

$produitModel = new Produit();

// Récupérer tous les produits
$produits = $produitModel->getAllProduits();

// Récupérer l'ID du produit à supprimer depuis l'URL
$produitIdASupprimer = isset($_GET['id']) ? $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit_id'])) {
    $produitId = $_POST['produit_id'];
    if ($produitModel->softDeleteProduit($produitId)) {
        $success = "Produit supprimé avec succès.";
        // Mettre à jour la liste des produits après la suppression
        $produits = $produitModel->getAllProduits();
        $produitIdASupprimer = null; // Réinitialiser l'ID à supprimer
    } else {
        $error = "Erreur lors de la suppression du produit.";
    }
}