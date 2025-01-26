<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

session_start();

$venteId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($venteId > 0) {
    $venteModel = new Vente();
    if ($venteModel->restoreVente($venteId)) {
        $_SESSION['success'] = "La vente a été restaurée avec succès.";
    } else {
        $_SESSION['error'] = "Échec de la restauration de la vente.";
    }
} else {
    $_SESSION['error'] = "ID de vente invalide.";
}

header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
exit();