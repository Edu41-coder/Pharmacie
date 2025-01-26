<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

error_log("Début du traitement de edit_commande.php");
error_log("Méthode de requête: " . $_SERVER['REQUEST_METHOD']);
error_log("Données GET reçues: " . print_r($_GET, true));
error_log("Données POST reçues: " . print_r($_POST, true));

$error = '';
$success = '';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$commandeModel = new Commande();
$inventaireModel = new Inventaire();
$statuts = ['En attente', 'En cours', 'Livrée', 'Annulée'];

if (isset($_GET['id'])) {
    $commandeId = $_GET['id'];
    $commande = $commandeModel->getCommandeById($commandeId);

    if (!$commande) {
        $error = "Commande non trouvée.";
        error_log("Erreur: Commande non trouvée pour l'ID $commandeId");
    } else {
        $produits = $commandeModel->getProduitsForCommande($commandeId);
        $produitsNonCommandes = $commandeModel->getProduitsNotInCommande($commandeId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newStatus = $_POST['statut'] ?? $commande['statut'];
            $newDate = !empty($_POST['date_commande']) ? $_POST['date_commande'] : $commande['date_commande'];
            $updatedQuantities = isset($_POST['quantites']) ? $_POST['quantites'] : [];

            // Validation des données
            if (!in_array($newStatus, $statuts)) {
                $error = "Statut invalide.";
            } elseif (!strtotime($newDate)) {
                $error = "Date invalide.";
            } else {
                foreach ($updatedQuantities as $produitId => $quantite) {
                    if (!is_numeric($quantite) || $quantite < 0) {
                        $error = "Quantité invalide pour le produit ID: $produitId";
                        break;
                    }
                }
            }

            if (empty($error)) {
                // Mise à jour des produits de la commande
                if ($commandeModel->updateCommandeProduits($commandeId, $updatedQuantities)) {
                    $newTotal = $commandeModel->calculateCommandeTotal($commandeId);
                    if ($commandeModel->updateCommande($commandeId, $newStatus, $newTotal, $newDate)) {
                        $success = "Commande mise à jour avec succès.";
                        $_SESSION['success_message'] = $success;
                    } else {
                        $error = "Erreur lors de la mise à jour de la commande.";
                    }
                } else {
                    $error = "Erreur lors de la mise à jour des produits de la commande.";
                }
            }

            // Rafraîchir les données après la mise à jour
            $commande = $commandeModel->getCommandeById($commandeId);
            $produits = $commandeModel->getProduitsForCommande($commandeId);
            $produitsNonCommandes = $commandeModel->getProduitsNotInCommande($commandeId);
        }
    }
} else {
    $error = "ID de commande non spécifié.";
    error_log("Erreur: ID de commande non spécifié");
}

// Réponse pour les requêtes AJAX
if ($isAjax) {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode([
            'commande' => $commande,
            'produits' => $produits,
            'produitsNonCommandes' => $produitsNonCommandes
        ]);
    } else {
        echo json_encode([
            'success' => empty($error),
            'message' => $success,
            'error' => $error
        ]);
    }
    exit;
}

error_log("Fin du traitement. Réponse: " . ($success ? 'Succès' : 'Erreur') . " - Message: " . ($success ? $success : $error));

// Si ce n'est pas une requête AJAX, rediriger vers la page d'édition avec un message de succès
if (!empty($success) && !$isAjax) {
    $_SESSION['success_message'] = $success;
    header("Location: /Pharmacie_S/Views/commande/edit_commande.php?id=$commandeId");
    exit;
}