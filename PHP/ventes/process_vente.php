<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/VenteProcessor.php';

try {
    $processor = new VenteProcessor();
    $processor->process();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: /Pharmacie_S/Views/ventes/create_vente.php');
    exit();
}