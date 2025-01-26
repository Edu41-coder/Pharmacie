<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Au

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();

$userModel = new User();
$users = $userModel->getAllUsers();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];

    if ($userModel->deleteUser($userId)) {
        $_SESSION['success'] = "Utilisateur supprimé avec succès.";
        header('Location: /Pharmacie_S/Views/users/delete_user.php');
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
        header('Location: /Pharmacie_S/Views/users/delete_user.php');
        exit();
    }
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
foreach ($users as &$user) {
    $user['role'] = $userModel->getUserRole($user['user_id']);
}
unset($user); // Détruire la référence pour éviter les effets de bord
?>