<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$auth = new Authentification();
$clientModel = new Client();

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
    error_log("Échec de la vérification du token CSRF dans edit_client.php");
    $_SESSION['error'] = "Erreur de sécurité. Veuillez réessayer.";
    header('Location: /Pharmacie_S/Views/clients/index_clients.php');
    exit();
}

error_log("Vérification du token CSRF réussie dans edit_client.php");

if (isset($_GET['id'])) {
    $clientId = $_GET['id'];
    $item = $clientModel->getClientById($clientId);

    if (!$item) {
        $_SESSION['error'] = "Client non trouvé.";
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $email = $_POST['email'];
            $telephone = !empty($_POST['telephone']) ? $_POST['telephone'] : null;
            $adresse = !empty($_POST['adresse']) ? $_POST['adresse'] : null;
            $commentaire = !empty($_POST['commentaire']) ? $_POST['commentaire'] : null;
            $numero_carte_vitale = !empty($_POST['numero_carte_vitale']) ? $_POST['numero_carte_vitale'] : null;

            if (empty($nom) || empty($prenom) || empty($email)) {
                $_SESSION['error'] = "Le nom, le prénom et l'email sont obligatoires.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "L'email fourni n'est pas valide.";
            } else {
                if ($clientModel->updateClient($clientId, [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'telephone' => $telephone,
                    'adresse' => $adresse,
                    'commentaire' => $commentaire,
                    'numero_carte_vitale' => $numero_carte_vitale
                ])) {
                    $_SESSION['success'] = "Client modifié avec succès.";
                    error_log("Client modifié avec succès : ID " . $clientId);
                } else {
                    $_SESSION['error'] = "Erreur lors de la modification du client.";
                    error_log("Erreur lors de la modification du client : ID " . $clientId);
                }
            }
        }
    }
} else {
    $_SESSION['error'] = "ID de client non spécifié.";
    error_log("Tentative de modification de client sans ID spécifié");
}

// Régénérer le token CSRF après le traitement
if (isset($_SESSION['success'])) {
    // Régénérer le token CSRF seulement après un traitement réussi
    $auth->regenerateCsrfToken();
    error_log("CSRF Token régénéré après traitement réussi dans edit_client.php");
}

// Rediriger vers la page de vue
header("Location: /Pharmacie_S/Views/clients/edit_client.php?id=" . $clientId);
exit();
