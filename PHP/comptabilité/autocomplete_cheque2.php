<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

// Log pour vérifier que le script est exécuté
error_log("Script autocomplete_cheques.php exécuté");

// Récupération des paramètres
$term = isset($_GET['term']) ? htmlspecialchars(strip_tags($_GET['term'])) : '';
$criteria = isset($_GET['criteria']) ? htmlspecialchars(strip_tags($_GET['criteria'])) : 'all';
$etat = isset($_GET['etat']) ? htmlspecialchars(strip_tags($_GET['etat'])) : '';
$includeDeletedVentes = isset($_GET['include_deleted']) && $_GET['include_deleted'] === '1';
$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : null;
$dateFin = isset($_GET['date_fin']) ? $_GET['date_fin'] : null;

error_log("Paramètres reçus: " . json_encode($_GET));

try {
    $db = new PDO("mysql:host=localhost;dbname=pharmacie", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!class_exists('Cheque')) {
        throw new Exception("Classe Cheque non trouvée.");
    }

    $chequeObj = new Cheque();
    $cheques = $chequeObj->searchCheques($term, $criteria, $etat, $dateDebut, $dateFin, $includeDeletedVentes);
    if (!empty($cheques)) {
        error_log("Premier chèque récupéré: " . json_encode($cheques[0], JSON_PRETTY_PRINT));
    }
    error_log("Nombre de chèques récupérés: " . count($cheques));
    error_log("Chèques: " . json_encode($cheques));

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Access-Control-Allow-Origin: *');

    // Log avant d'envoyer la réponse
    $jsonResponse = json_encode($cheques, JSON_UNESCAPED_UNICODE);
    error_log("Réponse JSON à renvoyer: " . $jsonResponse);
    echo $jsonResponse;
} catch (Exception $e) {
    error_log("Erreur dans autocomplete_cheques.php: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    $errorResponse = json_encode(['error' => 'Une erreur est survenue lors de la récupération des données.', 'details' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    error_log("Réponse d'erreur JSON à renvoyer: " . $errorResponse);
    echo $errorResponse;
}

error_log("Fin de autocomplete_cheques.php");
