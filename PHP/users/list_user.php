<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();

$userModel = new User();
$users = $userModel->getAllUsers();

foreach ($users as &$user) {
    $user['role'] = $userModel->getUserRole($user['user_id']);
}
unset($user); // Détruire la référence pour éviter les effets de bord

?>