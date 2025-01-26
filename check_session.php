<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$auth = new Authentification();
$auth->checkSessionValidity(); // Vérifie la validité de la session

// Si la session est valide, aucune action n'est nécessaire
// Si elle n'est pas valide, la méthode `checkSessionValidity()` redirigera automatiquement