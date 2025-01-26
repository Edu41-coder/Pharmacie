<?php
$pageTitle = "Voir facture";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Config/Database_Mongo.php';

$vente_id = $_GET['vente_id'] ?? '';

if (!$vente_id) {
    echo "ID de vente manquant.";
    exit;
}

$mongoDb = Database_Mongo::getInstance()->getBdd();
$factureModel = new FactureModel($mongoDb);

$facture = $factureModel->getFactureByVenteId($vente_id);

if (!$facture) {
    echo "Facture non trouvée.";
    exit;
}

// Afficher les détails de la facture ici
echo "<h1>Facture pour la vente #{$facture['vente']['vente_id']}</h1>";
echo "<pre>";
print_r($facture);
echo "</pre>";

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php';
?>