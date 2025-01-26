<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();

$userModel = new User();
$users = $userModel->getAllUsers();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        error_log("Échec de la vérification du token CSRF dans modify_user.php");
        $_SESSION['error'] = "Erreur de sécurité. Veuillez réessayer.";
        header('Location: /Pharmacie_S/Views/users/modify_user.php');
        exit();
    }

    error_log("Vérification du token CSRF réussie dans modify_user.php");

    $userId = $_POST['user_id'];
    $email = $_POST['email'];

    // Vérifier si l'email existe déjà pour un autre utilisateur
    $existingUser = $userModel->getUserByEmail($email);
    if ($existingUser && $existingUser['user_id'] != $userId) {
        $_SESSION['error'] = "L'email saisi existe déjà pour un autre utilisateur.";
        header('Location: /Pharmacie_S/Views/users/modify_user.php?user_id=' . $userId);
        exit();
    }

    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $email,
        'role_id' => $_POST['role_id']
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
    }

    if ($userModel->updateUser($userId, $data)) {
        $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
        error_log("Utilisateur modifié avec succès : ID " . $userId);
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur.";
        error_log("Erreur lors de la modification de l'utilisateur : ID " . $userId);
    }

    // Rediriger vers la page de vue
    header('Location: /Pharmacie_S/Views/users/modify_user.php?user_id=' . $userId);
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

// Récupérer les rôles des utilisateurs
foreach ($users as &$user) {
    $user['role'] = $userModel->getUserRole($user['user_id']);
}
unset($user); // Détruire la référence pour éviter les effets de bord

// Récupérer les informations de l'utilisateur sélectionné
$selectedUser = null;
if (isset($_GET['user_id'])) {
    $selectedUser = $userModel->getUserById($_GET['user_id']);
    if ($selectedUser) {
        $selectedUser['role'] = $userModel->getUserRole($selectedUser['user_id']);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header('Location: /Pharmacie_S/Views/users/modify_user.php');
        exit();
    }
}

// Régénérer le token CSRF après le traitement
if (isset($_SESSION['success'])) {
    // Régénérer le token CSRF seulement après un traitement réussi
    $auth->regenerateCsrfToken();
    error_log("CSRF Token régénéré après traitement réussi dans modify_user.php");
}
?>