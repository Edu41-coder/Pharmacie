<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';

if (isset($_GET['id'])) {
    $venteId = $_GET['id'];
    $venteModel = new Vente();
    $vente = $venteModel->getVenteById($venteId);

    if (!$vente) {
        $_SESSION['error'] = "Vente non trouvée.";
        header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
        exit();
    }

    // Récupérer les détails du client
    $clientModel = new Client();
    $client = $clientModel->getClientById($vente['client_id']);

    // Récupérer les détails de l'utilisateur
    $userModel = new User();
    $user = $userModel->getUserById($vente['user_id']);

    // Vérifier si le client et l'utilisateur existent
    if (!$client) {
        $client = [
            'nom' => 'Client inconnu',
            'prenom' => ''
        ];
    }

    if (!$user) {
        $user = [
            'nom' => 'Utilisateur inconnu',
            'prenom' => ''
        ];
    }

} else {
    $_SESSION['error'] = "ID de vente non spécifié.";
    header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
    exit();
}

// Récupérer les messages de session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Préparer les données pour l'affichage
$venteData = [
    'vente_id' => $vente['vente_id'],
    'client_id' => $vente['client_id'],
    'client_nom' => ($client['nom'] ?? 'Client inconnu') . ' ' . ($client['prenom'] ?? ''),
    'user_id' => $vente['user_id'],
    'user_nom' => ($user['nom'] ?? 'Utilisateur inconnu') . ' ' . ($user['prenom'] ?? ''),
    'date' => $vente['date'],
    'montant' => $vente['montant'],
    'montant_regle' => $vente['montant_regle'],
    'a_rembourser' => $vente['a_rembourser'],
    'commentaire' => $vente['commentaire']
];

// Ces données seront disponibles dans le fichier de vue