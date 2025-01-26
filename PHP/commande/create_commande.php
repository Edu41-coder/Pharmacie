<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Commande.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/ACommander.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Inventaire.php';

$error = '';
$success = '';

$commandeModel = new Commande();
$aCommanderModel = new ACommander();
$inventoryModel = new Inventaire();

$inventaire = $inventoryModel->getAllInventaireProducts();
$produitsACommander = $aCommanderModel->getAllACommanders();

$produitsAjoutés = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_a_commander') {
    $aCommanderModel = new ACommander();
    $produitsACommander = $aCommanderModel->getAllACommanders();
    echo json_encode($produitsACommander);
    exit;
}

// ... (reste du code)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_commande = date('Y-m-d H:i:s');
    $commande_id = $commandeModel->createCommande($date_commande);
    
    if ($commande_id) {
        if (isset($_POST['action']) && $_POST['action'] === 'finaliser') {
            $produits = json_decode($_POST['produits'], true);
            $success = true;
            
            foreach ($produits as $produit) {
                if (!$commandeModel->addProduitToCommande($commande_id, $produit['produit_id'], $produit['quantite'])) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                $total = $commandeModel->calculateCommandeTotal($commande_id);
                $commandeModel->updateCommande($commande_id, 'En attente', $total, $date_commande);
                echo json_encode(['success' => true, 'message' => 'Commande créée avec succès.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'ajout des produits à la commande.']);
            }
            exit;
        } elseif (isset($_POST['submit_all']) && isset($_POST['add_all']) && $_POST['add_all'] === 'all') {
            $success = true;
            foreach ($produitsACommander as $produit) {
                if ($commandeModel->addProduitToCommande($commande_id, $produit['produit_id'], $produit['quantite'])) {
                    $produitsAjoutés[] = [
                        'produit_id' => $produit['produit_id'],
                        'nom' => $produit['nom'],
                        'quantite' => $produit['quantite']
                    ];
                } else {
                    $success = false;
                    break;
                }
            }
            if ($success) {
                $success = "Tous les produits de 'A Commander' ont été ajoutés à la commande.";
                // Optionnel : Vider la table a_commander après l'ajout
                // $aCommanderModel->deleteAllACommanders();
            } else {
                $error = "Erreur lors de l'ajout de tous les produits à la commande.";
            }
        } elseif (isset($_POST['add_single'])) {
            $produitId = $_POST['produit_id'];
            $quantite = $_POST['quantite'];

            if ($commandeModel->addProduitToCommande($commande_id, $produitId, $quantite)) {
                $success = "Le produit a été ajouté à la commande avec une quantité de $quantite.";
            } else {
                $error = "Erreur lors de l'ajout du produit à la commande.";
            }
        }
        
        $total = $commandeModel->calculateCommandeTotal($commande_id);
        $commandeModel->updateCommande($commande_id, 'En attente', $total, $date_commande);
    } else {
        $error = "Erreur lors de la création de la commande.";
    }
}

$isTableEmpty = $commandeModel->isTableEmpty();
$commandeProduitIds = $isTableEmpty ? [] : $commandeModel->getAllProduitIds(null);

// Convertir $produitsAjoutés en JSON pour le passer au JavaScript
$produitsAjoutésJSON = json_encode($produitsAjoutés);

// Si ce n'est pas une requête AJAX, préparer les données pour l'affichage
if (!isset($_POST['action']) || $_POST['action'] !== 'finaliser') {
    // Le code pour préparer l'affichage reste inchangé
} else {
    // Pour les requêtes AJAX, la réponse a déjà été envoyée et le script s'est terminé
    exit;
}