<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$inventaireModel = new Inventaire();

// Configuration de la pagination
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Configuration du tri
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'produit_id';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Valider les colonnes de tri autorisées
$allowedColumns = [
    'produit_id',
    'nom',
    'stock',
    'alerte',
    'declencher_alerte'
];

if (!in_array($sortColumn, $allowedColumns)) {
    $sortColumn = 'produit_id';
}

// Récupérer l'ID de recherche
$searchId = isset($_GET['search']) ? (int)$_GET['search'] : null;

// Récupération des données
try {
    // Récupérer tous les produits pour le select2 (recherche)
    $allInventaire = $inventaireModel->getAllInventaire();
    
    // Récupérer le nombre total de produits (toujours nécessaire pour la pagination)
    $totalItems = $inventaireModel->getTotalInventaire();
    
    if ($searchId) {
        // Si un produit est recherché, récupérer uniquement ce produit
        $produitRecherche = $inventaireModel->getInventaireById($searchId);
        if ($produitRecherche) {
            $inventaire = [$produitRecherche];
        } else {
            $inventaire = [];
            $message = "Produit non trouvé.";
        }
    } else {
        // Sinon, récupérer les produits paginés normalement
        $inventaire = $inventaireModel->getInventairePaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection);
    }
    
    // Calculer le nombre total de pages
    $totalPages = ceil($totalItems / $itemsPerPage);
    
} catch (Exception $e) {
    $error = "Une erreur est survenue lors de la récupération de l'inventaire.";
    $inventaire = [];
    $totalItems = 0;
    $totalPages = 1;
}

// Récupérer les informations sur la dernière modification
$lastChange = $inventaireModel->getLastModification();

// Récupérer le message depuis l'URL si non défini
if (!isset($message)) {
    $message = isset($_GET['message']) ? $_GET['message'] : '';
}

// Formater le message de dernière modification
$lastChangeMessage = '';
if ($lastChange) {
    $action = $lastChange['action'] ?? 'modification';
    $lastChangeMessage = "Dernière $action de l'inventaire : " .
        date('d/m/Y à H:i:s', strtotime($lastChange['last_modified']));

    if ($action !== 'suppression totale' && isset($lastChange['produit_id'])) {
        $lastChangeMessage .= " (Produit : \"" . htmlspecialchars($lastChange['nom'] ?? 'N/A') . "\", " .
            "ID: " . htmlspecialchars($lastChange['produit_id']) . ", " .
            "Stock actuel : " . htmlspecialchars($lastChange['stock'] ?? 'N/A') . ")";
    }
}

// Définir un flag pour indiquer si nous sommes en mode recherche
$isSearchMode = !empty($searchId);