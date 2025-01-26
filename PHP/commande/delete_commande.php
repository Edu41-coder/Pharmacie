<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';
$commandeModel = new Commande();
$produitModel = new Produit();

// Gestion de la requête AJAX pour obtenir les produits d'une commande
if (isset($_GET['action']) && $_GET['action'] == 'getProduits' && isset($_GET['commande_id'])) {
    $commande_id = $_GET['commande_id'];
    $produits = $commandeModel->getProduitsForCommande($commande_id);
    echo json_encode($produits);
    exit;
}

$commandesExistantes = $commandeModel->getAllCommandes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['commande_id'])) {
        $commandeId = $_POST['commande_id'];

        if ($commandeId === 'all') {
            $deletedCount = $commandeModel->deleteAllCommandes();
            $success = "$deletedCount commande(s) supprimée(s) avec succès.";
        } else {
            if (isset($_POST['produit_id'])) {
                $produitId = $_POST['produit_id'];
                if ($produitId === 'all') {
                    if ($commandeModel->deleteCommande($commandeId)) {
                        $success = "Tous les produits de la commande ont été supprimés avec succès.";
                    } else {
                        $error = "Erreur lors de la suppression de tous les produits de la commande.";
                    }
                } else {
                    if ($commandeModel->removeProduitFromCommande($commandeId, $produitId)) {
                        // Recalculer et mettre à jour le total de la commande
                        $newTotal = $commandeModel->calculateCommandeTotal($commandeId);
                        if ($commandeModel->updateCommandeTotal($commandeId, $newTotal)) {
                            $success = "Le produit a été supprimé de la commande et le total a été mis à jour avec succès.";
                        } else {
                            $error = "Le produit a été supprimé, mais une erreur est survenue lors de la mise à jour du total de la commande.";
                        }
                    } else {
                        $error = "Erreur lors de la suppression du produit de la commande.";
                    }
                }
            } else {
                if ($commandeModel->deleteCommande($commandeId)) {
                    $success = "Commande supprimée avec succès.";
                } else {
                    $error = "Erreur lors de la suppression de la commande.";
                }
            }
        }
        
        // Mettre à jour la liste des commandes existantes après la suppression
        $commandesExistantes = $commandeModel->getAllCommandes();
    }
}

// Récupérer l'ID de la commande à supprimer depuis l'URL
$commandeIdASupprimer = isset($_GET['commande_id']) ? $_GET['commande_id'] : null;

// Récupérer l'ID du produit à supprimer s'il est passé dans l'URL
$produitIdASupprimer = isset($_GET['produit_id']) ? intval($_GET['produit_id']) : null;