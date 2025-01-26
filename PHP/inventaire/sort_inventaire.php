<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

try {
    // Récupérer et valider les paramètres
    $sort = isset($_GET['sort']) && !empty($_GET['sort']) ? $_GET['sort'] : 'produit_id';
    $direction = isset($_GET['direction']) && in_array(strtolower($_GET['direction']), ['asc', 'desc']) ? 
                 strtolower($_GET['direction']) : 'asc';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $itemsPerPage = 5; // Changé de 13 à 5 pour correspondre à index_inventaire.php

    // Valider la colonne de tri
    $allowedColumns = ['produit_id', 'nom', 'stock', 'alerte', 'declencher_alerte'];
    if (!in_array($sort, $allowedColumns)) {
        $sort = 'produit_id';
    }

    // Calculer l'offset
    $offset = ($page - 1) * $itemsPerPage;

    // Instancier la classe Inventaire
    $inventaireModel = new Inventaire();
    
    // Récupérer le nombre total d'éléments
    $totalItems = $inventaireModel->getTotalInventaire();
    
    // Calculer le nombre total de pages
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Vérifier que la page demandée n'excède pas le total
    if ($page > $totalPages) {
        $page = max(1, $totalPages);
        $offset = ($page - 1) * $itemsPerPage;
    }
    
    // Récupérer les données triées et paginées
    $inventaire = $inventaireModel->getInventairePaginesEtTries($offset, $itemsPerPage, $sort, $direction);
    
    // Préparer la réponse
    $response = [
        'data' => $inventaire,
        'pagination' => [
            'currentPage' => (int)$page,
            'itemsPerPage' => (int)$itemsPerPage,
            'totalItems' => (int)$totalItems,
            'totalPages' => (int)$totalPages
        ],
        'sorting' => [
            'column' => $sort,
            'direction' => $direction
        ]
    ];

    // Envoyer la réponse
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erreur dans sort_inventaire.php: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Une erreur est survenue lors du tri de l\'inventaire',
        'details' => $e->getMessage()
    ]);
}