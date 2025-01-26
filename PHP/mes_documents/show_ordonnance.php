<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /Pharmacie_S/Views/mes_documents/mes_ordonnances.php');
    exit();
}

$ordonnanceId = intval($_GET['id']);
$ordonnanceObj = new Ordonnance();

// Récupérer les détails de l'ordonnance
$ordonnance = $ordonnanceObj->getOrdonnanceById($ordonnanceId);

if (!$ordonnance) {
    // Rediriger si l'ordonnance n'existe pas
    header('Location: /Pharmacie_S/Views/mes_documents/mes_ordonnances.php');
    exit();
}