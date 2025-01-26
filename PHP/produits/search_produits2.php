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
        $searchTerm = trim($_POST['search']);
        
        // Validation du terme de recherche
        if (strlen($searchTerm) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        // Nettoyage et sécurisation du terme de recherche
        $searchTerm = filter_var($searchTerm, FILTER_SANITIZE_STRING);
        
        // Recherche des produits dans l'inventaire
        $produits = $inventaireModel->searchInventaireProducts($searchTerm);
        
        // Envoi de la réponse
        header('Content-Type: application/json');
        echo json_encode($produits);
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