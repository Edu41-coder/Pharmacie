<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';
$clients = [];

$clientModel = new Client();

// Récupérer tous les clients
$clients = $clientModel->getAllClients();

// Récupérer l'ID du client à supprimer depuis l'URL
$clientIdASupprimer = isset($_GET['id']) ? $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $clientId = $_POST['client_id'];
    if ($clientModel->deleteClient($clientId)) {
        $success = "Client supprimé avec succès.";
        // Mettre à jour la liste des clients après la suppression
        $clients = $clientModel->getAllClients();
        $clientIdASupprimer = null; // Réinitialiser l'ID à supprimer
    } else {
        $error = "Erreur lors de la suppression du client.";
    }
}