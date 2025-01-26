<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';
$commande = null;
$produits = [];

if (isset($_GET['id'])) {
    $commandeId = $_GET['id'];
    $commandeModel = new Commande();
    $commande = $commandeModel->getCommandeById($commandeId);

    if (!$commande) {
        $error = "Commande non trouvée.";
    } else {
        $produits = $commandeModel->getProduitsForCommande($commandeId);
        if (empty($produits)) {
            $error = "Aucun produit trouvé pour cette commande.";
        }
    }
} else {
    $error = "ID de commande non spécifié.";
}