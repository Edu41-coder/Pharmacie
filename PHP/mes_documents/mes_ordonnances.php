<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$ordonnanceObj = new Ordonnance();
$venteObj = new Vente();

// Configuration de la pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Récupération des ordonnances avec pagination
$ordonnances = $ordonnanceObj->getAllOrdonnancesPaginated($offset, $limit);
$totalOrdonnances = $ordonnanceObj->getTotalOrdonnances();
$totalPages = ceil($totalOrdonnances / $limit);

// Messages d'erreur/succès si nécessaire
$error = '';
$success = '';

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>