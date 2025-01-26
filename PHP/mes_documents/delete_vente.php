<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';
$ventes = [];

$venteModel = new Vente();

// Récupérer toutes les ventes
$ventes = $venteModel->getAllVentes();

// Récupérer l'ID de la vente à supprimer depuis l'URL
$venteIdASupprimer = isset($_GET['id']) ? $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vente_id'])) {
    $venteId = $_POST['vente_id'];
    if ($venteModel->softDeleteVente($venteId)) {
        $success = "Vente supprimée avec succès.";
        // Mettre à jour la liste des ventes après la suppression
        $ventes = $venteModel->getAllVentes();
        $venteIdASupprimer = null; // Réinitialiser l'ID à supprimer
    } else {
        $error = "Erreur lors de la suppression de la vente.";
    }
}