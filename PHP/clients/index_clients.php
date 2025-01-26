<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialisation des modèles
$clientModel = new Client();

// Configuration de la pagination
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Configuration du tri
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'client_id';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Valider les colonnes de tri autorisées
$allowedColumns = [
    'client_id',
    'nom',
    'prenom',
    'email',
    'telephone',
    'numero_carte_vitale',
    'cheques_impayes'
];

if (!in_array($sortColumn, $allowedColumns)) {
    $sortColumn = 'client_id';
}

// Récupération des données
try {
    // Récupérer le nombre total de clients
    $totalItems = $clientModel->getTotalClients();
    
    // Récupérer les clients paginés et triés
    $clients = $clientModel->getAllClientsPaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection);
} catch (Exception $e) {
    $error = "Une erreur est survenue lors de la récupération des clients.";
}

// Calculer le nombre total de pages
$totalPages = ceil($totalItems / $itemsPerPage);

// Gestion des messages
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);