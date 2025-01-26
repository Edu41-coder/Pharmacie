<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
    echo "Le fichier .env a été chargé avec succès.<br>";
} catch (Exception $e) {
    echo "Erreur lors du chargement du fichier .env : " . $e->getMessage() . "<br>";
}

$dbHost = $_ENV['DB_HOST'] ?? null;
$dbUser = $_ENV['DB_USERNAME'] ?? null;
$dbPass = $_ENV['DB_PASSWORD'] ?? '';
$dbName = $_ENV['DB_DATABASE'] ?? null;

if (!$dbHost || !$dbUser || $dbPass === null || !$dbName) {
    echo "Les variables d'environnement ne sont pas chargées correctement.<br>";
    echo "DB_HOST: " . ($dbHost ? $dbHost : 'Non défini') . "<br>";
    echo "DB_USERNAME: " . ($dbUser ? $dbUser : 'Non défini') . "<br>";
    echo "DB_PASSWORD: " . ($dbPass !== null ? $dbPass : 'Non défini') . "<br>";
    echo "DB_DATABASE: " . ($dbName ? $dbName : 'Non défini') . "<br>";
} else {
    echo "Les variables d'environnement sont chargées correctement.<br>";
    echo "DB_HOST: $dbHost<br>";
    echo "DB_USERNAME: $dbUser<br>";
    echo "DB_PASSWORD: $dbPass<br>";
    echo "DB_DATABASE: $dbName<br>";
}
?>