<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /Pharmacie_S/login.php');
    exit();
}

// Initialisation des modèles
$userModel = new User();
$produitModel = new Produit();
$role = $userModel->getUserRole($_SESSION['user_id'])['role_id'];
$isAdmin = ($role == 1);

// Vérifier si c'est une requête de recherche AJAX
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $searchResults = $produitModel->searchProduits($searchTerm, $isAdmin);
    echo json_encode($searchResults);
    exit();
}

// Configuration de la pagination
$itemsPerPage = 30;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Configuration du tri
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'produit_id';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Valider les colonnes de tri autorisées
$allowedColumns = [
    'produit_id',
    'nom',
    'prix_vente_ht',
    'prix_vente_ttc',
    'prescription',
    'taux_remboursement',
    'alerte',
    'declencher_alerte',
    'is_deleted'
];

if (!in_array($sortColumn, $allowedColumns)) {
    $sortColumn = 'produit_id';
}

// Récupération des données
try {
    // Récupérer le nombre total de produits
    $totalItems = $isAdmin ? $produitModel->getTotalProduits(true) : $produitModel->getTotalProduits();
    
    // Récupérer les produits paginés et triés
    $produits = $produitModel->getProduitsPaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection, $isAdmin);
    
    // Récupérer tous les produits pour la recherche
    $allProduits = $produitModel->getAllProduitsForSearch($isAdmin);
    
} catch (Exception $e) {
    $error = "Une erreur est survenue lors de la récupération des produits.";
}

// Calculer le nombre total de pages
$totalPages = ceil($totalItems / $itemsPerPage);

// Gestion des messages
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);
