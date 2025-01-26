<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$inventaireModel = new Inventaire();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit_id'])) {
    $produitId = $_POST['produit_id'];
    
    if ($produitId === 'all') {
        // Supprimer tous les produits de l'inventaire
        if ($inventaireModel->deleteAllInventaire()) {
            $message = "Tous les produits ont été supprimés de l'inventaire avec succès.";
        } else {
            $message = "Erreur lors de la suppression de tous les produits de l'inventaire.";
        }
    } else {
        // Supprimer un produit spécifique
        if ($inventaireModel->deleteInventaire($produitId)) {
            $message = "Produit supprimé de l'inventaire avec succès.";
        } else {
            $message = "Erreur lors de la suppression du produit de l'inventaire.";
        }
    }
    
    // Rediriger vers la page de suppression avec un message
    header('Location: /Pharmacie_S/Views/inventaire/delete_inventaire.php?message=' . urlencode($message));
    exit();
}

// Si ce n'est pas une requête POST, récupérer tous les produits de l'inventaire
$inventaire = $inventaireModel->getAllInventaire();

// Obtenir les informations sur la dernière modification
$lastChange = $inventaireModel->getLastModification();
?>