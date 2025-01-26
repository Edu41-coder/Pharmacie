<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Instancier le modèle Produit
$produitModel = new Produit();

// Récupérer l'ID du produit à modifier depuis l'URL
$produitIdAModifier = isset($_GET['id']) ? $_GET['id'] : null;

$error = '';
$success = '';

if ($produitIdAModifier) {
    // Tentative de récupération du produit
    $produit = $produitModel->getProduitById($produitIdAModifier, true);

    // Vérification si le produit existe
    if ($produit) {
        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'];
            $description = $_POST['description'];
            $prix_vente_ht = $_POST['prix_vente_ht'];
            $prescription = $_POST['prescription'];
            $taux_remboursement = !empty($_POST['taux_remboursement']) ? $_POST['taux_remboursement'] : null;
            $alerte = !empty($_POST['alerte']) ? $_POST['alerte'] : null;
            $declencher_alerte = $_POST['declencher_alerte'];
            $is_deleted = isset($_POST['is_deleted']) ? $_POST['is_deleted'] : 0; // 0 par défaut

            // Validation simplifiée
            if (is_numeric($prix_vente_ht) && $prix_vente_ht >= 0) {
                $produitModel->updateProduit($produitIdAModifier, $nom, $description, $prix_vente_ht, $prescription, $taux_remboursement, $alerte, $declencher_alerte, $is_deleted);
                
                $success = "Produit modifié avec succès.";
                
                // Récupérer à nouveau le produit mis à jour
                $produit = $produitModel->getProduitById($produitIdAModifier, true);
            } else {
                $error = "Le prix de vente doit être un nombre positif.";
            }
        }
    } else {
        $error = "Produit non trouvé.";
        header('Location: /Pharmacie_S/Views/produits/index_produits.php');
        exit();
    }
} else {
    $error = "ID de produit non spécifié.";
    header('Location: /Pharmacie_S/Views/produits/index_produits.php');
    exit();
}