<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer
$auth = new Authentification();
$user = $auth->checkAdminAuthentication();
?>