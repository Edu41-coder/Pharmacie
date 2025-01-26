<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Validation des paramètres
    $productId = filter_input(INPUT_GET, 'productId', FILTER_VALIDATE_INT);
    $sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?: 'produit_id';
    $direction = filter_input(INPUT_GET, 'direction', FILTER_SANITIZE_STRING) ?: 'asc';
    $itemsPerPage = 5;

    error_log("Recherche page pour - ID: $productId, Tri: $sort, Direction: $direction");

    if ($productId === false || $productId <= 0) {
        throw new Exception('ID de produit invalide');
    }

    $inventaireModel = new Inventaire();
    $position = $inventaireModel->findProductPosition($productId, $sort, $direction);
    
    error_log("Position retournée: " . ($position !== false ? $position : 'non trouvé'));

    if ($position === false) {
        throw new Exception('Produit non trouvé');
    }

    $page = ceil(($position + 1) / $itemsPerPage);
    
    error_log("Page calculée: $page");

    echo json_encode([
        'success' => true,
        'page' => $page,
        'debug' => [
            'position' => $position,
            'itemsPerPage' => $itemsPerPage,
            'calculatedPage' => $page
        ]
    ]);

} catch (Exception $e) {
    error_log("Erreur dans find_product_page.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}