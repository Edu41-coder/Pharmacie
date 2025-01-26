<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
$criteria = isset($_GET['criteria']) ? $_GET['criteria'] : 'all';

// Log pour le débogage
error_log("Début de autocomplete_clients.php");
error_log("Terme de recherche reçu: " . $searchTerm);
error_log("Critère de recherche: " . $criteria);

try {
    if (empty($searchTerm)) {
        throw new Exception("Le terme de recherche est vide.");
    }

    $clientModel = new Client();
    $clients = $clientModel->searchClients($searchTerm, $criteria);

    $suggestions = [];
    foreach ($clients as $client) {
        $suggestions[] = [
            'label' => $client['nom'] . ' ' . $client['prenom'] . ' - ' . $client['email'] . ' - ' . $client['telephone'] . ' - ' . $client['numero_carte_vitale']. ' - ' .($client['cheques_impayes'] ? 'Oui' : 'Non'),
            'value' => $client['client_id']
        ];
    }

    // Log pour le débogage
    error_log("Nombre de suggestions: " . count($suggestions));
    error_log("Suggestions: " . json_encode($suggestions));

    // Set the content type to JSON
    header('Content-Type: application/json');
    echo json_encode($suggestions);

} catch (Exception $e) {
    // Log the exception message
    error_log("Erreur dans autocomplete_clients.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());

    // Return an error response
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Une erreur est survenue lors de la récupération des données.']);
}

error_log("Fin de autocomplete_clients.php");
?>