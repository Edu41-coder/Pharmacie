<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';
$aCommanderModel = new ACommander();
$produitModel = new Produit();

$commandesExistantes = $aCommanderModel->getAllCommandersWithoutConditions();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit_id'])) {
    $produitId = $_POST['produit_id'];

    if ($produitId === 'all') {
        $deletedCount = 0;
        foreach ($commandesExistantes as $item) {
            if ($aCommanderModel->deleteACommander($item['produit_id'])) {
                $deletedCount++;
            }
        }
        $success = "$deletedCount produit(s) supprimé(s) de la commande avec succès.";
    } else {
        if ($aCommanderModel->deleteACommander($produitId)) {
            $success = "Produit supprimé de la commande avec succès.";
        } else {
            $error = "Erreur lors de la suppression du produit de la commande.";
        }
    }
    
    // Mettre à jour la liste des commandes existantes après la suppression
    $commandesExistantes = $aCommanderModel->getAllCommandersWithoutConditions();
}

// Récupérer l'ID du produit à supprimer depuis l'URL
$produitIdASupprimer = isset($_GET['produit_id']) ? $_GET['produit_id'] : null;