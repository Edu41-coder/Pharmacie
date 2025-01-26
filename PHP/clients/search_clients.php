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
        $clientModel = new Client();
        $searchTerm = trim($_POST['search']);
        
        // Validation du terme de recherche
        if (strlen($searchTerm) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        // Nettoyage et sécurisation du terme de recherche
        $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');
        
        // Recherche des clients
        $clients = $clientModel->searchClientsForSelect($searchTerm);
        
        // Envoi de la réponse
        header('Content-Type: application/json');
        echo json_encode($clients);
        exit;

    } catch (Exception $e) {
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