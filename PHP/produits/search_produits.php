<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérification et traitement de la recherche
if (isset($_POST['search'])) {
    try {
        $inventaireModel = new Inventaire();
        $produitModel = new Produit();
        $searchTerm = trim($_POST['search']);
        
        // Validation du terme de recherche
        if (strlen($searchTerm) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        // Nettoyage et sécurisation du terme de recherche
        $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');
        
        // Récupération des produits déjà sélectionnés
        $selectedProducts = isset($_POST['selectedProducts']) ? 
            json_decode($_POST['selectedProducts'], true) : [];
        
        // Validation des produits sélectionnés
        if (!is_array($selectedProducts)) {
            $selectedProducts = [];
        }

        // Log des données reçues
        error_log("Recherche: " . $searchTerm);
        error_log("Produits sélectionnés: " . json_encode($selectedProducts));
        
        // Recherche des produits dans l'inventaire
        $produits = $inventaireModel->searchInventaireProducts($searchTerm);
        
        // Log des produits trouvés
        error_log("Produits trouvés: " . json_encode($produits));
        
        // Enrichir les résultats avec les détails des produits
        $produitsEnrichis = array_map(function($produit) use ($produitModel) {
            $details = $produitModel->getProduitById($produit['produit_id']);
            return array_merge($produit, [
                'nom' => $details['nom'],
                'prescription' => $details['prescription'],
                'prix_vente_ht' => $details['prix_vente_ht'],
                'taux_remboursement' => $details['taux_remboursement']
            ]);
        }, $produits);
        
        // Log des produits après enrichissement
        error_log("Produits enrichis: " . json_encode($produitsEnrichis));
        
        // Filtrer uniquement les produits déjà sélectionnés
        $produitsFiltres = array_filter($produitsEnrichis, function($produit) use ($selectedProducts) {
            return !in_array((string)$produit['produit_id'], $selectedProducts, true);
        });
        
        // Réindexer le tableau après le filtrage
        $produitsFiltres = array_values($produitsFiltres);
        
        // Log des produits après filtrage
        error_log("Produits filtrés: " . json_encode($produitsFiltres));
        
        // Envoi de la réponse
        header('Content-Type: application/json');
        echo json_encode($produitsFiltres);
        exit;

    } catch (Exception $e) {
        error_log("Erreur lors de la recherche de produits: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la recherche']);
        exit;
    }
}

// Si on arrive ici, c'est que la requête est invalide
header('HTTP/1.1 400 Bad Request');
header('Content-Type: application/json');
echo json_encode(['error' => 'Requête invalide']);
exit;