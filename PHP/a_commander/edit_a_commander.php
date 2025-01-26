<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';

if (isset($_GET['id'])) {
    $produitId = $_GET['id'];
    $aCommanderModel = new ACommander();
    $item = $aCommanderModel->getACommanderByProduitId($produitId);

    if (!$item) {
        $error = "Produit non trouvé dans la commande.";
    } else {
        $produitModel = new Produit();
        $produitDetails = $produitModel->getProduitById($produitId);

        if (!$produitDetails) {
            $error = "Détails du produit non trouvés.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $quantite = $_POST['quantite'];

                if (!is_numeric($quantite) || $quantite < 0) {
                    $error = "La quantité doit être un nombre positif ou nul.";
                } else {
                    if ($aCommanderModel->updateACommander($produitId, $quantite)) {
                        $success = "Quantité mise à jour dans la commande avec succès.";
                        $item['quantite'] = $quantite; // Mettre à jour la quantité affichée
                    } else {
                        $error = "Erreur lors de la mise à jour de la quantité dans la commande.";
                    }
                }
            }
        }
    }
} else {
    $error = "ID de produit non spécifié.";
}