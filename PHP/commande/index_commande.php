<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

// Initialiser le modèle Commande
$commandeModel = new Commande();

// Configuration de la pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 3;
$offset = ($page - 1) * $limit;

// Get sorting parameters from URL
$sortColumn = $_GET['sortColumn'] ?? 'date_commande';
$sortOrder = $_GET['sortOrder'] ?? 'DESC';

// Traitement des filtres et de la recherche
$statut = isset($_GET['statut']) ? $_GET['statut'] : '';
$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$dateFin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
// Récupération des commandes avec pagination, filtres et tri
if ($statut || $dateDebut || $dateFin || $recherche) {
    $commandes = $commandeModel->filtrerCommandesPaginated($statut, $dateDebut, $dateFin, $recherche, $offset, $limit, $sortColumn, $sortOrder);
    $totalCommandes = $commandeModel->getTotalCommandesFiltrees($statut, $dateDebut, $dateFin, $recherche);
} else {
    $commandes = $commandeModel->getAllCommandesPaginated($offset, $limit, $sortColumn, $sortOrder);
    $totalCommandes = $commandeModel->getTotalCommandes();
}

// Calcul du nombre total de pages
$totalPages = ceil($totalCommandes / $limit);

// Vérification de l'état et définition des messages
if (empty($commandes)) {
    $error = "Aucune commande trouvée.";
} else {
    $success = "Liste des commandes prête.";
}

// Récupérer les messages d'erreur ou de succès depuis les paramètres d'URL
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = urldecode($_GET['success']);
}
// Construction des paramètres d'URL pour la pagination
$queryParams = [];
if ($statut) $queryParams['statut'] = $statut;
if ($dateDebut) $queryParams['date_debut'] = $dateDebut;
if ($dateFin) $queryParams['date_fin'] = $dateFin;
if ($recherche) $queryParams['recherche'] = $recherche;
if ($sortColumn) $queryParams['sortColumn'] = $sortColumn;
if ($sortOrder) $queryParams['sortOrder'] = $sortOrder;

$queryString = http_build_query($queryParams);
